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
		$this->options[ CheckoutSetup::APPLIED_OPTION ] = CheckoutSetup::FREE_SHIPPING_MIN;
		$this->zone_with_free_shipping( '2500' );

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
	 * No zone of ours, nothing to sync — and the marker is still written, so
	 * the lookup is not repeated on every request for a zone that is never
	 * coming back.
	 */
	public function test_no_zone_is_left_alone_and_not_retried(): void {
		$this->options = array();

		( new CheckoutSetup() )->maybe_sync_free_shipping();

		$this->assertSame( CheckoutSetup::FREE_SHIPPING_MIN, $this->options[ CheckoutSetup::APPLIED_OPTION ] );
	}
}
