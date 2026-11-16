<?php
/**
 * Unit tests for the penguin: one variable product in six colours
 * (PenguinColours) and the six-penguin package (PenguinFamily).
 *
 * @package CosyPaw\Tests
 */

declare(strict_types=1);

namespace {
	if ( ! class_exists( 'WC_Product_Attribute' ) ) {
		/**
		 * Records what the attribute was given.
		 */
		class WC_Product_Attribute {
			/** @var array<string,mixed> */
			public array $props = array();

			public function __call( string $method, array $args ) {
				if ( 0 === strpos( $method, 'set_' ) ) {
					$this->props[ substr( $method, 4 ) ] = $args[0];
				}
				return null;
			}
		}
	}

	if ( ! class_exists( 'WC_Product_Variation' ) ) {
		/**
		 * Records a saved variation.
		 */
		class WC_Product_Variation extends WC_Product {
			/** @var array<int,WC_Product_Variation> Saved variations by id. */
			public static array $saved = array();

			/** @var array<string,mixed> */
			public array $props = array();

			public function __call( string $method, array $args ) {
				if ( 0 === strpos( $method, 'set_' ) ) {
					$this->props[ substr( $method, 4 ) ] = $args[0];
				}
				return null;
			}

			public function get_attributes(): array {
				return (array) ( $this->props['attributes'] ?? array() );
			}

			public function save(): int {
				$id                 = 900 + count( self::$saved );
				self::$saved[ $id ] = $this;

				return $id;
			}
		}
	}

	if ( ! class_exists( 'WC_Product_Variable' ) ) {
		/**
		 * The parent once it has been made variable.
		 */
		class WC_Product_Variable extends WC_Product {
			/** @var array<int,WC_Product_Variable> Saved parents by id. */
			public static array $saved = array();

			/** @var array<int,int> Ids passed to sync(). */
			public static array $synced = array();

			/** @var array<string,mixed> */
			public array $props = array();

			public function __construct( int $id = 0 ) {
				parent::__construct( 'Pingvin', '790', true, $id );
				$this->props = self::$saved[ $id ]->props ?? array();
			}

			public function __call( string $method, array $args ) {
				if ( 0 === strpos( $method, 'set_' ) ) {
					$this->props[ substr( $method, 4 ) ] = $args[0];
				}
				return null;
			}

			public function is_type( $type ): bool {
				return 'variable' === $type;
			}

			public function get_attributes(): array {
				return (array) ( $this->props['attributes'] ?? array() );
			}

			public function get_default_attributes(): array {
				return (array) ( $this->props['default_attributes'] ?? array() );
			}

			public function get_children(): array {
				return array_keys( WC_Product_Variation::$saved );
			}

			public function save(): int {
				self::$saved[ $this->get_id() ] = $this;

				return $this->get_id();
			}

			public static function sync( $id ): void {
				self::$synced[] = (int) $id;
			}
		}
	}
}

namespace Theme\Tests {

	use Brain\Monkey;
	use Brain\Monkey\Functions;
	use PHPUnit\Framework\TestCase;
	use Theme\PenguinColours;
	use Theme\PenguinFamily;
	use Theme\WooCommerce;

	if ( ! defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', __DIR__ . '/' );
	}

	require_once __DIR__ . '/stubs.php';
	require_once dirname( __DIR__ ) . '/inc/Stock.php';
	require_once dirname( __DIR__ ) . '/inc/Catalog.php';
	require_once dirname( __DIR__ ) . '/inc/WooCommerce.php';
	require_once dirname( __DIR__ ) . '/inc/ProductLine.php';
	require_once dirname( __DIR__ ) . '/inc/PenguinColours.php';
	require_once dirname( __DIR__ ) . '/inc/PenguinFamily.php';

	final class PenguinTest extends TestCase {

		/** @var array<string,mixed> */
		private array $options = array();

		/** @var array<int,string> Product types set with wp_set_object_terms(). */
		private array $types = array();

		/** @var \WC_Product Simple stand-in for the live penguin product. */
		private \WC_Product $simple;

		protected function setUp(): void {
			parent::setUp();
			Monkey\setUp();

			\WC_Product_Variation::$saved = array();
			\WC_Product_Variable::$saved  = array();
			\WC_Product_Variable::$synced = array();
			$this->options                = array( WooCommerce::PRODUCT_MAP_OPTION => array( 'pingvin' => 10 ) );
			$this->types                  = array();

			// What the penguin is on the live shop today: a simple towel with
			// a photograph and a price.
			$this->simple = new class( 'Pingvin', '790', true, 10 ) extends \WC_Product {
				public function is_type( $type ): bool {
					return 'simple' === $type;
				}

				public function get_regular_price(): string {
					return '850';
				}

				public function get_image_id(): int {
					return 77;
				}

				public function is_in_stock(): bool {
					return true;
				}
			};

			Functions\stubTranslationFunctions();
			Functions\stubs(
				array(
					'add_action'             => true,
					'add_filter'             => true,
					'current_user_can'       => true,
					'is_admin'               => false,
					'get_template_directory' => '/nonexistent-theme',
				)
			);

			Functions\when( 'get_option' )->alias( fn( string $name, $fallback = false ) => $this->options[ $name ] ?? $fallback );
			Functions\when( 'update_option' )->alias(
				function ( string $name, $value ): bool {
					$this->options[ $name ] = $value;
					return true;
				}
			);
			Functions\when( 'wp_set_object_terms' )->alias(
				function ( int $id, $terms, string $taxonomy ): array {
					$this->types[ $id ] = (string) $terms;
					return array();
				}
			);
			Functions\when( 'wc_get_product' )->alias(
				function ( $id ) {
					if ( 10 === (int) $id ) {
						return \WC_Product_Variable::$saved[10] ?? $this->simple;
					}
					return \WC_Product_Variation::$saved[ (int) $id ] ?? false;
				}
			);
		}

		protected function tearDown(): void {
			Monkey\tearDown();
			parent::tearDown();
		}

		/**
		 * The simple penguin becomes a variable product with a "Boja"
		 * attribute and one variation per colour, at the product's own price,
		 * the blue one keeping its photograph.
		 *
		 * @return void
		 */
		public function test_it_makes_the_penguin_a_variable_product(): void {
			( new PenguinColours() )->maybe_run();

			$this->assertSame( 'variable', $this->types[10] );
			$this->assertSame( PenguinColours::VERSION, $this->options['cosypaw_penguin_colours_version'] );

			$parent = \WC_Product_Variable::$saved[10];
			$attr   = $parent->props['attributes'][ PenguinColours::ATTRIBUTE_KEY ];
			$this->assertSame( 'Boja', $attr->props['name'] );
			$this->assertSame( array_keys( PenguinColours::COLOURS ), $attr->props['options'] );
			$this->assertTrue( $attr->props['variation'] );
			$this->assertSame( array( 'boja' => 'Plava' ), $parent->props['default_attributes'] );
			$this->assertSame( array( 10 ), \WC_Product_Variable::$synced );

			$colours = array();
			foreach ( \WC_Product_Variation::$saved as $variation ) {
				$colours[] = $variation->props['attributes']['boja'];
				$this->assertSame( 10, $variation->props['parent_id'] );
				$this->assertSame( '850', $variation->props['regular_price'] );
				$this->assertSame( 'instock', $variation->props['stock_status'] );
				$this->assertSame( 'publish', $variation->props['status'] );
			}
			$this->assertSame( array_keys( PenguinColours::COLOURS ), $colours );

			// The blue variation keeps the product's own photograph.
			$blue = array_values( \WC_Product_Variation::$saved )[0];
			$this->assertSame( 77, $blue->props['image_id'] );
		}

		/**
		 * Run again, nothing is added: a colour that has a variation is left
		 * as the shop has it.
		 *
		 * @return void
		 */
		public function test_it_does_not_add_a_colour_twice(): void {
			$colours = new PenguinColours();
			$this->assertTrue( $colours->convert() );
			$this->assertCount( 6, \WC_Product_Variation::$saved );

			$this->assertTrue( $colours->convert() );
			$this->assertCount( 6, \WC_Product_Variation::$saved );
		}

		/**
		 * Only the version that is not yet done runs, and a shop without the
		 * penguin is not marked done.
		 *
		 * @return void
		 */
		public function test_it_waits_for_the_product_and_runs_once(): void {
			$this->options[ WooCommerce::PRODUCT_MAP_OPTION ] = array();

			$colours = new PenguinColours();
			$colours->maybe_run();
			$this->assertArrayNotHasKey( 'cosypaw_penguin_colours_version', $this->options );
			$this->assertSame( array(), \WC_Product_Variation::$saved );

			$this->options[ WooCommerce::PRODUCT_MAP_OPTION ] = array( 'pingvin' => 10 );
			$colours->maybe_run();
			$this->assertCount( 6, \WC_Product_Variation::$saved );

			$colours->maybe_run();
			$this->assertCount( 6, \WC_Product_Variation::$saved );
		}

		/**
		 * Every colour with a photograph has it in assets/penguins/.
		 *
		 * @return void
		 */
		public function test_colour_photographs_ship_with_the_theme(): void {
			foreach ( PenguinColours::COLOURS as $colour => $data ) {
				if ( '' === $data['image'] ) {
					$this->assertSame( PenguinColours::ORIGINAL, $colour );
					continue;
				}
				$this->assertFileExists( dirname( __DIR__ ) . '/assets/penguins/' . $data['image'], $colour );
				$this->assertNotSame( '', $data['alt'] );
			}
		}

		/**
		 * The package: one product, the price of six towels as two 3-for-2
		 * packages, photographs shipped, and it names every colour the
		 * penguin comes in.
		 *
		 * @return void
		 */
		public function test_the_family_package_is_defined(): void {
			$this->assertSame( array( 'porodica-pingvina' ), array_keys( PenguinFamily::ITEMS ) );

			$item = PenguinFamily::ITEMS['porodica-pingvina'];
			$this->assertSame( 2 * 1490, $item['price'] );
			$this->assertFileExists( dirname( __DIR__ ) . '/assets/penguins/' . $item['image'] );
			foreach ( array_keys( $item['gallery'] ) as $file ) {
				$this->assertFileExists( dirname( __DIR__ ) . '/assets/penguins/' . $file );
			}

			foreach ( array( 'plava', 'tamno plava', 'braon', 'roze', 'siva', 'tirkizna' ) as $colour ) {
				$this->assertStringContainsString( $colour, (string) $item['short'] );
			}
			$this->assertCount( 6, PenguinColours::COLOURS );
		}
	}
}
