<?php
/**
 * Stock — what "Nema na lageru" in wp-admin does to the storefront.
 *
 * WooCommerce's own stock check only covers the product being added: a towel
 * marked out of stock could not be bought on its own, but it still went into
 * the cart inside a package from the builder (the line is the package
 * product, the towel only rides along as `cosypaw_motifs`) and inside a gift
 * bundle. And the theme's cards are built from the catalogue, which never
 * asked about stock, so they kept offering it with nothing on them to say
 * otherwise.
 *
 * The rule is the shop's stock status and nothing else: a product is sold out
 * when WooCommerce says it is not in stock. The cards print it as
 * "Rasprodato" over the photo and leave the buttons off (see
 * template-parts/motif-card.php and the other card templates); this class
 * refuses it on the way into the cart and stops a cart that already holds it
 * from reaching the order.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

namespace Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stock.
 */
final class Stock {

	/**
	 * Catalogue data object.
	 *
	 * @var Catalog
	 */
	private Catalog $catalog;

	/**
	 * Constructor.
	 *
	 * @param Catalog $catalog Catalogue data object.
	 */
	public function __construct( Catalog $catalog ) {
		$this->catalog = $catalog;

		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'validate_motifs' ), 10, 2 );
		add_action( 'woocommerce_check_cart_items', array( $this, 'check_cart' ) );
		add_filter( 'woocommerce_get_availability_text', array( $this, 'availability_text' ), 10, 2 );
		add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'loop_badge' ), 9 );
	}

	/**
	 * Whether a product is sold out.
	 *
	 * Only a product WooCommerce can answer for: no product, or a stand-in
	 * without a stock status, is not sold out — whether it can be bought at
	 * all is the caller's own check.
	 *
	 * @param mixed $product The product (\WC_Product at runtime), or null.
	 * @return bool
	 */
	public static function sold_out( $product ): bool {
		return is_object( $product ) && is_callable( array( $product, 'is_in_stock' ) ) && ! $product->is_in_stock();
	}

	/**
	 * The "Rasprodato" badge laid over a card's photo.
	 *
	 * @return void
	 */
	public static function badge(): void {
		printf( '<span class="sold-out-badge">%s</span>', esc_html__( 'Rasprodato', 'cosypaw' ) );
	}

	/**
	 * Refuse a package from the builder that holds a sold-out towel.
	 *
	 * The package product itself is in stock, so WooCommerce lets it through;
	 * the towel in it is what the shop does not have. The builder no longer
	 * offers one, but a page left open from before the stock changed still
	 * can.
	 *
	 * @param bool|mixed $passed     Whether the add has passed so far.
	 * @param int|mixed  $product_id Product being added.
	 * @return bool|mixed
	 */
	public function validate_motifs( $passed, $product_id ) {
		unset( $product_id );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- WooCommerce's add-to-cart carries no nonce; this only ever refuses.
		if ( ! $passed || empty( $_REQUEST['cosypaw_motifs'] ) ) {
			return $passed;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$names = $this->sold_out_motifs( sanitize_text_field( wp_unslash( $_REQUEST['cosypaw_motifs'] ) ) );
		if ( ! $names ) {
			return $passed;
		}

		wc_add_notice(
			sprintf(
				/* translators: %s: towel name(s), e.g. "Sova". */
				__( 'Rasprodato: %s. Izaberi drugi peškir za paket.', 'cosypaw' ),
				implode( ', ', $names )
			),
			'error'
		);

		return false;
	}

	/**
	 * Stop a cart that holds a sold-out towel or bundle part from checking out.
	 *
	 * WooCommerce already does this for a product that is itself out of stock.
	 * A package or a gift bundle stays in stock when something packed in it
	 * runs out, so the cart has to be told — with what ran out, so the
	 * shopper knows which line to change.
	 *
	 * @return void
	 */
	public function check_cart(): void {
		if ( ! function_exists( 'WC' ) || ! WC()->cart instanceof \WC_Cart ) {
			return;
		}

		foreach ( WC()->cart->get_cart() as $item ) {
			$names = array();

			if ( ! empty( $item['cosypaw_motifs'] ) ) {
				$names = $this->sold_out_motifs( (string) $item['cosypaw_motifs'] );
			}

			if ( ! empty( $item[ GiftBundlePicker::ITEM_KEY ] ) ) {
				foreach ( GiftBundles::decode( (string) $item[ GiftBundlePicker::ITEM_KEY ] ) as $part ) {
					if ( GiftBundles::part_sold_out( $part[0], $part[1] ) ) {
						$names[] = GiftBundles::describe( array( $part ) )[0]['name'];
					}
				}
			}

			if ( ! $names ) {
				continue;
			}

			$product = $item['data'] ?? null;

			wc_add_notice(
				sprintf(
					/* translators: 1: what ran out, e.g. "Sova", 2: the cart line it is packed in. */
					__( 'Rasprodato: %1$s (u stavci „%2$s“). Ukloni tu stavku iz korpe i složi je ponovo.', 'cosypaw' ),
					implode( ', ', array_unique( $names ) ),
					is_object( $product ) && is_callable( array( $product, 'get_name' ) ) ? (string) $product->get_name() : ''
				),
				'error'
			);
		}
	}

	/**
	 * "Rasprodato" in place of WooCommerce's "Nema na lageru", on the product
	 * page and wherever else WooCommerce states availability.
	 *
	 * @param string|mixed $text    Availability text.
	 * @param mixed        $product The product (\WC_Product at runtime).
	 * @return string|mixed
	 */
	public function availability_text( $text, $product ) {
		return self::sold_out( $product ) ? __( 'Rasprodato', 'cosypaw' ) : $text;
	}

	/**
	 * The badge on a sold-out product in WooCommerce's own product loops
	 * (shop, category and related products).
	 *
	 * @return void
	 */
	public function loop_badge(): void {
		global $product;

		if ( self::sold_out( $product ) ) {
			self::badge();
		}
	}

	/**
	 * Names of the sold-out towels in a comma list of motif ids.
	 *
	 * @param string $raw Comma-separated motif ids.
	 * @return string[]
	 */
	private function sold_out_motifs( string $raw ): array {
		$map   = $this->catalog->motif_map();
		$names = array();

		foreach ( explode( ',', $raw ) as $id ) {
			$id = trim( $id );
			if ( ! empty( $map[ $id ]['sold_out'] ) ) {
				$names[ $id ] = (string) $map[ $id ]['name'];
			}
		}

		return array_values( $names );
	}
}
