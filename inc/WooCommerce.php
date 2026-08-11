<?php
/**
 * WooCommerce integration — strictly hook-driven, with Transients caching demo.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

namespace Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce.
 *
 * Instantiated by Bootstrap ONLY when class_exists('WooCommerce') is true.
 * All shop/loop/single-product presentation changes are made via
 * remove_action()/add_action()/add_filter() — no template overrides for those
 * views (cart.php / checkout.php are the only allowed exception; see /woocommerce).
 */
final class WooCommerce {

	/**
	 * Transient key for the cached category aggregation.
	 *
	 * @var string
	 */
	private const CATEGORY_CACHE_KEY = 'cosypaw_top_categories';

	/**
	 * Cache lifetime in seconds (12 hours).
	 *
	 * @var int
	 */
	private const CATEGORY_CACHE_TTL = 12 * HOUR_IN_SECONDS;

	/**
	 * Option key: motif id => WC product id map.
	 *
	 * @var string
	 */
	public const PRODUCT_MAP_OPTION = 'cosypaw_product_map';

	/**
	 * Option key: package id => WC product id map.
	 *
	 * @var string
	 */
	public const PACKAGE_MAP_OPTION = 'cosypaw_package_map';

	/**
	 * Order line-item meta key holding the raw motif ids of a bundle.
	 *
	 * @var string
	 */
	private const ORDER_MOTIFS_META = 'cosypaw_motifs';

	/**
	 * Theme text domain.
	 *
	 * @var string
	 */
	private string $text_domain;

	/**
	 * Catalog data source.
	 *
	 * @var Catalog
	 */
	private Catalog $catalog;

	/**
	 * Product seeder (admin tooling).
	 *
	 * @var ProductSeeder
	 */
	private ProductSeeder $seeder;

	/**
	 * Per-product name overrides (admin-editable EN/RU names).
	 *
	 * @var ProductNames
	 */
	private ProductNames $names;

	/**
	 * Cached list of our product IDs (motifs + packages).
	 *
	 * @var int[]|null
	 */
	private ?array $our_ids = null;

	/**
	 * Constructor — registers all WooCommerce hooks.
	 *
	 * @param string  $text_domain Theme text domain.
	 * @param Catalog $catalog     Catalog data source.
	 */
	public function __construct( string $text_domain, Catalog $catalog ) {
		$this->text_domain = $text_domain;
		$this->catalog     = $catalog;
		$this->seeder      = new ProductSeeder( $catalog );
		$this->names       = new ProductNames();

		$this->register_layout_hooks();
		$this->register_cache_invalidation();
		$this->register_commerce_hooks();
	}

	/**
	 * Register real-commerce hooks: catalog id injection, cart fragment, scripts.
	 *
	 * @return void
	 */
	private function register_commerce_hooks(): void {
		add_filter( 'cosypaw_catalog_products', array( $this, 'inject_product_ids' ) );
		add_filter( 'cosypaw_catalog_packages', array( $this, 'inject_package_ids' ) );

		// Live cart-count badge: WooCommerce swaps the matching selector on AJAX add.
		add_filter( 'woocommerce_add_to_cart_fragments', array( $this, 'cart_count_fragment' ) );

		// The landing front page needs WC's AJAX add-to-cart + fragments scripts.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_cart_scripts' ) );

		// Bundle builder: carry the chosen motifs as cart-item data, show them
		// in the cart/checkout, and persist them to the order.
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_motifs_cart_item_data' ), 10, 2 );
		add_filter( 'woocommerce_get_item_data', array( $this, 'display_motifs_cart_item_data' ), 10, 2 );
		add_filter( 'woocommerce_cart_item_thumbnail', array( $this, 'motifs_cart_item_thumbnail' ), 10, 2 );
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'save_motifs_order_item' ), 10, 3 );
		add_filter( 'woocommerce_order_item_display_meta_key', array( $this, 'display_motifs_order_meta_key' ), 10, 3 );
		add_filter( 'woocommerce_order_item_display_meta_value', array( $this, 'display_motifs_order_meta_value' ), 10, 3 );

		// Translate seeded product titles via gettext (their names are already
		// msgids in /languages). Scoped to our product/package IDs only.
		add_filter( 'the_title', array( $this, 'translate_product_title' ), 10, 2 );
		add_filter( 'woocommerce_cart_item_name', array( $this, 'translate_cart_item_name' ), 10, 2 );
		add_filter( 'woocommerce_order_item_name', array( $this, 'translate_order_item_name' ), 10, 2 );
	}

	/**
	 * Attach the bundle's chosen motifs to the cart item (from the AJAX add).
	 *
	 * Different motif sets stay separate cart lines (the data changes the cart
	 * item hash); identical sets stack as quantity.
	 *
	 * @param array<string,mixed> $cart_item_data Existing cart item data.
	 * @param int                 $product_id     Product being added.
	 * @return array<string,mixed>
	 */
	public function add_motifs_cart_item_data( array $cart_item_data, int $product_id ): array {
		unset( $product_id );

		if ( ! empty( $_REQUEST['cosypaw_motifs'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$raw = sanitize_text_field( wp_unslash( $_REQUEST['cosypaw_motifs'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$ids = $this->known_motif_ids( $raw );
			if ( $ids ) {
				$cart_item_data['cosypaw_motifs'] = implode( ',', $ids );
			}
		}

		return $cart_item_data;
	}

	/**
	 * Filter a raw comma list down to known motif ids (preserving order/dupes).
	 *
	 * @param string $raw Comma-separated motif ids.
	 * @return string[]
	 */
	private function known_motif_ids( string $raw ): array {
		$map = $this->catalog->motif_map();
		$ids = array();
		foreach ( explode( ',', $raw ) as $id ) {
			$id = trim( $id );
			if ( '' !== $id && isset( $map[ $id ] ) ) {
				$ids[] = $id;
			}
		}

		return $ids;
	}

	/**
	 * Resolve stored motif ids to translated display names.
	 *
	 * @param string $stored Comma-separated motif ids.
	 * @return string Comma-separated names.
	 */
	private function motif_names( string $stored ): string {
		$map   = $this->catalog->motif_map();
		$names = array();
		foreach ( explode( ',', $stored ) as $id ) {
			$id = trim( $id );
			if ( isset( $map[ $id ] ) ) {
				$names[] = $map[ $id ]['name'];
			}
		}

		return implode( ', ', $names );
	}

	/**
	 * Show the chosen motifs (by name) under the product in the cart/checkout.
	 *
	 * @param array<int,array<string,string>> $item_data Existing item data rows.
	 * @param array<string,mixed>             $cart_item Cart item.
	 * @return array<int,array<string,string>>
	 */
	public function display_motifs_cart_item_data( array $item_data, array $cart_item ): array {
		if ( ! empty( $cart_item['cosypaw_motifs'] ) ) {
			$item_data[] = array(
				'key'   => __( 'Motivi', 'cosypaw' ),
				'value' => $this->motif_names( (string) $cart_item['cosypaw_motifs'] ),
			);
		}

		return $item_data;
	}

	/**
	 * Replace the cart/mini-cart thumbnail of a bundle with a composite of the
	 * chosen motif images.
	 *
	 * @param string              $thumbnail Default thumbnail HTML.
	 * @param array<string,mixed> $cart_item Cart item.
	 * @return string
	 */
	public function motifs_cart_item_thumbnail( $thumbnail, $cart_item ): string {
		if ( empty( $cart_item['cosypaw_motifs'] ) ) {
			return (string) $thumbnail;
		}

		$map  = $this->catalog->motif_map();
		$tiles = '';
		foreach ( explode( ',', (string) $cart_item['cosypaw_motifs'] ) as $id ) {
			$id = trim( $id );
			if ( ! isset( $map[ $id ] ) ) {
				continue;
			}
			$tiles .= sprintf(
				'<span class="cosypaw-bundle-thumb__tile" role="img" aria-label="%1$s" style="background-image:url(%2$s)"></span>',
				esc_attr( $map[ $id ]['name'] ),
				esc_url( $map[ $id ]['image_sm'] )
			);
		}

		if ( '' === $tiles ) {
			return (string) $thumbnail;
		}

		return '<span class="cosypaw-bundle-thumb" aria-hidden="false">' . $tiles . '</span>';
	}

	/**
	 * Persist the chosen motifs onto the order line item — as raw motif ids.
	 *
	 * Storing the display names instead would freeze the buyer's language into
	 * the order: a Russian checkout would leave "Мотивы: Жираф, Панда" on the
	 * line item, unreadable to the shop owner and unchanged by any later locale.
	 * Ids are language-neutral, so the label and the names are resolved at render
	 * time by display_motifs_order_meta_key()/_value() — the order then reads in
	 * whatever language the viewer is using, admin included.
	 *
	 * @param object $item   Order line item (\WC_Order_Item_Product at runtime).
	 * @param string $key    Cart item key.
	 * @param array  $values Cart item values.
	 * @return void
	 */
	public function save_motifs_order_item( $item, $key, $values ): void {
		unset( $key );
		if ( ! empty( $values['cosypaw_motifs'] ) ) {
			$item->add_meta_data( self::ORDER_MOTIFS_META, (string) $values['cosypaw_motifs'], true );
		}
	}

	/**
	 * Label the raw motif meta row in the current language.
	 *
	 * @param string $display_key Default display key.
	 * @param object $meta        Meta row (\WC_Meta_Data at runtime).
	 * @param object $item        Order item (\WC_Order_Item at runtime).
	 * @return string
	 */
	public function display_motifs_order_meta_key( $display_key, $meta, $item ): string {
		unset( $item );

		if ( $meta && self::ORDER_MOTIFS_META === $meta->key ) {
			return __( 'Motivi', 'cosypaw' );
		}

		return (string) $display_key;
	}

	/**
	 * Resolve the raw motif ids to translated names for display.
	 *
	 * @param string $display_value Default display value.
	 * @param object $meta          Meta row (\WC_Meta_Data at runtime).
	 * @param object $item          Order item (\WC_Order_Item at runtime).
	 * @return string
	 */
	public function display_motifs_order_meta_value( $display_value, $meta, $item ): string {
		unset( $item );

		if ( $meta && self::ORDER_MOTIFS_META === $meta->key ) {
			return esc_html( $this->motif_names( (string) $meta->value ) );
		}

		return (string) $display_value;
	}

	/**
	 * IDs of every product we created (motifs + packages). Cached per request.
	 *
	 * @return int[]
	 */
	private function our_product_ids(): array {
		if ( null === $this->our_ids ) {
			$ids = array_merge(
				array_values( (array) get_option( self::PRODUCT_MAP_OPTION, array() ) ),
				array_values( (array) get_option( self::PACKAGE_MAP_OPTION, array() ) )
			);
			$this->our_ids = array_map( 'intval', $ids );
		}

		return $this->our_ids;
	}

	/**
	 * WooCommerce page IDs (cart, checkout, shop, my account). Their titles are
	 * DB content created in English at install, so we translate them via gettext.
	 *
	 * @return int[]
	 */
	private function wc_page_ids(): array {
		$ids = array();
		foreach ( array( 'woocommerce_cart_page_id', 'woocommerce_checkout_page_id', 'woocommerce_shop_page_id', 'woocommerce_myaccount_page_id' ) as $option ) {
			$id = (int) get_option( $option );
			if ( $id ) {
				$ids[] = $id;
			}
		}

		return $ids;
	}

	/**
	 * Translate the title of a seeded product OR a WooCommerce page through the
	 * theme text domain (titles double as msgids in /languages).
	 *
	 * @param string $title   Post title.
	 * @param int    $post_id Post ID (0 in some contexts).
	 * @return string
	 */
	public function translate_product_title( $title, $post_id = 0 ): string {
		$post_id = (int) $post_id;

		if ( ! $post_id ) {
			return (string) $title;
		}

		if ( 'product' === get_post_type( $post_id ) ) {
			return $this->product_name( $post_id, (string) $title );
		}

		if ( in_array( $post_id, $this->wc_page_ids(), true ) ) {
			// translators: dynamic page title, already present as a msgid.
			return __( $title, 'cosypaw' ); // phpcs:ignore WordPress.WP.I18n
		}

		return (string) $title;
	}

	/**
	 * A product's name in the current language.
	 *
	 * A manual override from the product editor wins for any product. Gettext is
	 * consulted only for the products we seeded, whose titles are known msgids —
	 * running an arbitrary shop-entered title through __() risks colliding with
	 * an unrelated theme string that happens to match.
	 *
	 * @param int    $product_id Product ID.
	 * @param string $source     Source-language name (post_title).
	 * @return string
	 */
	private function product_name( int $product_id, string $source ): string {
		$override = $this->names->get( $product_id );

		if ( '' !== $override ) {
			return $override;
		}

		if ( in_array( $product_id, $this->our_product_ids(), true ) ) {
			// translators: dynamic product title, already present as a msgid.
			return __( $source, 'cosypaw' ); // phpcs:ignore WordPress.WP.I18n
		}

		return $source;
	}

	/**
	 * Translate the product name shown in the cart (HTML anchor preserved).
	 *
	 * @param string $name      Cart item name HTML.
	 * @param array  $cart_item Cart item.
	 * @return string
	 */
	public function translate_cart_item_name( $name, $cart_item ): string {
		$product = isset( $cart_item['data'] ) ? $cart_item['data'] : null;
		if ( $product ) {
			$original = (string) $product->get_name();
			$name     = str_replace( $original, $this->product_name( (int) $product->get_id(), $original ), (string) $name );
		}

		return (string) $name;
	}

	/**
	 * Translate the product name shown in order line items.
	 *
	 * @param string $name Item name.
	 * @param object $item Order item (\WC_Order_Item at runtime).
	 * @return string
	 */
	public function translate_order_item_name( $name, $item ): string {
		$pid = is_callable( array( $item, 'get_product_id' ) ) ? (int) $item->get_product_id() : 0;
		if ( $pid ) {
			return $this->product_name( $pid, (string) $name );
		}

		return (string) $name;
	}

	/**
	 * Inject mapped WC product ids + add-to-cart URLs into the motif catalog.
	 *
	 * @param array<int,array<string,mixed>> $products Catalog products.
	 * @return array<int,array<string,mixed>>
	 */
	public function inject_product_ids( array $products ): array {
		return $this->inject_ids( $products, (array) get_option( self::PRODUCT_MAP_OPTION, array() ) );
	}

	/**
	 * Inject mapped WC product ids + add-to-cart URLs into the package list.
	 *
	 * @param array<int,array<string,mixed>> $packages Catalog packages.
	 * @return array<int,array<string,mixed>>
	 */
	public function inject_package_ids( array $packages ): array {
		return $this->inject_ids( $packages, (array) get_option( self::PACKAGE_MAP_OPTION, array() ) );
	}

	/**
	 * Shared id-injection: adds product_id + add_to_cart_url to mapped rows.
	 *
	 * @param array<int,array<string,mixed>> $rows Catalog rows (each has an 'id').
	 * @param array<string,int>              $map  id => product id map.
	 * @return array<int,array<string,mixed>>
	 */
	private function inject_ids( array $rows, array $map ): array {
		foreach ( $rows as &$row ) {
			$key = isset( $row['id'] ) ? (string) $row['id'] : '';
			if ( '' !== $key && isset( $map[ $key ] ) ) {
				$pid                    = (int) $map[ $key ];
				$row['product_id']      = $pid;
				$row['add_to_cart_url'] = esc_url_raw( add_query_arg( 'add-to-cart', $pid ) );

				$product = wc_get_product( $pid );

				if ( ! $product instanceof \WC_Product ) {
					$row['available'] = false;
					continue;
				}

				// Trashing or unpublishing a product is how the shop retires a
				// motif, but the Catalog is a static list and keeps rendering its
				// tile — with a working add-to-cart URL for something that can no
				// longer be bought. Flag it so the front page can leave it out.
				// The row itself stays in the catalog: motif_map() still has to
				// resolve the name for orders placed while it was on sale.
				$row['available'] = 'publish' === get_post_status( $pid ) && $product->is_purchasable();

				// Once a product exists, WooCommerce owns the name and the price.
				// The Catalog values are only the seed: editing either in wp-admin
				// has to move the landing page too, and a price shown there that
				// differs from the one WooCommerce charges at checkout is worse
				// than no price at all. product_name() keeps the manual EN/RU
				// override and the .po translation in front of the stored title.
				$row['name'] = $this->product_name( $pid, (string) $product->get_name() );

				$price = $product->get_price();
				if ( '' !== $price && is_numeric( $price ) ) {
					$row['price'] = (int) round( (float) $price );

					// Packages advertise a per-towel price derived from the total.
					if ( ! empty( $row['qty'] ) ) {
						$row['per'] = (int) round( $row['price'] / (int) $row['qty'] );
					}
				}
			}
		}
		unset( $row );

		return $rows;
	}

	/**
	 * Replace the nav cart-count badge fragment on AJAX add-to-cart.
	 *
	 * @param array<string,string> $fragments Cart fragments (selector => HTML).
	 * @return array<string,string>
	 */
	public function cart_count_fragment( array $fragments ): array {
		$count = ( function_exists( 'WC' ) && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;

		ob_start();
		?>
		<span class="cart-btn__badge" data-cart-count<?php echo $count < 1 ? ' hidden' : ''; ?>><?php echo esc_html( (string) $count ); ?></span>
		<?php
		$fragments['span.cart-btn__badge'] = (string) ob_get_clean();

		return $fragments;
	}

	/**
	 * Ensure WC AJAX add-to-cart + cart-fragments scripts load on the front page.
	 *
	 * @return void
	 */
	public function enqueue_cart_scripts(): void {
		if ( is_front_page() ) {
			wp_enqueue_script( 'wc-add-to-cart' );
			wp_enqueue_script( 'wc-cart-fragments' );
		}
	}

	/**
	 * Register hook-based layout modifications.
	 *
	 * @return void
	 */
	private function register_layout_hooks(): void {
		// Example: restructure the shop loop wrappers via hooks (not templates).
		remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
		remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
		add_action( 'woocommerce_before_main_content', array( $this, 'open_content_wrapper' ), 10 );
		add_action( 'woocommerce_after_main_content', array( $this, 'close_content_wrapper' ), 10 );

		// Add a custom CSS class to every loop "add to cart" button.
		add_filter( 'woocommerce_loop_add_to_cart_args', array( $this, 'loop_add_to_cart_args' ), 10, 2 );

		// Tune products-per-row / per-page for the grid layout.
		add_filter( 'loop_shop_columns', static fn(): int => 3 );
		add_filter( 'loop_shop_per_page', static fn(): int => 12 );

		// Related products: 3 across, 3 total — matches the motif grid rhythm.
		add_filter( 'woocommerce_output_related_products_args', array( $this, 'related_products_args' ) );
	}

	/**
	 * Constrain the single-product "related products" block to a tidy 3-up row.
	 *
	 * @param array<string,mixed> $args Related products args.
	 * @return array<string,mixed>
	 */
	public function related_products_args( array $args ): array {
		$args['posts_per_page'] = 3;
		$args['columns']        = 3;

		return $args;
	}

	/**
	 * Open the main content wrapper (replaces default WC wrapper).
	 *
	 * @return void
	 */
	public function open_content_wrapper(): void {
		echo '<main id="primary" class="site-main shop-main" tabindex="-1">';
	}

	/**
	 * Close the main content wrapper.
	 *
	 * @return void
	 */
	public function close_content_wrapper(): void {
		echo '</main>';
	}

	/**
	 * Add a custom CSS class to the loop add-to-cart button.
	 *
	 * Unit-tested in tests/WooCommerceTest.php.
	 *
	 * @param array<string,mixed> $args    Button args.
	 * @param object              $product The product (\WC_Product at runtime).
	 * @return array<string,mixed>
	 */
	public function loop_add_to_cart_args( array $args, $product ): array {
		$class          = isset( $args['class'] ) ? (string) $args['class'] : '';
		$args['class']  = trim( $class . ' cosypaw-add-to-cart' );

		return $args;
	}

	/**
	 * Aggregate top product categories with Transients caching.
	 *
	 * Demonstrates the set/get half of the cache pattern. Expensive queries are
	 * run once and cached for self::CATEGORY_CACHE_TTL.
	 *
	 * @return array<int,array{id:int,name:string,count:int}>
	 */
	public function get_top_categories(): array {
		$cached = get_transient( self::CATEGORY_CACHE_KEY );
		if ( false !== $cached && is_array( $cached ) ) {
			return $cached;
		}

		$terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'orderby'    => 'count',
				'order'      => 'DESC',
				'number'     => 5,
				'hide_empty' => true,
			)
		);

		$result = array();
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$result[] = array(
					'id'    => (int) $term->term_id,
					'name'  => (string) $term->name,
					'count' => (int) $term->count,
				);
			}
		}

		set_transient( self::CATEGORY_CACHE_KEY, $result, self::CATEGORY_CACHE_TTL );

		return $result;
	}

	/**
	 * Register cache invalidation hooks.
	 *
	 * @return void
	 */
	private function register_cache_invalidation(): void {
		// Invalidate when a product changes (WC) or any product post is saved (core).
		add_action( 'woocommerce_update_product', array( $this, 'flush_category_cache' ) );
		add_action( 'save_post_product', array( $this, 'flush_category_cache' ) );
	}

	/**
	 * Delete the cached category aggregation.
	 *
	 * @return void
	 */
	public function flush_category_cache(): void {
		delete_transient( self::CATEGORY_CACHE_KEY );
	}
}
