<?php
/**
 * Unit tests for the floating cart pill.
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
use Theme\FloatingCart;

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
require_once dirname( __DIR__ ) . '/inc/FloatingCart.php';

final class FloatingCartTest extends TestCase {

	/**
	 * Live package prices, as the seeded shop charges them.
	 *
	 * @var array<string,int>
	 */
	private const LIVE_PRICES = array(
		'solo' => 990,
		'duo'  => 1490,
		'trio' => 1980,
	);

	/**
	 * A mapped motif product id (one towel).
	 *
	 * @var int
	 */
	private const MOTIF_ID = 42;

	/**
	 * The cart the pill is rendered against.
	 *
	 * @var \WC_Cart
	 */
	private \WC_Cart $cart;

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		Functions\stubs(
			array(
				'add_action'       => true,
				'add_filter'       => true,
				'is_admin'         => false,
				'wp_doing_ajax'    => false,
				'esc_url'          => static fn( $url ) => $url,
				'esc_url_raw'      => static fn( $url ) => $url,
				'esc_html'         => static fn( $text ) => $text,
				'esc_attr'         => static fn( $text ) => $text,
				'wp_kses_post'     => static fn( $html ) => $html,
				'add_query_arg'    => static fn( $key, $value = '' ) => '?' . $key . '=' . $value,
				'__'               => static fn( $text ) => $text,
				'esc_html__'       => static fn( $text ) => $text,
				'determine_locale' => 'sr_RS',
				'get_post_meta'    => '',
				'get_post_status'  => 'publish',
				'wc_get_cart_url'  => 'http://example.test/korpa/',
			)
		);

		Functions\when( 'get_option' )->justReturn( array( 'zirafa' => self::MOTIF_ID ) );

		Functions\when( 'apply_filters' )->alias(
			static function ( string $hook, $value = null ) {
				if ( 'cosypaw_catalog_packages' !== $hook ) {
					return $value;
				}

				foreach ( $value as &$package ) {
					$id                    = (string) $package['id'];
					$package['price']      = self::LIVE_PRICES[ $id ] ?? $package['price'];
					$package['product_id'] = 100 + count( $value );
				}
				unset( $package );

				return $value;
			}
		);

		$this->cart = new \WC_Cart();

		// The theme calls WC()->cart; Brain\Monkey cannot stub a method chain,
		// so WC() returns a throwaway object carrying this test's cart.
		$holder = new \stdClass();
		Functions\when( 'WC' )->alias(
			function () use ( $holder ) {
				$holder->cart = $this->cart;

				return $holder;
			}
		);
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Render the pill against a cart holding this many towels.
	 *
	 * @param int $towels How many motif towels the cart holds.
	 * @return string
	 */
	private function pill( int $towels ): string {
		$contents = array();
		if ( $towels > 0 ) {
			$contents[] = array(
				'product_id' => self::MOTIF_ID,
				'quantity'   => $towels,
				'data'       => new \WC_Product( 'Žirafa', (string) self::LIVE_PRICES['solo'] ),
			);
		}

		$this->cart = new \WC_Cart( $contents );

		// WooCommerce calculates totals — fees included — before anything
		// renders, so the pill must be tested against a discounted cart.
		$pricing = new BundlePricing( 'cosypaw', new Catalog() );
		$pricing->apply_bundle_discount( $this->cart );

		return ( new FloatingCart( 'cosypaw', $pricing ) )->fragment( array() )['a.cart-fab'];
	}

	/**
	 * The fragment is keyed by the class it carries, or WooCommerce would
	 * replace the pill with something it can never replace again.
	 */
	public function test_the_fragment_can_replace_itself(): void {
		$fragments = ( new FloatingCart( 'cosypaw', new BundlePricing( 'cosypaw', new Catalog() ) ) )->fragment( array() );

		$this->assertArrayHasKey( 'a.cart-fab', $fragments );
		$this->assertStringContainsString( 'class="cart-fab"', $fragments['a.cart-fab'] );
	}

	/**
	 * An empty cart still ships the element — hidden, because a fragment can
	 * only replace a selector that is already on the page.
	 */
	public function test_an_empty_cart_ships_a_hidden_pill(): void {
		$markup = $this->pill( 0 );

		$this->assertStringContainsString( 'class="cart-fab" hidden', $markup );
		$this->assertStringContainsString( 'cart-fab__count">0<', $markup );
	}

	/**
	 * With items in it the pill shows itself, the count and the running total.
	 */
	public function test_a_filled_cart_shows_count_and_total(): void {
		$markup = $this->pill( 2 );

		// Not `hidden` — matched on the attribute, since the icon carries an
		// aria-hidden that a bare substring search would trip over.
		$this->assertStringContainsString( 'class="cart-fab">', $markup );
		$this->assertStringContainsString( 'cart-fab__count">2<', $markup );
		// Two towels at 990, less the 490 the Duo saves.
		$this->assertStringContainsString( '1.490 RSD', $markup );
	}

	/**
	 * The price on the pill is the price after the package saving.
	 *
	 * This is the regression that shipped: WooCommerce's get_cart_total() is
	 * the cart *contents* total and excludes fees, so the pill advertised
	 * 2.970 for three towels the shop was charging 1.980 for — the exact
	 * number the package pricing exists to replace. It passed review because
	 * the WC_Cart stub folded fees into its own get_cart_total(); the stub
	 * mirrors WooCommerce now, and this asserts the undiscounted figure is
	 * nowhere on the pill.
	 */
	public function test_the_pill_never_quotes_the_undiscounted_price(): void {
		$markup = $this->pill( 3 );

		$this->assertStringContainsString( '1.980 RSD', $markup );
		$this->assertStringNotContainsString( '2.970', $markup );
	}

	/**
	 * Two towels are one short of a Trio, and the pill says what that costs.
	 */
	public function test_the_nudge_quotes_the_next_towel(): void {
		$markup = $this->pill( 2 );

		$this->assertStringContainsString( 'cart-fab__nudge', $markup );
		$this->assertStringContainsString( '490 RSD', $markup );
	}

	/**
	 * A whole Trio is already at an optimum: the fourth towel is full price,
	 * so the pill offers nothing rather than dressing it up.
	 */
	public function test_a_complete_package_gets_no_nudge(): void {
		$this->assertStringNotContainsString( 'cart-fab__nudge', $this->pill( 3 ) );
	}

	/**
	 * The link goes to the cart, and the icon is hidden from screen readers so
	 * the label reads once.
	 */
	public function test_the_pill_links_to_the_cart(): void {
		$markup = $this->pill( 1 );

		$this->assertStringContainsString( 'href="http://example.test/korpa/"', $markup );
		$this->assertStringContainsString( 'screen-reader-text', $markup );
		$this->assertStringContainsString( 'aria-hidden="true"', $markup );
	}
}
