<?php
/**
 * Upsell — the package argument on the three pages that were not making it.
 *
 * The shop's whole offer is "the more towels, the cheaper each one gets". The
 * landing says so, the product page says so, and the floating pill says so.
 * The cart, the checkout and the thank-you page said nothing at all — and
 * those are the only three views a visitor reaches *after* they have already
 * decided to spend money, which is the cheapest moment there is to sell one
 * more towel.
 *
 * Every number here is derived, never authored. The marginal price of the next
 * towel comes from BundlePricing's own plan, so the two can never disagree,
 * and the free-delivery threshold is read off the shipping method that will
 * actually grant it rather than copied from the design. A reprice in wp-admin
 * moves both, or silences them; it cannot leave a claim behind that the
 * checkout will not honour. Where a number cannot be resolved this module
 * renders nothing, which is the honest outcome — an offer the cart cannot keep
 * is worse than no offer.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

namespace Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Upsell.
 */
final class Upsell {

	/**
	 * Query flag that keeps an add-to-cart on the page it was clicked from.
	 *
	 * Without it WooCommerce::add_to_cart_redirect() sends every add to the
	 * product page — right for a click in the motif grid, wrong for a click in
	 * the cart, which would answer "one more, please" by throwing the cart
	 * away and showing a product. Read back in that method; the value is an
	 * allowlisted page name, never a URL, so it can carry no open redirect.
	 *
	 * @var string
	 */
	public const STAY_PARAM = 'cosypaw-stay';

	/**
	 * Stay-here values.
	 *
	 * @var string
	 */
	public const STAY_CART     = 'cart';
	public const STAY_CHECKOUT = 'checkout';

	/**
	 * How many motifs the thank-you page offers.
	 *
	 * Three: one row on a phone, and few enough to read as a suggestion rather
	 * than a second catalogue on a page whose job is already done.
	 *
	 * @var int
	 */
	private const PICKS = 3;

	/**
	 * Theme text domain.
	 *
	 * @var string
	 */
	private string $text_domain;

	/**
	 * Package pricing — the source of every marginal price quoted here.
	 *
	 * @var BundlePricing
	 */
	private BundlePricing $pricing;

	/**
	 * Catalog data provider.
	 *
	 * @var Catalog
	 */
	private Catalog $catalog;

	/**
	 * Memoised product id => catalog motif row, for the current request.
	 *
	 * @var array<int,array<string,mixed>>|null
	 */
	private ?array $motifs = null;

	/**
	 * Constructor.
	 *
	 * @param string        $text_domain Theme text domain.
	 * @param BundlePricing $pricing     Package pricing module.
	 * @param Catalog       $catalog     Catalog data provider.
	 */
	public function __construct( string $text_domain, BundlePricing $pricing, Catalog $catalog ) {
		$this->text_domain = $text_domain;
		$this->pricing     = $pricing;
		$this->catalog     = $catalog;

		// Inside the collaterals, beside the totals. Under the cart table it was
		// a full-width band with the whole left half of the totals row empty
		// underneath it — two stacked blocks where the page had room for one
		// line. Priority 5 puts it in front of the cross-sells and the totals,
		// which is the reading order the grid then lays out left to right.
		add_action( 'woocommerce_cart_collaterals', array( $this, 'cart_panel' ), 5 );

		// Above the checkout form, not above the Place order button. The offer
		// is a link, and following a link from the middle of a half-typed
		// checkout costs the buyer their address — a nudge that expensive is
		// not worth the towel it sells. Here it is read before any typing has
		// started, when leaving costs nothing.
		add_action( 'woocommerce_before_checkout_form', array( $this, 'checkout_panel' ), 5 );

		// An empty cart offers one thing: a link back to the shop. The towels
		// themselves answer better than a link to the page that lists them.
		// Not on woocommerce_cart_is_empty — that action fires above the
		// "Return to shop" button, and the strip belongs under the message it
		// answers. Priority 5 keeps it inside WooCommerce::close_content_wrapper().
		add_action( 'woocommerce_after_main_content', array( $this, 'empty_cart_picks' ), 5 );

		add_action( 'woocommerce_thankyou', array( $this, 'thankyou_picks' ), 15 );
	}

	/**
	 * The catalogue as a strip under an empty cart.
	 *
	 * The same strip the filled cart carries beside its totals, with nothing in
	 * the cart to subtract from it — so here it is the whole sellable range.
	 * Fires on every WooCommerce view, so it checks the one it belongs on.
	 *
	 * @return void
	 */
	public function empty_cart_picks(): void {
		$cart = $this->cart();

		if ( ! function_exists( 'is_cart' ) || ! is_cart() || null === $cart || ! $cart->is_empty() ) {
			return;
		}

		// A shop with nothing sellable in it gets no heading over an empty rail.
		if ( ! $this->motifs() ) {
			return;
		}

		echo '<div class="cosypaw-upsell cosypaw-upsell--empty" data-upsell-panel>';

		printf(
			'<span class="cosypaw-upsell__title">%s</span>',
			esc_html__( 'Možda vam se svidi', 'cosypaw' )
		);

		$this->motif_slider( self::STAY_CART );

		echo '</div>';
	}

	/**
	 * The cart panel: how far to free delivery, and what one more towel costs.
	 *
	 * @return void
	 */
	public function cart_panel(): void {
		$this->panel( self::STAY_CART, true );
	}

	/**
	 * The same offer at the top of the checkout, without the progress bar.
	 *
	 * @return void
	 */
	public function checkout_panel(): void {
		$this->panel( self::STAY_CHECKOUT, false );
	}

	/**
	 * The slot the offer lives in — printed even when there is no offer.
	 *
	 * WooCommerce's cart script answers an AJAX remove or quantity change by
	 * replacing exactly two nodes of the re-rendered page: the cart form and
	 * `.cart_totals` (see update_wc_div() in cart.js). Everything else in the
	 * collaterals, this offer included, keeps whatever it said before the
	 * change — so a cart that dropped from three towels back to two kept the
	 * silence a whole Trio had earned. CartUpsell re-renders this slot from the
	 * same response WooCommerce is already holding, which needs the slot to be
	 * on the page in both states: it is the anchor an offer comes back into.
	 *
	 * Left empty it is an unstyled, marginless div — a grid cell holding the
	 * totals in their column, and nothing at all to look at.
	 *
	 * @param string $stay Which page an add-to-cart from here returns to.
	 * @param bool   $bar  Whether to draw the free-delivery progress bar.
	 * @return void
	 */
	private function panel( string $stay, bool $bar ): void {
		// No whitespace inside the wrapper: the CSS that collapses the empty
		// slot on a phone matches :empty, which a stray newline would defeat.
		echo '<div class="cosypaw-upsell-slot" data-upsell-panel>';
		$this->offer( $stay, $bar );
		echo '</div>';
	}

	/**
	 * Render the offer itself, where there is one to make.
	 *
	 * @param string $stay Which page an add-to-cart from here returns to.
	 * @param bool   $bar  Whether to draw the free-delivery progress bar.
	 * @return void
	 */
	private function offer( string $stay, bool $bar ): void {
		$towel    = $this->next_towel();
		$shipping = $this->free_shipping();

		// Nothing to say: a cart already at a package optimum, with delivery
		// settled, is not owed a banner.
		if ( null === $towel && ( null === $shipping || 'gap' !== $shipping['state'] ) ) {
			return;
		}

		// One towel away from free delivery is a single sentence, not two
		// competing ones — see towel_line().
		$closes = null !== $towel && null !== $shipping && 'gap' === $shipping['state'] && $shipping['gap'] <= $shipping['unit'];

		echo '<div class="cosypaw-upsell">';

		printf(
			'<span class="cosypaw-upsell__title">%s</span>',
			esc_html__( 'Još malo, pa povoljnije', 'cosypaw' )
		);

		if ( null !== $towel ) {
			$this->towel_line( $towel, $closes );
		}

		if ( $bar && null !== $shipping && 'gap' === $shipping['state'] && ! $closes ) {
			$this->shipping_bar( $shipping );
		}

		$this->motif_slider( $stay );

		echo '</div>';
	}

	/**
	 * The "one more towel" line: what the next towel costs and what it saves.
	 *
	 * @param array{price:int,saving:int} $towel  Marginal towel.
	 * @param bool                        $closes Whether it also wins free delivery.
	 * @return void
	 */
	private function towel_line( array $towel, bool $closes ): void {
		$price = Catalog::format_price( $towel['price'] );

		$copy = $closes
			/* translators: %s: formatted price of one more towel, e.g. "490 RSD". */
			? sprintf( __( 'Još jedan peškirić košta %s — i dostava je na nama.', 'cosypaw' ), $price )
			: sprintf(
				/* translators: 1: formatted price of one more towel, 2: formatted saving against the single price. */
				__( 'Još jedan peškirić košta %1$s umesto pune cene — ušteda %2$s.', 'cosypaw' ),
				$price,
				Catalog::format_price( $towel['saving'] )
			);

		printf( '<p class="cosypaw-upsell__copy">%s</p>', esc_html( $copy ) );
	}

	/**
	 * The catalogue, as a strip that adds a towel on click.
	 *
	 * The offer used to be a button for one more of whatever was already in the
	 * cart, which is the one towel the buyer has already chosen — a second copy
	 * of it is the least interesting thing on sale. The strip lists the motifs
	 * the cart does *not* have, so the marginal price above is spent on a towel
	 * they have not seen up close yet.
	 *
	 * Each tile is a plain add-to-cart link: WooCommerce's AJAX add does not
	 * redraw the cart table, so on this page a reload is the honest answer to
	 * the click, and the link carries the page it was clicked from back with it.
	 *
	 * @param string $stay Which page an add-to-cart from here returns to.
	 * @return void
	 */
	private function motif_slider( string $stay ): void {
		$in_cart = $this->cart_motif_ids();
		$offer   = array();

		foreach ( $this->motifs() as $product_id => $motif ) {
			if ( ! in_array( $product_id, $in_cart, true ) ) {
				$offer[ $product_id ] = $motif;
			}
		}

		// A cart holding one of everything is not owed an empty rail; the
		// catalogue itself is then the offer.
		if ( ! $offer ) {
			return;
		}

		echo '<div class="cosypaw-upsell__slider" data-upsell-slider>';

		// Hidden until the script measures an overflow: without one they are
		// two buttons that scroll nothing, and without script they never work.
		printf(
			'<button type="button" class="cosypaw-upsell__nav cosypaw-upsell__nav--prev" data-upsell-prev aria-label="%s" hidden>%s</button>',
			esc_attr__( 'Prethodni peškirići', 'cosypaw' ),
			$this->chevron( true ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup.
		);

		echo '<ul class="cosypaw-upsell__track" data-upsell-track>';

		foreach ( $offer as $product_id => $motif ) {
			printf(
				'<li class="cosypaw-upsell__slide">' .
					'<a class="cosypaw-upsell__pick" href="%1$s" rel="nofollow">' .
						'<img class="cosypaw-upsell__img" src="%2$s" width="192" height="192" alt="" loading="lazy" decoding="async">' .
						'<span class="cosypaw-upsell__name">%3$s</span>' .
						'<span class="cosypaw-upsell__price">%4$s</span>' .
						'<span class="cosypaw-upsell__add">%5$s</span>' .
					'</a>' .
				'</li>',
				esc_url( $this->add_url( $product_id, $stay ) ),
				esc_url( (string) ( $motif['image_th'] ?? $motif['image_sm'] ?? '' ) ),
				esc_html( (string) ( $motif['name'] ?? '' ) ),
				esc_html( Catalog::format_price( (int) ( $motif['price'] ?? 0 ) ) ),
				esc_html__( 'Dodaj', 'cosypaw' )
			);
		}

		echo '</ul>';

		printf(
			'<button type="button" class="cosypaw-upsell__nav cosypaw-upsell__nav--next" data-upsell-next aria-label="%s" hidden>%s</button>',
			esc_attr__( 'Sledeći peškirići', 'cosypaw' ),
			$this->chevron( false ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup.
		);

		echo '</div>';
	}

	/**
	 * A nav chevron.
	 *
	 * @param bool $back Whether it points back.
	 * @return string
	 */
	private function chevron( bool $back ): string {
		return sprintf(
			'<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="%s"/></svg>',
			$back ? 'm15 6-6 6 6 6' : 'm9 6 6 6-6 6'
		);
	}

	/**
	 * How far the cart is from free delivery, drawn as a bar.
	 *
	 * @param array{state:string,gap:int,min:int,pct:int,unit:int} $shipping Threshold state.
	 * @return void
	 */
	private function shipping_bar( array $shipping ): void {
		printf(
			'<p class="cosypaw-upsell__ship">%s</p>',
			esc_html(
				sprintf(
					/* translators: %s: formatted amount still to spend, e.g. "400 RSD". */
					__( 'Još %s do besplatne dostave', 'cosypaw' ),
					Catalog::format_price( $shipping['gap'] )
				)
			)
		);

		// aria-hidden: the sentence above is the accessible version of this,
		// and a progress element would otherwise announce the same fact twice.
		printf(
			'<span class="cosypaw-upsell__bar" aria-hidden="true"><span class="cosypaw-upsell__bar-fill" style="width:%d%%"></span></span>',
			(int) $shipping['pct']
		);
	}

	/**
	 * Motifs to look at now that the order is placed.
	 *
	 * Post-purchase, so nothing here is a discount or a countdown: the order is
	 * done and re-opening the sale would only unsettle it. It is the rest of
	 * the range, minus what was just bought, for the buyer who is still curious.
	 *
	 * @param mixed $order_id Order id, as WooCommerce passes it.
	 * @return void
	 */
	public function thankyou_picks( $order_id = 0 ): void {
		$order = function_exists( 'wc_get_order' ) ? wc_get_order( (int) $order_id ) : null;

		if ( ! $order || ! is_callable( array( $order, 'get_items' ) ) ) {
			return;
		}

		// The same template renders a failed or unpaid order, where the page is
		// asking for payment rather than confirming anything. Offering more
		// towels over the top of that would be answering the wrong question.
		$status = is_callable( array( $order, 'get_status' ) ) ? (string) $order->get_status() : '';

		if ( in_array( $status, array( 'failed', 'cancelled', 'pending' ), true ) ) {
			return;
		}

		$bought = array();
		foreach ( (array) $order->get_items() as $item ) {
			if ( is_callable( array( $item, 'get_product_id' ) ) ) {
				$bought[] = (int) $item->get_product_id();
			}
		}

		$picks = array();
		foreach ( $this->motifs() as $product_id => $motif ) {
			if ( in_array( $product_id, $bought, true ) ) {
				continue;
			}

			$picks[] = $motif;

			if ( count( $picks ) >= self::PICKS ) {
				break;
			}
		}

		if ( ! $picks ) {
			return;
		}

		echo '<section class="cosypaw-picks">';
		printf(
			'<h2 class="cosypaw-picks__title">%s</h2>',
			esc_html__( 'Ostatak družine te čeka', 'cosypaw' )
		);

		echo '<div class="cosypaw-picks__grid">';

		foreach ( $picks as $motif ) {
			$link = (string) ( $motif['permalink'] ?? '' );

			if ( '' === $link ) {
				continue;
			}

			printf(
				'<a class="cosypaw-picks__card" href="%1$s">' .
					'<img class="cosypaw-picks__img" src="%2$s" width="360" height="360" alt="" loading="lazy" decoding="async">' .
					'<span class="cosypaw-picks__name">%3$s</span>' .
					'<span class="cosypaw-picks__price">%4$s</span>' .
				'</a>',
				esc_url( $link ),
				esc_url( (string) ( $motif['image_sm'] ?? '' ) ),
				esc_html( (string) ( $motif['name'] ?? '' ) ),
				esc_html( Catalog::format_price( (int) ( $motif['price'] ?? 0 ) ) )
			);
		}

		echo '</div></section>';
	}

	/**
	 * What one more towel costs.
	 *
	 * @return array{price:int,saving:int}|null
	 */
	private function next_towel(): ?array {
		$cart = $this->cart();

		if ( null === $cart ) {
			return null;
		}

		$step = $this->pricing->next_step( $this->pricing->cart_towels( $cart ) );

		if ( null === $step ) {
			return null;
		}

		return array(
			'price'  => (int) $step['price'],
			'saving' => (int) $step['saving'],
		);
	}

	/**
	 * The motifs the cart already holds.
	 *
	 * What the strip leaves out. A package counts for none of them: its motifs
	 * are item data rather than products, and the Trio a buyer assembled says
	 * nothing about which single towels they have seen.
	 *
	 * @return int[]
	 */
	private function cart_motif_ids(): array {
		$cart = $this->cart();

		if ( null === $cart ) {
			return array();
		}

		$motifs = $this->motifs();
		$found  = array();

		foreach ( $cart->get_cart() as $item ) {
			$product_id = (int) ( $item['product_id'] ?? 0 );

			if ( isset( $motifs[ $product_id ] ) ) {
				$found[] = $product_id;
			}
		}

		return $found;
	}

	/**
	 * Where the cart stands against the free-delivery threshold.
	 *
	 * Read off the shipping zone that will actually price this order, in the
	 * same terms WC_Shipping_Free_Shipping compares — the displayed subtotal
	 * less discounts. The bundle saving is booked as a *fee* and is therefore
	 * not in that number, which is correct: WooCommerce will not count it
	 * either when it decides whether delivery is free.
	 *
	 * Returns null wherever the answer cannot be established — no shipping
	 * needed, no free-delivery method, an unreadable cart — so a missing
	 * threshold is silence rather than a guess.
	 *
	 * @return array{state:string,gap:int,min:int,pct:int,unit:int}|null
	 */
	private function free_shipping(): ?array {
		$cart = $this->cart();

		if ( null === $cart || ! is_callable( array( $cart, 'needs_shipping' ) ) || ! $cart->needs_shipping() ) {
			return null;
		}

		if ( ! is_callable( array( WC(), 'shipping' ) ) || ! class_exists( '\WC_Shipping_Zones' ) ) {
			return null;
		}

		$packages = (array) WC()->shipping()->get_packages();

		if ( ! $packages ) {
			return null;
		}

		$min = 0.0;

		foreach ( $packages as $package ) {
			foreach ( (array) ( $package['rates'] ?? array() ) as $rate ) {
				// Already offered free delivery: the threshold is behind them,
				// and the panel says so instead of asking for more.
				if ( is_callable( array( $rate, 'get_method_id' ) ) && 'free_shipping' === $rate->get_method_id() ) {
					return array(
						'state' => 'earned',
						'gap'   => 0,
						'min'   => 0,
						'pct'   => 100,
						'unit'  => 0,
					);
				}
			}

			$zone = \WC_Shipping_Zones::get_zone_matching_package( $package );

			if ( ! $zone || ! is_callable( array( $zone, 'get_shipping_methods' ) ) ) {
				continue;
			}

			foreach ( (array) $zone->get_shipping_methods( true ) as $method ) {
				if ( 'free_shipping' !== ( $method->id ?? '' ) ) {
					continue;
				}

				// 'both' also wants a coupon, so spending alone cannot promise
				// anything; 'either' and 'min_amount' are won at the till.
				if ( ! in_array( (string) ( $method->requires ?? '' ), array( 'min_amount', 'either' ), true ) ) {
					continue;
				}

				$amount = (float) ( $method->min_amount ?? 0 );

				if ( $amount > 0.0 && ( $min <= 0.0 || $amount < $min ) ) {
					$min = $amount;
				}
			}
		}

		if ( $min <= 0.0 || ! is_callable( array( $cart, 'get_displayed_subtotal' ) ) ) {
			return null;
		}

		$total = (float) $cart->get_displayed_subtotal();

		if ( is_callable( array( $cart, 'get_discount_total' ) ) ) {
			$total -= (float) $cart->get_discount_total();
		}

		$gap = (int) ceil( $min - $total );

		if ( $gap < 1 ) {
			return null;
		}

		return array(
			'state' => 'gap',
			'gap'   => $gap,
			'min'   => (int) round( $min ),
			'pct'   => (int) max( 0, min( 100, round( $total / $min * 100 ) ) ),
			'unit'  => $this->unit_price(),
		);
	}

	/**
	 * What a single towel adds to the subtotal the threshold is measured on.
	 *
	 * The catalogue's cheapest motif, because that is the least a towel can
	 * add — quoting the dearest would claim free delivery one towel too early.
	 *
	 * @return int
	 */
	private function unit_price(): int {
		$prices = array();

		foreach ( $this->motifs() as $motif ) {
			$price = (int) ( $motif['price'] ?? 0 );

			if ( $price > 0 ) {
				$prices[] = $price;
			}
		}

		return $prices ? (int) min( $prices ) : Catalog::UNIT_PRICE;
	}

	/**
	 * Add-to-cart URL that comes back to the page it was clicked from.
	 *
	 * @param int    $product_id Product to add.
	 * @param string $stay       Allowlisted page name; see STAY_PARAM.
	 * @return string
	 */
	private function add_url( int $product_id, string $stay ): string {
		$base = self::STAY_CHECKOUT === $stay && function_exists( 'wc_get_checkout_url' )
			? (string) wc_get_checkout_url()
			: (string) ( function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/' ) );

		return add_query_arg(
			array(
				'add-to-cart'    => $product_id,
				self::STAY_PARAM => $stay,
			),
			$base
		);
	}

	/**
	 * Sellable motifs, keyed by product id.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	private function motifs(): array {
		if ( null !== $this->motifs ) {
			return $this->motifs;
		}

		$motifs = array();

		foreach ( $this->catalog->products() as $motif ) {
			$product_id = (int) ( $motif['product_id'] ?? 0 );

			// Rows carry no 'available' key at all until WooCommerce injects
			// one; a retired motif carries it as false and must not be offered.
			if ( $product_id > 0 && false !== ( $motif['available'] ?? true ) ) {
				$motifs[ $product_id ] = $motif;
			}
		}

		$this->motifs = $motifs;

		return $this->motifs;
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
