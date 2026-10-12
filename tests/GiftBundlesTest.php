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
	use Theme\GiftBundlePicker;
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
	require_once dirname( __DIR__ ) . '/inc/GiftBundlePicker.php';

	final class GiftBundlesTest extends TestCase {

		/** @var array<int,array{0:string,1:string}> Notices added, [message, type]. */
		private array $notices = array();

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
			$this->notices             = array();
			unset( $_REQUEST[ GiftBundlePicker::FIELD ] );

			Functions\stubTranslationFunctions();
			Functions\stubEscapeFunctions();
			Functions\when( 'wp_unslash' )->returnArg();
			Functions\when( 'sanitize_text_field' )->alias( static fn( $value ) => trim( (string) $value ) );
			Functions\when( 'wp_get_attachment_image_url' )->alias( static fn( int $id ) => 'https://cosypaw.test/media/' . $id . '.webp' );
			Functions\when( 'wc_add_notice' )->alias(
				function ( string $message, string $type = 'success' ): void {
					$this->notices[] = array( $message, $type );
				}
			);
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
			unset( $_REQUEST[ GiftBundlePicker::FIELD ] );
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

		/**
		 * Every part can be swapped like for like: a pillow for another colour
		 * of its shape, a towel for any shape, a long towel for another colour.
		 * The cloth set fills its own slot.
		 *
		 * @return void
		 */
		public function test_each_slot_offers_like_for_like_swaps(): void {
			$this->seed_shop();

			$slots = GiftBundles::slots( 'kutak-za-decju-sobu' );

			$this->assertSame( array( 'pillow', 'pillow', 'towel' ), array_column( $slots, 'line' ) );
			$this->assertSame( array( 'kruzic-zuti', 'kockasti-sivi', 'sova' ), array_column( $slots, 'default' ) );
			$this->assertSame( array( 'Jastuk Kružić', 'Jastuk Kockasti', 'Peškir' ), array_column( $slots, 'label' ) );

			$this->assertSame( array( 'kruzic-braon', 'kruzic-sivi', 'kruzic-zuti', 'kruzic-zeleni', 'kruzic-roze' ), array_keys( $slots[0]['choices'] ) );
			$this->assertSame( array( 'kockasti-roze', 'kockasti-braon', 'kockasti-sivi' ), array_keys( $slots[1]['choices'] ) );
			$this->assertSame( array_column( ( new Catalog() )->products(), 'id' ), array_keys( $slots[2]['choices'] ) );

			$this->assertSame( 'Žuta', $slots[0]['choices']['kruzic-zuti']['name'] );
			$this->assertSame( '#f0b417', $slots[0]['choices']['kruzic-zuti']['swatch'] );
			$this->assertSame( 'Sova', $slots[2]['choices']['sova']['name'] );
			$this->assertSame( '', $slots[2]['choices']['sova']['swatch'] );
			$this->assertStringEndsWith( 'sova-th.avif', $slots[2]['choices']['sova']['image'] );

			$long = GiftBundles::slots( 'kupatilo-za-porodicu' );
			$this->assertSame( array_keys( LongTowels::ITEMS ), array_keys( $long[0]['choices'] ) );

			$cloths = GiftBundles::slots( 'avokado-ljubav' )[1];
			$this->assertSame( array( Cloths::SET ), array_keys( $cloths['choices'] ) );
			$this->assertSame( 'Magične krpice', $cloths['label'] );

			foreach ( array_keys( GiftBundles::ITEMS ) as $key ) {
				$this->assertTrue( GiftBundles::packable( $key ), $key );
			}
		}

		/**
		 * A swap never changes what the bundle is worth: a colour repriced on
		 * its own, or one off sale, is not offered in a bundle.
		 *
		 * @return void
		 */
		public function test_only_what_sells_at_the_suggested_price_can_be_swapped_in(): void {
			$this->seed_shop();

			$pillows = $this->options[ Pillows::MAP_OPTION ];
			$this->prices[ $pillows['kruzic-roze'] ] = (string) ( Pillows::PRICE + 300 );
			unset( $this->prices[ $pillows['kruzic-zeleni'] ] );
			unset( $this->prices[ $this->options[ WooCommerce::PRODUCT_MAP_OPTION ]['panda'] ] );

			$slots = GiftBundles::slots( 'kutak-za-decju-sobu' );

			$this->assertSame( array( 'kruzic-braon', 'kruzic-sivi', 'kruzic-zuti' ), array_keys( $slots[0]['choices'] ) );
			$this->assertArrayNotHasKey( 'panda', $slots[2]['choices'] );
		}

		/**
		 * With the suggestion itself off sale, the slot still offers what
		 * sells at its launch price — but nothing is packed in its place
		 * unless it is picked.
		 *
		 * @return void
		 */
		public function test_a_suggestion_off_sale_has_to_be_swapped_by_hand(): void {
			$this->seed_shop();
			unset( $this->prices[ $this->options[ WooCommerce::PRODUCT_MAP_OPTION ]['sova'] ] );

			$towel = GiftBundles::slots( 'kutak-za-decju-sobu' )[2];
			$this->assertArrayNotHasKey( 'sova', $towel['choices'] );
			$this->assertArrayHasKey( 'zeka', $towel['choices'] );
			$this->assertTrue( GiftBundles::packable( 'kutak-za-decju-sobu' ) );

			$this->assertNull( GiftBundles::resolve( 'kutak-za-decju-sobu' ) );
			$this->assertSame(
				array( array( 'pillow', 'kruzic-zuti' ), array( 'pillow', 'kockasti-sivi' ), array( 'towel', 'zeka' ) ),
				GiftBundles::resolve( 'kutak-za-decju-sobu', array( 2 => 'zeka' ) )
			);
		}

		/**
		 * Picks are taken slot by slot; an unpicked slot keeps its suggestion,
		 * and anything that is not one of a slot's choices refuses the lot.
		 *
		 * @return void
		 */
		public function test_resolve_packs_the_picks_and_refuses_anything_else(): void {
			$this->seed_shop();

			$this->assertSame(
				array( array( 'pillow', 'kruzic-zuti' ), array( 'pillow', 'kockasti-sivi' ), array( 'towel', 'sova' ) ),
				GiftBundles::resolve( 'kutak-za-decju-sobu' )
			);
			$this->assertSame(
				array( array( 'pillow', 'kruzic-roze' ), array( 'pillow', 'kockasti-sivi' ), array( 'towel', 'kapibara' ) ),
				GiftBundles::resolve( 'kutak-za-decju-sobu', array( 0 => 'kruzic-roze', 2 => 'kapibara' ) )
			);

			// A square colour in the round slot, a long towel in a towel slot,
			// something that does not exist.
			$this->assertNull( GiftBundles::resolve( 'kutak-za-decju-sobu', array( 0 => 'kockasti-roze' ) ) );
			$this->assertNull( GiftBundles::resolve( 'kutak-za-decju-sobu', array( 2 => 'meda-krem' ) ) );
			$this->assertNull( GiftBundles::resolve( 'kutak-za-decju-sobu', array( 1 => 'nepostoji' ) ) );
			$this->assertNull( GiftBundles::resolve( 'nepostoji' ) );
		}

		/**
		 * The packing list survives the trip through a string, and reads as a
		 * row per part.
		 *
		 * @return void
		 */
		public function test_a_packing_list_is_stored_and_described(): void {
			$parts = array( array( 'pillow', 'kockasti-roze' ), array( 'long', 'meda-plavi' ), array( 'towel', 'keks' ), array( 'cloths', Cloths::SET ) );

			$stored = GiftBundles::encode( $parts );
			$this->assertSame( 'pillow:kockasti-roze,long:meda-plavi,towel:keks,cloths:set-avokado', $stored );
			$this->assertSame( $parts, GiftBundles::decode( $stored ) );
			$this->assertSame( array( array( 'towel', 'sova' ) ), GiftBundles::decode( 'towel:sova,,nesto:x,pillow:,bez-dvotacke' ) );

			$this->assertSame(
				array(
					array(
						'label' => 'Jastuk Kockasti',
						'name'  => 'Roze',
					),
					array(
						'label' => 'Peškir Meda',
						'name'  => 'Plava',
					),
					array(
						'label' => 'Peškir',
						'name'  => 'Čokoladni keks',
					),
					array(
						'label' => 'Magične krpice',
						'name'  => 'Avokado, limeta i žalfija',
					),
				),
				GiftBundles::describe( $parts )
			);
		}

		/**
		 * From the product page form to the cart: the picks become the cart
		 * item's packing list; a card add, posting none, gets the suggestion.
		 *
		 * @return void
		 */
		public function test_the_picks_travel_with_the_cart_item(): void {
			$this->seed_shop();
			$picker = new GiftBundlePicker();
			$bundle = $this->options[ GiftBundles::MAP_OPTION ]['kutak-za-decju-sobu'];

			$this->assertSame(
				array( GiftBundlePicker::ITEM_KEY => 'pillow:kruzic-zuti,pillow:kockasti-sivi,towel:sova' ),
				$picker->add_cart_item_data( array(), $bundle )
			);

			$_REQUEST[ GiftBundlePicker::FIELD ] = array(
				0 => 'kruzic-zeleni',
				1 => 'kockasti-braon',
				2 => 'pingvin',
			);

			$this->assertTrue( $picker->validate( true, $bundle ) );
			$data = $picker->add_cart_item_data( array(), $bundle );
			$this->assertSame( 'pillow:kruzic-zeleni,pillow:kockasti-braon,towel:pingvin', $data[ GiftBundlePicker::ITEM_KEY ] );

			$this->assertSame(
				array(
					array(
						'key'   => 'Jastuk Kružić',
						'value' => 'Zelena',
					),
					array(
						'key'   => 'Jastuk Kockasti',
						'value' => 'Braon',
					),
					array(
						'key'   => 'Peškir',
						'value' => 'Pingvin',
					),
				),
				$picker->cart_item_rows( array(), $data )
			);

			// Anything but a bundle is left alone.
			$towel = $this->options[ WooCommerce::PRODUCT_MAP_OPTION ]['sova'];
			$this->assertSame( array(), $picker->add_cart_item_data( array(), $towel ) );
			$this->assertTrue( $picker->validate( true, $towel ) );
		}

		/**
		 * A pick that cannot be packed stops the add with a note, rather than
		 * packing something nobody chose.
		 *
		 * @return void
		 */
		public function test_a_pick_that_cannot_be_packed_is_refused(): void {
			$this->seed_shop();
			$picker = new GiftBundlePicker();
			$bundle = $this->options[ GiftBundles::MAP_OPTION ]['kutak-za-decju-sobu'];

			$_REQUEST[ GiftBundlePicker::FIELD ] = array( 2 => 'meda-krem' );

			$this->assertFalse( $picker->validate( true, $bundle ) );
			$this->assertCount( 1, $this->notices );
			$this->assertSame( 'error', $this->notices[0][1] );
			$this->assertSame( array(), $picker->add_cart_item_data( array(), $bundle ) );

			// An add something else already refused stays refused, quietly.
			$this->assertFalse( $picker->validate( false, $bundle ) );
			$this->assertCount( 1, $this->notices );
		}

		/**
		 * The order line keeps the packing list as keys and shows it as a line
		 * per part.
		 *
		 * @return void
		 */
		public function test_the_order_line_says_what_to_pack(): void {
			$picker = new GiftBundlePicker();
			$meta   = (object) array(
				'key'   => GiftBundlePicker::ITEM_KEY,
				'value' => 'pillow:kruzic-zuti,towel:sova',
			);

			$this->assertSame( 'U paketu', $picker->order_meta_key( 'cosypaw_bundle', $meta ) );
			$this->assertSame( 'Jastuk Kružić: Žuta<br>Peškir: Sova', $picker->order_meta_value( '', $meta ) );

			$other = (object) array(
				'key'   => 'cosypaw_motifs',
				'value' => 'sova',
			);
			$this->assertSame( 'Peškiri', $picker->order_meta_key( 'Peškiri', $other ) );
			$this->assertSame( 'Sova', $picker->order_meta_value( 'Sova', $other ) );
		}
	}
}
