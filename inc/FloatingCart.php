<?php
/**
 * FloatingCart — the cart pill that follows the page, bottom right.
 *
 * The header is sticky, so the nav cart never scrolls away and this is not
 * about reaching the cart. It is about the two things the 18px nav badge has
 * no room for: what the cart costs, and what one more towel would cost under
 * the package pricing. On a phone it also moves the cart out of the top-right
 * corner, which is the furthest point from a thumb.
 *
 * Renders on every front-end view except the cart and checkout — being offered
 * a shortcut to the page you are already reading is noise — and stays in the
 * markup while the cart is empty so WooCommerce's add-to-cart fragments have
 * something to replace.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

namespace Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FloatingCart.
 */
final class FloatingCart {

	/**
	 * Theme text domain.
	 *
	 * @var string
	 */
	private string $text_domain;

	/**
	 * Package pricing, for the "one more towel" line.
	 *
	 * @var BundlePricing
	 */
	private BundlePricing $pricing;

	/**
	 * Constructor.
	 *
	 * @param string        $text_domain Theme text domain.
	 * @param BundlePricing $pricing     Package pricing module.
	 */
	public function __construct( string $text_domain, BundlePricing $pricing ) {
		$this->text_domain = $text_domain;
		$this->pricing     = $pricing;

		add_action( 'wp_footer', array( $this, 'render' ) );
		add_filter( 'woocommerce_add_to_cart_fragments', array( $this, 'fragment' ) );
	}

	/**
	 * Print the pill at the end of the document.
	 *
	 * @return void
	 */
	public function render(): void {
		if ( is_cart() || is_checkout() ) {
			return;
		}

		echo $this->markup(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup() escapes every part it interpolates.
	}

	/**
	 * Hand WooCommerce a fresh pill after an AJAX add-to-cart.
	 *
	 * Keyed by the class rather than a data attribute because WooCommerce feeds
	 * the key straight to jQuery as a selector and replaces the whole element,
	 * so the replacement has to carry the same hook back.
	 *
	 * @param array<string,string> $fragments Cart fragments (selector => HTML).
	 * @return array<string,string>
	 */
	public function fragment( array $fragments ): array {
		$fragments['a.cart-fab'] = $this->markup();

		return $fragments;
	}

	/**
	 * The pill itself.
	 *
	 * @return string
	 */
	private function markup(): string {
		if ( ! function_exists( 'WC' ) || ! WC()->cart || ! function_exists( 'wc_get_cart_url' ) ) {
			return '';
		}

		$cart  = WC()->cart;
		$count = (int) $cart->get_cart_contents_count();

		// An empty cart still ships the element: a fragment can only replace a
		// selector that is already on the page, and hiding it here rather than
		// skipping it is what lets the first add-to-cart bring it back.
		$hidden = $count < 1 ? ' hidden' : '';
		$total  = $count > 0 ? (string) $cart->get_cart_total() : '';
		$step   = $count > 0 ? $this->pricing->next_step( $this->pricing->cart_towels( $cart ) ) : null;

		$nudge = '';
		if ( null !== $step ) {
			$nudge = sprintf(
				'<span class="cart-fab__nudge">%s</span>',
				esc_html(
					sprintf(
						/* translators: %s: formatted price of one more towel, e.g. "500 RSD". */
						__( 'Još 1 peškirić za %s', 'cosypaw' ),
						Catalog::format_price( $step['price'] )
					)
				)
			);
		}

		return sprintf(
			'<a href="%1$s" class="cart-fab"%2$s>' .
				'<span class="screen-reader-text">%3$s</span>' .
				'<span class="cart-fab__icon" aria-hidden="true">%4$s<span class="cart-fab__count">%5$s</span></span>' .
				'<span class="cart-fab__text"><span class="cart-fab__total">%6$s</span>%7$s</span>' .
			'</a>',
			esc_url( wc_get_cart_url() ),
			$hidden,
			esc_html__( 'Pogledaj korpu', 'cosypaw' ),
			$this->icon(),
			esc_html( (string) $count ),
			wp_kses_post( $total ),
			$nudge
		);
	}

	/**
	 * The cart glyph, matching the one in the header.
	 *
	 * @return string
	 */
	private function icon(): string {
		return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M6 7h13l-1.2 8.4a2 2 0 0 1-2 1.7H9.2a2 2 0 0 1-2-1.7L6 4H3"/><circle cx="9.5" cy="20" r="1.2"/><circle cx="16.5" cy="20" r="1.2"/></svg>';
	}
}
