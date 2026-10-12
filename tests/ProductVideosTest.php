<?php
/**
 * Unit tests for the promo clips in the product gallery.
 *
 * @package CosyPaw\Tests
 */

declare(strict_types=1);

namespace Theme\Tests;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use Theme\ProductVideos;
use Theme\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once __DIR__ . '/stubs.php';
require_once dirname( __DIR__ ) . '/inc/Catalog.php';
require_once dirname( __DIR__ ) . '/inc/WooCommerce.php';
require_once dirname( __DIR__ ) . '/inc/ProductVideos.php';

final class ProductVideosTest extends TestCase {

	/**
	 * Template parts asked for, as [ slug, args ].
	 *
	 * @var array<int,array{0:string,1:array<string,mixed>}>
	 */
	private array $parts = array();

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		$this->parts = array();

		Functions\stubs(
			array(
				'add_action'                 => true,
				'add_filter'                 => true,
				'get_template_directory_uri' => 'https://cosypaw.test/theme',
			)
		);

		// Krofnica is a towel the theme seeded, Bundeva one the shop added in
		// wp-admin; Žirafa has no clip.
		Functions\when( 'get_option' )->alias(
			static fn( string $name, $fallback = false ) => WooCommerce::PRODUCT_MAP_OPTION === $name
				? array(
					'zirafa'  => 16,
					'krofna'  => '59',
					'bundeva' => 197,
				)
				: $fallback
		);

		Functions\when( 'get_template_part' )->alias(
			function ( string $slug, $name = null, array $args = array() ): void {
				$this->parts[] = array( $slug, $args );
			}
		);
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * A product is matched to its clip through the motif it is mapped under,
	 * and the three files are addressed by that motif id.
	 *
	 * @return void
	 */
	public function test_a_mapped_motif_with_a_clip_resolves_to_its_files(): void {
		$clip = ProductVideos::clip_for( 59 );

		$this->assertIsArray( $clip );
		$this->assertSame( 'krofna', $clip['id'] );
		$this->assertSame( 'https://cosypaw.test/theme/assets/videos/krofna.mp4', $clip['src'] );
		$this->assertSame( 'https://cosypaw.test/theme/assets/videos/krofna.webp', $clip['poster'] );
		$this->assertSame( 'https://cosypaw.test/theme/assets/videos/krofna-th.webp', $clip['thumb'] );
		$this->assertMatchesRegularExpression( '/^\d+% \d+%$/', $clip['focus'] );

		$this->assertSame( 'bundeva', ProductVideos::clip_for( 197 )['id'] ?? '' );
	}

	/**
	 * A towel without a clip, a product that is no towel and "no product" all
	 * come back empty.
	 *
	 * @return void
	 */
	public function test_everything_else_has_no_clip(): void {
		$this->assertNull( ProductVideos::clip_for( 16 ) );
		$this->assertNull( ProductVideos::clip_for( 4242 ) );
		$this->assertNull( ProductVideos::clip_for( 0 ) );
	}

	/**
	 * Every clip in the registry ships its video, its poster and its thumbnail.
	 *
	 * @return void
	 */
	public function test_every_clip_has_its_three_files(): void {
		$dir = dirname( __DIR__ ) . '/' . ProductVideos::DIR;

		$this->assertNotEmpty( ProductVideos::motifs() );

		foreach ( ProductVideos::motifs() as $id ) {
			foreach ( array( '.mp4', '.webp', '-th.webp' ) as $suffix ) {
				$this->assertFileExists( $dir . $id . $suffix, $id );
			}
		}
	}

	/**
	 * The clips reach the server only if the packaging allowlist lets their
	 * extension through; it was images only.
	 *
	 * @return void
	 */
	public function test_the_package_allowlist_ships_the_clips(): void {
		$script = (string) file_get_contents( dirname( __DIR__ ) . '/tools/package-theme.ps1' );

		$this->assertMatchesRegularExpression( '/^\$imgExt\s*=.*\'\.mp4\'/m', $script );
	}

	/**
	 * The slide goes into the gallery of a product with a clip, in the shape
	 * of that product's photograph.
	 *
	 * @return void
	 */
	public function test_the_slide_takes_the_shape_of_the_photograph(): void {
		$product           = new \WC_Product( 'Bundeva', '790', true, 197 );
		$product->image_id = 301;

		Functions\when( 'wc_get_product' )->justReturn( $product );
		Functions\when( 'wp_get_attachment_image_src' )->alias(
			static fn( int $id, string $size ) => 301 === $id && 'woocommerce_single' === $size
				? array( 'https://cosypaw.test/uploads/bundeva-600x900.webp', 600, 900, true )
				: false
		);

		( new ProductVideos() )->slide();

		$this->assertCount( 1, $this->parts );
		$this->assertSame( 'template-parts/product-video', $this->parts[0][0] );
		$this->assertSame( 'https://cosypaw.test/theme/assets/videos/bundeva.mp4', $this->parts[0][1]['src'] );
		$this->assertSame( 'Bundeva', $this->parts[0][1]['name'] );
		$this->assertSame( '600 / 900', $this->parts[0][1]['ratio'] );
	}

	/**
	 * A product with no photograph leaves the shape to the stylesheet.
	 *
	 * @return void
	 */
	public function test_a_product_without_a_photograph_leaves_the_shape_open(): void {
		Functions\when( 'wc_get_product' )->justReturn( new \WC_Product( 'Krofnica', '790', true, 59 ) );

		( new ProductVideos() )->slide();

		$this->assertCount( 1, $this->parts );
		$this->assertSame( '', $this->parts[0][1]['ratio'] );
	}

	/**
	 * Nothing is added to the gallery of a product without a clip.
	 *
	 * @return void
	 */
	public function test_other_products_get_no_slide_and_no_gallery_class(): void {
		Functions\when( 'wc_get_product' )->justReturn( new \WC_Product( 'Žirafa', '790', true, 16 ) );

		$videos = new ProductVideos();
		$videos->slide();

		$this->assertSame( array(), $this->parts );
		$this->assertSame( array( 'images' ), $videos->gallery_classes( array( 'images' ) ) );
	}

	/**
	 * The gallery of a product with a clip says so, for the thumbnail's badge.
	 *
	 * @return void
	 */
	public function test_the_gallery_of_a_product_with_a_clip_is_marked(): void {
		Functions\when( 'wc_get_product' )->justReturn( new \WC_Product( 'Krofnica', '790', true, 59 ) );

		$this->assertSame(
			array( 'woocommerce-product-gallery', 'images', ProductVideos::GALLERY_CLASS ),
			( new ProductVideos() )->gallery_classes( array( 'woocommerce-product-gallery', 'images' ) )
		);
	}
}
