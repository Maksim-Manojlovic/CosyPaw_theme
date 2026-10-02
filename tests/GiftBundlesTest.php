<?php
/**
 * Unit tests for the poklon paketi.
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
		 * Just enough of WC_Product_Simple for ProductLine::ensure().
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
	use Theme\Catalog;
	use Theme\Cloths;
	use Theme\GiftBundles;
	use Theme\LongTowels;
	use Theme\Pillows;
	use Theme\WooCommerce;

	if ( ! defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', __DIR__ . '/' );
	}
	if ( ! defined( 'OBJECT' ) ) {
		define( 'OBJECT', 'OBJECT' );
	}

	require_once __DIR__ . '/stubs.php';
	require_once dirname( __DIR__ ) . '/inc/Catalog.php';
	require_once dirname( __DIR__ ) . '/inc/WooCommerce.php';
	require_once dirname( __DIR__ ) . '/inc/BundlePricing.php';
	require_once dirname( __DIR__ ) . '/inc/ProductLine.php';
	require_once dirname( __DIR__ ) . '/inc/Cloths.php';
	require_once dirname( __DIR__ ) . '/inc/Pillows.php';
	require_once dirname( __DIR__ ) . '/inc/LongTowels.php';
	require_once dirname( __DIR__ ) . '/inc/GiftBundles.php';

	final class GiftBundlesTest extends TestCase {

		/** @var array<string,mixed> */
		private array $options = array();

		/** @var array<int,array<string,string>> */
		private array $meta = array();

		/** @var array<int,string> Product id => live price. */
		private array $prices = array();

		protected function setUp(): void {
			parent::setUp();
			Monkey\setUp();
			GiftBundles::flush();

			\WC_Product_Simple::$saved = array();
			$this->options             = array();
			$this->meta                = array();
			$this->prices              = array();

			Functions\stubTranslationFunctions();
			Functions\stubs(
				array(
					'add_action'                 => true,
					'add_filter'                 => true,
					'is_wp_error'                => false,
					'current_user_can'           => true,
					'get_template_directory'     => '/nonexistent-theme',
					'get_template_directory_uri' => 'https://cosypaw.test/theme',
					'get_post'                   => null,
					'get_post_thumbnail_id'      => 0,
					'get_page_by_path'           => null,
				)
			);

			Functions\when( 'get_term_by' )->justReturn( (object) array( 'term_id' => 70 ) );
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

			// The packages are only priced once mapped to real products, as on
			// a seeded shop.
			Functions\when( 'apply_filters' )->alias(
				static function ( string $tag, $value ) {
					if ( 'cosypaw_catalog_packages' === $tag ) {
						foreach ( $value as $i => $package ) {
							$value[ $i ]['product_id'] = 900 + $i;
						}
					}
					return $value;
				}
			);

			Functions\when( 'wc_get_product' )->alias(
				fn( int $id ) => isset( $this->prices[ $id ] ) ? new class( 'Proizvod', $this->prices[ $id ], true, $id ) extends \WC_Product {
					public function get_status(): string {
						return 'publish';
					}

					public function add_to_cart_url(): string {
						return '?add-to-cart=' . $this->get_id();
					}
				} : null
			);
			Functions\when( 'get_permalink' )->alias( fn( int $id ) => 'https://cosypaw.test/p/' . $id );
		}

		protected function tearDown(): void {
			Monkey\tearDown();
			parent::tearDown();
		}

		/**
		 * Map every product a bundle can hold, and every bundle, at launch
		 * prices — a freshly seeded shop.
		 *
		 * @return void
		 */
		private function seed_shop(): void {
			$id  = 1;
			$map = function ( array $keys, int $price ) use ( &$id ): array {
				$out = array();
				foreach ( $keys as $key ) {
					$out[ $key ]          = $id;
					$this->prices[ $id ] = (string) $price;
					++$id;
				}
				return $out;
			};

			$this->options[ WooCommerce::PRODUCT_MAP_OPTION ] = $map( array_column( ( new Catalog() )->products(), 'id' ), Catalog::UNIT_PRICE );
			$this->options[ Pillows::MAP_OPTION ]             = $map( array_keys( Pillows::ITEMS ), Pillows::PRICE );
			$this->options[ LongTowels::MAP_OPTION ]          = $map( array_keys( LongTowels::ITEMS ), LongTowels::PRICE );
			$this->options[ Cloths::MAP_OPTION ]              = $map( array( 'avokado-limeta', 'avokado-zalfija' ), Cloths::PRICE ) + $map( array( Cloths::SET ), Cloths::SET_PRICE );

			$bundles = array();
			foreach ( GiftBundles::ITEMS as $key => $item ) {
				$bundles += $map( array( $key ), (int) $item['price'] );
			}
			$this->options[ GiftBundles::MAP_OPTION ] = $bundles;
		}

		/**
		 * All ten bundles are created in their own category at their own
		 * prices, with the Yoast fields — never in the towel category.
		 *
		 * @return void
		 */
		public function test_it_creates_every_bundle_at_its_price(): void {
			$this->assertSame( 10, ( new GiftBundles() )->ensure() );

			$map = $this->options[ GiftBundles::MAP_OPTION ];
			foreach ( GiftBundles::ITEMS as $key => $item ) {
				$props = \WC_Product_Simple::$saved[ $map[ $key ] ];
				$this->assertSame( array( 70 ), $props['category_ids'], $key );
				$this->assertSame( (string) $item['price'], $props['regular_price'], $key );
				$this->assertNotSame( '', $this->meta[ $map[ $key ] ]['_yoast_wpseo_title'], $key );
			}
		}

		/**
		 * Every bundle names real products, ships a photograph, and keeps its
		 * Yoast fields within what search results show.
		 *
		 * @return void
		 */
		public function test_every_bundle_is_complete(): void {
			$motifs = array_column( ( new Catalog() )->products(), 'id' );
			$lines  = array(
				'towel'  => array_flip( $motifs ),
				'long'   => LongTowels::ITEMS,
				'pillow' => Pillows::ITEMS,
				'cloths' => array( Cloths::SET => true ),
			);

			foreach ( GiftBundles::ITEMS as $key => $item ) {
				$this->assertFileExists( dirname( __DIR__ ) . '/assets/bundles/' . $item['image'], $key );
				$this->assertArrayHasKey( $item['occasion'], GiftBundles::OCCASIONS, $key );
				$this->assertNotSame( '', $item['includes'], $key );
				$this->assertLessThanOrEqual( 65, mb_strlen( $item['seo_title'] ), $key );
				$this->assertLessThanOrEqual( 160, mb_strlen( $item['seo_description'] ), $key );

				foreach ( $item['contains'] as $part ) {
					$this->assertArrayHasKey( $part[1], $lines[ $part[0] ], "$key holds {$part[0]} {$part[1]}" );
				}
			}
		}

		/**
		 * On a freshly seeded shop every bundle is on offer and cheaper than
		 * the cheapest way to buy its contents — towels priced as the cart
		 * would price them, so three are the 2+1 package.
		 *
		 * @return void
		 */
		public function test_every_bundle_saves_against_the_cheapest_way_to_buy_it(): void {
			$this->seed_shop();

			$offers = GiftBundles::offers();
			$this->assertSame( array_keys( GiftBundles::ITEMS ), array_column( $offers, 'key' ) );

			$by_key = array_column( $offers, null, 'key' );

			// Three towels at the 2+1 price, plus the pillow.
			$this->assertSame( Catalog::PACK_PRICE + Pillows::PRICE, $by_key['dobrodoslica-za-bebu']['separately'] );
			// Two towels are a part-filled package too, as BundlePricing has it.
			$this->assertSame( Catalog::PACK_PRICE + 2 * LongTowels::PRICE, $by_key['kupatilo-za-porodicu']['separately'] );
			// Four: one package and a single.
			$this->assertSame( Catalog::PACK_PRICE + Catalog::UNIT_PRICE, $by_key['za-vrtic']['separately'] );
			// The cloths at the set's price.
			$this->assertSame( Catalog::UNIT_PRICE + Cloths::SET_PRICE, $by_key['avokado-ljubav']['separately'] );

			foreach ( $offers as $offer ) {
				$this->assertGreaterThan( 0, $offer['saving'], $offer['key'] );
				$this->assertSame( $offer['separately'] - $offer['price'], $offer['saving'] );
			}
		}

		/**
		 * A bundle is withdrawn while anything it holds is off sale, or while
		 * it is no longer cheaper than its parts.
		 *
		 * @return void
		 */
		public function test_a_bundle_is_withdrawn_when_it_cannot_deliver_its_saving(): void {
			$this->seed_shop();

			// The roze kružić is off sale: the baby bundle cannot be packed.
			unset( $this->prices[ $this->options[ Pillows::MAP_OPTION ]['kruzic-roze'] ] );
			// The avocado bundle repriced in wp-admin to what its parts cost.
			$this->prices[ $this->options[ GiftBundles::MAP_OPTION ]['avokado-ljubav'] ] = (string) ( Catalog::UNIT_PRICE + Cloths::SET_PRICE );

			$keys = array_column( GiftBundles::offers(), 'key' );

			$this->assertNotContains( 'dobrodoslica-za-bebu', $keys );
			$this->assertNotContains( 'avokado-ljubav', $keys );
			$this->assertContains( 'novi-dom', $keys );
		}

		/**
		 * Asking for some bundles returns those, in the order asked.
		 *
		 * @return void
		 */
		public function test_offers_can_be_picked_and_grouped(): void {
			$this->seed_shop();

			$picked = GiftBundles::offers( array( 'praznicna-kutija', 'dobrodoslica-za-bebu' ) );
			$this->assertSame( array( 'praznicna-kutija', 'dobrodoslica-za-bebu' ), array_column( $picked, 'key' ) );

			$groups = GiftBundles::by_occasion( GiftBundles::offers() );
			$this->assertSame( array_keys( GiftBundles::OCCASIONS ), array_keys( $groups ) );
			$this->assertCount( 3, $groups['deca']['offers'] );
		}
	}
}
