<?php
/**
 * Unit tests for what "out of stock" does to the cart.
 *
 * @package CosyPaw\Tests
 */

declare(strict_types=1);

namespace Theme\Tests;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use Theme\Catalog;
use Theme\Stock;

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once __DIR__ . '/stubs.php';
require_once dirname( __DIR__ ) . '/inc/Stock.php';
require_once dirname( __DIR__ ) . '/inc/Catalog.php';
require_once dirname( __DIR__ ) . '/inc/ProductLine.php';
require_once dirname( __DIR__ ) . '/inc/Cloths.php';
require_once dirname( __DIR__ ) . '/inc/Pillows.php';
require_once dirname( __DIR__ ) . '/inc/LongTowels.php';
require_once dirname( __DIR__ ) . '/inc/GiftBundles.php';
require_once dirname( __DIR__ ) . '/inc/GiftBundlePicker.php';

final class StockTest extends TestCase {

	/**
	 * Notices added, [message, type].
	 *
	 * @var array<int,array{0:string,1:string}>
	 */
	private array $notices = array();

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		$this->notices = array();
		unset( $_REQUEST['cosypaw_motifs'] );

		Functions\stubTranslationFunctions();
		Functions\stubEscapeFunctions();
		Functions\when( 'wp_unslash' )->returnArg();
		Functions\when( 'sanitize_text_field' )->alias( static fn( $value ) => trim( (string) $value ) );
		Functions\when( 'get_template_directory_uri' )->justReturn( 'https://cosypaw.test/theme' );
		Functions\stubs(
			array(
				'add_action' => true,
				'add_filter' => true,
			)
		);
		Functions\when( 'wc_add_notice' )->alias(
			function ( string $message, string $type = 'success' ): void {
				$this->notices[] = array( $message, $type );
			}
		);

		// The Sova is out of stock in wp-admin; every other motif is in.
		Functions\when( 'apply_filters' )->alias(
			static function ( string $hook, $value = null ) {
				if ( 'cosypaw_catalog_products' === $hook ) {
					foreach ( $value as &$motif ) {
						$motif['available'] = true;
						$motif['sold_out']  = 'sova' === $motif['id'];
					}
					unset( $motif );
				}

				return $value;
			}
		);
	}

	protected function tearDown(): void {
		unset( $_REQUEST['cosypaw_motifs'] );
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Sold out is WooCommerce's stock status, and only that.
	 *
	 * @return void
	 */
	public function test_sold_out_reads_the_stock_status(): void {
		$product = new \WC_Product();
		$this->assertFalse( Stock::sold_out( $product ) );

		$product->in_stock = false;
		$this->assertTrue( Stock::sold_out( $product ) );

		$this->assertFalse( Stock::sold_out( null ) );
	}

	/**
	 * A package from the builder is refused when a towel in it is sold out,
	 * with the towel named — the package product itself is in stock.
	 *
	 * @return void
	 */
	public function test_a_package_with_a_sold_out_towel_is_refused(): void {
		$stock = new Stock( new Catalog() );

		$_REQUEST['cosypaw_motifs'] = 'zeka,sova,zeka';
		$this->assertFalse( $stock->validate_motifs( true, 900 ) );
		$this->assertCount( 1, $this->notices );
		$this->assertSame( 'error', $this->notices[0][1] );
		$this->assertStringContainsString( 'Sova', $this->notices[0][0] );
	}

	/**
	 * Towels in stock, or an add with no towels at all, pass untouched; an
	 * add already refused stays refused without a second notice.
	 *
	 * @return void
	 */
	public function test_a_package_in_stock_passes(): void {
		$stock = new Stock( new Catalog() );

		$_REQUEST['cosypaw_motifs'] = 'zeka,pingvin';
		$this->assertTrue( $stock->validate_motifs( true, 900 ) );

		unset( $_REQUEST['cosypaw_motifs'] );
		$this->assertTrue( $stock->validate_motifs( true, 900 ) );

		$_REQUEST['cosypaw_motifs'] = 'sova';
		$this->assertFalse( $stock->validate_motifs( false, 900 ) );

		$this->assertSame( array(), $this->notices );
	}

	/**
	 * A cart that already holds a package with a towel that has since sold
	 * out cannot check out: the line is named.
	 *
	 * @return void
	 */
	public function test_a_cart_holding_a_sold_out_towel_is_stopped(): void {
		$cart = new \WC_Cart(
			array(
				array(
					'cosypaw_motifs' => 'zeka,sova,pingvin',
					'data'           => new \WC_Product( 'Paket 3 kom' ),
				),
				array(
					'cosypaw_motifs' => 'zeka',
					'data'           => new \WC_Product( 'Single' ),
				),
			)
		);
		Functions\when( 'WC' )->justReturn( (object) array( 'cart' => $cart ) );

		( new Stock( new Catalog() ) )->check_cart();

		$this->assertCount( 1, $this->notices );
		$this->assertSame( 'error', $this->notices[0][1] );
		$this->assertStringContainsString( 'Sova', $this->notices[0][0] );
		$this->assertStringContainsString( 'Paket 3 kom', $this->notices[0][0] );
	}

	/**
	 * "Rasprodato" stands in for WooCommerce's own out-of-stock wording.
	 *
	 * @return void
	 */
	public function test_availability_says_rasprodato(): void {
		$stock   = new Stock( new Catalog() );
		$product = new \WC_Product();

		$this->assertSame( 'Na stanju', $stock->availability_text( 'Na stanju', $product ) );

		$product->in_stock = false;
		$this->assertSame( 'Rasprodato', $stock->availability_text( 'Nema na lageru', $product ) );
	}
}
