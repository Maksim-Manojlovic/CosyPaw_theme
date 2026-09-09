<?php
/**
 * BundlePricing — cart-level package pricing for loose towels.
 *
 * The shop sells the same towel two ways: as a motif product bought on its own
 * from the grid, and as a Duo/Trio package assembled in the bundle builder.
 * Nothing connected the two, so a shopper who clicked "Kupi" three times paid
 * three single prices while the identical three towels cost a Trio price next
 * to it. This module closes that gap: it counts every towel in the cart, works
 * out the cheapest way the shop itself would sell that many, and books the
 * difference as a negative fee.
 *
 * It never edits line prices or swaps cart items — the motif lines stay at
 * their own price so the shopper can still recognise what they clicked, and a
 * single "Ušteda na paketima" row carries the whole saving.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

namespace Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BundlePricing.
 */
final class BundlePricing {

	/**
	 * Theme text domain.
	 *
	 * @var string
	 */
	private string $text_domain;

	/**
	 * Catalog data provider.
	 *
	 * @var Catalog
	 */
	private Catalog $catalog;

	/**
	 * Memoised tier list for the current request.
	 *
	 * packages() runs the `cosypaw_catalog_packages` filter, which hits
	 * wc_get_product() once per package. The fee hook fires on every
	 * calculate_totals() call — several times per checkout request — so the
	 * resolved tiers are cached rather than re-queried each time.
	 *
	 * @var array<int,array{id:string,name:string,qty:int,price:int}>|null
	 */
	private ?array $tiers = null;

	/**
	 * Memoised product id => towels-per-unit map for the current request.
	 *
	 * @var array<int,int>|null
	 */
	private ?array $units = null;

	/**
	 * Constructor.
	 *
	 * @param string  $text_domain Theme text domain.
	 * @param Catalog $catalog     Catalog data provider.
	 */
	public function __construct( string $text_domain, Catalog $catalog ) {
		$this->text_domain = $text_domain;
		$this->catalog     = $catalog;

		add_action( 'woocommerce_cart_calculate_fees', array( $this, 'apply_bundle_discount' ) );

		// Free delivery has to be judged on what the order is worth after this
		// module has repriced it. Three towels clicked one at a time are 2.070
		// in line items and 1.390 to pay, and WooCommerce measures only the
		// first of those — so the same three towels won free delivery bought
		// loose and lost it bought as the Trio, which is the one thing the
		// bundle discount exists to make identical.
		//
		// Priority 5, ahead of CheckoutSetup::hide_paid_delivery_when_free()
		// at 10: withdraw the free rate first, so the courier rate it would
		// otherwise remove is still there to fall back on.
		add_filter( 'woocommerce_package_rates', array( $this, 'require_threshold_after_saving' ), 5 );
	}

	/**
	 * Withdraw free delivery from a cart that only clears the bar before its
	 * package saving.
	 *
	 * WC_Shipping_Free_Shipping::is_available() compares the line items less
	 * coupons; a fee is neither, so WooCommerce cannot see this discount. It is
	 * not a coupon by accident — booking it as a fee is what keeps the motif
	 * lines at their own prices, so the shopper still recognises what they
	 * clicked. The consequence is that the threshold has to be re-tested here.
	 *
	 * @param array<string,object> $rates Shipping rates (\WC_Shipping_Rate at runtime).
	 * @return array<string,object>
	 */
	public function require_threshold_after_saving( array $rates ): array {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return $rates;
		}

		$threshold = CheckoutSetup::free_shipping_threshold();

		if ( $threshold < 1 ) {
			return $rates;
		}

		$cart = function_exists( 'WC' ) && WC()->cart ? WC()->cart : null;

		if ( ! $cart instanceof \WC_Cart || ! is_callable( array( $cart, 'get_displayed_subtotal' ) ) ) {
			return $rates;
		}

		if ( $this->payable_total( $cart ) >= (float) $threshold ) {
			return $rates;
		}

		foreach ( $rates as $key => $rate ) {
			if ( is_callable( array( $rate, 'get_method_id' ) ) && 'free_shipping' === $rate->get_method_id() ) {
				unset( $rates[ $key ] );
			}
		}

		return $rates;
	}

	/**
	 * What the cart is actually worth: line items, less coupons, less the
	 * package saving this module books.
	 *
	 * The saving is recomputed rather than read off the cart. WC_Cart_Totals
	 * calculates shipping *before* fees, so during a rate filter the cart's own
	 * fee total is still the previous calculation's — or zero on the first one.
	 * Asking plan_for() again is the only way to get an answer that belongs to
	 * the cart being rated.
	 *
	 * @param \WC_Cart $cart The cart being measured.
	 * @return float
	 */
	public function payable_total( \WC_Cart $cart ): float {
		$total = (float) $cart->get_displayed_subtotal();

		if ( is_callable( array( $cart, 'get_discount_total' ) ) ) {
			$total -= (float) $cart->get_discount_total();
		}

		return $total - (float) $this->saving( $cart );
	}

	/**
	 * The package saving this cart earns, in whole RSD.
	 *
	 * @param \WC_Cart $cart The cart being priced.
	 * @return int Saving, or 0 where the cart earns none.
	 */
	public function saving( \WC_Cart $cart ): int {
		$plan = $this->plan_for( $cart );

		return null === $plan ? 0 : $plan['discount'];
	}

	/**
	 * Book the package saving as a negative cart fee.
	 *
	 * Hooked on `woocommerce_cart_calculate_fees`, which WooCommerce fires
	 * inside every totals calculation after the item totals are known and
	 * after any previously added fees have been cleared — so this may add its
	 * fee unconditionally without checking for a duplicate.
	 *
	 * @param \WC_Cart|null $cart The cart being calculated.
	 * @return void
	 */
	public function apply_bundle_discount( $cart = null ): void {
		if ( ! $cart instanceof \WC_Cart ) {
			$cart = function_exists( 'WC' ) && WC()->cart ? WC()->cart : null;
		}

		if ( ! $cart instanceof \WC_Cart ) {
			return;
		}

		// wp-admin renders order screens against the cart in ways that would
		// double-book the fee; AJAX (the mini-cart, the bundle builder's add)
		// is a front-end request despite is_admin() being true for it.
		if ( is_admin() && ! wp_doing_ajax() ) {
			return;
		}

		$plan = $this->plan_for( $cart );

		if ( null === $plan ) {
			return;
		}

		$cart->add_fee( $this->fee_label( $plan['lines'] ), -$plan['discount'], false );
	}

	/**
	 * The cheapest package plan for this cart, and what it saves.
	 *
	 * Shared by the fee that books the saving and by every reader that has to
	 * agree with it — the free-delivery threshold above all, which would
	 * otherwise be answering a different question about the same cart.
	 *
	 * @param \WC_Cart $cart The cart being priced.
	 * @return array{discount:int,lines:array<string,int>}|null Null where the cart earns nothing.
	 */
	private function plan_for( \WC_Cart $cart ): ?array {
		$tiers = $this->tiers();
		if ( count( $tiers ) < 2 ) {
			return null;
		}

		$pool = $this->pool( $cart );
		if ( $pool['towels'] < 2 || $pool['subtotal'] <= 0.0 ) {
			return null;
		}

		$plan = self::plan( $pool['towels'], $tiers );
		if ( $plan['total'] < 1 ) {
			return null;
		}

		$plan = $this->upgrade_for_free_delivery( $plan, $pool['towels'], $tiers );

		// Rounded to whole RSD before comparing: the shop deals in dinars, and
		// a sub-dinar "saving" is rounding noise, not a discount.
		$discount = (int) round( $pool['subtotal'] ) - $plan['total'];
		if ( $discount < 1 ) {
			return null;
		}

		return array(
			'discount' => $discount,
			'lines'    => $plan['lines'],
		);
	}

	/**
	 * Trade the cheapest plan for a bigger package where that wins free
	 * delivery.
	 *
	 * Four towels are two Duos at 1.980 and a Trio plus a single at 2.080. The
	 * cheaper plan is the cheaper plan, and it stops 20 RSD short of the bar —
	 * so the shopper pays 100 less and then pays the courier, which is the
	 * worse of the two outcomes for them. Charging the 100 and carrying the
	 * delivery is the better basket, and it is also the one the shop's own
	 * front end offers: the builder sells one package at a time, so "two Duos"
	 * is an arrangement only this module ever proposed.
	 *
	 * Narrow on purpose. It fires only where the cheapest plan skips the
	 * largest package *and* taking it clears the threshold — otherwise the
	 * shopper would simply be charged more for nothing, which is what an
	 * unconditional "always fill with the biggest box" rule does at every
	 * count that does not happen to land past the bar.
	 *
	 * @param array{total:int,lines:array<string,int>}               $plan   Cheapest plan.
	 * @param int                                                    $towels Towels being priced.
	 * @param array<int,array{id:string,name:string,qty:int,price:int}> $tiers  Packages, largest first.
	 * @return array{total:int,lines:array<string,int>}
	 */
	private function upgrade_for_free_delivery( array $plan, int $towels, array $tiers ): array {
		$threshold = CheckoutSetup::free_shipping_threshold();

		if ( $threshold < 1 || $plan['total'] >= $threshold ) {
			return $plan;
		}

		$largest = $tiers[0] ?? null;
		$qty     = (int) ( $largest['qty'] ?? 0 );
		$price   = (int) ( $largest['price'] ?? 0 );

		// Nothing to upgrade to: too few towels for the largest package, or a
		// plan that already uses it and still falls short.
		if ( $qty < 1 || $price < 1 || $qty > $towels || ! empty( $plan['lines'][ $largest['id'] ] ) ) {
			return $plan;
		}

		$rest = self::plan( $towels - $qty, $tiers );

		if ( $towels > $qty && $rest['total'] < 1 ) {
			return $plan;
		}

		$total = $rest['total'] + $price;

		// Only worth it if it actually clears the bar. It cannot be cheaper —
		// the DP would have found it — so anything short of the threshold is
		// the shopper paying more and getting nothing.
		if ( $total < $threshold ) {
			return $plan;
		}

		// The largest package leads, which is the order tiers() is in and how
		// the breakdown reads on the cart row.
		$lines = array( (string) $largest['id'] => 1 );
		foreach ( $rest['lines'] as $id => $count ) {
			$lines[ $id ] = ( $lines[ $id ] ?? 0 ) + $count;
		}

		return array(
			'total' => $total,
			'lines' => $lines,
		);
	}

	/**
	 * Cheapest way the shop itself would sell this many towels.
	 *
	 * Exact, not greedy. Greedy — fill with the largest package, then the
	 * remainder — happens to be optimal at today's prices, but only by 10 RSD
	 * at four towels (Trio + single 2.970 against two Duos 2.980). One price
	 * edit in wp-admin flips that, and a greedy plan would then quietly
	 * overcharge. The DP below is O(towels x tiers) over a cart-sized number,
	 * so correctness costs nothing worth counting.
	 *
	 * Ties keep the plan found first, and tiers() hands the list over sorted
	 * largest-package-first, so an even split is described with the biggest
	 * packages that produce it.
	 *
	 * @param int                                                    $towels Towels to price.
	 * @param array<int,array{id:string,name:string,qty:int,price:int}> $tiers  Available packages.
	 * @return array{total:int,lines:array<string,int>} Total price, and how many of each tier id it uses.
	 */
	public static function plan( int $towels, array $tiers ): array {
		$empty = array(
			'total' => 0,
			'lines' => array(),
		);

		if ( $towels < 1 ) {
			return $empty;
		}

		$best    = array_fill( 0, $towels + 1, null );
		$best[0] = $empty;

		for ( $n = 1; $n <= $towels; $n++ ) {
			foreach ( $tiers as $tier ) {
				$qty   = (int) $tier['qty'];
				$price = (int) $tier['price'];

				if ( $qty < 1 || $price < 1 || $qty > $n ) {
					continue;
				}

				$prev = $best[ $n - $qty ];
				if ( null === $prev ) {
					continue;
				}

				$total = $prev['total'] + $price;
				if ( null !== $best[ $n ] && $total >= $best[ $n ]['total'] ) {
					continue;
				}

				$lines                = $prev['lines'];
				$lines[ $tier['id'] ] = ( $lines[ $tier['id'] ] ?? 0 ) + 1;
				$best[ $n ]           = array(
					'total' => $total,
					'lines' => $lines,
				);
			}
		}

		$plan = $best[ $towels ] ?? $empty;

		// The DP fills the small counts first, so a plan's lines come out in
		// the order the leftovers were settled — "1x Pojedinačno + 1x Trio
		// paket". Reorder largest package first, which is how the breakdown
		// reads on the cart row and how the packages page lists them.
		$sizes = array();
		foreach ( $tiers as $tier ) {
			$sizes[ (string) $tier['id'] ] = (int) $tier['qty'];
		}

		uksort(
			$plan['lines'],
			static fn( string $a, string $b ): int => ( $sizes[ $b ] ?? 0 ) <=> ( $sizes[ $a ] ?? 0 )
		);

		return $plan;
	}

	/**
	 * How many towels the cart holds, packages counted as their own quantity.
	 *
	 * @param \WC_Cart|null $cart Cart to count, or the session cart.
	 * @return int
	 */
	public function cart_towels( ?\WC_Cart $cart = null ): int {
		if ( ! $cart instanceof \WC_Cart ) {
			$cart = function_exists( 'WC' ) && WC()->cart ? WC()->cart : null;
		}

		return $cart instanceof \WC_Cart ? $this->pool( $cart )['towels'] : 0;
	}

	/**
	 * What one more towel would cost, when the packages make it cheap.
	 *
	 * The pill's nudge. It is the marginal price of the next towel under the
	 * best plan, not an invitation: at three towels the fourth costs full
	 * price, so nothing is offered rather than dressing a single up as a deal.
	 * That silence is correct — a cart sitting on a whole Trio is already at an
	 * optimum, and the next saving is two towels away, which is a bigger ask
	 * than a floating pill should make.
	 *
	 * @param int $towels Towels currently in the cart.
	 * @return array{price:int,saving:int}|null Marginal price and what it saves, or null.
	 */
	public function next_step( int $towels ): ?array {
		$tiers = $this->tiers();
		if ( $towels < 1 || count( $tiers ) < 2 ) {
			return null;
		}

		$single = 0;
		foreach ( $tiers as $tier ) {
			if ( 1 === $tier['qty'] ) {
				$single = $tier['price'];
				break;
			}
		}

		if ( $single < 1 ) {
			return null;
		}

		$marginal = $this->charged_total( $towels + 1, $tiers ) - $this->charged_total( $towels, $tiers );

		if ( $marginal < 1 || $marginal >= $single ) {
			return null;
		}

		return array(
			'price'  => $marginal,
			'saving' => $single - $marginal,
		);
	}

	/**
	 * What this many towels are actually charged.
	 *
	 * The cheapest plan, after upgrade_for_free_delivery() has had its say —
	 * which is the number the cart will bill. next_step() quotes a marginal
	 * price off two of these, and quoting it off the raw cheapest plan instead
	 * would advertise a fourth towel at 590 that the cart then charges 690 for.
	 *
	 * @param int                                                    $towels Towels to price.
	 * @param array<int,array{id:string,name:string,qty:int,price:int}> $tiers  Available packages.
	 * @return int
	 */
	private function charged_total( int $towels, array $tiers ): int {
		$plan = self::plan( $towels, $tiers );

		if ( $plan['total'] < 1 ) {
			return $plan['total'];
		}

		return $this->upgrade_for_free_delivery( $plan, $towels, $tiers )['total'];
	}

	/**
	 * The towels in the cart and what they currently cost.
	 *
	 * Both numbers come from the same pass so they can never describe
	 * different sets of items: whatever is counted as a towel is also what its
	 * price is taken from.
	 *
	 * @param \WC_Cart $cart The cart being calculated.
	 * @return array{towels:int,subtotal:float}
	 */
	private function pool( \WC_Cart $cart ): array {
		$units    = $this->units();
		$towels   = 0;
		$subtotal = 0.0;

		foreach ( $cart->get_cart() as $item ) {
			$product_id = (int) ( $item['product_id'] ?? 0 );
			$per_unit   = $units[ $product_id ] ?? 0;

			if ( $per_unit < 1 ) {
				continue;
			}

			$quantity = (int) ( $item['quantity'] ?? 0 );
			$product  = $item['data'] ?? null;

			if ( $quantity < 1 || ! $product instanceof \WC_Product ) {
				continue;
			}

			$price = $product->get_price();
			if ( '' === $price || ! is_numeric( $price ) ) {
				continue;
			}

			$towels   += $per_unit * $quantity;
			$subtotal += (float) $price * $quantity;
		}

		return array(
			'towels'   => $towels,
			'subtotal' => $subtotal,
		);
	}

	/**
	 * Packages the discount may be built from, largest first.
	 *
	 * Only mapped packages count. Catalog's seed prices are the design's
	 * numbers, not the shop's — pricing a live cart against them would invent
	 * a discount out of the gap between the two. An unseeded install therefore
	 * gets no bundle pricing at all, which is the honest outcome: without real
	 * package products there is no package price to offer.
	 *
	 * @return array<int,array{id:string,name:string,qty:int,price:int}>
	 */
	private function tiers(): array {
		if ( null !== $this->tiers ) {
			return $this->tiers;
		}

		$tiers = array();

		foreach ( $this->catalog->packages() as $package ) {
			$qty   = (int) ( $package['qty'] ?? 0 );
			$price = (int) ( $package['price'] ?? 0 );

			if ( $qty < 1 || $price < 1 || empty( $package['product_id'] ) ) {
				continue;
			}

			$tiers[] = array(
				'id'    => (string) ( $package['id'] ?? '' ),
				'name'  => (string) ( $package['name'] ?? '' ),
				'qty'   => $qty,
				'price' => $price,
			);
		}

		// A single-towel tier is what makes every count reachable; without it
		// the DP has no plan for four towels once the odd one is left over.
		$has_single = false;
		foreach ( $tiers as $tier ) {
			if ( 1 === $tier['qty'] ) {
				$has_single = true;
				break;
			}
		}

		if ( ! $has_single ) {
			$tiers = array();
		}

		usort( $tiers, static fn( array $a, array $b ): int => $b['qty'] <=> $a['qty'] );

		$this->tiers = $tiers;

		return $this->tiers;
	}

	/**
	 * Product id => how many towels one of it puts in the cart.
	 *
	 * Motif products are one towel each; a package product is worth its own
	 * quantity, which is what lets a Duo already in the cart combine with a
	 * loose motif into Trio pricing instead of sitting outside the count.
	 *
	 * @return array<int,int>
	 */
	private function units(): array {
		if ( null !== $this->units ) {
			return $this->units;
		}

		$units = array();

		foreach ( (array) get_option( WooCommerce::PRODUCT_MAP_OPTION, array() ) as $product_id ) {
			$product_id = (int) $product_id;
			if ( $product_id > 0 ) {
				$units[ $product_id ] = 1;
			}
		}

		foreach ( $this->catalog->packages() as $package ) {
			$product_id = (int) ( $package['product_id'] ?? 0 );
			$qty        = (int) ( $package['qty'] ?? 0 );

			if ( $product_id > 0 && $qty > 0 ) {
				$units[ $product_id ] = $qty;
			}
		}

		/**
		 * Filter which products count as towels, and for how many each.
		 *
		 * @param array<int,int> $units Product id => towels per unit.
		 */
		$this->units = (array) apply_filters( 'cosypaw_bundle_pricing_units', $units );

		return $this->units;
	}

	/**
	 * The wording on the discount row.
	 *
	 * The breakdown is part of the label rather than a separate notice so it
	 * survives onto the order: months later, the line still says which
	 * packages the saving was granted for.
	 *
	 * @param array<string,int> $lines Tier id => how many of it the plan uses.
	 * @return string
	 */
	private function fee_label( array $lines ): string {
		$names = array();
		foreach ( $this->tiers() as $tier ) {
			$names[ $tier['id'] ] = $tier['name'];
		}

		$parts = array();
		foreach ( $lines as $id => $count ) {
			// Not translated: "2x Trio paket" is a count against a name the
			// shop already stores per locale, and build-translations.php has
			// no msgctxt to keep a format this generic apart from other uses.
			$parts[] = sprintf( '%1$dx %2$s', $count, $names[ $id ] ?? $id );
		}

		if ( ! $parts ) {
			return __( 'Ušteda na paketima', 'cosypaw' );
		}

		/* translators: %s: the package breakdown, e.g. "1x Trio paket + 1x Pojedinačno". */
		return sprintf( __( 'Ušteda na paketima (%s)', 'cosypaw' ), implode( ' + ', $parts ) );
	}
}
