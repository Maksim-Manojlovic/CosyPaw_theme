<?php
/**
 * Unit tests for cart-level package pricing.
 *
 * The prices exercised here are the live shop's (990 / 1.490 / 1.980), not
 * Catalog's seed, because that is what the class actually prices against —
 * inject_package_ids() replaces the seed with WooCommerce's numbers before
 * BundlePricing ever sees a package.
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
require_once dirname( __DIR__ ) . '/inc/BundlePricing.php';

final class BundlePricingTest extends TestCase {

	/**
	 * What the live shop charges, keyed by package id.
	 *
	 * @var array<string,int>
	 */
	private const LIVE_PRICES = array(
		'solo' => 990,
		'duo'  => 1490,
		'trio' => 1980,
	);

	/**
	 * Product ids the tests pretend the seeder mapped.
	 *
	 * @var array<string,int>
	 */
	private const PACKAGE_IDS = array(
		'solo' => 101,
		'duo'  => 102,
		'trio' => 103,
	);

	/**
	 * A mapped motif product id (one towel).
	 *
	 * @var int
	 */
	private const MOTIF_ID = 42;

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
		Functions\when( 'apply_filters' )->alias(
			static function ( string $hook, $value = null ) use ( $ids ) {
				if ( 'cosypaw_catalog_packages' !== $hook ) {
					return $value;
				}

				foreach ( $value as &$package ) {
					$id = (string) $package['id'];

					$package['price'] = self::LIVE_PRICES[ $id ] ?? $package['price'];

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
			array( 'id' => 'trio', 'name' => 'Trio paket', 'qty' => 3, 'price' => self::LIVE_PRICES['trio'] ),
			array( 'id' => 'duo', 'name' => 'Duo paket', 'qty' => 2, 'price' => self::LIVE_PRICES['duo'] ),
			array( 'id' => 'solo', 'name' => 'Pojedinačno', 'qty' => 1, 'price' => self::LIVE_PRICES['solo'] ),
		);
	}

	/**
	 * The plan the shop asked for, towel by towel: pairs become a Duo, threes a
	 * Trio, the fourth towel is charged singly, and the fifth reopens a Duo
	 * alongside the Trio rather than splitting into two pairs.
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
			'one is a single'           => array( 1, 990, array( 'solo' => 1 ) ),
			'two make a duo'            => array( 2, 1490, array( 'duo' => 1 ) ),
			'three make a trio'         => array( 3, 1980, array( 'trio' => 1 ) ),
			'four are a trio + single'  => array( 4, 2970, array( 'trio' => 1, 'solo' => 1 ) ),
			'five are a trio + duo'     => array( 5, 3470, array( 'trio' => 1, 'duo' => 1 ) ),
			'six are two trios'         => array( 6, 3960, array( 'trio' => 2 ) ),
			'seven are two trios + one' => array( 7, 4950, array( 'trio' => 2, 'solo' => 1 ) ),
		);
	}

	/**
	 * Four motifs clicked one at a time are charged as a Trio plus a single,
	 * which is the whole point: the shopper never had to find the bundle
	 * builder to get the bundle price.
	 */
	public function test_loose_motifs_are_repriced_as_packages(): void {
		$fees = $this->fees_for( array( $this->motifs( 4 ) ) );

		$this->assertCount( 1, $fees );
		// 4 x 990 = 3.960 charged, 2.970 owed.
		$this->assertSame( -990.0, $fees[0]['amount'] );
		$this->assertFalse( $fees[0]['taxable'] );
	}

	/**
	 * Separate cart lines are one pool — clicking four different motifs must
	 * price the same as raising one motif's quantity to four.
	 */
	public function test_separate_lines_count_as_one_pool(): void {
		$fees = $this->fees_for(
			array(
				$this->line( self::MOTIF_ID, 990 ),
				$this->line( self::MOTIF_ID, 990 ),
				$this->line( self::MOTIF_ID, 990 ),
				$this->line( self::MOTIF_ID, 990 ),
			)
		);

		$this->assertCount( 1, $fees );
		$this->assertSame( -990.0, $fees[0]['amount'] );
	}

	/**
	 * A package bought in the bundle builder joins the same count: a Duo plus
	 * one loose motif is three towels, so it owes the Trio price.
	 */
	public function test_an_existing_package_joins_the_count(): void {
		$fees = $this->fees_for(
			array(
				$this->line( self::PACKAGE_IDS['duo'], self::LIVE_PRICES['duo'] ),
				$this->motifs( 1 ),
			)
		);

		$this->assertCount( 1, $fees );
		// 1.490 + 990 = 2.480 charged, 1.980 owed.
		$this->assertSame( -500.0, $fees[0]['amount'] );
	}

	/**
	 * A cart that is already priced at the best plan gets no fee at all —
	 * not a zero-value row the shopper has to read.
	 */
	public function test_an_optimal_cart_gets_no_fee(): void {
		$fees = $this->fees_for(
			array( $this->line( self::PACKAGE_IDS['trio'], self::LIVE_PRICES['trio'] ) )
		);

		$this->assertSame( array(), $fees );
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
		$this->assertStringContainsString( 'Trio paket', $fees[0]['name'] );
		$this->assertStringContainsString( 'Duo paket', $fees[0]['name'] );
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
}
