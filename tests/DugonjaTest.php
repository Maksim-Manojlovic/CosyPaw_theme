<?php
/**
 * Unit tests for the Dugonja: one variable product, a friend per variation,
 * picked by its head (Dugonja, Swatches, the swatch cards' group).
 *
 * @package CosyPaw\Tests
 */

declare(strict_types=1);

namespace Theme\Tests;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use Theme\Dugonja;
use Theme\Swatches;

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once __DIR__ . '/stubs.php';
require_once __DIR__ . '/stubs-variable.php';
require_once dirname( __DIR__ ) . '/inc/Stock.php';
require_once dirname( __DIR__ ) . '/inc/ProductLine.php';
require_once dirname( __DIR__ ) . '/inc/Pillows.php';
require_once dirname( __DIR__ ) . '/inc/Swatches.php';
require_once dirname( __DIR__ ) . '/inc/Dugonja.php';

/**
 * DugonjaTest.
 */
final class DugonjaTest extends TestCase {

	/** @var array<string,mixed> */
	private array $options = array();

	/** @var array<int,\WC_Product> Products by id. */
	private array $products = array();

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		$this->options  = array( Dugonja::ID_OPTION => 40 );
		$this->products = array();

		Functions\stubTranslationFunctions();
		Functions\stubEscapeFunctions();
		Functions\stubs(
			array(
				'add_action'                 => true,
				'add_filter'                 => true,
				'is_admin'                   => false,
				'get_template_directory_uri' => 'https://cosypaw.test/wp-content/themes/cosypaw',
				'get_permalink'              => 'https://cosypaw.test/proizvod/dugonja/',
				'sanitize_title'             => static fn( string $title ): string => strtolower( str_replace( array( 'Ž', 'ć', 'š' ), array( 'z', 'c', 's' ), $title ) ),
				'add_query_arg'              => static fn( string $key, string $value, string $url ): string => $url . '?' . $key . '=' . $value,
			)
		);

		Functions\when( 'get_option' )->alias( fn( string $name, $fallback = false ) => $this->options[ $name ] ?? $fallback );
		Functions\when( 'wc_get_product' )->alias( fn( $id = 0 ) => $this->products[ (int) $id ] ?? false );
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * The parent: variable, published, with the given variations as children.
	 *
	 * @param array<int,int> $children Variation ids.
	 * @param string         $status   Post status.
	 * @return \WC_Product
	 */
	private function parent( array $children, string $status = 'publish' ): \WC_Product {
		$parent = new class( Dugonja::NAME, '4490', true, 40 ) extends \WC_Product {
			/** @var array<int,int> */
			public array $children = array();
			public string $status  = 'publish';

			public function is_type( $type ): bool {
				return 'variable' === $type;
			}

			public function get_status(): string {
				return $this->status;
			}

			public function get_children(): array {
				return $this->children;
			}
		};

		$parent->children = $children;
		$parent->status   = $status;

		return $this->products[40] = $parent;
	}

	/**
	 * A variation for one friend.
	 *
	 * @param int    $id          Variation id.
	 * @param string $friend      FRIENDS key.
	 * @param bool   $purchasable Whether it can be bought.
	 * @param bool   $in_stock    Whether it is in stock.
	 * @return \WC_Product
	 */
	private function variation( int $id, string $friend, bool $purchasable = true, bool $in_stock = true ): \WC_Product {
		$variation = new class( $friend, '4490', $purchasable, $id ) extends \WC_Product {
			public string $friend = '';

			public function get_attributes(): array {
				return array( Dugonja::ATTRIBUTE_KEY => $this->friend );
			}

			public function add_to_cart_url(): string {
				return '?add-to-cart=40&variation_id=' . $this->get_id();
			}
		};

		$variation->friend   = $friend;
		$variation->in_stock = $in_stock;
		$variation->image_id = 500 + $id;

		return $this->products[ $id ] = $variation;
	}

	/**
	 * Every friend ships its photograph and its head with the theme, the
	 * group photographs too, and the Yoast fields fit their limits.
	 *
	 * @return void
	 */
	public function test_the_product_is_complete(): void {
		$theme = dirname( __DIR__ ) . '/';

		$this->assertSame( 4490, Dugonja::PRICE );
		$this->assertCount( 4, Dugonja::FRIENDS );

		foreach ( array_merge( Dugonja::IMAGE, Dugonja::GALLERY ) as $file => $alt ) {
			$this->assertFileExists( $theme . Dugonja::IMAGE_DIR . $file );
			$this->assertNotSame( '', $alt );
		}

		foreach ( Dugonja::FRIENDS as $friend => $data ) {
			$this->assertFileExists( $theme . Dugonja::IMAGE_DIR . $data['image'], $friend );
			$this->assertFileExists( $theme . Dugonja::HEAD_DIR . $data['head'], $friend );
			$this->assertStringContainsString( $friend, $data['alt'] );
			$this->assertMatchesRegularExpression( '/^#[0-9a-f]{6}$/', $data['swatch'] );
		}

		$this->assertLessThanOrEqual( 65, mb_strlen( Dugonja::SEO_TITLE ) );
		$this->assertLessThanOrEqual( 160, mb_strlen( Dugonja::SEO_DESCRIPTION ) );
		$this->assertStringContainsString( '100 cm', Dugonja::DESCRIPTION );
	}

	/**
	 * The copy states only what the shop confirmed: no washing, no
	 * orthopaedic claim, none of the promo pictures' numbers.
	 *
	 * @return void
	 */
	public function test_the_copy_claims_nothing_unconfirmed(): void {
		$copy = mb_strtolower( Dugonja::SHORT . ' ' . Dugonja::DESCRIPTION . ' ' . Dugonja::SEO_DESCRIPTION . ' ' . implode( ' ', Dugonja::spec_rows() ) );

		foreach ( array( 'pere', 'pranj', 'ortoped', 'ergonom', 'kičm', 'garancij', 'zadovoljnih' ) as $word ) {
			$this->assertStringNotContainsString( $word, $copy, $word );
		}
	}

	/**
	 * The card: a variant per friend that can be bought, in FRIENDS order,
	 * each with its variation, its photograph and its head; a sold-out friend
	 * stays (marked), one that cannot be bought is left out.
	 *
	 * @return void
	 */
	public function test_the_card_has_a_head_per_friend(): void {
		// Children out of FRIENDS order on purpose.
		$this->parent( array( 43, 41, 42, 44 ) );
		$this->variation( 41, 'Lavko' );
		$this->variation( 42, 'Žuća', true, false );
		$this->variation( 43, 'Sivko', false );
		$this->variation( 44, 'Slatkiš' );

		$group = Dugonja::group();

		$this->assertNotNull( $group );
		$this->assertSame( Dugonja::GROUP, $group['key'] );
		$this->assertSame( 'Izaberi drugara', $group['legend'] );
		$this->assertSame( array( 41, 42, 44 ), array_column( $group['variants'], 'id' ) );
		$this->assertSame( array( 'Lavko', 'Žuća', 'Slatkiš' ), array_column( $group['variants'], 'color' ) );
		$this->assertSame( array( false, true, false ), array_column( $group['variants'], 'sold_out' ) );

		$first = $group['variants'][0];
		$this->assertSame( 4490, $first['price'] );
		$this->assertSame( 541, $first['image_id'] );
		$this->assertSame( Dugonja::NAME . ', Lavko', $first['name'] );
		$this->assertSame( '?add-to-cart=40&variation_id=41', $first['add_to_cart_url'] );
		$this->assertStringEndsWith( '/assets/pillows/glave/dugonja-lavko.webp', $first['head'] );
		$this->assertStringEndsWith( '?attribute_drugar=Lavko', $first['permalink'] );
		$this->assertStringEndsWith( '?attribute_drugar=' . rawurlencode( 'Žuća' ), $group['variants'][1]['permalink'] );
	}

	/**
	 * The card opens on the group photograph: the parent's image, the price
	 * of a friend on sale, the product page, a "Novo" badge and a button that
	 * leads to the page to pick a friend on.
	 *
	 * @return void
	 */
	public function test_the_card_opens_on_the_group_photograph(): void {
		$this->parent( array( 41, 42 ) )->image_id = 300;
		$this->variation( 41, 'Lavko', true, false );
		$this->variation( 42, 'Žuća' );

		$cover = Dugonja::group()['cover'];

		$this->assertSame( 300, $cover['image_id'] );
		$this->assertSame( Dugonja::NAME, $cover['name'] );
		$this->assertSame( 4490, $cover['price'] );
		$this->assertSame( 'https://cosypaw.test/proizvod/dugonja/', $cover['permalink'] );
		$this->assertSame( 'Novo', $cover['badge'] );
		$this->assertSame( 'Izaberi drugara', $cover['button'] );
	}

	/**
	 * No card before the product exists, while it is a draft, or with nothing
	 * to sell.
	 *
	 * @return void
	 */
	public function test_no_card_without_a_product_on_sale(): void {
		$this->assertNull( Dugonja::group() );

		$this->parent( array( 41 ), 'draft' );
		$this->variation( 41, 'Lavko' );
		$this->assertNull( Dugonja::group() );

		$this->parent( array( 41 ) );
		$this->variation( 41, 'Lavko', false );
		$this->assertNull( Dugonja::group() );
	}

	/**
	 * On the product page the heads replace the "Drugar" dropdown, under
	 * their own legend; any other field is left alone.
	 *
	 * @return void
	 */
	public function test_the_heads_replace_the_dropdown(): void {
		$dugonja  = new Dugonja();
		$dropdown = '<select id="drugar" name="attribute_drugar"></select>';

		$html = $dugonja->swatch_field(
			$dropdown,
			array(
				'attribute' => 'Drugar',
				'options'   => array_keys( Dugonja::FRIENDS ),
				'selected'  => '',
				'id'        => '',
			)
		);

		$this->assertStringContainsString( 'data-colour-swatches="drugar"', $html );
		$this->assertStringContainsString( 'Izaberi drugara', $html );
		$this->assertSame( 4, substr_count( $html, 'colour-swatch--head' ) );
		$this->assertSame( 4, substr_count( $html, 'colour-swatch__dot--head' ) );
		$this->assertStringContainsString( 'glave/dugonja-slatkis.webp', $html );
		// Nothing picked: the page opens on the group photograph.
		$this->assertStringNotContainsString( ' checked', $html );
		$this->assertStringContainsString( $dropdown, $html );

		$this->assertSame( $dropdown, $dugonja->swatch_field( $dropdown, array( 'attribute' => 'Boja' ) ) );
	}

	/**
	 * Dots stay dots: an option without a picture is drawn as before, under
	 * the default legend.
	 *
	 * @return void
	 */
	public function test_radios_without_a_picture_keep_the_dot(): void {
		$html = Swatches::radios(
			array(
				array(
					'value'  => 'Plava',
					'label'  => 'Plava',
					'swatch' => '#5f86c9',
				),
				array(
					'value'  => 'Roze',
					'label'  => 'Roze',
					'swatch' => '#efb9b9',
				),
			),
			'Plava',
			'boja'
		);

		$this->assertStringContainsString( 'Izaberi boju', $html );
		$this->assertStringNotContainsString( '--head', $html );
		$this->assertSame( 2, substr_count( $html, 'colour-swatch__dot"' ) );
	}

	/**
	 * The variation names WooCommerce generates are translated in both
	 * parts; a name typed in wp-admin is left as it is.
	 *
	 * @return void
	 */
	public function test_a_generated_variation_name_is_translated(): void {
		$dugonja   = new Dugonja();
		$variation = new class( 'x', '4490', true, 41 ) extends \WC_Product {
			public function get_parent_id(): int {
				return 40;
			}
		};

		$this->assertSame( Dugonja::NAME . ' - Žuća', $dugonja->translate_variation_name( Dugonja::NAME . ' - Žuća', $variation ) );
		$this->assertSame( 'Nešto drugo', $dugonja->translate_variation_name( 'Nešto drugo', $variation ) );
	}

	/**
	 * Every string the product prints has an English and a Russian line.
	 *
	 * @return void
	 */
	public function test_its_strings_are_translated(): void {
		$map = (string) file_get_contents( dirname( __DIR__ ) . '/tools/build-translations.php' );

		$strings = array_merge(
			array( Dugonja::NAME, Dugonja::SHORT, Dugonja::DESCRIPTION, 'Drugar', 'Izaberi drugara' ),
			array_keys( Dugonja::FRIENDS ),
			array_keys( Dugonja::spec_rows() ),
			array_values( Dugonja::spec_rows() )
		);

		foreach ( $strings as $string ) {
			$this->assertStringContainsString( "'" . str_replace( "'", "\\'", $string ) . "'", $map, $string );
		}
	}

	/**
	 * Stub what ensure() reaches for, with the half-made product of the first
	 * run in the shop: the parent, recorded, with no variation.
	 *
	 * @param array<string,mixed> $meta Post meta, by key.
	 * @return void
	 */
	private function half_made_product( array &$meta ): void {
		\WC_Product_Variation::$saved = array();
		\WC_Product_Variable::$saved  = array();
		\WC_Product_Variable::$synced = array();
		$this->options                = array( Dugonja::ID_OPTION => 382 );

		Functions\stubs(
			array(
				'current_user_can'       => true,
				'get_post'               => true,
				'get_template_directory' => '/nonexistent-theme',
			)
		);
		Functions\when( 'update_option' )->alias(
			function ( string $name, $value ): bool {
				$this->options[ $name ] = $value;
				return true;
			}
		);
		Functions\when( 'get_post_meta' )->alias( fn( int $id, string $key ) => $meta[ $key ] ?? '' );
		Functions\when( 'update_post_meta' )->alias(
			function ( int $id, string $key, $value ) use ( &$meta ): bool {
				$meta[ $key ] = $value;
				return true;
			}
		);
		Functions\when( 'wc_get_product' )->alias(
			fn( $id ) => 382 === (int) $id
				? ( \WC_Product_Variable::$saved[382] ?? new \WC_Product_Variable( 382 ) )
				: ( \WC_Product_Variation::$saved[ (int) $id ] ?? false )
		);
	}

	/**
	 * The product the first run left behind (the parent, nothing else) is
	 * given a variation per friend at the launch price, in stock, and the
	 * Yoast fields; the version is recorded.
	 *
	 * @return void
	 */
	public function test_a_half_made_product_is_completed(): void {
		$meta = array();
		$this->half_made_product( $meta );

		( new Dugonja() )->maybe_run();

		$saved = array_values( \WC_Product_Variation::$saved );
		$this->assertCount( 4, $saved );
		$this->assertSame(
			array_keys( Dugonja::FRIENDS ),
			array_map( static fn( $v ): string => $v->props['attributes'][ Dugonja::ATTRIBUTE_KEY ], $saved )
		);
		foreach ( $saved as $variation ) {
			$this->assertSame( 382, $variation->props['parent_id'] );
			$this->assertSame( '4490', $variation->props['regular_price'] );
			$this->assertSame( 'instock', $variation->props['stock_status'] );
		}

		$this->assertSame( array( 382 ), \WC_Product_Variable::$synced );
		$this->assertSame( Dugonja::SEO_TITLE, $meta['_yoast_wpseo_title'] );
		$this->assertSame( Dugonja::VERSION, $this->options['cosypaw_dugonja_version'] );
	}

	/**
	 * Run again, nothing is added twice; a friend the shop already has keeps
	 * its variation, and a Yoast title typed in wp-admin stays.
	 *
	 * @return void
	 */
	public function test_completing_never_adds_twice(): void {
		$meta = array( '_yoast_wpseo_title' => 'Naslov iz wp-admina' );
		$this->half_made_product( $meta );

		$lavko = new \WC_Product_Variation();
		$lavko->set_attributes( array( Dugonja::ATTRIBUTE_KEY => 'Lavko' ) );
		$lavko->save();

		$dugonja = new Dugonja();
		$this->assertSame( 382, $dugonja->ensure() );
		$this->assertCount( 4, \WC_Product_Variation::$saved );

		$dugonja->ensure();
		$this->assertCount( 4, \WC_Product_Variation::$saved );
		$this->assertSame( 'Naslov iz wp-admina', $meta['_yoast_wpseo_title'] );
	}
}
