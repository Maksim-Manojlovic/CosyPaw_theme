<?php
/**
 * Unit tests for cart-level package pricing.
 *
 * The prices exercised here are the live shop's (790 single, 1.490 for the 2+1
 * package), not Catalog's seed, because that is what the class actually prices
 * against — inject_package_ids() replaces the seed with WooCommerce's numbers
 * before BundlePricing ever sees a package.
 *
 * @package CosyPaw\Tests
 */

declare(strict_types=1);

namespace Theme\Tests;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use Theme\BundlePricing;
use Theme\Catalog;

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}
if ( ! defined( 'HOUR_IN_SECONDS' ) ) {
	define( 'HOUR_IN_SECONDS', 3600 );
}

require_once __DIR__ . '/stubs.php';
require_once dirname( __DIR__ ) . '/inc/Catalog.php';
require_once dirname( __DIR__ ) . '/inc/ProductNames.php';
require_once dirname( __DIR__ ) . '/inc/WooCommerce.php';
require_once dirname( __DIR__ ) . '/inc/CheckoutSetup.php';
require_once dirname( __DIR__ ) . '/inc/BundlePricing.php';

final class BundlePricingTest extends TestCase {

	/**
	 * What the live shop charges, keyed by package id.
	 *
	 * @var array<string,int>
	 */
	private const LIVE_PRICES = array(
		'solo' => 790,
		'duo'  => 1490,
	);

	/**
	 * Product ids the tests pretend the seeder mapped.
	 *
	 * @var array<string,int>
	 */
	private const PACKAGE_IDS = array(
		'solo' => 101,
		'duo'  => 102,
	);

	/**
	 * A mapped motif product id (one towel).
	 *
	 * @var int
	 */
	private const MOTIF_ID = 42;

	/**
	 * Package prices for the test at hand.
	 *
	 * Defaults to LIVE_PRICES; a test that needs a different ladder sets its
	 * own before calling stub_packages().
	 *
	 * @var array<string,int>
	 */
	private array $prices = self::LIVE_PRICES;

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		Functions\stubs(
			array(
				'add_action'      => true,
				'add_filter'      => true,
				'is_admin'        => false,
				'wp_doing_ajax'   => false,
				'esc_url_raw'     => static fn( $url ) => $url,
				'add_query_arg'   => static fn( $key, $value = '' ) => '?' . $key . '=' . $value,
				'__'              => static fn( $text ) => $text,
				'_x'              => static fn( $text ) => $text,
				'esc_html__'      => static fn( $text ) => $text,
				'determine_locale' => 'sr_RS',
				'get_post_meta'   => '',
				'get_post_status' => 'publish',
			)
		);

		// The motif map: one mapped motif, worth one towel.
		Functions\when( 'get_option' )->justReturn( array( 'zirafa' => self::MOTIF_ID ) );

		$this->stub_packages( self::PACKAGE_IDS );
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Stand in for WooCommerce::inject_package_ids(): give the catalog packages
	 * the live prices and the given product ids.
	 *
	 * @param array<string,int> $ids Package id => WC product id (empty for unmapped).
	 * @return void
	 */
	private function stub_packages( array $ids ): void {
		$prices = $this->prices;

		Functions\when( 'apply_filters' )->alias(
			static function ( string $hook, $value = null ) use ( $ids, $prices ) {
				if ( 'cosypaw_catalog_packages' !== $hook ) {
					return $value;
				}

				foreach ( $value as &$package ) {
					$id = (string) $package['id'];

					$package['price'] = $prices[ $id ] ?? $package['price'];

					if ( isset( $ids[ $id ] ) ) {
						$package['product_id'] = $ids[ $id ];
					}
				}
				unset( $package );

				return $value;
			}
		);
	}

	/**
	 * Build a cart line.
	 *
	 * @param int $product_id Product id.
	 * @param int $price      Unit price.
	 * @param int $quantity   Line quantity.
	 * @return array<string,mixed>
	 */
	private function line( int $product_id, int $price, int $quantity = 1 ): array {
		return array(
			'product_id' => $product_id,
			'quantity'   => $quantity,
			'data'       => new \WC_Product( 'Žirafa', (string) $price ),
		);
	}

	/**
	 * A motif line (one towel at the single price).
	 *
	 * @param int $quantity How many.
	 * @return array<string,mixed>
	 */
	private function motifs( int $quantity ): array {
		return $this->line( self::MOTIF_ID, self::LIVE_PRICES['solo'], $quantity );
	}

	/**
	 * Run the fee hook over a cart and return the fees booked on it.
	 *
	 * @param array<int,array<string,mixed>> $contents Cart lines.
	 * @return array<int,array{name:string,amount:float,taxable:bool}>
	 */
	private function fees_for( array $contents ): array {
		$cart    = new \WC_Cart( $contents );
		$pricing = new BundlePricing( 'cosypaw', new Catalog() );
		$pricing->apply_bundle_discount( $cart );

		return $cart->fees;
	}

	/**
	 * The live tier list, as BundlePricing::plan() takes it.
	 *
	 * @return array<int,array{id:string,name:string,qty:int,price:int}>
	 */
	private function tiers(): array {
		return array(
			array( 'id' => 'duo', 'name' => '2+1 paket', 'qty' => 3, 'price' => self::LIVE_PRICES['duo'] ),
			array( 'id' => 'solo', 'name' => 'Single', 'qty' => 1, 'price' => self::LIVE_PRICES['solo'] ),
		);
	}

	/**
	 * The plan the shop asked for, towel by towel: every third towel opens a
	 * package and the leftovers are charged singly. Two towels are two singles,
	 * which is dearer than the three-towel package beside them — the shop's own
	 * offer, and the reason next_step() has something to say there.
	 *
	 * @dataProvider plan_provider
	 *
	 * @param int               $towels Towels in the cart.
	 * @param int               $total  Expected total.
	 * @param array<string,int> $lines  Expected package breakdown.
	 */
	public function test_plan_matches_the_shops_own_packages( int $towels, int $total, array $lines ): void {
		$plan = BundlePricing::plan( $towels, $this->tiers() );

		$this->assertSame( $total, $plan['total'] );
		$this->assertSame( $lines, $plan['lines'] );
	}

	/**
	 * Towel count => expected total and breakdown at the live prices.
	 *
	 * @return array<string,array{0:int,1:int,2:array<string,int>}>
	 */
	public static function plan_provider(): array {
		return array(
			'one is a single'              => array( 1, 790, array( 'solo' => 1 ) ),
			'two are two singles'          => array( 2, 1580, array( 'solo' => 2 ) ),
			'three make a package'         => array( 3, 1490, array( 'duo' => 1 ) ),
			'four are a package + single'  => array( 4, 2280, array( 'duo' => 1, 'solo' => 1 ) ),
			'five are a package + two'     => array( 5, 3070, array( 'duo' => 1, 'solo' => 2 ) ),
			'six are two packages'         => array( 6, 2980, array( 'duo' => 2 ) ),
			'seven are two packages + one' => array( 7, 3770, array( 'duo' => 2, 'solo' => 1 ) ),
		);
	}

	/**
	 * Four motifs clicked one at a time are charged as a package plus a single,
	 * which is the whole point: the shopper never had to find the bundle
	 * builder to get the bundle price.
	 */
	public function test_loose_motifs_are_repriced_as_packages(): void {
		$fees = $this->fees_for( array( $this->motifs( 4 ) ) );

		$this->assertCount( 1, $fees );
		// 4 x 790 = 3.160 charged, 2.280 owed.
		$this->assertSame( -880.0, $fees[0]['amount'] );
		$this->assertFalse( $fees[0]['taxable'] );
	}

	/**
	 * Separate cart lines are one pool — clicking four different motifs must
	 * price the same as raising one motif's quantity to four.
	 */
	public function test_separate_lines_count_as_one_pool(): void {
		$fees = $this->fees_for(
			array(
				$this->line( self::MOTIF_ID, 790 ),
				$this->line( self::MOTIF_ID, 790 ),
				$this->line( self::MOTIF_ID, 790 ),
				$this->line( self::MOTIF_ID, 790 ),
			)
		);

		$this->assertCount( 1, $fees );
		$this->assertSame( -880.0, $fees[0]['amount'] );
	}

	/**
	 * A package bought in the bundle builder joins the same count: a package
	 * plus three loose motifs is six towels, so it owes two package prices
	 * rather than one package and three singles.
	 */
	public function test_an_existing_package_joins_the_count(): void {
		$fees = $this->fees_for(
			array(
				$this->line( self::PACKAGE_IDS['duo'], self::LIVE_PRICES['duo'] ),
				$this->motifs( 3 ),
			)
		);

		$this->assertCount( 1, $fees );
		// 1.490 + 3 x 790 = 3.860 charged, 2.980 owed.
		$this->assertSame( -880.0, $fees[0]['amount'] );
	}

	/**
	 * A cart that is already priced at the best plan gets no fee at all —
	 * not a zero-value row the shopper has to read.
	 */
	public function test_an_optimal_cart_gets_no_fee(): void {
		$fees = $this->fees_for(
			array( $this->line( self::PACKAGE_IDS['duo'], self::LIVE_PRICES['duo'] ) )
		);

		$this->assertSame( array(), $fees );
	}

	/**
	 * Two towels are two singles. There is no two-towel package to fall back
	 * on, so the cart is charged what the lines say and gets no saving row —
	 * the offer there is the third towel, which next_step() makes.
	 */
	public function test_two_towels_are_not_a_package(): void {
		$this->assertSame( array(), $this->fees_for( array( $this->motifs( 2 ) ) ) );
	}

	/**
	 * One towel is one towel. Nothing to group, nothing to discount.
	 */
	public function test_a_single_towel_gets_no_fee(): void {
		$this->assertSame( array(), $this->fees_for( array( $this->motifs( 1 ) ) ) );
	}

	/**
	 * Products that are not towels stay out of the count entirely.
	 */
	public function test_unmapped_products_are_ignored(): void {
		$this->assertSame( array(), $this->fees_for( array( $this->line( 9999, 4000, 5 ) ) ) );
	}

	/**
	 * Without mapped package products there is no package price to offer, so
	 * the discount must not be invented from Catalog's seed numbers.
	 */
	public function test_unseeded_packages_produce_no_discount(): void {
		$this->stub_packages( array() );

		$this->assertSame( array(), $this->fees_for( array( $this->motifs( 4 ) ) ) );
	}

	/**
	 * The row says which packages the saving was granted for, so the order
	 * still explains itself months later.
	 */
	public function test_the_fee_label_names_the_packages(): void {
		$fees = $this->fees_for( array( $this->motifs( 5 ) ) );

		$this->assertCount( 1, $fees );
		$this->assertStringContainsString( '2+1 paket', $fees[0]['name'] );
		$this->assertStringContainsString( 'Single', $fees[0]['name'] );
	}

	/**
	 * The pill's nudge is the marginal price of the next towel — and silence
	 * where there is no saving to offer, rather than a single dressed as one.
	 *
	 * @dataProvider next_step_provider
	 *
	 * @param int      $towels Towels in the cart.
	 * @param int|null $price  Expected marginal price, or null for no nudge.
	 * @param int      $rebate Expected drop in the bill, where the towel is free.
	 */
	public function test_next_step_only_speaks_when_the_next_towel_is_cheap( int $towels, ?int $price, int $rebate = 0 ): void {
		$step = ( new BundlePricing( 'cosypaw', new Catalog() ) )->next_step( $towels );

		if ( null === $price ) {
			$this->assertNull( $step );

			return;
		}

		$this->assertNotNull( $step );
		$this->assertSame( $price, $step['price'] );
		$this->assertSame( $rebate, $step['rebate'] );
		$this->assertSame( 790 - ( $price - $rebate ), $step['saving'] );
	}

	/**
	 * Towel count => what one more costs, or null when it costs full price.
	 *
	 * The third towel is the offer: two singles are 1.580 and the package that
	 * holds three is 1.490, so it is free and takes 90 off the bill as well.
	 * Silence everywhere the next towel is simply a towel.
	 *
	 * @return array<string,array{0:int,1:int|null,2?:int}>
	 */
	public static function next_step_provider(): array {
		return array(
			'one towel is not there yet'    => array( 1, null ),
			'two towels open a package'     => array( 2, 0, 90 ),
			'three towels are complete'     => array( 3, null ),
			'four towels are mid-package'   => array( 4, null ),
			'five towels open a second one' => array( 5, 0, 90 ),
			'six towels are complete'       => array( 6, null ),
		);
	}

	/**
	 * Unseeded packages leave nothing to nudge towards.
	 */
	public function test_next_step_is_silent_without_packages(): void {
		$this->stub_packages( array() );

		$this->assertNull( ( new BundlePricing( 'cosypaw', new Catalog() ) )->next_step( 2 ) );
	}

	/**
	 * wp-admin renders order screens against the cart; a fee booked there would
	 * be double-counted against the one the front end already applied.
	 */
	public function test_admin_requests_are_skipped(): void {
		Functions\when( 'is_admin' )->justReturn( true );
		Functions\when( 'wp_doing_ajax' )->justReturn( false );

		$this->assertSame( array(), $this->fees_for( array( $this->motifs( 4 ) ) ) );
	}

	/**
	 * Four towels clear the free-delivery bar, which is what the ladder is
	 * shaped for: the package alone is 1.490 and stops short of the 2.000, and
	 * one more towel takes the basket to 2.280.
	 *
	 * The rate is checked rather than the fee, because the fee is only half the
	 * answer — the cart pays 3.160 in line items and the threshold is judged on
	 * the 2.280 that is left after the saving.
	 */
	public function test_four_towels_clear_the_free_delivery_bar(): void {
		$cart = $this->cart( array( array( 'id' => self::MOTIF_ID, 'qty' => 4, 'price' => self::LIVE_PRICES['solo'] ) ) );

		$pricing = new BundlePricing( 'cosypaw', new Catalog() );
		$pricing->apply_bundle_discount( $cart );

		$this->assertSame( 2280.0, $pricing->payable_total( $cart ) );

		$rates = $pricing->require_threshold_after_saving(
			array( 'free_shipping:1' => new \WC_Mock_Shipping_Rate( 'free_shipping' ) )
		);

		$this->assertArrayHasKey( 'free_shipping:1', $rates );
	}

	/**
	 * Free delivery is judged on what the cart is worth after this module has
	 * repriced it.
	 *
	 * Three towels clicked one at a time are 2.370 in line items and 1.490 to
	 * pay. WooCommerce sees only the first number — a fee is neither an item
	 * nor a coupon, so WC_Shipping_Free_Shipping cannot subtract it — and would
	 * hand free delivery to a cart that pays the package price, while refusing
	 * it to the identical package bought as one. The rate is withdrawn instead.
	 */
	public function test_free_delivery_is_withdrawn_below_the_threshold_after_the_saving(): void {
		$cart = $this->cart( array( array( 'id' => self::MOTIF_ID, 'qty' => 3, 'price' => self::LIVE_PRICES['solo'] ) ) );

		$rates = ( new BundlePricing( 'cosypaw', new Catalog() ) )->require_threshold_after_saving(
			array(
				'free_shipping:1' => new \WC_Mock_Shipping_Rate( 'free_shipping' ),
				'flat_rate:2'     => new \WC_Mock_Shipping_Rate( 'flat_rate' ),
			)
		);

		unset( $cart );

		$this->assertArrayNotHasKey( 'free_shipping:1', $rates );
		// The courier rate has to survive: withdrawing free delivery without it
		// would leave the order with no way to be delivered at all.
		$this->assertArrayHasKey( 'flat_rate:2', $rates );
	}

	/**
	 * ...and stays where the cart clears the bar on what it actually pays.
	 */
	public function test_free_delivery_survives_above_the_threshold(): void {
		$cart = $this->cart(
			array(
				array( 'id' => self::MOTIF_ID, 'qty' => 3, 'price' => self::LIVE_PRICES['solo'] ),
				array( 'id' => self::PACKAGE_IDS['duo'], 'qty' => 1, 'price' => self::LIVE_PRICES['duo'] ),
			)
		);

		$rates = ( new BundlePricing( 'cosypaw', new Catalog() ) )->require_threshold_after_saving(
			array( 'free_shipping:1' => new \WC_Mock_Shipping_Rate( 'free_shipping' ) )
		);

		unset( $cart );

		$this->assertArrayHasKey( 'free_shipping:1', $rates );
	}

	/**
	 * Build a cart of these lines and put it behind WC()->cart.
	 *
	 * @param array<int,array{id:int,qty:int,price:int}> $lines Product id, quantity, unit price.
	 * @return \WC_Cart
	 */
	private function cart( array $lines ): \WC_Cart {
		$contents = array();
		foreach ( $lines as $line ) {
			$contents[] = array(
				'product_id' => $line['id'],
				'quantity'   => $line['qty'],
				'data'       => new \WC_Product( 'Žirafa', (string) $line['price'], true, $line['id'] ),
			);
		}

		$cart = new \WC_Cart( $contents );
		Functions\when( 'WC' )->alias( static fn () => new \WC_Mock_WC( $cart ) );

		return $cart;
	}
}
