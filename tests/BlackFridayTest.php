<?php
/**
 * Unit tests for "Crni petak": the gift steps, the substitutions when a gift
 * is sold out, and the cart lines that carry the gifts.
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
use Theme\Cloths;
use Theme\LongTowels;

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once __DIR__ . '/stubs.php';
require_once dirname( __DIR__ ) . '/inc/Stock.php';
require_once dirname( __DIR__ ) . '/inc/Swatches.php';
require_once dirname( __DIR__ ) . '/inc/Catalog.php';
require_once dirname( __DIR__ ) . '/inc/ProductNames.php';
require_once dirname( __DIR__ ) . '/inc/WooCommerce.php';
require_once dirname( __DIR__ ) . '/inc/CheckoutSetup.php';
require_once dirname( __DIR__ ) . '/inc/BundlePricing.php';
require_once dirname( __DIR__ ) . '/inc/ProductLine.php';
require_once dirname( __DIR__ ) . '/inc/Cloths.php';
require_once dirname( __DIR__ ) . '/inc/LongTowels.php';
require_once dirname( __DIR__ ) . '/inc/BlackFriday.php';

final class BlackFridayTest extends TestCase {

	/**
	 * Product ids the gift products are mapped to.
	 *
	 * @var array<string,int>
	 */
	private const TOWEL_IDS = array(
		'meda-krem'  => 71,
		'meda-roze'  => 72,
		'meda-plavi' => 73,
		'meda-bez'   => 74,
	);

	/**
	 * @var array<string,int>
	 */
	private const CLOTH_IDS = array(
		'avokado-limeta'  => 81,
		'avokado-zalfija' => 82,
		Cloths::SET       => 83,
	);

	/**
	 * Product ids a test has sold out.
	 *
	 * @var array<int,bool>
	 */
	private array $sold_out = array();

	/**
	 * Options as get_option() answers them.
	 *
	 * @var array<string,mixed>
	 */
	private array $options = array();

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		$this->sold_out = array();
		$this->options  = array(
			LongTowels::MAP_OPTION => self::TOWEL_IDS,
			Cloths::MAP_OPTION     => self::CLOTH_IDS,
		);

		Functions\stubTranslationFunctions();
		Functions\stubEscapeFunctions();
		Functions\stubs(
			array(
				'add_action'                 => true,
				'add_filter'                 => true,
				'is_admin'                   => false,
				'wp_doing_ajax'              => false,
				'current_user_can'           => true,
				'get_permalink'              => 'https://example.test/p/',
				'get_template_directory_uri' => 'https://example.test/wp-content/themes/cosypaw',
				'trailingslashit'            => static fn( $path ) => rtrim( (string) $path, '/' ) . '/',
				'wp_kses'                    => static fn( $html ) => $html,
			)
		);
		Functions\when( 'apply_filters' )->alias( static fn( $hook, $value ) => $value );
		Functions\when( 'get_option' )->alias( fn( string $name, $fallback = false ) => $this->options[ $name ] ?? $fallback );
		Functions\when( 'wc_get_product' )->alias( fn( int $id ) => $this->product( $id ) );

		$_COOKIE[ BlackFriday::PREVIEW_COOKIE ] = '1';
	}

	protected function tearDown(): void {
		unset( $_COOKIE[ BlackFriday::PREVIEW_COOKIE ] );
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * A published, purchasable product double; out of stock where the test
	 * says so.
	 *
	 * @param int $id Product id.
	 * @return \WC_Product
	 */
	private function product( int $id ): \WC_Product {
		$price   = in_array( $id, self::TOWEL_IDS, true ) ? '399' : '299';
		$product = new class( 'Proizvod ' . $id, $price, true, $id ) extends \WC_Product {
			public function get_status(): string {
				return 'publish';
			}

			public function add_to_cart_url(): string {
				return '?add-to-cart=' . $this->get_id();
			}
		};

		$product->in_stock = empty( $this->sold_out[ $id ] );

		return $product;
	}

	/**
	 * Availability as the shop answers it, through the products' stock.
	 *
	 * @return callable(string):bool
	 */
	private function available(): callable {
		return fn( string $slot ): bool => ( new BlackFriday( $this->pricing() ) )->available( $slot );
	}

	/**
	 * Package pricing with no packages mapped, so a cart is worth its lines.
	 *
	 * @return BundlePricing
	 */
	private function pricing(): BundlePricing {
		return new BundlePricing( 'cosypaw', new Catalog() );
	}

	/**
	 * A paid line worth $price.
	 *
	 * @param int $price Line price.
	 * @return array<string,mixed>
	 */
	private static function paid_line( int $price ): array {
		return array(
			'key'        => 'paid',
			'product_id' => 900,
			'quantity'   => 1,
			'data'       => new \WC_Product( 'Jastuk', (string) $price, true, 900 ),
		);
	}

	public function test_each_step_starts_at_its_amount(): void {
		$this->assertSame( 0, BlackFriday::tier_for( 0 ) );
		$this->assertSame( 0, BlackFriday::tier_for( 1489 ) );
		$this->assertSame( 1, BlackFriday::tier_for( 1490 ) );
		$this->assertSame( 1, BlackFriday::tier_for( 2499.99 ) );
		$this->assertSame( 2, BlackFriday::tier_for( 2500 ) );
		$this->assertSame( 3, BlackFriday::tier_for( 3500 ) );
		$this->assertSame( 3, BlackFriday::tier_for( 12000 ) );
	}

	public function test_the_gap_is_to_the_next_step_only(): void {
		$this->assertSame( 490, BlackFriday::gap( 1000 ) );
		$this->assertSame( 1010, BlackFriday::gap( 1490 ) );
		$this->assertSame( 1000, BlackFriday::gap( 2500 ) );
		$this->assertSame( 0, BlackFriday::gap( 3500 ) );
	}

	public function test_the_bar_fills_a_third_per_step(): void {
		$this->assertSame( 0.0, BlackFriday::progress( 0 ) );
		$this->assertSame( 33.3, BlackFriday::progress( 1490 ) );
		$this->assertSame( 66.7, BlackFriday::progress( 2500 ) );
		$this->assertSame( 83.3, BlackFriday::progress( 3000 ) );
		$this->assertSame( 100.0, BlackFriday::progress( 5000 ) );
	}

	public function test_the_window_is_belgrade_time_and_inclusive(): void {
		$zone = new \DateTimeZone( BlackFriday::TIMEZONE );

		$this->assertFalse( BlackFriday::in_window( ( new \DateTimeImmutable( '2026-11-19 23:59:59', $zone ) )->getTimestamp() ) );
		$this->assertTrue( BlackFriday::in_window( ( new \DateTimeImmutable( '2026-11-20 00:00:00', $zone ) )->getTimestamp() ) );
		$this->assertTrue( BlackFriday::in_window( ( new \DateTimeImmutable( '2026-12-15 23:59:59', $zone ) )->getTimestamp() ) );
		$this->assertFalse( BlackFriday::in_window( ( new \DateTimeImmutable( '2026-12-16 00:00:00', $zone ) )->getTimestamp() ) );
	}

	public function test_the_shop_can_switch_it_off_before_the_end(): void {
		$during = ( new \DateTimeImmutable( '2026-12-01 12:00:00', new \DateTimeZone( BlackFriday::TIMEZONE ) ) )->getTimestamp();
		unset( $_COOKIE[ BlackFriday::PREVIEW_COOKIE ] );

		$this->assertTrue( BlackFriday::active( $during ), 'On by default, with the option never saved.' );

		$this->options[ BlackFriday::ENABLED_OPTION ] = 'no';
		$this->assertFalse( BlackFriday::active( $during ) );

		$this->options[ BlackFriday::ENABLED_OPTION ] = 'yes';
		$this->assertTrue( BlackFriday::active( $during ) );
	}

	public function test_the_switch_sits_in_woocommerces_general_settings(): void {
		$settings = ( new BlackFriday( $this->pricing() ) )->settings( array( array( 'id' => 'woocommerce_store_address' ) ) );
		$ids      = array_column( $settings, 'id' );

		$this->assertContains( BlackFriday::ENABLED_OPTION, $ids );
		$this->assertSame( 'woocommerce_store_address', $ids[0], 'Appended, never in front of the shop\'s own fields.' );
	}

	public function test_outside_the_window_only_a_shop_manager_previewing_sees_it(): void {
		$before = ( new \DateTimeImmutable( '2026-11-19 12:00:00', new \DateTimeZone( BlackFriday::TIMEZONE ) ) )->getTimestamp();

		$this->assertTrue( BlackFriday::active( $before ) );

		unset( $_COOKIE[ BlackFriday::PREVIEW_COOKIE ] );
		$this->assertFalse( BlackFriday::active( $before ) );

		$_COOKIE[ BlackFriday::PREVIEW_COOKIE ] = '1';
		Functions\when( 'current_user_can' )->justReturn( false );
		$this->assertFalse( BlackFriday::active( $before ) );
	}

	public function test_no_step_no_gift(): void {
		$this->assertSame( array(), BlackFriday::gifts( 0, array(), $this->available() ) );
	}

	public function test_the_first_step_is_one_cloth(): void {
		$this->assertSame( array( 'cloth:avokado-limeta' ), BlackFriday::gifts( 1, array(), $this->available() ) );

		$this->sold_out[ self::CLOTH_IDS['avokado-limeta'] ] = true;
		$this->assertSame( array( 'cloth:avokado-zalfija' ), BlackFriday::gifts( 1, array(), $this->available() ) );
	}

	public function test_the_second_step_gives_the_towel_without_a_pick(): void {
		$this->assertSame( array( 'towel:meda-krem' ), BlackFriday::gifts( 2, array(), $this->available() ) );
	}

	public function test_the_second_step_gives_what_was_picked(): void {
		$this->assertSame(
			array( 'towel:meda-roze' ),
			BlackFriday::gifts( 2, array( 'pick' => 'towel', 'colour' => 'meda-roze' ), $this->available() )
		);
		$this->assertSame(
			array( 'set:' . Cloths::SET ),
			BlackFriday::gifts( 2, array( 'pick' => 'cloths', 'colour' => 'meda-roze' ), $this->available() )
		);
	}

	public function test_a_sold_out_colour_falls_back_to_another_colour(): void {
		$this->sold_out[ self::TOWEL_IDS['meda-roze'] ] = true;

		$this->assertSame(
			array( 'towel:meda-krem' ),
			BlackFriday::gifts( 2, array( 'pick' => 'towel', 'colour' => 'meda-roze' ), $this->available() )
		);
	}

	public function test_a_sold_out_pick_falls_back_to_the_other_gift(): void {
		foreach ( self::TOWEL_IDS as $id ) {
			$this->sold_out[ $id ] = true;
		}
		$this->assertSame( array( 'set:' . Cloths::SET ), BlackFriday::gifts( 2, array( 'pick' => 'towel' ), $this->available() ) );

		$this->sold_out = array( self::CLOTH_IDS['avokado-zalfija'] => true );
		$this->assertSame( array( 'towel:meda-krem' ), BlackFriday::gifts( 2, array( 'pick' => 'cloths' ), $this->available() ) );
	}

	public function test_with_both_gifts_gone_the_second_step_still_gives_a_cloth(): void {
		foreach ( self::TOWEL_IDS as $id ) {
			$this->sold_out[ $id ] = true;
		}
		$this->sold_out[ self::CLOTH_IDS['avokado-zalfija'] ] = true;

		$this->assertSame( array( 'cloth:avokado-limeta' ), BlackFriday::gifts( 2, array(), $this->available() ) );
	}

	public function test_the_third_step_gives_both(): void {
		$this->assertSame(
			array( 'towel:meda-plavi', 'set:' . Cloths::SET ),
			BlackFriday::gifts( 3, array( 'pick' => 'cloths', 'colour' => 'meda-plavi' ), $this->available() )
		);
	}

	public function test_the_third_step_without_the_set_gives_a_single_cloth(): void {
		$this->sold_out[ self::CLOTH_IDS['avokado-zalfija'] ] = true;

		$this->assertSame(
			array( 'towel:meda-krem', 'cloth:avokado-limeta' ),
			BlackFriday::gifts( 3, array(), $this->available() )
		);
	}

	public function test_a_pick_is_held_to_the_allowlist(): void {
		$this->assertSame(
			array( 'pick' => 'towel', 'colour' => 'meda-krem' ),
			BlackFriday::sanitize_choice( array( 'pick' => 'everything', 'colour' => 'meda-zlatni' ) )
		);
		$this->assertSame(
			array( 'pick' => 'cloths', 'colour' => 'meda-bez' ),
			BlackFriday::sanitize_choice( array( 'pick' => 'cloths', 'colour' => 'meda-bez' ) )
		);
	}

	public function test_only_known_products_can_fill_a_slot(): void {
		$this->assertNull( BlackFriday::row_for( 'towel:avokado-limeta' ) );
		$this->assertNull( BlackFriday::row_for( 'cloth:meda-krem' ) );
		$this->assertNull( BlackFriday::row_for( 'set:avokado-limeta' ) );
		$this->assertNull( BlackFriday::row_for( 'pillow:krug-roze' ) );
		$this->assertSame( 72, BlackFriday::row_for( 'towel:meda-roze' )['id'] );
	}

	public function test_the_cart_is_brought_to_exactly_the_earned_gifts(): void {
		$cart = new \WC_Cart(
			array(
				'paid'  => self::paid_line( 2600 ),
				'stale' => array(
					'key'                   => 'stale',
					'product_id'            => 81,
					'quantity'              => 2,
					BlackFriday::ITEM_KEY   => 'cloth:avokado-limeta',
					BlackFriday::VALUE_KEY  => 299,
					'data'                  => new \WC_Product( 'Krpica', '0', true, 81 ),
				),
			)
		);

		$changed = ( new BlackFriday( $this->pricing() ) )->apply( $cart, array( 'towel:meda-roze' ) );

		$this->assertTrue( $changed );

		$gifts = array_values( array_filter( $cart->get_cart(), array( BlackFriday::class, 'is_gift' ) ) );
		$this->assertCount( 1, $gifts );
		$this->assertSame( 'towel:meda-roze', $gifts[0][ BlackFriday::ITEM_KEY ] );
		$this->assertSame( 72, $gifts[0]['product_id'] );
		$this->assertSame( 1, $gifts[0]['quantity'] );
		$this->assertSame( 399, $gifts[0][ BlackFriday::VALUE_KEY ] );

		// Not left where "recently removed" would offer it back.
		$this->assertArrayNotHasKey( 'stale', $cart->get_removed_cart_contents() );
		$this->assertArrayHasKey( 'paid', $cart->get_cart() );
	}

	public function test_a_tampered_quantity_is_reset_and_a_duplicate_dropped(): void {
		$gift = static fn( string $key, int $qty ): array => array(
			'key'                 => $key,
			'product_id'          => 72,
			'quantity'            => $qty,
			BlackFriday::ITEM_KEY => 'towel:meda-roze',
			'data'                => new \WC_Product( 'Meda', '0', true, 72 ),
		);

		$cart = new \WC_Cart(
			array(
				'paid' => self::paid_line( 2600 ),
				'a'    => $gift( 'a', 5 ),
				'b'    => $gift( 'b', 1 ),
			)
		);

		( new BlackFriday( $this->pricing() ) )->apply( $cart, array( 'towel:meda-roze' ) );

		$this->assertSame( 1, $cart->get_cart_item( 'a' )['quantity'] );
		$this->assertSame( array(), $cart->get_cart_item( 'b' ) );
	}

	public function test_a_cart_already_right_is_left_alone(): void {
		$cart = new \WC_Cart( array( 'paid' => self::paid_line( 1600 ) ) );
		$bf   = new BlackFriday( $this->pricing() );

		$this->assertTrue( $bf->apply( $cart, array( 'cloth:avokado-limeta' ) ) );
		$this->assertFalse( $bf->apply( $cart, array( 'cloth:avokado-limeta' ) ) );
	}

	public function test_reconcile_reads_the_step_off_the_cart_and_recalculates_once(): void {
		$cart = new \WC_Cart( array( 'paid' => self::paid_line( 3600 ) ) );
		$bf   = new BlackFriday( $this->pricing() );

		$bf->reconcile( $cart );

		$slots = array_column( array_filter( $cart->get_cart(), array( BlackFriday::class, 'is_gift' ) ), BlackFriday::ITEM_KEY );
		$this->assertSame( array( 'towel:meda-krem', 'set:' . Cloths::SET ), array_values( $slots ) );
		$this->assertSame( 1, $cart->calculations );

		$bf->reconcile( $cart );
		$this->assertSame( 1, $cart->calculations, 'A cart that already holds its gifts is not recalculated again.' );
	}

	public function test_reconcile_takes_every_gift_back_once_the_promotion_is_over(): void {
		$cart = new \WC_Cart( array( 'paid' => self::paid_line( 3600 ) ) );
		$bf   = new BlackFriday( $this->pricing() );
		$bf->reconcile( $cart );
		$this->assertCount( 3, $cart->get_cart() );

		// Switched off by the shop, and nobody previewing.
		$this->options[ BlackFriday::ENABLED_OPTION ] = 'no';
		unset( $_COOKIE[ BlackFriday::PREVIEW_COOKIE ] );
		$bf->reconcile( $cart );

		$this->assertSame( array( 'paid' ), array_keys( $cart->get_cart() ) );
	}

	public function test_gift_lines_are_priced_at_zero(): void {
		$cart = new \WC_Cart(
			array(
				'paid' => self::paid_line( 2600 ),
				'gift' => array(
					'key'                 => 'gift',
					'product_id'          => 72,
					'quantity'            => 1,
					BlackFriday::ITEM_KEY => 'towel:meda-roze',
					'data'                => new \WC_Product( 'Meda', '399', true, 72 ),
				),
			)
		);

		( new BlackFriday( $this->pricing() ) )->price_gifts( $cart );

		$this->assertSame( '0', $cart->get_cart_item( 'gift' )['data']->get_price() );
		$this->assertSame( '2600', $cart->get_cart_item( 'paid' )['data']->get_price() );
	}

	public function test_a_gift_line_reads_gratis_with_its_value_struck(): void {
		$bf   = new BlackFriday( $this->pricing() );
		$gift = array(
			BlackFriday::ITEM_KEY  => 'towel:meda-roze',
			BlackFriday::VALUE_KEY => 399,
		);

		$this->assertSame( '<del>399 RSD</del> <strong class="gift-line__free">Gratis</strong>', $bf->line_price( '0 RSD', $gift ) );
		$this->assertSame( '<span class="gift-line__qty">1</span>', $bf->line_quantity( '<input>', 'k', $gift ) );
		$this->assertSame( '790 RSD', $bf->line_price( '790 RSD', array( 'quantity' => 1 ) ) );
	}

	public function test_the_ladder_says_how_far_the_next_gift_is(): void {
		$cart = new \WC_Cart( array( 'paid' => self::paid_line( 1000 ) ) );
		Functions\when( 'WC' )->justReturn( new \WC_Mock_WC( $cart ) );

		$html = ( new BlackFriday( $this->pricing() ) )->ladder();

		$this->assertStringContainsString( 'Još 490 RSD do prvog poklona', $html );
		$this->assertStringContainsString( 'width:22.4%', $html );
		$this->assertStringNotContainsString( 'is-reached', $html );
	}

	public function test_the_popup_offers_both_gifts_and_states_the_steps(): void {
		Functions\when( 'is_cart' )->justReturn( false );
		Functions\when( 'is_checkout' )->justReturn( false );

		if ( ! class_exists( '\WC_AJAX' ) ) {
			eval( 'class WC_AJAX { public static function get_endpoint( $a ) { return "/?wc-ajax=" . $a; } }' );
		}

		$html = ( new BlackFriday( $this->pricing() ) )->popup();

		$this->assertStringContainsString( 'data-gift-pick="towel"', $html );
		$this->assertStringContainsString( 'data-gift-pick="cloths"', $html );
		$this->assertSame( 4, substr_count( $html, 'name="gift-colour"' ) );
		$this->assertSame( 1, substr_count( $html, ' checked' ) );
		$this->assertStringContainsString( 'data-gift-auto="1"', $html );
		$this->assertStringContainsString( 'od 1.490 RSD', $html );
		$this->assertStringContainsString( 'od 3.500 RSD', $html );
		$this->assertStringContainsString( 'data-gift-fab', $html );
	}

	public function test_a_sold_out_colour_cannot_be_picked_in_the_popup(): void {
		Functions\when( 'is_cart' )->justReturn( true );
		Functions\when( 'is_checkout' )->justReturn( false );

		if ( ! class_exists( '\WC_AJAX' ) ) {
			eval( 'class WC_AJAX { public static function get_endpoint( $a ) { return "/?wc-ajax=" . $a; } }' );
		}

		$this->sold_out[ self::TOWEL_IDS['meda-krem'] ] = true;

		$html = ( new BlackFriday( $this->pricing() ) )->popup();

		$this->assertMatchesRegularExpression( '/value="meda-krem"[^>]*disabled/', $html );
		$this->assertMatchesRegularExpression( '/value="meda-roze"[^>]*checked/', $html );
		// On the cart it waits to be asked, and the cart has its own button.
		$this->assertStringContainsString( 'data-gift-auto="0"', $html );
		$this->assertStringNotContainsString( 'data-gift-fab', $html );
	}
}
