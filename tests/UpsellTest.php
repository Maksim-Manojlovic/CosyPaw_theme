<?php
/**
 * Unit tests for the cart / checkout / thank-you package offer.
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
use Theme\Upsell;

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
require_once dirname( __DIR__ ) . '/inc/Upsell.php';

final class UpsellTest extends TestCase {

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
	 * Product id of the first mapped motif (one towel).
	 *
	 * @var int
	 */
	private const MOTIF_ID = 501;

	/**
	 * Product id of the Duo package (two towels).
	 *
	 * @var int
	 */
	private const DUO_ID = 102;

	/**
	 * The cart the offer is rendered against.
	 *
	 * @var \WC_Cart
	 */
	private \WC_Cart $cart;

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		Functions\stubs(
			array(
				'add_action'                => true,
				'add_filter'                => true,
				'is_admin'                  => false,
				'wp_doing_ajax'             => false,
				'esc_url'                   => static fn( $url ) => $url,
				'esc_url_raw'               => static fn( $url ) => $url,
				'esc_html'                  => static fn( $text ) => $text,
				'esc_attr'                  => static fn( $text ) => $text,
				'wp_kses_post'              => static fn( $html ) => $html,
				'__'                        => static fn( $text ) => $text,
				'esc_html__'                => static fn( $text ) => $text,
				'esc_attr__'                => static fn( $text ) => $text,
				'determine_locale'          => 'sr_RS',
				'get_post_meta'             => '',
				'get_post_status'           => 'publish',
				'wc_get_cart_url'           => 'http://example.test/korpa/',
				'wc_get_checkout_url'       => 'http://example.test/placanje/',
				'wc_get_page_permalink'     => 'http://example.test/prodavnica/',
				'home_url'                  => 'http://example.test/',
				'get_template_directory_uri' => 'http://example.test/theme',
			)
		);

		// The array form is the only one this module uses, and the pill's
		// single-argument stub cannot express it.
		Functions\when( 'add_query_arg' )->alias(
			static fn( $args, $url = '' ) => $url . '?' . http_build_query( (array) $args )
		);

		// BundlePricing counts a product as a towel only while the motif map
		// knows about it; without this a cart of three holds three of nothing.
		Functions\when( 'get_option' )->justReturn( array( 'zirafa' => self::MOTIF_ID ) );

		Functions\when( 'apply_filters' )->alias(
			static function ( string $hook, $value = null ) {
				if ( 'cosypaw_catalog_packages' === $hook ) {
					$product_id = 100;
					foreach ( $value as &$package ) {
						$id                    = (string) $package['id'];
						$package['price']      = self::LIVE_PRICES[ $id ] ?? $package['price'];
						$package['per']        = (int) round( $package['price'] / (int) $package['qty'] );
						$package['product_id'] = ++$product_id;
					}
					unset( $package );

					return $value;
				}

				if ( 'cosypaw_catalog_products' === $hook ) {
					$product_id = 500;
					foreach ( $value as &$motif ) {
						++$product_id;
						$motif['product_id'] = $product_id;
						$motif['price']      = self::LIVE_PRICES['solo'];
						$motif['available']  = true;
						$motif['permalink']  = 'http://example.test/peskiric/' . $motif['id'] . '/';
					}
					unset( $motif );

					return $value;
				}

				// BundlePricing reads its towels-per-unit map through a filter.
				return $value;
			}
		);

		$this->cart = new \WC_Cart();

		// The theme calls WC()->cart; Brain\Monkey cannot stub a method chain,
		// so WC() returns a throwaway object carrying this test's cart. It has
		// no shipping() method, which is exactly the "cannot establish a
		// threshold" case: free_shipping() then stays silent.
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
	 * Render the cart panel against a cart holding these lines.
	 *
	 * @param array<int,array{id:int,qty:int,price:int}> $lines Product id, quantity, unit price.
	 * @return string
	 */
	private function panel( array $lines ): string {
		$contents = array();
		foreach ( $lines as $line ) {
			$contents[] = array(
				'product_id' => $line['id'],
				'quantity'   => $line['qty'],
				'data'       => new \WC_Product( 'Žirafa', (string) $line['price'], true, $line['id'] ),
			);
		}

		$this->cart = new \WC_Cart( $contents );

		$pricing = new BundlePricing( 'cosypaw', new Catalog() );
		$pricing->apply_bundle_discount( $this->cart );

		$upsell = new Upsell( 'cosypaw', $pricing, new Catalog() );

		ob_start();
		$upsell->cart_panel();

		return (string) ob_get_clean();
	}

	/**
	 * Two towels are one short of a Trio, and the cart says what that costs.
	 */
	public function test_the_cart_quotes_the_next_towel(): void {
		$markup = $this->panel( array( array( 'id' => self::MOTIF_ID, 'qty' => 2, 'price' => self::LIVE_PRICES['solo'] ) ) );

		$this->assertStringContainsString( 'cosypaw-upsell', $markup );
		// The third towel closes a 2.970 Trio down to 1.980: 490 for the towel,
		// 500 saved against buying it on its own.
		$this->assertStringContainsString( '490 RSD', $markup );
		$this->assertStringContainsString( '500 RSD', $markup );
	}

	/**
	 * Each tile on the strip is an add-to-cart that comes back to the cart,
	 * rather than to the product page add_to_cart_redirect() would choose.
	 */
	public function test_the_strip_adds_from_the_cart(): void {
		$markup = $this->panel( array( array( 'id' => self::MOTIF_ID, 'qty' => 2, 'price' => self::LIVE_PRICES['solo'] ) ) );

		$this->assertStringContainsString( 'cosypaw-upsell__track', $markup );
		$this->assertStringContainsString( 'add-to-cart=' . ( self::MOTIF_ID + 1 ) . '&', $markup );
		$this->assertStringContainsString( Upsell::STAY_PARAM . '=' . Upsell::STAY_CART, $markup );
		$this->assertStringContainsString( 'http://example.test/korpa/', $markup );
	}

	/**
	 * The towel already in the cart is the one towel its owner has chosen, and
	 * a second copy of it is the least interesting thing on sale. The strip
	 * offers the other nineteen.
	 */
	public function test_the_strip_leaves_out_what_is_already_in_the_cart(): void {
		$markup = $this->panel( array( array( 'id' => self::MOTIF_ID, 'qty' => 2, 'price' => self::LIVE_PRICES['solo'] ) ) );

		$this->assertStringNotContainsString( 'add-to-cart=' . self::MOTIF_ID . '&', $markup );
		$this->assertSame( 19, substr_count( $markup, 'class="cosypaw-upsell__slide"' ) );
	}

	/**
	 * A package carries its motifs as item data rather than as products, so a
	 * cart holding one says nothing about which single towels its owner has
	 * seen: the strip offers the whole catalogue.
	 */
	public function test_a_package_only_cart_gets_the_whole_strip(): void {
		$markup = $this->panel( array( array( 'id' => self::DUO_ID, 'qty' => 1, 'price' => self::LIVE_PRICES['duo'] ) ) );

		// A Duo is two towels, so the third is still the cheap one.
		$this->assertStringContainsString( 'cosypaw-upsell', $markup );
		$this->assertStringContainsString( 'add-to-cart=' . self::MOTIF_ID . '&', $markup );
		$this->assertSame( 20, substr_count( $markup, 'class="cosypaw-upsell__slide"' ) );
	}

	/**
	 * A whole Trio is already at an optimum — the fourth towel is full price.
	 * With no threshold to quote either, there is nothing to say and no box is
	 * printed to say it in.
	 *
	 * The slot around it stays, empty: it is what CartUpsell re-renders into
	 * when a towel comes back out of the cart, and a cart that had nothing to
	 * offer could otherwise never offer anything again without a full reload.
	 */
	public function test_a_complete_package_gets_no_panel(): void {
		$markup = $this->panel( array( array( 'id' => self::MOTIF_ID, 'qty' => 3, 'price' => self::LIVE_PRICES['solo'] ) ) );

		$this->assertSame( '<div class="cosypaw-upsell-slot" data-upsell-panel></div>', $markup );
	}

	/**
	 * The checkout carries the same offer, on a link that returns to the
	 * checkout — following it back to the cart would be a detour around the
	 * form the buyer is standing in front of.
	 */
	public function test_the_checkout_returns_to_the_checkout(): void {
		$this->cart = new \WC_Cart(
			array(
				array(
					'product_id' => self::MOTIF_ID,
					'quantity'   => 2,
					'data'       => new \WC_Product( 'Žirafa', (string) self::LIVE_PRICES['solo'], true, self::MOTIF_ID ),
				),
			)
		);

		$pricing = new BundlePricing( 'cosypaw', new Catalog() );
		$upsell  = new Upsell( 'cosypaw', $pricing, new Catalog() );

		ob_start();
		$upsell->checkout_panel();
		$markup = (string) ob_get_clean();

		$this->assertStringContainsString( Upsell::STAY_PARAM . '=' . Upsell::STAY_CHECKOUT, $markup );
		$this->assertStringContainsString( 'http://example.test/placanje/', $markup );
	}

	/**
	 * The thank-you page offers the rest of the range — never the towel the
	 * order it is confirming already contains.
	 */
	public function test_the_thankyou_page_skips_what_was_just_bought(): void {
		$order = new \WC_Mock_Order( 'processing', array( new \WC_Mock_Order_Item( self::MOTIF_ID ) ) );

		Functions\when( 'wc_get_order' )->justReturn( $order );

		ob_start();
		( new Upsell( 'cosypaw', new BundlePricing( 'cosypaw', new Catalog() ), new Catalog() ) )->thankyou_picks( 7 );
		$markup = (string) ob_get_clean();

		$this->assertStringContainsString( 'cosypaw-picks', $markup );
		$this->assertSame( 3, substr_count( $markup, 'cosypaw-picks__card' ) );
		$this->assertStringNotContainsString( '/peskiric/zirafa/', $markup );
	}

	/**
	 * The same template renders a failed order, where the page is asking to be
	 * paid rather than confirming anything. Nothing is sold over the top of it.
	 */
	public function test_a_failed_order_gets_no_picks(): void {
		$order = new \WC_Mock_Order( 'failed', array( new \WC_Mock_Order_Item( self::MOTIF_ID ) ) );

		Functions\when( 'wc_get_order' )->justReturn( $order );

		ob_start();
		( new Upsell( 'cosypaw', new BundlePricing( 'cosypaw', new Catalog() ), new Catalog() ) )->thankyou_picks( 7 );

		$this->assertSame( '', (string) ob_get_clean() );
	}

	/**
	 * An empty cart has no collaterals and no hook of its own, so the strip is
	 * appended to the page content the cart shortcode has already rendered.
	 */
	public function test_an_empty_cart_gets_the_whole_strip_under_its_content(): void {
		Functions\when( 'is_cart' )->justReturn( true );
		Functions\when( 'is_main_query' )->justReturn( true );
		Functions\when( 'in_the_loop' )->justReturn( true );

		$this->cart = new \WC_Cart();

		$upsell = new Upsell( 'cosypaw', new BundlePricing( 'cosypaw', new Catalog() ), new Catalog() );
		$markup = $upsell->append_empty_cart_picks( '<p class="cart-empty">Vaša korpa je trenutno prazna.</p>' );

		$this->assertStringContainsString( 'Vaša korpa je trenutno prazna.', $markup );
		$this->assertStringContainsString( 'cosypaw-upsell--empty', $markup );
		$this->assertStringContainsString( 'data-upsell-slider', $markup );
		$this->assertStringContainsString( 'cosypaw-stay=cart', $markup );
	}

	/**
	 * A cart with something in it already carries the strip beside its totals.
	 * A second copy under the table would be the same offer made twice.
	 */
	public function test_a_filled_cart_leaves_its_content_alone(): void {
		Functions\when( 'is_cart' )->justReturn( true );
		Functions\when( 'is_main_query' )->justReturn( true );
		Functions\when( 'in_the_loop' )->justReturn( true );

		$this->cart = new \WC_Cart(
			array(
				array(
					'product_id' => self::MOTIF_ID,
					'quantity'   => 1,
					'data'       => new \WC_Product( 'Žirafa', (string) self::LIVE_PRICES['solo'], true, self::MOTIF_ID ),
				),
			)
		);

		$upsell = new Upsell( 'cosypaw', new BundlePricing( 'cosypaw', new Catalog() ), new Catalog() );

		$this->assertSame( '<p>korpa</p>', $upsell->append_empty_cart_picks( '<p>korpa</p>' ) );
	}

	/**
	 * The filter runs on every page's content; only the cart page is its own.
	 */
	public function test_every_other_page_keeps_its_content(): void {
		Functions\when( 'is_cart' )->justReturn( false );
		Functions\when( 'is_main_query' )->justReturn( true );
		Functions\when( 'in_the_loop' )->justReturn( true );

		$this->cart = new \WC_Cart();

		$upsell = new Upsell( 'cosypaw', new BundlePricing( 'cosypaw', new Catalog() ), new Catalog() );

		$this->assertSame( '<p>o nama</p>', $upsell->append_empty_cart_picks( '<p>o nama</p>' ) );
	}
}
