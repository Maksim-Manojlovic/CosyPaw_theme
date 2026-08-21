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

		$tiers = $this->tiers();
		if ( count( $tiers ) < 2 ) {
			return;
		}

		$pool = $this->pool( $cart );
		if ( $pool['towels'] < 2 || $pool['subtotal'] <= 0.0 ) {
			return;
		}

		$plan = self::plan( $pool['towels'], $tiers );
		if ( $plan['total'] < 1 ) {
			return;
		}

		// Rounded to whole RSD before comparing: the shop deals in dinars, and
		// a sub-dinar "saving" is rounding noise, not a discount.
		$discount = (int) round( $pool['subtotal'] ) - $plan['total'];
		if ( $discount < 1 ) {
			return;
		}

		$cart->add_fee( $this->fee_label( $plan['lines'] ), -$discount, false );
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
			$name = $names[ $id ] ?? $id;
			/* translators: 1: how many of this package, 2: package name, e.g. "1x Trio paket". */
			$parts[] = sprintf( _x( '%1$dx %2$s', 'bundle plan line', 'cosypaw' ), $count, $name );
		}

		if ( ! $parts ) {
			return __( 'Ušteda na paketima', 'cosypaw' );
		}

		/* translators: %s: the package breakdown, e.g. "1x Trio paket + 1x Pojedinačno". */
		return sprintf( __( 'Ušteda na paketima (%s)', 'cosypaw' ), implode( ' + ', $parts ) );
	}
}
