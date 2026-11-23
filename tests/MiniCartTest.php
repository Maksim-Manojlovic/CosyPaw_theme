<?php
/**
 * Unit tests for the mini cart: what the panel shows, and which changes its
 * endpoints accept.
 *
 * @package CosyPaw\Tests
 */

declare(strict_types=1);

namespace Theme\Tests;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use Theme\BlackFriday;
use Theme\BundlePricing;
use Theme\Catalog;
use Theme\FloatingCart;
use Theme\MiniCart;
use Theme\Upsell;

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}
if ( ! defined( 'HOUR_IN_SECONDS' ) ) {
	define( 'HOUR_IN_SECONDS', 3600 );
}

require_once __DIR__ . '/stubs.php';
require_once dirname( __DIR__ ) . '/inc/Stock.php';
require_once dirname( __DIR__ ) . '/inc/Swatches.php';
require_once dirname( __DIR__ ) . '/inc/Catalog.php';
require_once dirname( __DIR__ ) . '/inc/ProductNames.php';
require_once dirname( __DIR__ ) . '/inc/WooCommerce.php';
require_once dirname( __DIR__ ) . '/inc/BundlePricing.php';
require_once dirname( __DIR__ ) . '/inc/CheckoutSetup.php';
require_once dirname( __DIR__ ) . '/inc/Storefront.php';
require_once dirname( __DIR__ ) . '/inc/Upsell.php';
require_once dirname( __DIR__ ) . '/inc/FloatingCart.php';
require_once dirname( __DIR__ ) . '/inc/ProductLine.php';
require_once dirname( __DIR__ ) . '/inc/Cloths.php';
require_once dirname( __DIR__ ) . '/inc/LongTowels.php';
require_once dirname( __DIR__ ) . '/inc/BlackFriday.php';
require_once dirname( __DIR__ ) . '/inc/MiniCart.php';

/**
 * Thrown by the wp_send_json_* stubs, so a test can read what an endpoint
 * answered (the real functions exit).
 */
final class JsonSent extends \RuntimeException {

	/**
	 * @var array<string,mixed>
	 */
	public array $data;

	/**
	 * @var bool
	 */
	public bool $success;

	/**
	 * @param bool                $success Whether success was sent.
	 * @param array<string,mixed> $data    Payload.
	 */
	public function __construct( bool $success, array $data ) {
		parent::__construct( 'json sent' );
		$this->success = $success;
		$this->data    = $data;
	}
}

final class MiniCartTest extends TestCase {

	/**
	 * Product id of the first mapped motif.
	 */
	private const MOTIF_ID = 501;

	/**
	 * @var \WC_Cart
	 */
	private \WC_Cart $cart;

	/**
	 * Session values, as WC()->session holds them.
	 *
	 * @var array<string,mixed>
	 */
	private array $session = array();

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		$this->session = array();

		Functions\stubs(
			array(
				'add_action'                     => true,
				'add_filter'                     => true,
				'is_admin'                       => false,
				'wp_doing_ajax'                  => false,
				'esc_url'                        => static fn( $url ) => $url,
				'esc_url_raw'                    => static fn( $url ) => $url,
				'esc_html'                       => static fn( $text ) => $text,
				'esc_attr'                       => static fn( $text ) => $text,
				'wp_kses_post'                   => static fn( $html ) => $html,
				'wp_kses'                        => static fn( $html ) => $html,
				'wp_strip_all_tags'              => static fn( $text ) => strip_tags( (string) $text ),
				'__'                             => static fn( $text ) => $text,
				'esc_html__'                     => static fn( $text ) => $text,
				'esc_attr__'                     => static fn( $text ) => $text,
				'determine_locale'               => 'sr_RS',
				'get_post_meta'                  => '',
				'get_post_status'                => 'publish',
				'get_permalink'                  => 'http://example.test/p/',
				'wc_get_cart_url'                => 'http://example.test/korpa/',
				'wc_get_checkout_url'            => 'http://example.test/placanje/',
				'wc_get_page_permalink'          => 'http://example.test/prodavnica/',
				'wc_get_formatted_cart_item_data' => '',
				'wp_create_nonce'                => 'n0nce',
				'home_url'                       => static fn( $path = '' ) => 'http://example.test' . $path,
				'get_template_directory_uri'     => 'http://example.test/theme',
				'trailingslashit'                => static fn( $path ) => rtrim( (string) $path, '/' ) . '/',
				'current_user_can'               => false,
				'is_cart'                        => false,
				'is_checkout'                    => false,
			)
		);

		Functions\when( 'add_query_arg' )->alias(
			static fn( $args, $url = '' ) => $url . '?' . http_build_query( (array) $args )
		);

		// The motif map makes 501 a towel; the long-towel and cloth maps stay
		// empty, so Crni petak (when on) has no gift to put in.
		Functions\when( 'get_option' )->alias(
			static fn( string $name, $fallback = false ) => WooCommerceAlias::option( $name, $fallback )
		);

		Functions\when( 'apply_filters' )->alias(
			static function ( string $hook, $value = null ) {
				if ( 'cosypaw_catalog_packages' === $hook ) {
					$product_id = 100;
					foreach ( $value as &$package ) {
						$package['price']      = 'solo' === $package['id'] ? 790 : 1490;
						$package['per']        = (int) round( $package['price'] / (int) $package['qty'] );
						$package['product_id'] = ++$product_id;
					}
					unset( $package );

					return $value;
				}

				if ( 'cosypaw_catalog_products' === $hook ) {
					$product_id = 500;
					foreach ( $value as &$motif ) {
						$motif['product_id'] = ++$product_id;
						$motif['price']      = 790;
						$motif['available']  = true;
					}
					unset( $motif );

					return $value;
				}

				if ( 'cosypaw_black_friday_active' === $hook ) {
					return false;
				}

				return $value;
			}
		);

		Functions\when( 'wp_send_json_success' )->alias(
			static function ( $data = null ) {
				throw new JsonSent( true, (array) $data );
			}
		);
		Functions\when( 'wp_send_json_error' )->alias(
			static function ( $data = null ) {
				throw new JsonSent( false, (array) $data );
			}
		);

		$this->cart = new \WC_Cart();
		$this->wire_wc();
	}

	protected function tearDown(): void {
		unset( $_POST['key'], $_POST['qty'], $_POST['nonce'], $_POST['product_id'], $_GET['wc-ajax'] );
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * WC() hands back this test's cart and a session that records into
	 * $this->session.
	 *
	 * @return void
	 */
	private function wire_wc(): void {
		$session = new class( $this->session ) {
			/** @var array<string,mixed> */
			public array $store;

			/**
			 * @param array<string,mixed> $store Backing values.
			 */
			public function __construct( array &$store ) {
				$this->store = &$store;
			}

			/**
			 * @param string $key Key.
			 * @return mixed
			 */
			public function get( string $key ) {
				return $this->store[ $key ] ?? null;
			}

			/**
			 * @param string $key   Key.
			 * @param mixed  $value Value.
			 * @return void
			 */
			public function set( string $key, $value ): void {
				$this->store[ $key ] = $value;
			}
		};

		Functions\when( 'WC' )->alias(
			function () use ( $session ) {
				$wc          = new \WC_Mock_WC( $this->cart );
				$wc->session = $session;

				return $wc;
			}
		);
	}

	/**
	 * The module under test.
	 *
	 * @return MiniCart
	 */
	private function mini_cart(): MiniCart {
		$pricing = new BundlePricing( 'cosypaw', new Catalog() );

		return new MiniCart(
			new Upsell( 'cosypaw', $pricing, new Catalog() ),
			new FloatingCart( 'cosypaw', $pricing ),
			new BlackFriday( $pricing )
		);
	}

	/**
	 * A motif line.
	 *
	 * @param string $key Cart item key.
	 * @param int    $id  Product id.
	 * @param int    $qty Quantity.
	 * @return array<string,mixed>
	 */
	private static function line( string $key, int $id, int $qty = 1 ): array {
		return array(
			'key'        => $key,
			'product_id' => $id,
			'quantity'   => $qty,
			'data'       => new \WC_Product( 'Žirafa', '790', true, $id ),
		);
	}

	public function test_an_empty_cart_offers_towels_and_the_builder(): void {
		$html = $this->mini_cart()->body();

		$this->assertStringContainsString( 'Korpa je prazna', $html );
		$this->assertSame( MiniCart::PICKS, substr_count( $html, 'data-add="' ) );
		$this->assertStringContainsString( 'href="http://example.test/#napravi-paket"', $html );
		$this->assertStringNotContainsString( 'data-nonce', $html );
	}

	public function test_lines_carry_their_controls_the_total_and_the_checkout(): void {
		$this->cart = new \WC_Cart(
			array(
				'a' => self::line( 'a', self::MOTIF_ID ),
				'b' => self::line( 'b', self::MOTIF_ID + 1 ),
			)
		);

		$html = $this->mini_cart()->body();

		$this->assertSame( 2, substr_count( $html, 'class="mini-cart__line"' ) );
		$this->assertStringContainsString( 'data-key="a"', $html );
		$this->assertStringContainsString( 'data-qty="0"', $html, 'Each line has its remove button.' );
		$this->assertStringContainsString( 'data-qty="2"', $html, 'And its plus.' );
		$this->assertStringContainsString( 'data-nonce="n0nce"', $html );
		$this->assertStringContainsString( 'href="http://example.test/placanje/"', $html );
		$this->assertStringContainsString( 'Završi porudžbinu', $html );
		$this->assertStringContainsString( '1.580 RSD', $html );
	}

	public function test_picks_leave_out_what_the_cart_holds_and_quote_the_next_towel(): void {
		$this->cart = new \WC_Cart(
			array(
				'a' => self::line( 'a', self::MOTIF_ID ),
				'b' => self::line( 'b', self::MOTIF_ID + 1 ),
			)
		);

		$html = $this->mini_cart()->body();

		$this->assertStringNotContainsString( 'data-add="' . self::MOTIF_ID . '"', $html );
		$this->assertStringNotContainsString( 'data-add="' . ( self::MOTIF_ID + 1 ) . '"', $html );
		// Two towels at 790 already pay for the 2+1 at 1.490: the third is free.
		$this->assertStringContainsString( 'Sledeći peškir je gratis', $html );
		$this->assertStringContainsString( '<del>790 RSD</del> Gratis', $html );
	}

	public function test_a_gift_line_cannot_be_changed_but_can_be_swapped(): void {
		$gift                          = self::line( 'g', 72 );
		$gift[ BlackFriday::ITEM_KEY ] = 'towel:meda-roze';

		$this->cart = new \WC_Cart( array( 'g' => $gift ) );

		$html = $this->mini_cart()->body();

		$this->assertStringContainsString( 'mini-cart__line is-gift', $html );
		$this->assertStringNotContainsString( 'data-qty=', $html );
		$this->assertStringContainsString( 'data-gift-open>Promeni poklon', $html );
	}

	public function test_the_first_steps_cloth_is_not_offered_as_a_choice(): void {
		$gift                          = self::line( 'g', 81 );
		$gift[ BlackFriday::ITEM_KEY ] = 'cloth:avokado-limeta';

		$this->cart = new \WC_Cart( array( 'g' => $gift ) );

		$this->assertStringNotContainsString( 'Promeni poklon', $this->mini_cart()->body() );
	}

	public function test_the_shell_is_not_printed_on_the_cart(): void {
		Functions\when( 'is_cart' )->justReturn( true );

		if ( ! class_exists( '\WC_AJAX' ) ) {
			eval( 'class WC_AJAX { public static function get_endpoint( $a ) { return "/?wc-ajax=" . $a; } }' );
		}

		$this->assertSame( '', $this->mini_cart()->shell() );
	}

	public function test_a_reloading_add_opens_the_panel_on_the_next_page_once(): void {
		if ( ! class_exists( '\WC_AJAX' ) ) {
			eval( 'class WC_AJAX { public static function get_endpoint( $a ) { return "/?wc-ajax=" . $a; } }' );
		}

		$mini = $this->mini_cart();
		$mini->flag_open( 'k', self::MOTIF_ID, 1, 0, array(), array() );

		$this->assertStringContainsString( 'data-open-on-load', $mini->shell() );
		$this->assertStringNotContainsString( 'data-open-on-load', $mini->shell() );
	}

	public function test_a_gift_or_an_ajax_add_does_not_flag_the_panel(): void {
		$mini = $this->mini_cart();

		$mini->flag_open( 'k', 72, 1, 0, array(), array( BlackFriday::ITEM_KEY => 'towel:meda-roze' ) );
		$this->assertEmpty( $this->session[ MiniCart::OPEN_FLAG ] ?? null );

		$_GET['wc-ajax'] = 'add_to_cart';
		$mini->flag_open( 'k', self::MOTIF_ID, 1, 0, array(), array() );
		$this->assertEmpty( $this->session[ MiniCart::OPEN_FLAG ] ?? null );
	}

	public function test_a_quantity_change_needs_the_nonce(): void {
		Functions\when( 'check_ajax_referer' )->justReturn( false );
		$this->cart = new \WC_Cart( array( 'a' => self::line( 'a', self::MOTIF_ID ) ) );

		$_POST['key'] = 'a';
		$_POST['qty'] = '3';

		try {
			$this->mini_cart()->ajax_qty();
			$this->fail( 'The endpoint should have answered.' );
		} catch ( JsonSent $sent ) {
			$this->assertFalse( $sent->success );
		}

		$this->assertSame( 1, $this->cart->get_cart_item( 'a' )['quantity'] );
	}

	public function test_a_gift_line_is_not_the_shoppers_to_change(): void {
		Functions\when( 'check_ajax_referer' )->justReturn( true );
		Functions\when( 'wp_unslash' )->returnArg();
		Functions\when( 'sanitize_text_field' )->returnArg();

		$gift                          = self::line( 'g', 72 );
		$gift[ BlackFriday::ITEM_KEY ] = 'towel:meda-roze';
		$this->cart                    = new \WC_Cart( array( 'g' => $gift ) );

		$_POST['key'] = 'g';
		$_POST['qty'] = '0';

		try {
			$this->mini_cart()->ajax_qty();
			$this->fail( 'The endpoint should have answered.' );
		} catch ( JsonSent $sent ) {
			$this->assertFalse( $sent->success );
		}

		$this->assertNotSame( array(), $this->cart->get_cart_item( 'g' ) );
	}

	public function test_quantities_change_and_zero_removes(): void {
		Functions\when( 'check_ajax_referer' )->justReturn( true );
		Functions\when( 'wp_unslash' )->returnArg();
		Functions\when( 'sanitize_text_field' )->returnArg();

		$this->cart = new \WC_Cart(
			array(
				'a' => self::line( 'a', self::MOTIF_ID ),
				'b' => self::line( 'b', self::MOTIF_ID + 1 ),
			)
		);

		$_POST['key'] = 'a';
		$_POST['qty'] = '3';

		try {
			$this->mini_cart()->ajax_qty();
		} catch ( JsonSent $sent ) {
			$this->assertTrue( $sent->success );
			$this->assertSame( 4, $sent->data['count'] );
		}

		$_POST['key'] = 'b';
		$_POST['qty'] = '0';

		try {
			$this->mini_cart()->ajax_qty();
		} catch ( JsonSent $sent ) {
			$this->assertTrue( $sent->success );
		}

		$this->assertSame( array( 'a' ), array_keys( $this->cart->get_cart() ) );
	}

	public function test_only_an_offered_motif_can_be_added(): void {
		Functions\when( 'wp_unslash' )->returnArg();
		Functions\when( 'absint' )->alias( static fn( $n ) => abs( (int) $n ) );

		$_POST['product_id'] = '72';

		try {
			$this->mini_cart()->ajax_add();
			$this->fail( 'The endpoint should have answered.' );
		} catch ( JsonSent $sent ) {
			$this->assertFalse( $sent->success );
		}

		$this->assertTrue( $this->cart->is_empty() );

		$_POST['product_id'] = (string) self::MOTIF_ID;

		try {
			$this->mini_cart()->ajax_add();
		} catch ( JsonSent $sent ) {
			$this->assertTrue( $sent->success );
			$this->assertTrue( $sent->data['added'] );
		}

		$this->assertSame( 1, $this->cart->get_cart_contents_count() );
	}
}

/**
 * Options for the mini cart tests: the motif map only.
 */
final class WooCommerceAlias {

	/**
	 * @param string $name     Option name.
	 * @param mixed  $fallback Default.
	 * @return mixed
	 */
	public static function option( string $name, $fallback ) {
		return 'cosypaw_product_map' === $name ? array( 'zirafa' => 501, 'koala' => 502 ) : $fallback;
	}
}
