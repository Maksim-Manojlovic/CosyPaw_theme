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
require_once dirname( __DIR__ ) . '/inc/FloatingCart.php';
require_once dirname( __DIR__ ) . '/inc/ShopStrings.php';
require_once dirname( __DIR__ ) . '/inc/Seo.php';
require_once dirname( __DIR__ ) . '/inc/ReviewRequest.php';
require_once dirname( __DIR__ ) . '/inc/Reviews.php';
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
				// Faithful enough to WordPress's own to be worth testing links
				// against: both signatures, and the URL it is handed is kept.
				'add_query_arg'      => static function ( $key, $value = '', $url = '' ) {
					$args = is_array( $key ) ? $key : array( $key => $value );
					$base = is_array( $key ) ? (string) $value : (string) $url;
					$base = '' !== $base ? $base : 'http://example.test/';

					return $base . ( str_contains( $base, '?' ) ? '&' : '?' ) . http_build_query( $args );
				},
				'esc_url_raw'        => static fn( $url ) => $url,
				'wp_unslash'         => static fn( $value ) => $value,
				'wp_trim_words'      => static fn( $text, $words = 55, $more = '…' ) => $text,
				'wc_get_cart_url'    => 'http://example.test/korpa/',
				'wc_get_page_permalink' => static fn( $page ) => 'http://example.test/' . $page . '/',
				'absint'             => static fn( $value ) => abs( (int) $value ),
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
				'esc_url'            => static fn( $url ) => $url,
				'esc_attr'           => static fn( $text ) => $text,
				'home_url'           => static fn( $path = '/' ) => 'http://example.test' . $path,
				'apply_filters'      => static fn( $hook, $value = null ) => $value,
				'get_permalink'      => static fn( $id ) => 'http://example.test/product/' . $id . '/',
			)
		);

		// A WooCommerce with no cart session, which is what a unit test has.
		// Another test file defines WC(), so leaving it unmocked here is an
		// error rather than a function_exists() miss.
		Functions\when( 'WC' )->justReturn( (object) array( 'cart' => null ) );
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
	 * inject_product_ids() adds product_id, add_to_cart_url and permalink to
	 * mapped rows only.
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
		// The permalink is what puts a review form within reach of the grid: no
		// link on the card, no way for a customer to ever reach one.
		$this->assertSame( 'http://example.test/product/42/', $out[0]['permalink'] );
		// Unmapped row is untouched.
		$this->assertArrayNotHasKey( 'product_id', $out[1] );
		$this->assertArrayNotHasKey( 'permalink', $out[1] );
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
		// WC() is mocked to an empty cart rather than left undefined: Brain\Monkey
		// declares a mocked function for the whole process, so a sibling test
		// file that mocks WC() would otherwise decide this one's branch.
		$holder = new \stdClass();
		$holder->cart = new \WC_Cart();
		Functions\when( 'WC' )->justReturn( $holder );

		$wc        = new WooCommerce( 'cosypaw', new Catalog() );
		$fragments = $wc->cart_count_fragment( array() );

		$this->assertArrayHasKey( 'span.cart-btn__badge', $fragments );
		$this->assertStringContainsString( 'cart-btn__badge', $fragments['span.cart-btn__badge'] );
		// Zero items → hidden.
		$this->assertStringContainsString( 'hidden', $fragments['span.cart-btn__badge'] );
		// The replacement swaps the whole element, so the marker that keeps the
		// demo cart off a live badge has to come back with it.
		$this->assertStringContainsString( 'data-cart-owner="wc"', $fragments['span.cart-btn__badge'] );
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

	/**
	 * A motif page routes to the package builder with that motif carried along,
	 * so the single-towel page still argues for the offer the shop is built on.
	 */
	public function test_bundle_cta_links_the_motif_into_the_builder(): void {
		Functions\when( 'get_option' )->justReturn( array( 'zirafa' => 42 ) );
		Functions\when( 'wc_get_product' )->justReturn( new \WC_Product( 'Žirafa', '790', true, 42 ) );

		$wc = new WooCommerce( 'cosypaw', new Catalog() );

		ob_start();
		$wc->bundle_cta();
		$out = (string) ob_get_clean();

		$this->assertStringContainsString( 'motif=zirafa', $out );
		$this->assertStringContainsString( '#napravi-paket', $out );
		$this->assertStringContainsString( 'Dodaj u paket', $out );
	}

	/**
	 * The offer the rest of the site makes — the price per piece falling, the
	 * free towel, the free shipping — has to survive the trip to a product
	 * page, which is where a customer decides between one towel and three.
	 */
	public function test_bundle_cta_states_the_package_offer(): void {
		Functions\when( 'get_option' )->justReturn( array( 'zirafa' => 42 ) );
		Functions\when( 'wc_get_product' )->justReturn( new \WC_Product( 'Žirafa', '790', true, 42 ) );

		$wc = new WooCommerce( 'cosypaw', new Catalog() );

		ob_start();
		$wc->bundle_cta();
		$out = (string) ob_get_clean();

		$this->assertStringContainsString( 'Duo paket', $out );
		$this->assertStringContainsString( 'Trio paket', $out );
		$this->assertStringContainsString( '2+1 GRATIS', $out );
		$this->assertStringContainsString( 'Besplatna dostava', $out );
		// Every row is a way in, with the size it names already chosen.
		$this->assertStringContainsString( 'package=trio', $out );
	}

	/**
	 * Adding a towel used to end the conversation on the page it started. The
	 * panel only appears for the product that was just added.
	 */
	public function test_added_panel_answers_what_now(): void {
		Functions\when( 'get_option' )->justReturn( array( 'zirafa' => 42 ) );
		Functions\when( 'wc_get_product' )->justReturn( new \WC_Product( 'Žirafa', '790', true, 42 ) );

		$wc = new WooCommerce( 'cosypaw', new Catalog() );

		$_GET['cosypaw-added'] = '42';
		ob_start();
		$wc->added_panel();
		$out = (string) ob_get_clean();
		unset( $_GET['cosypaw-added'] );

		$this->assertStringContainsString( 'Žirafa', $out );
		$this->assertStringContainsString( 'Napravi paket', $out );
		$this->assertStringContainsString( 'Nastavi kupovinu', $out );
		$this->assertStringContainsString( 'Trio paket', $out );
	}

	/**
	 * Most motifs have no review of their own, and an empty product page reads
	 * as a shop nobody has bought from. The newest review from anywhere in the
	 * shop stands in — carrying the name of the towel it was written about,
	 * because without it the quote would be a review of this one.
	 */
	public function test_review_proof_borrows_the_shops_newest_when_a_motif_has_none(): void {
		Functions\when( 'get_option' )->justReturn( array( 'zirafa' => 42 ) );
		Functions\when( 'wc_get_product' )->justReturn( new \WC_Product( 'Žirafa', '790', true, 42 ) );
		Functions\when( 'get_comments' )->justReturn( array() );
		Functions\when( 'get_transient' )->justReturn(
			array(
				array(
					'product_id' => 7,
					'rating'     => 5,
					'author'     => 'Milica',
					'text'       => 'Ćerka bira baš ovaj svako veče.',
					'id'         => 3,
				),
			)
		);
		Functions\when( 'get_the_title' )->justReturn( 'Pingvin' );

		$wc = new WooCommerce( 'cosypaw', new Catalog() );

		ob_start();
		$wc->review_proof();
		$out = (string) ob_get_clean();

		$this->assertStringContainsString( 'Milica', $out );
		$this->assertStringContainsString( 'o proizvodu', $out );
		$this->assertStringContainsString( 'Pingvin', $out );
		// A borrowed review must never carry this product's structured data.
		$this->assertStringNotContainsString( 'itemprop', $out );
	}

	/**
	 * Once the towel has a review of its own, that is the one worth quoting —
	 * and it needs no "about" label, because it is about this page.
	 */
	public function test_review_proof_prefers_the_products_own_review(): void {
		Functions\when( 'get_option' )->justReturn( array( 'zirafa' => 42 ) );
		Functions\when( 'wc_get_product' )->justReturn( new \WC_Product( 'Žirafa', '790', true, 42 ) );
		Functions\when( 'get_comments' )->justReturn(
			array(
				(object) array(
					'comment_ID'      => 12,
					'comment_author'  => 'Jelena',
					'comment_content' => 'Stigao brzo, mekan i baš onakav kao na slici.',
				),
			)
		);
		Functions\when( 'get_comment_meta' )->justReturn( 5 );

		$wc = new WooCommerce( 'cosypaw', new Catalog() );

		ob_start();
		$wc->review_proof();
		$out = (string) ob_get_clean();

		$this->assertStringContainsString( 'Jelena', $out );
		$this->assertStringNotContainsString( 'o proizvodu', $out );
	}

	/**
	 * The invitation replaces an empty tab and leaves a full one alone.
	 */
	public function test_reviews_tab_intro_only_wraps_an_empty_tab(): void {
		Functions\when( 'get_option' )->justReturn( array( 'zirafa' => 42 ) );

		$product = new \WC_Product( 'Žirafa', '790', true, 42 );
		Functions\when( 'wc_get_product' )->justReturn( $product );

		$wc   = new WooCommerce( 'cosypaw', new Catalog() );
		$tabs = array( 'reviews' => array( 'callback' => static fn() => print 'WOO' ) );

		$empty = $wc->reviews_tab_intro( $tabs );
		ob_start();
		call_user_func( $empty['reviews']['callback'] );
		$out = (string) ob_get_clean();

		$this->assertStringContainsString( 'Budi prvi', $out );
		$this->assertStringContainsString( 'Žirafa', $out );
		// Wrapping, not replacing: WooCommerce's own tab still runs.
		$this->assertStringContainsString( 'WOO', $out );

		$product->set_review_count( 4 );
		$this->assertSame( $tabs, $wc->reviews_tab_intro( $tabs ) );
	}

	/**
	 * Another product's flag is not this product's news.
	 */
	public function test_added_panel_stays_off_a_product_that_was_not_added(): void {
		Functions\when( 'get_option' )->justReturn( array( 'zirafa' => 42 ) );
		Functions\when( 'wc_get_product' )->justReturn( new \WC_Product( 'Žirafa', '790', true, 42 ) );

		$wc = new WooCommerce( 'cosypaw', new Catalog() );

		$_GET['cosypaw-added'] = '99';
		ob_start();
		$wc->added_panel();
		$out = (string) ob_get_clean();
		unset( $_GET['cosypaw-added'] );

		$this->assertSame( '', $out );
	}

	/**
	 * The flag rides back on the redirect WooCommerce was already making — and
	 * never over a shop that sends its customers to the cart instead.
	 */
	public function test_add_to_cart_redirect_carries_the_flag_without_overruling_the_shop(): void {
		Functions\when( 'get_option' )->justReturn( array( 'zirafa' => 42 ) );

		$wc = new WooCommerce( 'cosypaw', new Catalog() );

		$_REQUEST['add-to-cart'] = '42';
		$flagged                 = $wc->add_to_cart_redirect( '' );
		$settled                 = $wc->add_to_cart_redirect( 'http://example.test/korpa/' );
		unset( $_REQUEST['add-to-cart'] );

		$this->assertStringContainsString( 'cosypaw-added=42', $flagged );
		$this->assertSame( 'http://example.test/korpa/', $settled );
	}

	/**
	 * A package is already sold as a package, and anything the shop added by
	 * hand has no motif to carry — neither gets the route.
	 */
	public function test_bundle_cta_stays_off_an_unmapped_product(): void {
		Functions\when( 'get_option' )->justReturn( array( 'zirafa' => 42 ) );
		Functions\when( 'wc_get_product' )->justReturn( new \WC_Product( 'Trio', '2190', true, 900 ) );

		$wc = new WooCommerce( 'cosypaw', new Catalog() );

		ob_start();
		$wc->bundle_cta();

		$this->assertSame( '', (string) ob_get_clean() );
	}

	/**
	 * The shared facts are printed under every seeded product, so the written
	 * description never has to repeat them twenty times.
	 */
	public function test_product_specs_print_the_shared_facts(): void {
		Functions\when( 'get_option' )->justReturn( array( 'zirafa' => 42 ) );
		Functions\when( 'wc_get_product' )->justReturn( new \WC_Product( 'Žirafa', '790', true, 42 ) );

		$wc = new WooCommerce( 'cosypaw', new Catalog() );

		ob_start();
		$wc->product_specs();
		$out = (string) ob_get_clean();

		$this->assertStringContainsString( 'cosypaw-specs', $out );
		$this->assertStringContainsString( 'Materijal', $out );
		$this->assertStringContainsString( 'Održavanje', $out );
	}

	/**
	 * Nothing the shop added by hand gets the theme's spec list bolted onto it.
	 */
	public function test_product_specs_stay_off_a_product_we_did_not_seed(): void {
		Functions\when( 'get_option' )->justReturn( array( 'zirafa' => 42 ) );
		Functions\when( 'wc_get_product' )->justReturn( new \WC_Product( 'Nešto drugo', '500', true, 4242 ) );

		$wc = new WooCommerce( 'cosypaw', new Catalog() );

		ob_start();
		$wc->product_specs();

		$this->assertSame( '', (string) ob_get_clean() );
	}

	/**
	 * An English or Russian visitor reads the override typed on the product
	 * screen; an empty override leaves the Serbian excerpt alone.
	 */
	public function test_short_description_prefers_the_per_locale_override(): void {
		Functions\when( 'get_option' )->justReturn( array( 'zirafa' => 42 ) );
		Functions\when( 'wc_get_product' )->justReturn( new \WC_Product( 'Žirafa', '790', true, 42 ) );
		Functions\when( 'determine_locale' )->justReturn( 'en_US' );
		Functions\when( 'get_post_meta' )->alias(
			static fn( $id, $key ) => '_cosypaw_desc_en_US' === $key ? 'A long-necked friend.' : ''
		);

		$wc = new WooCommerce( 'cosypaw', new Catalog() );

		$this->assertSame(
			'A long-necked friend.',
			$wc->translate_short_description( 'Žirafa duga vrata.' )
		);
	}
}
