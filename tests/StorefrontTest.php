<?php
/**
 * Unit tests for the shared storefront data.
 *
 * @package CosyPaw\Tests
 */

declare(strict_types=1);

namespace Theme\Tests;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use Theme\Storefront;

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once dirname( __DIR__ ) . '/inc/Catalog.php';
require_once dirname( __DIR__ ) . '/inc/WooCommerce.php';
require_once dirname( __DIR__ ) . '/inc/ProductCategories.php';
require_once dirname( __DIR__ ) . '/inc/Storefront.php';

final class StorefrontTest extends TestCase {

	/**
	 * Product id => subcategory slugs it is filed under.
	 *
	 * @var array<int,string[]>
	 */
	private array $filed = array();

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		$this->filed = array();

		Functions\stubTranslationFunctions();
		Functions\when( 'has_term' )->alias(
			fn( string $slug, string $taxonomy, int $id ): bool => in_array( $slug, $this->filed[ $id ] ?? array(), true )
		);
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * A catalogue row.
	 *
	 * @param string $id         Motif id.
	 * @param int    $product_id WooCommerce product id, 0 for none.
	 * @return array<string,mixed>
	 */
	private function row( string $id, int $product_id = 0 ): array {
		return array(
			'id'         => $id,
			'name'       => ucfirst( $id ),
			'product_id' => $product_id,
		);
	}

	/**
	 * Until the shop files anything, every motif is in the unfiled group —
	 * the page still shows them all, just without chapter headings.
	 *
	 * @return void
	 */
	public function test_nothing_filed_puts_every_motif_in_the_unfiled_group(): void {
		$groups = Storefront::by_subcategory( array( $this->row( 'zeka', 1 ), $this->row( 'krofna', 2 ) ) );

		$this->assertSame( array( '' ), array_keys( $groups ) );
		$this->assertCount( 2, $groups[''] );
	}

	/**
	 * Groups come out in ProductCategories order, empty ones dropped, the
	 * unfiled group last.
	 *
	 * @return void
	 */
	public function test_groups_follow_the_category_order_and_skip_empty_ones(): void {
		$this->filed = array(
			1 => array( 'zalogajcici' ),
			2 => array( 'zivotinjice' ),
		);

		$groups = Storefront::by_subcategory(
			array( $this->row( 'krofna', 1 ), $this->row( 'zeka', 2 ), $this->row( 'lala', 3 ) )
		);

		$this->assertSame( array( 'zivotinjice', 'zalogajcici', '' ), array_keys( $groups ) );
		$this->assertSame( 'zeka', $groups['zivotinjice'][0]['id'] );
		$this->assertSame( 'lala', $groups[''][0]['id'] );
	}

	/**
	 * A towel filed twice shows once, under whichever of its subcategories
	 * the page lists first.
	 *
	 * @return void
	 */
	public function test_a_motif_in_two_subcategories_is_shown_once(): void {
		$this->filed = array( 1 => array( 'peskirici-u-torbi', 'zivotinjice' ) );
		$rows        = array( $this->row( 'zirafa-torba', 1 ) );

		$this->assertSame( array( 'peskirici-u-torbi' ), array_keys( Storefront::by_subcategory( $rows ) ) );
		$this->assertSame(
			array( 'zivotinjice' ),
			array_keys( Storefront::by_subcategory( $rows, array( 'zivotinjice', 'peskirici-u-torbi' ) ) )
		);
	}

	/**
	 * A motif with no product behind it cannot be filed, and is not lost.
	 *
	 * @return void
	 */
	public function test_a_motif_without_a_product_is_unfiled(): void {
		$groups = Storefront::by_subcategory( array( $this->row( 'demo' ) ) );

		$this->assertSame( 'demo', $groups[''][0]['id'] );
	}

	/**
	 * Retired motifs are off sale; unmapped ones (no 'available' key) are not.
	 *
	 * @return void
	 */
	public function test_in_stock_treats_a_missing_flag_as_on_sale(): void {
		$this->assertTrue( Storefront::in_stock( array() ) );
		$this->assertTrue( Storefront::in_stock( array( 'available' => true ) ) );
		$this->assertFalse( Storefront::in_stock( array( 'available' => false ) ) );
	}

	/**
	 * Standalone photo alt names the product, not just the motif.
	 *
	 * @return void
	 */
	public function test_motif_alt_names_the_product(): void {
		$this->assertSame(
			'Dečiji peškir od mikrofibera, motiv Zeka',
			Storefront::motif_alt( array( 'name' => 'Zeka' ) )
		);
	}
}
