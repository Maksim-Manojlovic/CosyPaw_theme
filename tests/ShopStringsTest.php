<?php
/**
 * Unit tests for the WooCommerce string layer.
 *
 * @package CosyPaw\Tests
 */

declare(strict_types=1);

namespace Theme\Tests;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use Theme\ShopStrings;

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once dirname( __DIR__ ) . '/inc/ShopStrings.php';

final class ShopStringsTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		Functions\stubs(
			array(
				'add_filter'       => true,
				'determine_locale' => 'sr_RS',
			)
		);
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * The bug that started this: WooCommerce's sr_RS gives "Subtotal" and
	 * "Total" the same word, so the cart printed "Укупно 2.970" above the
	 * package saving and "Укупно 1.980" below it, and the first number is the
	 * one a customer reads.
	 */
	public function test_the_subtotal_row_stops_calling_itself_the_total(): void {
		$strings = new ShopStrings();

		$subtotal = $strings->filter_gettext( 'Укупно', 'Subtotal', 'woocommerce' );
		$total    = $strings->filter_gettext( 'Укупно', 'Total', 'woocommerce' );

		$this->assertSame( 'Međuzbir', $subtotal );
		$this->assertSame( 'Ukupno', $total );
		$this->assertNotSame( $subtotal, $total );
	}

	/**
	 * Everything else is transliterated, which is what covers the strings
	 * nobody listed — and the ones a WooCommerce update will add.
	 *
	 * @dataProvider cyrillic_provider
	 *
	 * @param string $cyrillic What WooCommerce prints.
	 * @param string $latin    What the shop should print.
	 */
	public function test_serbian_cyrillic_becomes_latin( string $cyrillic, string $latin ): void {
		$this->assertSame( $latin, ShopStrings::to_latin( $cyrillic ) );
	}

	/**
	 * Strings taken off the live cart and checkout.
	 *
	 * @return array<string,array{0:string,1:string}>
	 */
	public static function cyrillic_provider(): array {
		return array(
			'cart column'    => array( 'Количина', 'Količina' ),
			'remove item'    => array( 'Уклони ставку', 'Ukloni stavku' ),
			'thumbnail'      => array( 'Сличица', 'Sličica' ),
			'update cart'    => array( 'Ажурирање корпе', 'Ažuriranje korpe' ),
			'checkout'       => array( 'Наставите са плаћањем', 'Nastavite sa plaćanjem' ),
			'cart totals'    => array( 'Укупна вредност корпе', 'Ukupna vrednost korpe' ),
			'digraph nj'     => array( 'куповина њих', 'kupovina njih' ),
			'digraph dz'     => array( 'џак', 'džak' ),
			'latin passes'   => array( 'Ušteda na paketima', 'Ušteda na paketima' ),
			'numbers pass'   => array( '2.970 RSD', '2.970 RSD' ),
		);
	}

	/**
	 * Other people's plugins — and the theme's own Latin copy — are left alone.
	 */
	public function test_only_woocommerce_is_touched(): void {
		$strings = new ShopStrings();

		$this->assertSame( 'Укупно', $strings->filter_gettext( 'Укупно', 'Subtotal', 'some-plugin' ) );
		$this->assertSame( 'Ušteda', $strings->filter_gettext( 'Ušteda', 'Saving', 'cosypaw' ) );
	}

	/**
	 * An English or Russian visitor gets WooCommerce exactly as it ships.
	 */
	public function test_other_languages_are_untouched(): void {
		Functions\when( 'determine_locale' )->justReturn( 'ru_RU' );

		$strings = new ShopStrings();

		// Russian is Cyrillic too, and must not be mangled into Latin.
		$this->assertSame( 'Итого', $strings->filter_gettext( 'Итого', 'Subtotal', 'woocommerce' ) );
	}

	/**
	 * Prices read the same everywhere. WooCommerce renders RSD as "рсд";
	 * Catalog::format_price() has always written "RSD".
	 */
	public function test_the_currency_reads_as_the_rest_of_the_site_writes_it(): void {
		$strings = new ShopStrings();

		$this->assertSame( 'RSD', $strings->filter_currency_symbol( 'рсд', 'RSD' ) );
		$this->assertSame( '€', $strings->filter_currency_symbol( '€', 'EUR' ) );
	}
}
