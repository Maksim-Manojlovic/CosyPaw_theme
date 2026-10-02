<?php
/**
 * Unit tests for the Cuddle Puff pillows and the long towels — the product
 * lines built on \Theme\ProductLine after the cloths.
 *
 * @package CosyPaw\Tests
 */

declare(strict_types=1);

namespace {
	if ( ! class_exists( 'WP_Post' ) ) {
		/**
		 * Minimal WP_Post: an id is all ProductLine::ensure() reads.
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
		 * Just enough of WC_Product_Simple for ProductLine::ensure(): records
		 * what was set and hands out ids on save.
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
	use Theme\LongTowels;
	use Theme\Pillows;

	if ( ! defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', __DIR__ . '/' );
	}
	if ( ! defined( 'OBJECT' ) ) {
		define( 'OBJECT', 'OBJECT' );
	}

	require_once __DIR__ . '/stubs.php';
	require_once dirname( __DIR__ ) . '/inc/ProductLine.php';
	require_once dirname( __DIR__ ) . '/inc/Cloths.php';
	require_once dirname( __DIR__ ) . '/inc/Pillows.php';
	require_once dirname( __DIR__ ) . '/inc/LongTowels.php';

	final class ProductLinesTest extends TestCase {

		/** @var array<string,mixed> */
		private array $options = array();

		/** @var array<int,array<string,string>> */
		private array $meta = array();

		/** @var array<string,int> Category slug => term id handed out. */
		private array $terms = array();

		protected function setUp(): void {
			parent::setUp();
			Monkey\setUp();

			\WC_Product_Simple::$saved = array();
			$this->options             = array();
			$this->meta                = array();
			$this->terms               = array(
				Pillows::CATEGORY            => 61,
				LongTowels::CATEGORY         => 62,
				'peskiri'                    => 7, // WooCommerce::TOWEL_CATEGORY.
			);

			Functions\stubTranslationFunctions();
			Functions\stubs(
				array(
					'add_action'             => true,
					'add_filter'             => true,
					'is_wp_error'            => false,
					'current_user_can'       => true,
					'get_template_directory' => '/nonexistent-theme',
					'get_post'               => null,
					'get_post_thumbnail_id'  => 0,
					'get_page_by_path'       => null,
				)
			);

			Functions\when( 'get_term_by' )->alias( fn( string $field, string $slug ) => isset( $this->terms[ $slug ] ) ? (object) array( 'term_id' => $this->terms[ $slug ] ) : false );
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
		 * A sellable product double.
		 *
		 * @param int    $id    Product id.
		 * @param string $price Price.
		 * @return \WC_Product
		 */
		private static function product( int $id, string $price ): \WC_Product {
			return new class( 'Proizvod', $price, true, $id ) extends \WC_Product {
				public function get_status(): string {
					return 'publish';
				}

				public function add_to_cart_url(): string {
					return '?add-to-cart=' . $this->get_id();
				}
			};
		}

		/**
		 * Every colour of both shapes is its own product, in the pillow
		 * category, at the launch price, with its Yoast fields.
		 *
		 * @return void
		 */
		public function test_it_creates_every_pillow_in_its_own_category(): void {
			$this->assertSame( count( Pillows::ITEMS ), ( new Pillows() )->ensure() );

			$this->assertCount( 8, \WC_Product_Simple::$saved );
			foreach ( \WC_Product_Simple::$saved as $id => $props ) {
				$this->assertSame( array( 61 ), $props['category_ids'] );
				$this->assertSame( (string) Pillows::PRICE, $props['regular_price'] );
				$this->assertSame( 'publish', $props['status'] );
				$this->assertNotSame( '', $this->meta[ $id ]['_yoast_wpseo_title'] );
				$this->assertNotSame( '', $this->meta[ $id ]['_yoast_wpseo_metadesc'] );
			}

			$this->assertSame( array_keys( Pillows::ITEMS ), array_keys( $this->options[ Pillows::MAP_OPTION ] ) );
			$this->assertSame( 1599, Pillows::PRICE );
		}

		/**
		 * The long towels are not towels in the package sense: their own
		 * category, never the one that turns a product into a motif and counts
		 * it towards a 2+1.
		 *
		 * @return void
		 */
		public function test_long_towels_stay_out_of_the_towel_category(): void {
			$this->assertSame( 4, ( new LongTowels() )->ensure() );

			foreach ( \WC_Product_Simple::$saved as $props ) {
				$this->assertSame( array( 62 ), $props['category_ids'] );
				$this->assertNotContains( 7, $props['category_ids'] );
				$this->assertSame( '499', $props['regular_price'] );
			}
		}

		/**
		 * Each line keeps its own map and version, so bumping one never
		 * re-runs another.
		 *
		 * @return void
		 */
		public function test_each_line_runs_once_per_version_on_its_own(): void {
			( new Pillows() )->maybe_ensure();
			$this->assertCount( 8, \WC_Product_Simple::$saved );
			$this->assertArrayNotHasKey( LongTowels::MAP_OPTION, $this->options );

			\WC_Product_Simple::$saved = array();
			( new Pillows() )->maybe_ensure();
			$this->assertSame( array(), \WC_Product_Simple::$saved );

			( new LongTowels() )->maybe_ensure();
			$this->assertCount( 4, \WC_Product_Simple::$saved );
		}

		/**
		 * Every item carries what the cards and the media library need: a
		 * photograph that ships with the theme, alt text, a colour and its
		 * swatch, and Yoast fields within the lengths search results show.
		 *
		 * @return void
		 */
		public function test_every_item_is_complete(): void {
			$dirs = array(
				Pillows::class    => dirname( __DIR__ ) . '/assets/pillows/',
				LongTowels::class => dirname( __DIR__ ) . '/assets/long-towels/',
			);

			foreach ( $dirs as $line => $dir ) {
				foreach ( $line::ITEMS as $key => $item ) {
					$this->assertFileExists( $dir . $item['image'], $key );
					foreach ( array_keys( $item['gallery'] ?? array() ) as $file ) {
						$this->assertFileExists( $dir . $file, $key );
					}
					$this->assertNotSame( '', $item['alt'], $key );
					$this->assertNotSame( '', $item['color'], $key );
					$this->assertMatchesRegularExpression( '/^#[0-9a-f]{6}$/', $item['swatch'], $key );
					$this->assertLessThanOrEqual( 65, mb_strlen( $item['seo_title'] ), $key );
					$this->assertLessThanOrEqual( 160, mb_strlen( $item['seo_description'] ), $key );
				}
			}
		}

		/**
		 * The swatch cards get one group per shape, in ITEMS order, each
		 * colour carrying its name and swatch; a shape with nothing on sale
		 * gets no card.
		 *
		 * @return void
		 */
		public function test_groups_fold_the_colours_into_one_card_per_shape(): void {
			$this->options[ Pillows::MAP_OPTION ] = array(
				'kruzic-braon'  => 1,
				'kruzic-sivi'   => 2,
				'kockasti-roze' => 3,
			);

			Functions\when( 'wc_get_product' )->alias( fn( int $id ) => self::product( $id, '1599' ) );
			Functions\when( 'get_permalink' )->alias( fn( int $id ) => 'https://cosypaw.test/p/' . $id );

			$rows   = Pillows::products();
			$groups = Pillows::groups( $rows );

			$this->assertSame( array( Pillows::ROUND, Pillows::SQUARE ), array_column( $groups, 'key' ) );
			$this->assertSame( array( 1, 2 ), array_column( $groups[0]['variants'], 'id' ) );
			$this->assertSame( array( 'Braon', 'Siva' ), array_column( $groups[0]['variants'], 'color' ) );
			$this->assertSame( Pillows::ITEMS['kockasti-roze']['swatch'], $groups[1]['variants'][0]['swatch'] );
			$this->assertSame( 1599, Pillows::from_price( $rows ) );

			$this->assertSame( array( Pillows::ROUND ), array_column( Pillows::groups( array_slice( $rows, 0, 2 ) ), 'key' ) );
		}

		/**
		 * A product belongs to exactly one line: the maps are separate, so a
		 * pillow is never translated or given specs as a towel or a cloth.
		 *
		 * @return void
		 */
		public function test_ownership_reads_each_lines_own_map(): void {
			$this->options[ Pillows::MAP_OPTION ]    = array( 'kruzic-braon' => 9 );
			$this->options[ LongTowels::MAP_OPTION ] = array( 'meda-krem' => 10 );

			$this->assertTrue( Pillows::owns( 9 ) );
			$this->assertFalse( Pillows::owns( 10 ) );
			$this->assertTrue( LongTowels::owns( 10 ) );
			$this->assertFalse( Cloths::is_cloth( 9 ) );
			$this->assertFalse( Pillows::owns( 0 ) );
		}
	}
}
