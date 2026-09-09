<?php
/**
 * Unit tests for the shipping-zone side of CheckoutSetup — specifically the
 * sync that carries a changed free-delivery threshold into WooCommerce.
 *
 * @package CosyPaw\Tests
 */

declare(strict_types=1);

namespace Theme\Tests;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use Theme\CheckoutSetup;

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once __DIR__ . '/stubs.php';
require_once dirname( __DIR__ ) . '/inc/Catalog.php';
require_once dirname( __DIR__ ) . '/inc/ProductNames.php';
require_once dirname( __DIR__ ) . '/inc/WooCommerce.php';
require_once dirname( __DIR__ ) . '/inc/CheckoutSetup.php';

final class CheckoutSetupTest extends TestCase {

	/**
	 * The options table, as this test sees it.
	 *
	 * @var array<string,mixed>
	 */
	private array $options = array();

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		Functions\stubs(
			array(
				'add_action'   => true,
				'add_filter'   => true,
				'is_admin'     => false,
				'__'           => static fn( $text ) => $text,
				'apply_filters' => static fn( $hook, $value = null ) => $value,
				'wc_get_base_location' => array( 'country' => 'RS', 'state' => '' ),
			)
		);

		$this->options = array( CheckoutSetup::ZONE_OPTION => 7 );

		Functions\when( 'get_option' )->alias(
			fn ( string $key, $default = false ) => $this->options[ $key ] ?? $default
		);
		Functions\when( 'update_option' )->alias(
			function ( string $key, $value ): bool {
				$this->options[ $key ] = $value;

				return true;
			}
		);
	}

	protected function tearDown(): void {
		\WC_Shipping_Zones::$zone = null;
		\WC_Shipping_Zone::$id    = 7;

		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Attach a zone holding one free-delivery method at this amount.
	 *
	 * @param string $min_amount Stored threshold.
	 * @return void
	 */
	private function zone_with_free_shipping( string $min_amount ): void {
		$method     = new \stdClass();
		$method->id = 'free_shipping';

		\WC_Shipping_Zones::$zone            = new \WC_Shipping_Zone( array( 3 => $method ) );
		$this->options['woocommerce_free_shipping_3_settings'] = array(
			'title'      => 'Besplatna dostava',
			'requires'   => 'min_amount',
			'min_amount' => $min_amount,
		);
	}

	/**
	 * The zone used to learn a new threshold only when somebody remembered to
	 * re-run the seeder, so the whole site could advertise a bar the cart did
	 * not enforce. The first request that sees a changed amount writes it.
	 */
	public function test_a_changed_threshold_reaches_the_shipping_zone(): void {
		$this->zone_with_free_shipping( '1390' );

		( new CheckoutSetup() )->maybe_sync_free_shipping();

		$this->assertSame(
			(string) CheckoutSetup::FREE_SHIPPING_MIN,
			$this->options['woocommerce_free_shipping_3_settings']['min_amount']
		);
	}

	/**
	 * ...and only on a change to the theme's own constant. The amount is the
	 * shop's to edit in wp-admin, so once this threshold has been applied an
	 * edit has to survive every later request — otherwise the sync would undo
	 * an administrator's deliberate change on the next page load.
	 */
	public function test_an_amount_edited_in_wp_admin_survives(): void {
		$this->zone_with_free_shipping( '2500' );

		// A first run applies the threshold and records the marker; the edit
		// lands after it, and the second run has to leave the edit alone.
		( new CheckoutSetup() )->maybe_sync_free_shipping();
		$this->options['woocommerce_free_shipping_3_settings']['min_amount'] = '2500';

		( new CheckoutSetup() )->maybe_sync_free_shipping();

		$this->assertSame( '2500', $this->options['woocommerce_free_shipping_3_settings']['min_amount'] );
	}

	/**
	 * A zone with no free-delivery method at all is how the shop ends up
	 * promising free delivery on every page while WooCommerce grants it
	 * nowhere: create_shipping_zone() skips the method whenever the threshold
	 * cannot be resolved, and never revisits it. The sync attaches one.
	 */
	public function test_a_zone_missing_the_method_gets_one(): void {
		\WC_Shipping_Zones::$zone = new \WC_Shipping_Zone();

		( new CheckoutSetup() )->maybe_sync_free_shipping();

		$this->assertArrayHasKey( 'woocommerce_free_shipping_1_settings', $this->options );
		$this->assertSame(
			(string) CheckoutSetup::FREE_SHIPPING_MIN,
			$this->options['woocommerce_free_shipping_1_settings']['min_amount']
		);
		$this->assertSame( 'min_amount', $this->options['woocommerce_free_shipping_1_settings']['requires'] );
	}

	/**
	 * The cart page quotes no postage — it is settled with the courier — so
	 * WooCommerce's delivery block and its "calculate shipping" form had
	 * nothing to say there, and sat directly above the row that does. Both are
	 * suppressed on the cart and left alone everywhere else, checkout included,
	 * where the method is actually chosen.
	 */
	public function test_the_cart_drops_woocommerce_delivery_block(): void {
		$checkout = new CheckoutSetup();

		Functions\when( 'is_cart' )->justReturn( true );
		$this->assertFalse( $checkout->hide_cart_delivery_block( true ) );
		$this->assertSame( 'no', $checkout->hide_cart_shipping_calculator( 'yes' ) );

		Functions\when( 'is_cart' )->justReturn( false );
		$this->assertTrue( $checkout->hide_cart_delivery_block( true ) );
		$this->assertSame( 'yes', $checkout->hide_cart_shipping_calculator( 'yes' ) );
	}

	/**
	 * A marker written by an older, weaker sync must not silence the new one:
	 * version 1 recorded "applied" even where it had found no zone, so a shop
	 * in that state would never retry. The version in the marker forces the
	 * one re-run that repairs it.
	 */
	public function test_a_marker_from_the_old_sync_does_not_block_the_new_one(): void {
		$this->options[ CheckoutSetup::APPLIED_OPTION ] = CheckoutSetup::FREE_SHIPPING_MIN;
		$this->zone_with_free_shipping( '1390' );

		( new CheckoutSetup() )->maybe_sync_free_shipping();

		$this->assertSame(
			(string) CheckoutSetup::FREE_SHIPPING_MIN,
			$this->options['woocommerce_free_shipping_3_settings']['min_amount']
		);
	}

	/**
	 * A shop whose zone this theme never recorded — built by hand in wp-admin,
	 * or seeded before ZONE_OPTION existed — is the state cosypaw.rs was in:
	 * the sync found nothing, attached no method, and every page went on
	 * advertising a threshold WooCommerce granted nowhere. The zone is now
	 * resolved the way WooCommerce resolves one when it rates a cart, and
	 * recorded so the lookup happens once.
	 */
	public function test_a_zone_this_theme_never_recorded_is_found_and_adopted(): void {
		$this->options = array();
		\WC_Shipping_Zone::$id    = 4;
		\WC_Shipping_Zones::$zone = new \WC_Shipping_Zone();

		( new CheckoutSetup() )->maybe_sync_free_shipping();

		$this->assertSame( 4, $this->options[ CheckoutSetup::ZONE_OPTION ] );
		$this->assertSame(
			(string) CheckoutSetup::FREE_SHIPPING_MIN,
			$this->options['woocommerce_free_shipping_1_settings']['min_amount']
		);
	}

	/**
	 * No zone at all: nothing to attach to, and — crucially — no marker. A run
	 * that changed nothing must not record the threshold as applied, which is
	 * exactly how one failed sync used to silence every later one.
	 */
	public function test_a_failed_sync_leaves_no_marker_behind(): void {
		$this->options = array();

		( new CheckoutSetup() )->maybe_sync_free_shipping();

		$this->assertArrayNotHasKey( CheckoutSetup::APPLIED_OPTION, $this->options );
	}
}
