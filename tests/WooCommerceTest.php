<?php
/**
 * Unit tests for the WooCommerce integration and Bootstrap's conditional
 * instantiation. Uses Brain\Monkey to mock WordPress functions — no full
 * WordPress install required.
 *
 * @package CosyPaw\Tests
 */

declare(strict_types=1);

namespace Theme\Tests;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use Theme\Bootstrap;
use Theme\Catalog;
use Theme\WooCommerce;

// --- Minimal runtime constants the theme files expect (normally from WP core). ---
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}
if ( ! defined( 'HOUR_IN_SECONDS' ) ) {
	define( 'HOUR_IN_SECONDS', 3600 );
}

require_once __DIR__ . '/stubs.php';

// The runtime theme is NOT Composer-autoloaded (it uses spl_autoload_register in
// functions.php). For tests we require the classes under test directly.
require_once dirname( __DIR__ ) . '/inc/Setup.php';
require_once dirname( __DIR__ ) . '/inc/Assets.php';
require_once dirname( __DIR__ ) . '/inc/Catalog.php';
require_once dirname( __DIR__ ) . '/inc/ProductSeeder.php';
require_once dirname( __DIR__ ) . '/inc/ProductNames.php';
require_once dirname( __DIR__ ) . '/inc/CheckoutSetup.php';
require_once dirname( __DIR__ ) . '/inc/WooCommerce.php';
require_once dirname( __DIR__ ) . '/inc/BundlePricing.php';
require_once dirname( __DIR__ ) . '/inc/Seo.php';
require_once dirname( __DIR__ ) . '/inc/Bootstrap.php';

final class WooCommerceTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		// No-op stubs for the WP hook/registration functions used in constructors.
		Functions\stubs(
			array(
				'add_action'         => true,
				'add_filter'         => true,
				'remove_action'      => true,
				'add_theme_support'  => true,
				'register_nav_menus' => true,
				'load_theme_textdomain' => true,
				// CheckoutSetup registers one filter only outside wp-admin.
				'is_admin'           => false,
				'untrailingslashit'  => static fn( $s ) => rtrim( (string) $s, '/' ),
				'add_query_arg'      => static fn( $key, $value = '' ) => 'http://example.test/?' . $key . '=' . $value,
				'esc_url_raw'        => static fn( $url ) => $url,
				'esc_html'           => static fn( $text ) => $text,
				// i18n stubs return the original string.
				'__'                 => static fn( $text ) => $text,
				'esc_html__'         => static fn( $text ) => $text,
				// Per-product name overrides: default to the source language,
				// where no override is ever consulted.
				'determine_locale'   => 'sr_RS',
				'get_post_meta'      => '',
				// Mapped products default to on-sale and purchasable.
				'get_post_status'    => 'publish',
				'wc_get_product'     => static fn() => new \WC_Product(),
			)
		);
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * The woocommerce_loop_add_to_cart_args filter must append the theme's
	 * custom CSS class while preserving the existing class.
	 */
	public function test_loop_add_to_cart_args_appends_custom_class(): void {
		$wc = new WooCommerce( 'cosypaw', new Catalog() );

		$args   = array( 'class' => 'button add_to_cart_button' );
		$result = $wc->loop_add_to_cart_args( $args, (object) array() );

		$this->assertArrayHasKey( 'class', $result );
		$this->assertStringContainsString( 'cosypaw-add-to-cart', $result['class'] );
		// Original classes are preserved.
		$this->assertStringContainsString( 'button', $result['class'] );
		$this->assertStringContainsString( 'add_to_cart_button', $result['class'] );
	}

	/**
	 * Works even when no class key is present initially.
	 */
	public function test_loop_add_to_cart_args_handles_missing_class(): void {
		$wc     = new WooCommerce( 'cosypaw', new Catalog() );
		$result = $wc->loop_add_to_cart_args( array(), (object) array() );

		$this->assertSame( 'cosypaw-add-to-cart', $result['class'] );
	}

	/**
	 * inject_product_ids() adds product_id + add_to_cart_url to mapped rows only.
	 */
	public function test_inject_product_ids_adds_mapped_ids(): void {
		Functions\when( 'get_option' )->justReturn( array( 'zirafa' => 42 ) );

		$wc   = new WooCommerce( 'cosypaw', new Catalog() );
		$rows = array(
			array( 'id' => 'zirafa', 'name' => 'Žirafa' ),
			array( 'id' => 'koala', 'name' => 'Koala' ),
		);
		$out = $wc->inject_product_ids( $rows );

		$this->assertSame( 42, $out[0]['product_id'] );
		$this->assertArrayHasKey( 'add_to_cart_url', $out[0] );
		// Unmapped row is untouched.
		$this->assertArrayNotHasKey( 'product_id', $out[1] );
	}

	/**
	 * A manual per-locale name set on the product overrides the Catalog's
	 * gettext name everywhere the catalog is rendered.
	 */
	public function test_inject_product_ids_applies_manual_name_override(): void {
		Functions\when( 'get_option' )->justReturn( array( 'zirafa' => 42 ) );
		Functions\when( 'determine_locale' )->justReturn( 'en_US' );
		Functions\when( 'get_post_meta' )->alias(
			static fn( $post_id, $key ) => ( 42 === $post_id && '_cosypaw_name_en_US' === $key ) ? 'Gentle Giraffe' : ''
		);

		$wc   = new WooCommerce( 'cosypaw', new Catalog() );
		$rows = array(
			array( 'id' => 'zirafa', 'name' => 'Giraffe' ),
			array( 'id' => 'koala', 'name' => 'Koala' ),
		);
		$out = $wc->inject_product_ids( $rows );

		$this->assertSame( 'Gentle Giraffe', $out[0]['name'] );
		// Unmapped row keeps its translated name.
		$this->assertSame( 'Koala', $out[1]['name'] );
	}

	/**
	 * A retired (trashed) product is flagged unavailable so the front page can
	 * drop its tile, while the row itself stays in the catalog for order history.
	 */
	public function test_inject_product_ids_flags_retired_products_unavailable(): void {
		Functions\when( 'get_option' )->justReturn( array( 'zirafa' => 42, 'koala' => 43 ) );
		Functions\when( 'get_post_status' )->alias( static fn( $id ) => 42 === $id ? 'trash' : 'publish' );

		$wc  = new WooCommerce( 'cosypaw', new Catalog() );
		$out = $wc->inject_product_ids(
			array(
				array( 'id' => 'zirafa', 'name' => 'Žirafa' ),
				array( 'id' => 'koala', 'name' => 'Koala' ),
			)
		);

		$this->assertFalse( $out[0]['available'] );
		$this->assertTrue( $out[1]['available'] );
		// The retired row is still present, name intact.
		$this->assertSame( 'Žirafa', $out[0]['name'] );
	}

	/**
	 * With no override stored, the name and price come from the WooCommerce
	 * product — editing either in wp-admin has to reach the landing page.
	 */
	public function test_inject_product_ids_takes_name_and_price_from_the_product(): void {
		Functions\when( 'get_option' )->justReturn( array( 'zirafa' => 42 ) );
		Functions\when( 'determine_locale' )->justReturn( 'en_US' );
		Functions\when( 'get_post_meta' )->justReturn( '' );
		Functions\when( 'wc_get_product' )->justReturn( new \WC_Product( 'Zirafica', '990' ) );

		$wc  = new WooCommerce( 'cosypaw', new Catalog() );
		$out = $wc->inject_product_ids(
			array( array( 'id' => 'zirafa', 'name' => 'Žirafa', 'price' => 790 ) )
		);

		$this->assertSame( 'Zirafica', $out[0]['name'] );
		$this->assertSame( 990, $out[0]['price'] );
	}

	/**
	 * A package's advertised per-towel price is recomputed from the price
	 * WooCommerce actually charges, so the two can never drift apart.
	 */
	public function test_inject_package_ids_recomputes_per_unit_price(): void {
		Functions\when( 'get_option' )->justReturn( array( 'trio' => 42 ) );
		Functions\when( 'wc_get_product' )->justReturn( new \WC_Product( 'Trio paket', '1800' ) );

		$wc  = new WooCommerce( 'cosypaw', new Catalog() );
		$out = $wc->inject_package_ids(
			array( array( 'id' => 'trio', 'name' => 'Trio paket', 'qty' => 3, 'price' => 1600, 'per' => 534 ) )
		);

		$this->assertSame( 1800, $out[0]['price'] );
		$this->assertSame( 600, $out[0]['per'] );
	}

	/**
	 * cart_count_fragment() exposes the nav badge under the selector WooCommerce
	 * swaps on AJAX add-to-cart, hidden at zero items.
	 */
	public function test_cart_count_fragment_outputs_badge_selector(): void {
		$wc        = new WooCommerce( 'cosypaw', new Catalog() );
		$fragments = $wc->cart_count_fragment( array() );

		$this->assertArrayHasKey( 'span.cart-btn__badge', $fragments );
		$this->assertStringContainsString( 'cart-btn__badge', $fragments['span.cart-btn__badge'] );
		// WooCommerce is undefined in this process → zero count → hidden.
		$this->assertStringContainsString( 'hidden', $fragments['span.cart-btn__badge'] );
	}

	/**
	 * Conditional instantiation (negative branch): when the WooCommerce class is
	 * NOT defined, Bootstrap must register an admin_notices hook instead of
	 * building the WooCommerce module.
	 *
	 * This process intentionally does not define a `WooCommerce` class, so
	 * class_exists('WooCommerce') is false.
	 */
	public function test_bootstrap_registers_admin_notice_when_woocommerce_missing(): void {
		$this->assertFalse(
			class_exists( 'WooCommerce' ),
			'Test precondition: WooCommerce must be undefined in this process.'
		);

		$registered_hooks = array();
		Functions\when( 'add_action' )->alias(
			static function ( $hook, $callback = null ) use ( &$registered_hooks ) {
				$registered_hooks[] = $hook;
				return true;
			}
		);
		Functions\when( 'get_template_directory' )->justReturn( '/tmp/cosypaw' );
		Functions\when( 'get_template_directory_uri' )->justReturn( 'http://example.test/wp-content/themes/cosypaw' );

		new Bootstrap( 'cosypaw' );

		$this->assertContains(
			'admin_notices',
			$registered_hooks,
			'Bootstrap should register an admin notice when WooCommerce is inactive.'
		);
	}

	/**
	 * Conditional instantiation (positive branch): when a `WooCommerce` class
	 * exists, Bootstrap builds the WooCommerce module and does NOT register the
	 * "missing WooCommerce" admin notice.
	 *
	 * Run in a separate process so defining the stub class does not leak into the
	 * negative-branch test above.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_bootstrap_builds_woocommerce_when_active(): void {
		if ( ! class_exists( 'WooCommerce' ) ) {
			// Stand-in for the real WooCommerce plugin class.
			eval( 'class WooCommerce {}' );
		}

		$registered_hooks = array();
		Functions\when( 'add_action' )->alias(
			static function ( $hook, $callback = null ) use ( &$registered_hooks ) {
				$registered_hooks[] = $hook;
				return true;
			}
		);
		Functions\when( 'get_template_directory' )->justReturn( '/tmp/cosypaw' );
		Functions\when( 'get_template_directory_uri' )->justReturn( 'http://example.test/wp-content/themes/cosypaw' );

		new Bootstrap( 'cosypaw' );

		$this->assertNotContains(
			'admin_notices',
			$registered_hooks,
			'Bootstrap should NOT register the missing-WooCommerce notice when WC is active.'
		);
	}
}
