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
	 * Payment/shipping configuration and its runtime label filters.
	 *
	 * @var CheckoutSetup
	 */
	private CheckoutSetup $checkout;

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
		$this->checkout    = new CheckoutSetup();

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

		// Per-locale short description: the Serbian one is the product excerpt,
		// the other two are overrides typed on the product screen.
		add_filter( 'woocommerce_short_description', array( $this, 'translate_short_description' ) );
	}

	/**
	 * Swap in the per-locale short description on a seeded product.
	 *
	 * Runs on the excerpt of whatever product is being rendered, so it has to
	 * find the product itself: the filter carries no id.
	 *
	 * @param string $description Short description (the product excerpt).
	 * @return string
	 */
	public function translate_short_description( $description ): string {
		$product_id = 0;

		if ( function_exists( 'wc_get_product' ) ) {
			$product = wc_get_product();
			if ( $product instanceof \WC_Product ) {
				$product_id = (int) $product->get_id();
			}
		}

		if ( ! $product_id || ! in_array( $product_id, $this->our_product_ids(), true ) ) {
			return (string) $description;
		}

		$override = $this->names->get_description( $product_id );

		return '' !== $override ? $override : (string) $description;
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
				'key'   => __( 'Peškirići', 'cosypaw' ),
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
			return __( 'Peškirići', 'cosypaw' );
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
	 * Inject mapped WC product ids, add-to-cart URLs and permalinks into the motif catalog.
	 *
	 * @param array<int,array<string,mixed>> $products Catalog products.
	 * @return array<int,array<string,mixed>>
	 */
	public function inject_product_ids( array $products ): array {
		return $this->inject_ids( $products, (array) get_option( self::PRODUCT_MAP_OPTION, array() ) );
	}

	/**
	 * Inject mapped WC product ids, add-to-cart URLs and permalinks into the package list.
	 *
	 * @param array<int,array<string,mixed>> $packages Catalog packages.
	 * @return array<int,array<string,mixed>>
	 */
	public function inject_package_ids( array $packages ): array {
		$packages = $this->inject_ids( $packages, (array) get_option( self::PACKAGE_MAP_OPTION, array() ) );

		return $this->derive_package_savings( $packages );
	}

	/**
	 * Recompute the crossed-out price and the saving badge from the live prices.
	 *
	 * inject_ids() already replaces a package's price with what WooCommerce
	 * actually charges, but the comparison alongside it — "1.580 RSD" struck
	 * through, "Ušteda 380 RSD" on the ribbon — stayed at the Catalog seed. Once
	 * the shop repriced, the card advertised a discount off a price nobody has
	 * charged in months, next to the real one. Both numbers are derived from the
	 * single-towel package here so the card can only ever compare the live
	 * prices against each other.
	 *
	 * Skipped whole if the single-towel package is unmapped: the Catalog seed is
	 * at least internally consistent, which a half-derived card would not be.
	 *
	 * @param array<int,array<string,mixed>> $packages Packages, ids already injected.
	 * @return array<int,array<string,mixed>>
	 */
	private function derive_package_savings( array $packages ): array {
		$unit = 0;
		foreach ( $packages as $row ) {
			if ( 1 === (int) ( $row['qty'] ?? 0 ) && ! empty( $row['product_id'] ) && ! empty( $row['price'] ) ) {
				$unit = (int) $row['price'];
				break;
			}
		}

		if ( $unit < 1 ) {
			return $packages;
		}

		foreach ( $packages as &$row ) {
			$qty = (int) ( $row['qty'] ?? 0 );
			if ( $qty < 2 || empty( $row['product_id'] ) ) {
				continue;
			}

			$full    = $unit * $qty;
			$price   = (int) $row['price'];
			$saving  = $full - $price;
			$is_deal = $saving > 0;

			// A bundle priced at or above the loose towels is not a saving, and
			// a struck-through price under the one being charged reads as a
			// price rise. Drop both rather than print a negative discount.
			$row['old'] = $is_deal ? $full : null;

			// Same reasoning as the struck price: the "2+1 GRATIS" line has to
			// come off what the shop charges today, or it keeps promising a
			// free towel the moment someone nudges the bundle price up.
			$row['gratis'] = Catalog::gratis_count( $qty, $price, $unit );

			if ( ! empty( $row['badge_saving'] ) ) {
				/* translators: %s: formatted amount saved, e.g. "490 RSD". */
				$row['badge'] = $is_deal ? sprintf( __( 'Ušteda %s', 'cosypaw' ), Catalog::format_price( $saving ) ) : null;
			}
		}
		unset( $row );

		return $packages;
	}

	/**
	 * Shared id-injection: adds product_id, add_to_cart_url and permalink to rows.
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

				$permalink        = get_permalink( $pid );
				$row['permalink'] = is_string( $permalink ) ? $permalink : '';

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
		<?php
		// data-cart-owner has to survive the replacement: WooCommerce swaps the
		// whole element, and a fragment without the marker would hand the badge
		// back to the demo cart on the next page load.
		?>
		<span class="cart-btn__badge" data-cart-count data-cart-owner="wc"<?php echo $count < 1 ? ' hidden' : ''; ?>><?php echo esc_html( (string) $count ); ?></span>
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

		// Single product: the bundle route sits right under WooCommerce's own
		// add-to-cart (priority 30), and the shared facts under the meta (40).
		// The just-added panel goes above the title, where a customer who has
		// only pressed one button is still looking.
		add_action( 'woocommerce_single_product_summary', array( $this, 'added_panel' ), 4 );
		add_action( 'woocommerce_single_product_summary', array( $this, 'bundle_cta' ), 31 );
		add_action( 'woocommerce_single_product_summary', array( $this, 'review_proof' ), 33 );
		add_action( 'woocommerce_single_product_summary', array( $this, 'product_specs' ), 45 );

		// Late, so it wraps the reviews tab whatever else has touched it.
		add_filter( 'woocommerce_product_tabs', array( $this, 'reviews_tab_intro' ), 98 );

		// Below the product, in the landing's own order: the gift banner after
		// the tabs (10), the FAQ after the related products (20). Both answer
		// what a visitor asks next, and both were only on the front page.
		add_action( 'woocommerce_after_single_product_summary', array( $this, 'gift_banner' ), 12 );
		add_action( 'woocommerce_after_single_product_summary', array( $this, 'faq_section' ), 25 );

		// Come back to the product page carrying a flag the panel above reads.
		add_filter( 'woocommerce_add_to_cart_redirect', array( $this, 'add_to_cart_redirect' ) );
	}

	/**
	 * The motif id a seeded product was created from, or '' for anything else.
	 *
	 * @param int $product_id Product ID.
	 * @return string
	 */
	private function motif_id_for_product( int $product_id ): string {
		$map = (array) get_option( self::PRODUCT_MAP_OPTION, array() );
		$key = array_search( $product_id, array_map( 'intval', $map ), true );

		return is_string( $key ) ? $key : '';
	}

	/**
	 * Route from a single motif to the package builder, with that motif already
	 * in the basket it lands in.
	 *
	 * The shop's whole offer is "the more towels, the cheaper each one gets", so
	 * a product page that only sells one is quietly arguing against it. This is
	 * the primary action; WooCommerce's own add-to-cart stays above it for the
	 * customer who really does want a single towel.
	 *
	 * @return void
	 */
	public function bundle_cta(): void {
		$product = function_exists( 'wc_get_product' ) ? wc_get_product() : null;

		if ( ! $product instanceof \WC_Product ) {
			return;
		}

		$motif_id = $this->motif_id_for_product( (int) $product->get_id() );

		// Packages have no motif id of their own, and neither does anything the
		// shop added by hand. Both are already sold as what they are.
		if ( '' === $motif_id ) {
			return;
		}

		echo '<div class="cosypaw-offer">';
		$this->offer_rows( $motif_id );

		printf(
			'<a class="cosypaw-bundle-cta" href="%1$s">%2$s<span class="cosypaw-bundle-cta__hint">%3$s</span></a>',
			esc_url( $this->builder_url( $motif_id ) ),
			esc_html__( 'Dodaj u paket', 'cosypaw' ),
			esc_html__( 'Cena po komadu pada sa svakim sledećim peškirićem', 'cosypaw' )
		);
		echo '</div>';
	}

	/**
	 * The landing's builder, with a motif already chosen and, optionally, a size.
	 *
	 * @param string $motif_id Motif id to carry along.
	 * @param string $package  Package id to open on, or '' for the default.
	 * @return string
	 */
	private function builder_url( string $motif_id, string $package = '' ): string {
		$args = array( 'motif' => rawurlencode( $motif_id ) );

		if ( '' !== $package ) {
			$args['package'] = rawurlencode( $package );
		}

		return add_query_arg( $args, home_url( '/' ) ) . '#napravi-paket';
	}

	/**
	 * The package offer, printed as a row per bundle.
	 *
	 * The shop's argument is on every other page — "2+1 GRATIS", free shipping
	 * on the Trio, the per-piece price falling with each towel — and the
	 * product page was the one place making none of it. The numbers come from
	 * the catalogue, which reads the live WooCommerce prices, so a reprice
	 * cannot leave a stale claim behind here.
	 *
	 * @param string $motif_id Motif the rows carry into the builder.
	 * @return void
	 */
	private function offer_rows( string $motif_id ): void {
		$rows = array();

		foreach ( $this->catalog->packages() as $package ) {
			if ( (int) ( $package['qty'] ?? 0 ) > 1 ) {
				$rows[] = $package;
			}
		}

		if ( ! $rows ) {
			return;
		}

		printf( '<span class="cosypaw-offer__title">%s</span>', esc_html__( 'Uzmi više, plati manje', 'cosypaw' ) );
		echo '<ul class="cosypaw-offer__list">';

		foreach ( $rows as $package ) {
			$tags   = array();
			$gratis = (int) ( $package['gratis'] ?? 0 );

			// Derived in Catalog::gratis_count() from the live prices, so it
			// only appears while the bundle really does hand a towel over.
			if ( $gratis > 0 ) {
				$tags[] = sprintf(
					/* translators: 1: towels paid for, 2: towels given free. */
					__( '%1$d+%2$d GRATIS', 'cosypaw' ),
					(int) $package['qty'] - $gratis,
					$gratis
				);
			}

			if ( ! empty( $package['free_ship'] ) ) {
				$tags[] = __( 'Besplatna dostava', 'cosypaw' );
			}

			// Already reads "Ušteda 490 RSD" where there is a saving to state,
			// and is null the moment the arithmetic stops supporting one.
			if ( ! empty( $package['badge_saving'] ) && ! empty( $package['badge'] ) ) {
				$tags[] = (string) $package['badge'];
			}

			$tag_html = '';
			foreach ( $tags as $tag ) {
				$tag_html .= '<span class="cosypaw-offer__tag">' . esc_html( $tag ) . '</span>';
			}

			printf(
				'<li class="cosypaw-offer__row"><a href="%1$s"><span class="cosypaw-offer__name">%2$s</span><span class="cosypaw-offer__per">%3$s</span><span class="cosypaw-offer__tags">%4$s</span></a></li>',
				esc_url( $this->builder_url( $motif_id, (string) ( $package['id'] ?? '' ) ) ),
				esc_html( (string) ( $package['name'] ?? '' ) ),
				esc_html(
					sprintf(
						/* translators: %s: formatted per-piece price, e.g. "660 RSD". */
						__( '%s / kom', 'cosypaw' ),
						Catalog::format_price( (int) ( $package['per'] ?? 0 ) )
					)
				),
				$tag_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from esc_html() above.
			);
		}

		echo '</ul>';
	}

	/**
	 * Send a customer back to the product page carrying a flag added_panel reads.
	 *
	 * Only where WooCommerce was going to return them there anyway: a shop set
	 * to jump to the cart after every add has already answered "what now", and
	 * this has no business overruling it.
	 *
	 * @param string $url Redirect target WooCommerce settled on, '' for none.
	 * @return string
	 */
	public function add_to_cart_redirect( $url ) {
		if ( ! empty( $url ) || ! function_exists( 'get_permalink' ) ) {
			return $url;
		}

		// The cart and checkout upsells add a towel from a page that is not a
		// product page, and answering that click with a product page throws
		// away the very cart they were reading. This filter runs on `wp_loaded`,
		// before the query is parsed, so is_cart() cannot be asked — the link
		// says where it came from instead. The value is matched against a
		// two-name allowlist and never used as a URL.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$stay = isset( $_REQUEST[ Upsell::STAY_PARAM ] ) ? sanitize_key( wp_unslash( $_REQUEST[ Upsell::STAY_PARAM ] ) ) : '';

		if ( Upsell::STAY_CHECKOUT === $stay && function_exists( 'wc_get_checkout_url' ) ) {
			return wc_get_checkout_url();
		}

		if ( Upsell::STAY_CART === $stay && function_exists( 'wc_get_cart_url' ) ) {
			return wc_get_cart_url();
		}

		// Display-only flag, on a request WooCommerce has already validated.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$product_id = isset( $_REQUEST['add-to-cart'] ) ? absint( wp_unslash( $_REQUEST['add-to-cart'] ) ) : 0;

		if ( $product_id < 1 || ! in_array( $product_id, $this->our_product_ids(), true ) ) {
			return $url;
		}

		$permalink = get_permalink( $product_id );

		return $permalink ? add_query_arg( 'cosypaw-added', $product_id, $permalink ) : $url;
	}

	/**
	 * Whether the cart currently holds a product.
	 *
	 * Compares product ids rather than cart-item keys: a bundle carries its
	 * motifs as item data, which hashes into a different key for the same
	 * product, and the question here is only "is this towel in there".
	 *
	 * True where there is no cart to ask — the flag is then the best evidence
	 * available, which is the situation in the unit tests.
	 *
	 * @param int $product_id Product to look for.
	 * @return bool
	 */
	private function cart_holds( int $product_id ): bool {
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return true;
		}

		foreach ( (array) WC()->cart->get_cart() as $item ) {
			if ( (int) ( $item['product_id'] ?? 0 ) === $product_id ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * What to do next, printed above the title of a product just added.
	 *
	 * Adding a towel used to end the conversation: the page reloaded, said the
	 * product was in the cart, and left the customer looking at the single
	 * towel they had already bought. This puts the two things they might want
	 * next — the package that makes each towel cheaper, and the rest of the
	 * range — where they are still looking.
	 *
	 * @return void
	 */
	public function added_panel(): void {
		// Display-only flag; the add itself was validated by WooCommerce.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$added = isset( $_GET['cosypaw-added'] ) ? absint( wp_unslash( $_GET['cosypaw-added'] ) ) : 0;

		$product = function_exists( 'wc_get_product' ) ? wc_get_product() : null;

		if ( $added < 1 || ! $product instanceof \WC_Product || $added !== (int) $product->get_id() ) {
			return;
		}

		// A bookmarked or shared URL carries the flag long after the cart has
		// moved on, and "Sova je u korpi" has to be true when it is printed.
		if ( ! $this->cart_holds( $added ) ) {
			return;
		}

		$motif_id = $this->motif_id_for_product( (int) $product->get_id() );

		echo '<div class="cosypaw-added" role="status">';
		printf(
			'<p class="cosypaw-added__title">%s</p>',
			esc_html(
				sprintf(
					/* translators: %s: product name. */
					__( '%s je u korpi.', 'cosypaw' ),
					(string) $product->get_name()
				)
			)
		);

		// A package is already the offer; a single towel gets the case for one.
		if ( '' !== $motif_id ) {
			$this->offer_rows( $motif_id );
		}

		echo '<div class="cosypaw-added__actions">';

		if ( '' !== $motif_id ) {
			printf(
				'<a class="cosypaw-added__btn" href="%1$s">%2$s</a>',
				esc_url( $this->builder_url( $motif_id, $this->catalog->default_package() ) ),
				esc_html__( 'Napravi paket', 'cosypaw' )
			);
		}

		$shop = function_exists( 'wc_get_page_permalink' ) ? (string) wc_get_page_permalink( 'shop' ) : '';
		printf(
			'<a class="cosypaw-added__link" href="%1$s">%2$s</a>',
			esc_url( '' !== $shop ? $shop : home_url( '/#galerija' ) ),
			esc_html__( 'Nastavi kupovinu', 'cosypaw' )
		);

		if ( function_exists( 'wc_get_cart_url' ) ) {
			printf(
				'<a class="cosypaw-added__link" href="%1$s">%2$s</a>',
				esc_url( wc_get_cart_url() ),
				esc_html__( 'Idi u korpu', 'cosypaw' )
			);
		}

		echo '</div></div>';
	}

	/**
	 * One review, beside the buy button.
	 *
	 * Twenty motifs split a small pile of reviews twenty ways, so most product
	 * pages have none of their own and read as deserted at the moment someone
	 * is deciding. This shows the product's own newest review where it has one,
	 * and otherwise borrows the shop's newest — labelled with the towel it was
	 * written about, because an unlabelled one would be a review of this towel.
	 *
	 * Deliberately plain markup with no itemprop: a borrowed review must never
	 * reach this product's structured data, where it would be a rating Google
	 * reads as this product's own.
	 *
	 * @return void
	 */
	public function review_proof(): void {
		$product = function_exists( 'wc_get_product' ) ? wc_get_product() : null;

		if ( ! $product instanceof \WC_Product || ! in_array( (int) $product->get_id(), $this->our_product_ids(), true ) ) {
			return;
		}

		$row = $this->proof_row( (int) $product->get_id() );

		if ( ! $row ) {
			return;
		}

		$rating    = (int) ( $row['rating'] ?? 5 );
		$permalink = (string) ( $row['permalink'] ?? '' );
		$about     = (string) ( $row['meta'] ?? '' );

		echo '<figure class="cosypaw-proof">';
		$this->rating_stars( $rating );
		printf( '<blockquote class="cosypaw-proof__quote">%s</blockquote>', esc_html( (string) ( $row['quote'] ?? '' ) ) );

		echo '<figcaption class="cosypaw-proof__by">';
		printf( '<span class="cosypaw-proof__name">%s</span>', esc_html( (string) ( $row['name'] ?? '' ) ) );

		// The label only makes sense on a borrowed review — on the product's
		// own it would say "about Sova" underneath Sova.
		if ( ! empty( $row['borrowed'] ) && '' !== $about ) {
			$label = sprintf(
				/* translators: %s: product name the review was written about. */
				__( 'o proizvodu %s', 'cosypaw' ),
				$about
			);

			printf(
				'<span class="cosypaw-proof__about">%s</span>',
				'' !== $permalink
					? sprintf( '<a href="%1$s">%2$s</a>', esc_url( $permalink ), esc_html( $label ) )
					: esc_html( $label )
			);
		}

		echo '</figcaption></figure>';
	}

	/**
	 * The review to quote beside the buy button, or an empty array for none.
	 *
	 * @param int $product_id Product being viewed.
	 * @return array<string,mixed>
	 */
	private function proof_row( int $product_id ): array {
		$own = $this->own_review( $product_id );

		if ( $own ) {
			return $own;
		}

		if ( ! class_exists( Reviews::class ) ) {
			return array();
		}

		$pool = Reviews::latest( 1 );
		$row  = $pool[0] ?? array();

		if ( ! $row ) {
			return array();
		}

		$row['borrowed'] = true;

		return $row;
	}

	/**
	 * The product's own newest approved review, reduced to the proof row shape.
	 *
	 * @param int $product_id Product to read.
	 * @return array<string,mixed>
	 */
	private function own_review( int $product_id ): array {
		if ( ! function_exists( 'get_comments' ) ) {
			return array();
		}

		$comments = (array) get_comments(
			array(
				'post_id'  => $product_id,
				'status'   => 'approve',
				'type__in' => array( 'review', 'comment' ),
				'parent'   => 0,
				'number'   => 1,
				'orderby'  => 'comment_date_gmt',
				'order'    => 'DESC',
			)
		);

		$comment = $comments[0] ?? null;

		if ( ! is_object( $comment ) || empty( $comment->comment_ID ) ) {
			return array();
		}

		$rating = function_exists( 'get_comment_meta' )
			? (int) get_comment_meta( (int) $comment->comment_ID, 'rating', true )
			: 0;

		$author = trim( (string) ( $comment->comment_author ?? '' ) );

		return array(
			'quote'     => wp_trim_words( (string) ( $comment->comment_content ?? '' ), 28, '…' ),
			// Same reasoning as Reviews::render(): a byline is owed even where
			// none was given, and "Kupac" beats inventing one.
			'name'      => '' !== $author ? $author : __( 'Kupac', 'cosypaw' ),
			'rating'    => $rating > 0 ? $rating : 5,
			'permalink' => '#comment-' . (int) $comment->comment_ID,
			'meta'      => '',
			'borrowed'  => false,
		);
	}

	/**
	 * Five stars, filled to the rating. Mirrors the landing's testimonials.
	 *
	 * @param int $rating Stars earned, 1-5.
	 * @return void
	 */
	private function rating_stars( int $rating ): void {
		$rating = max( 1, min( 5, $rating ) );

		printf(
			'<span class="cosypaw-proof__stars" role="img" aria-label="%s">',
			esc_attr(
				sprintf(
					/* translators: %d: star rating, 1-5. */
					__( '%d od 5 zvezdica', 'cosypaw' ),
					$rating
				)
			)
		);

		for ( $i = 1; $i <= 5; $i++ ) {
			printf(
				'<svg class="cosypaw-proof__star%s" width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l2.9 6.3 6.8.6-5.1 4.5 1.5 6.7L12 17l-6 3.6 1.5-6.7L2.4 9.4l6.8-.6z"/></svg>',
				$i <= $rating ? '' : ' cosypaw-proof__star--empty'
			);
		}

		echo '</span>';
	}

	/**
	 * Turn the empty reviews tab into an invitation.
	 *
	 * "Još nema recenzija" is the whole tab on most motifs, and it reads as a
	 * shop nobody has bought from. The line below asks for the first review and
	 * points at the ones the shop does have; WooCommerce's own empty notice is
	 * hidden by the stylesheet where this runs, so the tab says it once.
	 *
	 * @param array<string,array<string,mixed>> $tabs Product tabs.
	 * @return array<string,array<string,mixed>>
	 */
	public function reviews_tab_intro( $tabs ) {
		if ( ! is_array( $tabs ) || empty( $tabs['reviews']['callback'] ) ) {
			return $tabs;
		}

		$product = function_exists( 'wc_get_product' ) ? wc_get_product() : null;

		// A product with reviews of its own needs no invitation, and the tab
		// is then about them.
		if ( ! $product instanceof \WC_Product || (int) $product->get_review_count() > 0 ) {
			return $tabs;
		}

		$original = $tabs['reviews']['callback'];
		$name     = (string) $product->get_name();

		$tabs['reviews']['callback'] = function ( ...$args ) use ( $original, $name ) {
			$this->reviews_tab_invite( $name );

			if ( is_callable( $original ) ) {
				call_user_func_array( $original, $args );
			}
		};

		return $tabs;
	}

	/**
	 * The invitation printed above an empty reviews tab.
	 *
	 * @param string $name Product name.
	 * @return void
	 */
	public function reviews_tab_invite( string $name ): void {
		echo '<div class="cosypaw-reviews-intro">';
		printf(
			'<p class="cosypaw-reviews-intro__text">%s</p>',
			esc_html(
				sprintf(
					/* translators: %s: product name. */
					__( 'Budi prvi koji je ocenio %s.', 'cosypaw' ),
					$name
				)
			)
		);
		printf(
			'<a class="cosypaw-reviews-intro__link" href="%1$s">%2$s</a>',
			esc_url( home_url( '/#utisci' ) ),
			esc_html__( 'Pročitaj utiske kupaca', 'cosypaw' )
		);
		echo '</div>';
	}

	/**
	 * Whether the product being viewed is one of ours.
	 *
	 * @return bool
	 */
	private function is_our_single_product(): bool {
		$product = function_exists( 'wc_get_product' ) ? wc_get_product() : null;

		return $product instanceof \WC_Product
			&& in_array( (int) $product->get_id(), $this->our_product_ids(), true );
	}

	/**
	 * The gift banner, under the product.
	 *
	 * "Stiže spremno za poklon" is the shop's answer to what arrives in the
	 * post, and the unboxing clip is the evidence — both of which only the
	 * front page was making. A product page is where the question is asked.
	 *
	 * @return void
	 */
	public function gift_banner(): void {
		if ( ! $this->is_our_single_product() ) {
			return;
		}

		echo '<div class="cosypaw-product-block">';
		get_template_part( 'template-parts/gift-banner' );
		echo '</div>';
	}

	/**
	 * The FAQ accordion, under the product.
	 *
	 * Fabric, washing, delivery, payment — the four things someone reads
	 * before a first order, which a product page was sending them back to the
	 * front page to find. The template part carries no structured data, so the
	 * FAQPage node stays where it belongs, on one URL.
	 *
	 * @return void
	 */
	public function faq_section(): void {
		if ( ! $this->is_our_single_product() ) {
			return;
		}

		get_template_part( 'template-parts/faq' );
	}

	/**
	 * The facts every towel shares, printed under each product.
	 *
	 * Twenty products whose descriptions all repeat the same fabric and washing
	 * instructions read as twenty copies of one page to a search engine. Keeping
	 * the shared half here leaves the written description free to be about the
	 * motif alone.
	 *
	 * @return void
	 */
	public function product_specs(): void {
		$product = function_exists( 'wc_get_product' ) ? wc_get_product() : null;

		if ( ! $product instanceof \WC_Product || ! in_array( (int) $product->get_id(), $this->our_product_ids(), true ) ) {
			return;
		}

		/**
		 * The shared spec list, label => value.
		 *
		 * Deliberately short and only what the shop already states elsewhere on
		 * the site. Nothing here is measured, so no dimensions are claimed.
		 *
		 * @param array<string,string> $specs      Label => value.
		 * @param int                  $product_id Product ID.
		 */
		$specs = (array) apply_filters(
			'cosypaw_product_specs',
			array(
				__( 'Materijal', 'cosypaw' )  => __( 'Plišana mikrofibra — mekana, lagana i jako upijajuća.', 'cosypaw' ),
				__( 'Kačenje', 'cosypaw' )    => __( 'Alka za kačenje, da peškirić uvek stoji na svom mestu.', 'cosypaw' ),
				__( 'Održavanje', 'cosypaw' ) => __( 'Mašinsko pranje na 40°C, bez omekšivača. Suši se brzo i ne gubi oblik.', 'cosypaw' ),
				__( 'Dostava', 'cosypaw' )    => __( 'Plaćanje pouzećem, isporuka 2–4 dana širom Srbije.', 'cosypaw' ),
			),
			(int) $product->get_id()
		);

		if ( ! $specs ) {
			return;
		}

		echo '<dl class="cosypaw-specs">';
		foreach ( $specs as $label => $value ) {
			printf(
				'<div class="cosypaw-specs__row"><dt>%1$s</dt><dd>%2$s</dd></div>',
				esc_html( (string) $label ),
				esc_html( (string) $value )
			);
		}
		echo '</dl>';
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
