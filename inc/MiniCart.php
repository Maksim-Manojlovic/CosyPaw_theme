<?php
/**
 * MiniCart — the cart as a panel from the right, without leaving the page.
 *
 * The cart pill (FloatingCart) and the header's cart icon used to go to
 * /cart/. They open this panel now: the lines with their quantities, where
 * the cart stands against free delivery and the Crni petak gifts, up to three
 * motifs it does not hold yet (one click adds one), the total, and the way on
 * to the checkout. It also opens by itself after an add to cart. /cart/ stays
 * for a direct visit, a middle click, and the panel's own "Pogledaj korpu".
 *
 * The footer only carries an empty shell; what is in it is fetched over
 * wc-ajax each time the panel opens or changes. Front pages are cached, and a
 * cart printed into cached HTML would show one shopper another's basket.
 *
 * Every number in the panel is one the rest of the shop already states: the
 * lines are priced by WooCommerce through the same filters as the cart table
 * (so a gift reads "Gratis" and a package lists its towels), the package
 * saving is BundlePricing's fee, the offer is Upsell's, the gift ladder is
 * BlackFriday's and the total is the pill's.
 *
 * Not rendered on the cart or the checkout, which are the cart.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

namespace Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MiniCart.
 */
final class MiniCart {

	/**
	 * wc-ajax action: the panel's contents.
	 */
	public const AJAX_VIEW = 'cosypaw_mini_cart';

	/**
	 * wc-ajax action: set a line's quantity (0 removes it).
	 */
	public const AJAX_QTY = 'cosypaw_mini_cart_qty';

	/**
	 * wc-ajax action: add one of an offered motif.
	 */
	public const AJAX_ADD = 'cosypaw_mini_cart_add';

	/**
	 * Nonce action for changing quantities.
	 */
	public const NONCE = 'cosypaw-mini-cart';

	/**
	 * Session flag: open the panel on the next page, after an add to cart that
	 * reloaded the page (a product page's own form).
	 */
	public const OPEN_FLAG = 'cosypaw_mini_cart_open';

	/**
	 * Motifs offered in the panel. Three: enough to be a choice, few enough
	 * that the checkout button is never pushed out of sight.
	 */
	public const PICKS = 3;

	/**
	 * The cart page's offer: next towel, delivery, motifs.
	 *
	 * @var Upsell
	 */
	private Upsell $upsell;

	/**
	 * The cart pill, refreshed after every change.
	 *
	 * @var FloatingCart
	 */
	private FloatingCart $fab;

	/**
	 * Crni petak, for the gift ladder. Null where it is not built.
	 *
	 * @var BlackFriday|null
	 */
	private ?BlackFriday $black_friday;

	/**
	 * Constructor.
	 *
	 * @param Upsell           $upsell       Cart offer.
	 * @param FloatingCart     $fab          Cart pill.
	 * @param BlackFriday|null $black_friday Crni petak.
	 */
	public function __construct( Upsell $upsell, FloatingCart $fab, ?BlackFriday $black_friday = null ) {
		$this->upsell       = $upsell;
		$this->fab          = $fab;
		$this->black_friday = $black_friday;

		add_action( 'wp_footer', array( $this, 'render' ), 5 );

		add_action( 'wc_ajax_' . self::AJAX_VIEW, array( $this, 'ajax_view' ) );
		add_action( 'wc_ajax_' . self::AJAX_QTY, array( $this, 'ajax_qty' ) );
		add_action( 'wc_ajax_' . self::AJAX_ADD, array( $this, 'ajax_add' ) );

		add_action( 'woocommerce_add_to_cart', array( $this, 'flag_open' ), 10, 6 );
	}

	/* ---------------------------------------------------------------------
	 * The shell.
	 * ------------------------------------------------------------------- */

	/**
	 * Print the empty panel at the end of the document.
	 *
	 * @return void
	 */
	public function render(): void {
		echo $this->shell(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shell() escapes every part it interpolates.
	}

	/**
	 * The panel with nothing in it yet.
	 *
	 * @return string Empty on the cart and the checkout, or without WooCommerce.
	 */
	public function shell(): string {
		if ( ! class_exists( '\WC_AJAX' ) || ! function_exists( 'WC' ) ) {
			return '';
		}

		$on_till = ( function_exists( 'is_cart' ) && is_cart() ) || ( function_exists( 'is_checkout' ) && is_checkout() );

		if ( $on_till ) {
			// A product page's add that landed here instead (the cart page's
			// own add links) is already on show.
			$this->take_open_flag();
			return '';
		}

		return sprintf(
			'<dialog class="mini-cart" data-mini-cart data-endpoint-view="%1$s" data-endpoint-qty="%2$s" data-endpoint-add="%3$s" data-cart-url="%8$s"%4$s aria-labelledby="mini-cart-title">' .
				'<div class="mini-cart__panel">' .
					'<div class="mini-cart__head">' .
						'<h2 class="mini-cart__title" id="mini-cart-title">%5$s <span class="mini-cart__count" data-mini-cart-count></span></h2>' .
						'<form method="dialog"><button type="submit" class="mini-cart__close" aria-label="%6$s">&times;</button></form>' .
					'</div>' .
					'<div class="mini-cart__body" data-mini-cart-body aria-busy="true">' .
						'<p class="mini-cart__loading">%7$s</p>' .
					'</div>' .
				'</div>' .
			'</dialog>',
			esc_url( \WC_AJAX::get_endpoint( self::AJAX_VIEW ) ),
			esc_url( \WC_AJAX::get_endpoint( self::AJAX_QTY ) ),
			esc_url( \WC_AJAX::get_endpoint( self::AJAX_ADD ) ),
			$this->take_open_flag() ? ' data-open-on-load' : '',
			esc_html__( 'Korpa', 'cosypaw' ),
			esc_attr__( 'Zatvori', 'cosypaw' ),
			esc_html__( 'Učitavam korpu…', 'cosypaw' ),
			esc_url( function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/' ) )
		);
	}

	/**
	 * After an add to cart that reloads the page, open the panel on the next
	 * one. Not for the gifts BlackFriday puts in, not for an AJAX add (the
	 * script opens the panel itself), and not in wp-admin.
	 *
	 * @param string              $key            Cart item key.
	 * @param int                 $product_id     Product id.
	 * @param int                 $quantity       Quantity.
	 * @param int                 $variation_id   Variation id.
	 * @param array<string,mixed> $variation      Variation attributes.
	 * @param array<string,mixed> $cart_item_data Extra item data.
	 * @return void
	 */
	public function flag_open( $key = '', $product_id = 0, $quantity = 1, $variation_id = 0, $variation = array(), $cart_item_data = array() ): void {
		unset( $key, $product_id, $quantity, $variation_id, $variation );

		// wc-ajax requests are not wp_doing_ajax(), hence the query check.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only routing check.
		if ( is_admin() || wp_doing_ajax() || ! empty( $_GET['wc-ajax'] ) ) {
			return;
		}

		if ( is_array( $cart_item_data ) && ! empty( $cart_item_data[ BlackFriday::ITEM_KEY ] ) ) {
			return;
		}

		$session = WC()->session ?? null;

		if ( $session ) {
			$session->set( self::OPEN_FLAG, 1 );
		}
	}

	/**
	 * Read and clear the open-on-load flag.
	 *
	 * @return bool
	 */
	private function take_open_flag(): bool {
		$session = function_exists( 'WC' ) ? ( WC()->session ?? null ) : null;

		if ( ! $session || ! $session->get( self::OPEN_FLAG ) ) {
			return false;
		}

		$session->set( self::OPEN_FLAG, null );

		return true;
	}

	/* ---------------------------------------------------------------------
	 * The endpoints.
	 * ------------------------------------------------------------------- */

	/**
	 * The panel's contents.
	 *
	 * @return void
	 */
	public function ajax_view(): void {
		$this->respond();
	}

	/**
	 * Set a line's quantity; 0 removes it. A gift line is not the shopper's
	 * to change.
	 *
	 * @return void
	 */
	public function ajax_qty(): void {
		if ( ! check_ajax_referer( self::NONCE, 'nonce', false ) ) {
			$this->respond( __( 'Sesija je istekla — pokušajte ponovo.', 'cosypaw' ), 403 );
		}

		$cart = $this->cart();
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- verified above.
		$key = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
		$qty = isset( $_POST['qty'] ) ? max( 0, (int) $_POST['qty'] ) : 0;
		// phpcs:enable

		$item = null !== $cart ? $cart->get_cart_item( $key ) : array();

		if ( ! $item || BlackFriday::is_gift( $item ) ) {
			$this->respond( __( 'Ta stavka više nije u korpi.', 'cosypaw' ), 400 );
		}

		$product = $item['data'] ?? null;
		$max     = is_object( $product ) && is_callable( array( $product, 'get_max_purchase_quantity' ) ) ? (int) $product->get_max_purchase_quantity() : -1;

		if ( $max > 0 ) {
			$qty = min( $qty, $max );
		}

		if ( $qty < 1 ) {
			$cart->remove_cart_item( $key );
		} else {
			$cart->set_quantity( $key, $qty, false );
		}

		$this->respond();
	}

	/**
	 * Add one of the motifs the panel offers.
	 *
	 * No nonce, like WooCommerce's own add-to-cart links: adding a towel is
	 * not an action anyone can be harmed by, and an empty cart may have no
	 * session yet for a nonce to belong to. Only an offered motif is added.
	 *
	 * @return void
	 */
	public function ajax_add(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- see the docblock.
		$product_id = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;
		$cart       = $this->cart();

		if ( null === $cart || $product_id < 1 || ! $this->upsell->offers( $product_id ) ) {
			$this->respond( __( 'Taj peškir trenutno nije u ponudi.', 'cosypaw' ), 400 );
		}

		/** This filter is documented in woocommerce/includes/class-wc-form-handler.php */
		$valid = (bool) apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, 1 );

		if ( ! $valid || false === $cart->add_to_cart( $product_id, 1 ) ) {
			$this->respond( __( 'Dodavanje nije uspelo — pokušajte ponovo.', 'cosypaw' ), 400 );
		}

		$this->respond( '', 200, true );
	}

	/**
	 * Recalculate and answer with the panel, the pill and the count.
	 *
	 * @param string $error  Message to show at the top of the panel.
	 * @param int    $status HTTP status.
	 * @param bool   $added  Whether a line was just added.
	 * @return void
	 */
	private function respond( string $error = '', int $status = 200, bool $added = false ): void {
		$cart = $this->cart();

		if ( null !== $cart ) {
			$cart->calculate_totals();
			$this->set_cookies( $cart );
		}

		// WooCommerce's notices from a refused add would otherwise surface on
		// the shopper's next page, after the panel already said so.
		if ( function_exists( 'wc_clear_notices' ) ) {
			wc_clear_notices();
		}

		$count = null !== $cart ? (int) $cart->get_cart_contents_count() : 0;

		$data = array(
			'html'  => $this->body( $error ),
			'count' => $count,
			'fab'   => $this->fab->html(),
			'added' => $added,
		);

		if ( 200 === $status ) {
			wp_send_json_success( $data );
		}

		wp_send_json_error( $data, $status );
	}

	/**
	 * The cookies WooCommerce sets after a change to the cart. Its own setter
	 * runs on `wp` (before a wc-ajax handler) and on shutdown (after this one
	 * has printed), so neither would see the change.
	 *
	 * @param \WC_Cart $cart Cart.
	 * @return void
	 */
	private function set_cookies( \WC_Cart $cart ): void {
		if ( headers_sent() || ! function_exists( 'wc_setcookie' ) ) {
			return;
		}

		if ( ! $cart->is_empty() ) {
			wc_setcookie( 'woocommerce_items_in_cart', '1' );
			wc_setcookie( 'woocommerce_cart_hash', $cart->get_cart_hash() );
		} elseif ( isset( $_COOKIE['woocommerce_items_in_cart'] ) ) {
			wc_setcookie( 'woocommerce_items_in_cart', '0', time() - HOUR_IN_SECONDS );
			wc_setcookie( 'woocommerce_cart_hash', '', time() - HOUR_IN_SECONDS );
		}
	}

	/* ---------------------------------------------------------------------
	 * The contents.
	 * ------------------------------------------------------------------- */

	/**
	 * Everything inside the panel under its heading.
	 *
	 * @param string $error Message to show first.
	 * @return string
	 */
	public function body( string $error = '' ): string {
		$cart  = $this->cart();
		$offer = $this->upsell->drawer_offer( self::PICKS );
		$alert = '' !== $error ? sprintf( '<p class="mini-cart__error" role="alert">%s</p>', esc_html( $error ) ) : '';

		if ( null === $cart || $cart->is_empty() ) {
			return sprintf(
				'<div class="mini-cart__scroll">%1$s<div class="mini-cart__empty"><p class="mini-cart__empty-title">%2$s</p><p>%3$s</p></div>%4$s%5$s</div>' .
				'<div class="mini-cart__foot"><a class="btn btn--primary mini-cart__checkout" href="%6$s">%7$s</a></div>',
				$alert,
				esc_html__( 'Korpa je prazna', 'cosypaw' ),
				esc_html__( 'Izaberite peškire za paket ili dodajte neki od ovih:', 'cosypaw' ),
				$this->progress( $offer ),
				$this->picks( $offer ),
				esc_url( home_url( '/' ) . '#napravi-paket' ),
				esc_html__( 'Izaberi paket', 'cosypaw' )
			);
		}

		return sprintf(
			'<div class="mini-cart__scroll" data-nonce="%1$s" data-count="%2$d">%3$s%4$s<ul class="mini-cart__lines">%5$s</ul>%6$s%7$s</div>%8$s',
			esc_attr( wp_create_nonce( self::NONCE ) ),
			(int) $cart->get_cart_contents_count(),
			$alert,
			$this->progress( $offer ),
			$this->lines( $cart ),
			$this->fees( $cart ),
			$this->picks( $offer ),
			$this->foot( $cart, $offer )
		);
	}

	/**
	 * Where the cart stands: the gift ladder while Crni petak runs, and free
	 * delivery wherever the ladder's sentence does not already say it.
	 *
	 * @param array<string,mixed> $offer Upsell::drawer_offer().
	 * @return string
	 */
	private function progress( array $offer ): string {
		$out     = '';
		$summary = null !== $this->black_friday ? $this->black_friday->summary() : null;

		if ( null !== $summary ) {
			$out .= self::bar(
				'mini-cart__progress--gift',
				__( 'Crni petak', 'cosypaw' ),
				(string) $summary['text'],
				(float) $summary['pct'],
				sprintf( '<button type="button" class="mini-cart__gift-link" data-gift-open>%s</button>', esc_html__( 'Pokloni', 'cosypaw' ) )
			);
		}

		$shipping = $offer['shipping'] ?? null;

		if ( null !== $shipping && 'gap' === $shipping['state'] && ! ( $summary['covers_delivery'] ?? false ) ) {
			$text = sprintf(
				/* translators: %s: formatted amount still to spend, e.g. "320 RSD". */
				__( 'Još %s do besplatne dostave', 'cosypaw' ),
				Catalog::format_price( (int) $shipping['gap'] )
			);

			if ( '' !== (string) $offer['postage'] ) {
				/* translators: %s: courier price range, e.g. "400–600 RSD". */
				$text .= ' ' . sprintf( __( '(umesto %s poštarine)', 'cosypaw' ), (string) $offer['postage'] );
			}

			$out .= self::bar( 'mini-cart__progress--ship', '', $text, (float) $shipping['pct'], '' );
		}

		return $out;
	}

	/**
	 * One progress row: a label, a sentence and a bar.
	 *
	 * @param string $modifier Extra class.
	 * @param string $label    Small label above, or empty.
	 * @param string $text     The sentence.
	 * @param float  $pct      Bar fill, 0–100.
	 * @param string $extra    Trusted markup after the sentence.
	 * @return string
	 */
	private static function bar( string $modifier, string $label, string $text, float $pct, string $extra ): string {
		return sprintf(
			'<div class="mini-cart__progress %1$s">%2$s<p class="mini-cart__progress-text">%3$s %4$s</p><span class="mini-cart__bar" aria-hidden="true"><span style="width:%5$s%%"></span></span></div>',
			esc_attr( $modifier ),
			'' !== $label ? '<span class="mini-cart__progress-label">' . esc_html( $label ) . '</span>' : '',
			esc_html( $text ),
			$extra,
			esc_attr( (string) round( max( 0.0, min( 100.0, $pct ) ), 1 ) )
		);
	}

	/**
	 * The cart lines.
	 *
	 * @param \WC_Cart $cart Cart.
	 * @return string
	 */
	private function lines( \WC_Cart $cart ): string {
		$out = '';

		foreach ( $cart->get_cart() as $key => $item ) {
			$product = $item['data'] ?? null;

			if ( ! is_object( $product ) || ! is_callable( array( $product, 'get_name' ) ) ) {
				continue;
			}

			$key      = (string) $key;
			$qty      = (int) ( $item['quantity'] ?? 1 );
			$gift     = BlackFriday::is_gift( $item );
			$url      = is_callable( array( $product, 'get_id' ) ) ? (string) get_permalink( (int) ( $item['product_id'] ?? $product->get_id() ) ) : '';
			$name     = (string) apply_filters( 'woocommerce_cart_item_name', $product->get_name(), $item, $key );
			$thumb    = (string) apply_filters( 'woocommerce_cart_item_thumbnail', is_callable( array( $product, 'get_image' ) ) ? $product->get_image( 'woocommerce_thumbnail' ) : '', $item, $key );
			$subtotal = (string) apply_filters( 'woocommerce_cart_item_subtotal', $cart->get_product_subtotal( $product, $qty ), $item, $key );
			$meta     = function_exists( 'wc_get_formatted_cart_item_data' ) ? trim( (string) wc_get_formatted_cart_item_data( $item, true ) ) : '';
			$max      = is_callable( array( $product, 'get_max_purchase_quantity' ) ) ? (int) $product->get_max_purchase_quantity() : -1;

			$controls = $gift
				? sprintf( '<button type="button" class="mini-cart__gift-link" data-gift-open>%s</button>', esc_html__( 'Promeni poklon', 'cosypaw' ) )
				: sprintf(
					'<div class="mini-cart__qty">' .
						'<button type="button" data-qty="%1$d" aria-label="%2$s">&minus;</button>' .
						'<span aria-live="polite">%3$d</span>' .
						'<button type="button" data-qty="%4$d" aria-label="%5$s"%6$s>+</button>' .
					'</div>',
					$qty - 1,
					esc_attr( sprintf( /* translators: %s: product name. */ __( 'Jedan manje: %s', 'cosypaw' ), wp_strip_all_tags( $name ) ) ),
					$qty,
					$qty + 1,
					esc_attr( sprintf( /* translators: %s: product name. */ __( 'Još jedan: %s', 'cosypaw' ), wp_strip_all_tags( $name ) ) ),
					$max > 0 && $qty >= $max ? ' disabled' : ''
				);

			$remove = $gift ? '' : sprintf(
				'<button type="button" class="mini-cart__remove" data-qty="0" aria-label="%1$s"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-12M9 7V4h6v3"/></svg></button>',
				esc_attr( sprintf( /* translators: %s: product name. */ __( 'Ukloni: %s', 'cosypaw' ), wp_strip_all_tags( $name ) ) )
			);

			$out .= sprintf(
				'<li class="mini-cart__line%1$s" data-key="%2$s">' .
					'<a class="mini-cart__thumb" href="%3$s" tabindex="-1" aria-hidden="true">%4$s</a>' .
					'<div class="mini-cart__info">' .
						'<a class="mini-cart__name" href="%3$s">%5$s</a>' .
						'%6$s' .
						'<div class="mini-cart__row">%7$s<span class="mini-cart__price">%8$s</span></div>' .
					'</div>' .
					'%9$s' .
				'</li>',
				$gift ? ' is-gift' : '',
				esc_attr( $key ),
				esc_url( $url ),
				wp_kses_post( $thumb ),
				esc_html( wp_strip_all_tags( $name ) ),
				'' !== $meta ? '<p class="mini-cart__meta">' . nl2br( esc_html( $meta ) ) . '</p>' : '',
				$controls,
				wp_kses_post( $subtotal ),
				$remove
			);
		}

		return $out;
	}

	/**
	 * The package saving (and any other fee), under the lines.
	 *
	 * @param \WC_Cart $cart Cart.
	 * @return string
	 */
	private function fees( \WC_Cart $cart ): string {
		$fees = is_callable( array( $cart, 'get_fees' ) ) ? (array) $cart->get_fees() : array();
		$out  = '';

		foreach ( $fees as $fee ) {
			$amount = (float) ( $fee->total ?? $fee->amount ?? 0 );

			if ( 0.0 === $amount ) {
				continue;
			}

			$out .= sprintf(
				'<li><span>%1$s</span><strong>%2$s</strong></li>',
				esc_html( (string) ( $fee->name ?? '' ) ),
				wp_kses_post( (string) wc_price( $amount ) )
			);
		}

		return '' !== $out ? '<ul class="mini-cart__fees">' . $out . '</ul>' : '';
	}

	/**
	 * Up to three motifs the cart does not hold, each added with one click,
	 * at the price the next towel actually costs.
	 *
	 * @param array<string,mixed> $offer Upsell::drawer_offer().
	 * @return string
	 */
	private function picks( array $offer ): string {
		$motifs = (array) ( $offer['motifs'] ?? array() );

		if ( ! $motifs ) {
			return '';
		}

		$towel = $offer['towel'] ?? null;

		if ( null !== $towel && (int) $towel['price'] < 1 ) {
			$title = __( 'Sledeći peškir je gratis', 'cosypaw' );
		} elseif ( null !== $towel ) {
			/* translators: %s: formatted price of one more towel, e.g. "490 RSD". */
			$title = sprintf( __( 'Sledeći peškir za %s', 'cosypaw' ), Catalog::format_price( (int) $towel['price'] ) );
		} else {
			$title = __( 'Dodajte još', 'cosypaw' );
		}

		$tiles = '';

		foreach ( $motifs as $product_id => $motif ) {
			$full  = (int) ( $motif['price'] ?? 0 );
			$price = null !== $towel ? min( $full, (int) $towel['price'] ) : $full;

			$tiles .= sprintf(
				'<li class="mini-cart__pick">' .
					'<img src="%1$s" width="96" height="96" alt="" loading="lazy" decoding="async">' .
					'<span class="mini-cart__pick-name">%2$s</span>' .
					'<span class="mini-cart__pick-price">%3$s%4$s</span>' .
					'<button type="button" class="mini-cart__pick-add" data-add="%5$d" aria-label="%6$s">%7$s</button>' .
				'</li>',
				esc_url( (string) ( $motif['image_th'] ?? $motif['image_sm'] ?? '' ) ),
				esc_html( (string) ( $motif['name'] ?? '' ) ),
				$price < $full ? '<del>' . esc_html( Catalog::format_price( $full ) ) . '</del> ' : '',
				esc_html( $price < 1 ? __( 'Gratis', 'cosypaw' ) : Catalog::format_price( $price ) ),
				(int) $product_id,
				esc_attr( sprintf( /* translators: %s: towel name. */ __( 'Dodaj u korpu: %s', 'cosypaw' ), (string) ( $motif['name'] ?? '' ) ) ),
				esc_html__( '+ Dodaj', 'cosypaw' )
			);
		}

		return sprintf(
			'<div class="mini-cart__picks"><p class="mini-cart__picks-title">%1$s</p><ul>%2$s</ul></div>',
			esc_html( $title ),
			$tiles
		);
	}

	/**
	 * The total, what delivery costs, and the way on.
	 *
	 * @param \WC_Cart            $cart  Cart.
	 * @param array<string,mixed> $offer Upsell::drawer_offer().
	 * @return string
	 */
	private function foot( \WC_Cart $cart, array $offer ): string {
		// The pill's total: contents plus fees, so the package saving is in
		// it and the courier's postage is not.
		$total    = (float) $cart->get_cart_contents_total() + (float) $cart->get_fee_total();
		$shipping = $offer['shipping'] ?? null;

		if ( null !== $shipping && 'earned' === $shipping['state'] ) {
			$delivery = __( 'Dostava: besplatna', 'cosypaw' );
		} elseif ( '' !== (string) $offer['postage'] ) {
			/* translators: %s: courier price range, e.g. "400–600 RSD". */
			$delivery = sprintf( __( 'Dostava: %s, plaća se kuriru', 'cosypaw' ), (string) $offer['postage'] );
		} else {
			$delivery = '';
		}

		return sprintf(
			'<div class="mini-cart__foot">' .
				'<div class="mini-cart__total"><span>%1$s</span><strong>%2$s</strong></div>' .
				'%3$s' .
				'<a class="btn btn--primary mini-cart__checkout" href="%4$s">%5$s</a>' .
				'<a class="mini-cart__view" href="%6$s">%7$s</a>' .
			'</div>',
			esc_html__( 'Ukupno', 'cosypaw' ),
			wp_kses_post( (string) wc_price( $total ) ),
			'' !== $delivery ? '<p class="mini-cart__delivery">' . esc_html( $delivery ) . '</p>' : '',
			esc_url( function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/' ) ),
			esc_html__( 'Završi porudžbinu', 'cosypaw' ),
			esc_url( function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/' ) ),
			esc_html__( 'Pogledaj korpu', 'cosypaw' )
		);
	}

	/**
	 * The session cart, where there is one.
	 *
	 * @return \WC_Cart|null
	 */
	private function cart(): ?\WC_Cart {
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return null;
		}

		return WC()->cart instanceof \WC_Cart ? WC()->cart : null;
	}
}
