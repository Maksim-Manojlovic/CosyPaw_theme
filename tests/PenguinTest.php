<?php
/**
 * Unit tests for the penguin: one variable product in six colours
 * (PenguinColours) and the six-penguin package (PenguinFamily).
 *
 * @package CosyPaw\Tests
 */

declare(strict_types=1);

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
	require_once __DIR__ . '/stubs-variable.php';
	require_once dirname( __DIR__ ) . '/inc/Stock.php';
	require_once dirname( __DIR__ ) . '/inc/Catalog.php';
	require_once dirname( __DIR__ ) . '/inc/WooCommerce.php';
	require_once dirname( __DIR__ ) . '/inc/ProductLine.php';
	require_once dirname( __DIR__ ) . '/inc/Swatches.php';
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
			Functions\stubEscapeFunctions();
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
		 * Every colour's photograph joins the product's gallery, ahead of the
		 * pictures it held, so they are thumbnails beside the main picture;
		 * the main picture itself is not repeated, and a rerun adds nothing.
		 *
		 * @return void
		 */
		public function test_the_colour_photographs_join_the_gallery(): void {
			// Six variations already there, each with its own photograph; the
			// blue one is the product's own (77).
			foreach ( array_keys( PenguinColours::COLOURS ) as $i => $colour ) {
				$variation = new \WC_Product_Variation();
				$variation->set_attributes( array( 'boja' => $colour ) );
				$variation->set_image_id( 0 === $i ? 77 : 200 + $i );
				$variation->save();
			}

			$colours = new PenguinColours();
			$colours->convert();

			$parent = \WC_Product_Variable::$saved[10];
			$this->assertSame( array( 201, 202, 203, 204, 205, 55 ), $parent->props['gallery_image_ids'] );

			$colours->convert();
			$this->assertSame( array( 201, 202, 203, 204, 205, 55 ), \WC_Product_Variable::$saved[10]->props['gallery_image_ids'] );
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
		 * On the product page the dots stand in for the dropdown, which stays
		 * in the markup; any other attribute is left as WooCommerce made it.
		 *
		 * @return void
		 */
		public function test_the_dots_replace_the_colour_dropdown(): void {
			$colours = new PenguinColours();
			$select  = '<select id="boja"></select>';

			$html = $colours->swatch_field(
				$select,
				array(
					'attribute' => 'Boja',
					'options'   => array( 'Plava', 'Braon', 'Roze', 'Nepoznata' ),
					'selected'  => 'Braon',
					'id'        => 'boja',
				)
			);

			$this->assertStringContainsString( 'data-colour-swatches="boja"', $html );
			$this->assertSame( 3, substr_count( $html, 'type="radio"' ), 'unknown colours get no dot' );
			$this->assertStringContainsString( 'value="Braon" checked', $html );
			$this->assertStringContainsString( '--swatch: #b08a62', $html );
			$this->assertStringContainsString( $select, $html );

			$other = array(
				'attribute' => 'Veličina',
				'options'   => array( 'S', 'M' ),
			);
			$this->assertSame( $select, $colours->swatch_field( $select, $other ) );

			$lone = array(
				'attribute' => 'Boja',
				'options'   => array( 'Plava' ),
			);
			$this->assertSame( $select, $colours->swatch_field( $select, $lone ), 'one colour is nothing to choose' );
		}

		/**
		 * The penguin's catalogue row gets its buyable colours, in the product
		 * page's order, each with the variation to put in the cart.
		 *
		 * @return void
		 */
		public function test_the_card_gets_the_colours_that_can_be_bought(): void {
			$make = static fn( int $id, string $colour, bool $in_stock ) => new class( $id, $colour, $in_stock ) extends \WC_Product {
				private string $colour;
				private bool $stock;

				public function __construct( int $id, string $colour, bool $stock ) {
					parent::__construct( 'Pingvin', '790', true, $id );
					$this->colour = $colour;
					$this->stock  = $stock;
				}

				public function get_attributes(): array {
					return array( 'boja' => $this->colour );
				}

				public function get_image_id(): int {
					return $this->get_id() + 1000;
				}

				public function is_in_stock(): bool {
					return $this->stock;
				}
			};

			$variations = array(
				101 => $make( 101, 'Braon', true ),
				102 => $make( 102, 'Plava', true ),
				103 => $make( 103, 'Siva', false ),
				104 => $make( 104, 'Roze', true ),
			);

			$parent = new class( 'Pingvin', '790', true, 10 ) extends \WC_Product {
				public function is_type( $type ): bool {
					return 'variable' === $type;
				}

				public function get_children(): array {
					return array( 101, 102, 103, 104 );
				}
			};

			Functions\when( 'wc_get_product' )->alias( fn( $id ) => 10 === (int) $id ? $parent : ( $variations[ (int) $id ] ?? false ) );
			Functions\when( 'wp_get_attachment_image_src' )->alias( fn( int $id ) => array( 'https://x.test/img-' . $id . '.webp', 600, 800 ) );
			Functions\when( 'wp_get_attachment_image_srcset' )->alias( fn( int $id ) => 'https://x.test/img-' . $id . '.webp 600w' );
			Functions\when( 'add_query_arg' )->alias( fn( array $args ) => '/?' . http_build_query( $args ) );
			Functions\when( 'esc_url_raw' )->returnArg();

			$rows = ( new PenguinColours() )->card_colours(
				array(
					array(
						'id'         => 'zirafa',
						'product_id' => 5,
					),
					array(
						'id'         => 'pingvin',
						'product_id' => 10,
					),
				)
			);

			$this->assertArrayNotHasKey( 'colours', $rows[0] );

			$colours = $rows[1]['colours'];
			$this->assertSame( array( 'Plava', 'Braon', 'Roze' ), array_column( $colours, 'value' ), 'order of the page, sold-out Siva left out' );
			$this->assertSame( 102, $colours[0]['variation_id'] );
			$this->assertSame( 'https://x.test/img-1101.webp', $colours[1]['image'] );
			$this->assertSame( '#b08a62', $colours[1]['swatch'] );
			$this->assertSame( '/?add-to-cart=10&variation_id=101&attribute_boja=Braon', $colours[1]['url'] );
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
