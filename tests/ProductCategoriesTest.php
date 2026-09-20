<?php
/**
 * Unit tests for the towel category tree.
 *
 * @package CosyPaw\Tests
 */

declare(strict_types=1);

namespace Theme\Tests;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use Theme\ProductCategories;

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once dirname( __DIR__ ) . '/inc/WooCommerce.php';
require_once dirname( __DIR__ ) . '/inc/ProductCategories.php';

final class ProductCategoriesTest extends TestCase {

	/**
	 * Terms the fake taxonomy holds, slug => (object) term.
	 *
	 * @var array<string,object>
	 */
	private array $terms = array();

	/**
	 * Next term id the fake wp_insert_term() hands out.
	 *
	 * @var int
	 */
	private int $next_id = 100;

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		$this->terms   = array();
		$this->next_id = 100;

		Functions\when( 'get_term_by' )->alias(
			function ( string $field, string $value, string $taxonomy ) {
				return $this->terms[ $value ] ?? false;
			}
		);

		Functions\when( 'wp_insert_term' )->alias(
			function ( string $name, string $taxonomy, array $args = array() ) {
				$slug = (string) ( $args['slug'] ?? $name );

				$this->terms[ $slug ] = (object) array(
					'term_id' => ++$this->next_id,
					'name'    => $name,
					'slug'    => $slug,
					'parent'  => (int) ( $args['parent'] ?? 0 ),
				);

				return array( 'term_id' => $this->terms[ $slug ]->term_id );
			}
		);

		Functions\when( 'is_wp_error' )->justReturn( false );
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * The four subcategories, hanging off Peškirići.
	 *
	 * @return void
	 */
	public function test_it_creates_the_four_subcategories_under_the_towel_category(): void {
		$created = ( new ProductCategories() )->ensure_terms();

		// Four children, plus the parent this shop did not have.
		$this->assertSame( 4, $created );
		$this->assertArrayHasKey( 'peskirici', $this->terms );

		$parent = $this->terms['peskirici']->term_id;
		foreach ( ProductCategories::SUBCATEGORIES as $slug => $name ) {
			$this->assertArrayHasKey( $slug, $this->terms, "missing: $slug" );
			$this->assertSame( $name, $this->terms[ $slug ]->name );
			$this->assertSame( $parent, $this->terms[ $slug ]->parent, "$slug is not under Peškirići" );
		}
	}

	/**
	 * An existing Peškirići is reused rather than duplicated — it is the term
	 * every towel in the shop is already filed under, and a second one would
	 * quietly unmap all of them.
	 *
	 * @return void
	 */
	public function test_it_hangs_the_tree_off_the_towel_category_the_shop_already_has(): void {
		$this->terms['peskirici'] = (object) array(
			'term_id' => 16,
			'name'    => 'Peškirići',
			'slug'    => 'peskirici',
			'parent'  => 0,
		);

		( new ProductCategories() )->ensure_terms();

		$this->assertSame( 16, $this->terms['peskirici']->term_id );
		foreach ( array_keys( ProductCategories::SUBCATEGORIES ) as $slug ) {
			$this->assertSame( 16, $this->terms[ $slug ]->parent );
		}
	}

	/**
	 * Running it twice creates nothing the second time, and running it after a
	 * category was deleted restores only that one.
	 *
	 * @return void
	 */
	public function test_it_is_idempotent_and_fills_in_only_what_is_missing(): void {
		$categories = new ProductCategories();
		$categories->ensure_terms();

		$this->assertSame( 0, $categories->ensure_terms() );

		unset( $this->terms['zalogajcici'] );

		$this->assertSame( 1, $categories->ensure_terms() );
		$this->assertArrayHasKey( 'zalogajcici', $this->terms );
	}

	/**
	 * A renamed category keeps its name. The shop's wording wins over ours.
	 *
	 * @return void
	 */
	public function test_it_leaves_a_renamed_category_alone(): void {
		$categories = new ProductCategories();
		$categories->ensure_terms();

		$this->terms['zivotinjice']->name = 'Naše životinjice';
		$categories->ensure_terms();

		$this->assertSame( 'Naše životinjice', $this->terms['zivotinjice']->name );
	}

	/**
	 * existing_terms() reports what is actually in the taxonomy, not what the
	 * class wishes were there.
	 *
	 * @return void
	 */
	public function test_it_reports_which_subcategories_exist(): void {
		$categories = new ProductCategories();

		$this->assertSame( array(), $categories->existing_terms() );

		$categories->ensure_terms();
		unset( $this->terms['cvetici-i-listici'] );

		$this->assertSame(
			array( 'peskirici-u-torbi', 'zivotinjice', 'zalogajcici' ),
			array_keys( $categories->existing_terms() )
		);
	}

	/**
	 * The parent is not one of the children. Putting a towel in "Peškirići" is
	 * what makes it a towel; the subcategory is ticked alongside, never instead.
	 *
	 * @return void
	 */
	public function test_the_parent_is_not_one_of_the_children(): void {
		$this->assertCount( 4, ProductCategories::SUBCATEGORIES );
		$this->assertArrayNotHasKey( 'peskirici', ProductCategories::SUBCATEGORIES );
	}
}
