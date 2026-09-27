<?php
/**
 * Unit tests for the magične krpice.
 *
 * @package CosyPaw\Tests
 */

declare(strict_types=1);

namespace {
	if ( ! class_exists( 'WP_Post' ) ) {
		/**
		 * Minimal WP_Post: an id is all Cloths::ensure() reads.
		 */
		class WP_Post {
			public int $ID;

			public function __construct( int $id ) {
				$this->ID = $id;
			}
		}
	}

	if ( ! class_exists( 'WC_Product_Simple' ) ) {
		/**
		 * Just enough of WC_Product_Simple for Cloths::ensure(): records what
		 * was set and hands out ids on save.
		 */
		class WC_Product_Simple {
			/** @var array<int,array<string,mixed>> Saved products, id => props. */
			public static array $saved = array();

			/** @var array<string,mixed> */
			public array $props = array();

			private int $id = 0;

			public function __call( string $method, array $args ) {
				if ( 0 === strpos( $method, 'set_' ) ) {
					$this->props[ substr( $method, 4 ) ] = $args[0];
				}
				return null;
			}

			public function save(): int {
				if ( 0 === $this->id ) {
					$this->id = 500 + count( self::$saved );
				}
				self::$saved[ $this->id ] = $this->props;

				return $this->id;
			}
		}
	}
}

namespace Theme\Tests {

	use Brain\Monkey;
	use Brain\Monkey\Functions;
	use PHPUnit\Framework\TestCase;
	use Theme\Cloths;

	if ( ! defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', __DIR__ . '/' );
	}
	if ( ! defined( 'OBJECT' ) ) {
		define( 'OBJECT', 'OBJECT' );
	}

	require_once __DIR__ . '/stubs.php';
	require_once dirname( __DIR__ ) . '/inc/Cloths.php';

	final class ClothsTest extends TestCase {

		/** @var array<string,mixed> */
		private array $options = array();

		/** @var array<string,object> Products findable by slug. */
		private array $by_slug = array();

		/** @var array<int,array<string,string>> */
		private array $meta = array();

		protected function setUp(): void {
			parent::setUp();
			Monkey\setUp();

			\WC_Product_Simple::$saved = array();
			$this->options             = array();
			$this->by_slug             = array();
			$this->meta                = array();

			Functions\stubTranslationFunctions();
			Functions\stubs(
				array(
					'add_action'             => true,
					'add_filter'             => true,
					'is_wp_error'            => false,
					'current_user_can'       => true,
					'get_template_directory' => '/nonexistent-theme',
					'get_post'               => null,
				)
			);

			Functions\when( 'get_term_by' )->justReturn( (object) array( 'term_id' => 42 ) );
			Functions\when( 'get_page_by_path' )->alias( fn( string $slug ) => $this->by_slug[ $slug ] ?? null );
			Functions\when( 'get_option' )->alias( fn( string $name, $fallback = false ) => $this->options[ $name ] ?? $fallback );
			Functions\when( 'update_option' )->alias(
				function ( string $name, $value ): bool {
					$this->options[ $name ] = $value;
					return true;
				}
			);
			Functions\when( 'update_post_meta' )->alias(
				function ( int $id, string $key, $value ): bool {
					$this->meta[ $id ][ $key ] = (string) $value;
					return true;
				}
			);
		}

		protected function tearDown(): void {
			Monkey\tearDown();
			parent::tearDown();
		}

		/**
		 * Both cloths are created in their own category at the launch price,
		 * with the Yoast fields, and never in the towel category.
		 *
		 * @return void
		 */
		public function test_it_creates_both_cloths_outside_the_towel_category(): void {
			$this->assertSame( 2, ( new Cloths() )->ensure() );

			$this->assertCount( 2, \WC_Product_Simple::$saved );
			foreach ( \WC_Product_Simple::$saved as $id => $props ) {
				$this->assertSame( array( 42 ), $props['category_ids'] );
				$this->assertSame( (string) Cloths::PRICE, $props['regular_price'] );
				$this->assertSame( 'publish', $props['status'] );
				$this->assertNotSame( '', $this->meta[ $id ]['_yoast_wpseo_title'] );
				$this->assertNotSame( '', $this->meta[ $id ]['_yoast_wpseo_metadesc'] );
			}

			$this->assertSame( array( 'avokado-limeta', 'avokado-zalfija' ), array_keys( $this->options[ Cloths::MAP_OPTION ] ) );
		}

		/**
		 * A cloth the shop already made under the same slug is adopted, not
		 * duplicated.
		 *
		 * @return void
		 */
		public function test_it_adopts_a_product_that_already_has_the_slug(): void {
			$this->by_slug['magicna-krpica-avokado-limeta'] = new \WP_Post( 77 );

			$this->assertSame( 1, ( new Cloths() )->ensure() );
			$this->assertSame( 77, $this->options[ Cloths::MAP_OPTION ]['avokado-limeta'] );
		}

		/**
		 * Once an install is at the current version, a cloth deleted in
		 * wp-admin stays deleted.
		 *
		 * @return void
		 */
		public function test_it_runs_once_per_version(): void {
			$cloths = new Cloths();
			$cloths->maybe_ensure();
			$this->assertCount( 2, \WC_Product_Simple::$saved );

			\WC_Product_Simple::$saved = array();
			$cloths->maybe_ensure();
			$this->assertSame( array(), \WC_Product_Simple::$saved );
		}

		/**
		 * The storefront only lists cloths that exist, are published and can
		 * be bought.
		 *
		 * @return void
		 */
		public function test_products_lists_only_what_can_be_sold(): void {
			$this->options[ Cloths::MAP_OPTION ] = array(
				'avokado-limeta'  => 1,
				'avokado-zalfija' => 2,
			);

			$make = static fn( int $id, string $status ) => new class( 'Krpica', '299', true, $id, $status ) extends \WC_Product {
				private string $status;

				public function __construct( string $name, string $price, bool $purchasable, int $id, string $status ) {
					parent::__construct( $name, $price, $purchasable, $id );
					$this->status = $status;
				}

				public function get_status(): string {
					return $this->status;
				}

				public function add_to_cart_url(): string {
					return '?add-to-cart=' . $this->get_id();
				}
			};

			Functions\when( 'wc_get_product' )->alias( fn( int $id ) => 1 === $id ? $make( 1, 'publish' ) : $make( 2, 'draft' ) );
			Functions\when( 'get_permalink' )->alias( fn( int $id ) => 'https://cosypaw.test/p/' . $id );

			$rows = Cloths::products();

			$this->assertCount( 1, $rows );
			$this->assertSame( 1, $rows[0]['id'] );
			$this->assertSame( 299, $rows[0]['price'] );
			$this->assertSame( 299, Cloths::from_price( $rows ) );
		}

		/**
		 * With nothing on sale the price copy still has a number to quote.
		 *
		 * @return void
		 */
		public function test_from_price_falls_back_to_the_launch_price(): void {
			$this->assertSame( Cloths::PRICE, Cloths::from_price( array() ) );
		}

		/**
		 * is_cloth() knows the mapped ids and nothing else.
		 *
		 * @return void
		 */
		public function test_is_cloth_reads_the_map(): void {
			$this->options[ Cloths::MAP_OPTION ] = array( 'avokado-limeta' => 9 );

			$this->assertTrue( Cloths::is_cloth( 9 ) );
			$this->assertFalse( Cloths::is_cloth( 10 ) );
			$this->assertFalse( Cloths::is_cloth( 0 ) );
		}
	}
}
