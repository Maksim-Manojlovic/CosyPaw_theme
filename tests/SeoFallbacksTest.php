<?php
/**
 * Unit tests for the Yoast fallbacks and the noindex list.
 *
 * @package CosyPaw\Tests
 */

declare(strict_types=1);

namespace {
	if ( ! class_exists( 'WP_Term' ) ) {
		/**
		 * Minimal WP_Term: the slug and name are all SeoFallbacks reads.
		 */
		class WP_Term {
			public string $slug = '';
			public string $name = '';
		}
	}
}

namespace Theme\Tests {

	use Brain\Monkey;
	use Brain\Monkey\Functions;
	use PHPUnit\Framework\TestCase;
	use Theme\SeoFallbacks;

	if ( ! defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', __DIR__ . '/' );
	}

	require_once dirname( __DIR__ ) . '/inc/WooCommerce.php';
	require_once dirname( __DIR__ ) . '/inc/SeoFallbacks.php';

	final class SeoFallbacksTest extends TestCase {

		protected function setUp(): void {
			parent::setUp();
			Monkey\setUp();

			Functions\stubs(
				array(
					'add_filter'        => true,
					'is_shop'           => false,
					'is_tax'            => false,
					'is_single'         => false,
					'is_page'           => false,
					'is_singular'       => false,
					'wc_get_page_id'    => 7,
					'get_post_meta'     => '',
					'get_option'        => array(),
					'wp_strip_all_tags' => static fn( string $text ): string => strip_tags( $text ),
				)
			);
		}

		protected function tearDown(): void {
			Monkey\tearDown();
			parent::tearDown();
		}

		/**
		 * A category term, as WordPress hands it to the template.
		 *
		 * @param string $slug Slug.
		 * @param string $name Name.
		 * @return \WP_Term
		 */
		private function term( string $slug, string $name = 'Ime' ): \WP_Term {
			$term       = new \WP_Term();
			$term->slug = $slug;
			$term->name = $name;

			return $term;
		}

		/**
		 * Put the view on a product category.
		 *
		 * @param \WP_Term $term Term.
		 * @return void
		 */
		private function on_category( \WP_Term $term ): void {
			Functions\when( 'is_tax' )->alias(
				static fn( $tax = '', $slug = '' ): bool => 'product_cat' === $tax && ( '' === $slug || $slug === $term->slug )
			);
			Functions\when( 'get_queried_object' )->justReturn( $term );
		}

		/**
		 * Make the package map hold the two tiers.
		 *
		 * @return void
		 */
		private function with_package_tiers(): void {
			Functions\when( 'get_option' )->justReturn( array( 'solo' => 11, 'duo' => 12 ) );
		}

		/**
		 * Anything Yoast already has is the shop's, and wins.
		 *
		 * @return void
		 */
		public function test_a_title_and_description_yoast_has_are_left_alone_on_the_shop(): void {
			Functions\when( 'is_shop' )->justReturn( true );
			Functions\when( 'get_post_meta' )->justReturn( 'Moj naslov' );

			$fallbacks = new SeoFallbacks();

			$this->assertSame( 'Naslov iz Yoast-a', $fallbacks->title( 'Naslov iz Yoast-a' ) );
			$this->assertSame( 'Moj opis', $fallbacks->description( 'Moj opis' ) );
		}

		/**
		 * @return void
		 */
		public function test_the_shop_gets_its_own_title_and_description_when_yoast_has_none(): void {
			Functions\when( 'is_shop' )->justReturn( true );

			$fallbacks = new SeoFallbacks();

			$this->assertSame( SeoFallbacks::SHOP['title'], $fallbacks->title( 'Shop - CosyPaw' ) );
			$this->assertSame( SeoFallbacks::SHOP['description'], $fallbacks->description( '' ) );
		}

		/**
		 * @return void
		 */
		public function test_a_known_category_gets_its_copy_and_an_unknown_one_is_built_from_its_name(): void {
			$fallbacks = new SeoFallbacks();

			$this->on_category( $this->term( 'magicne-krpe', 'Magične krpe' ) );
			$this->assertSame( SeoFallbacks::CATEGORIES['magicne-krpe']['title'], $fallbacks->title( 'Magične krpe - CosyPaw' ) );
			$this->assertSame( SeoFallbacks::CATEGORIES['magicne-krpe']['description'], $fallbacks->description( '' ) );

			$this->on_category( $this->term( 'nova-linija', 'Nova linija' ) );
			$this->assertSame( 'Nova linija | CosyPaw', $fallbacks->title( 'Nova linija - CosyPaw' ) );
			$this->assertStringStartsWith( 'Nova linija — CosyPaw', $fallbacks->description( '' ) );
		}

		/**
		 * @return void
		 */
		public function test_every_fallback_fits_in_a_snippet(): void {
			$all = SeoFallbacks::CATEGORIES + array( 'shop' => SeoFallbacks::SHOP );

			foreach ( $all as $slug => $copy ) {
				$this->assertLessThanOrEqual( 160, mb_strlen( $copy['description'] ), $slug );
				$this->assertLessThanOrEqual( 65, mb_strlen( $copy['title'] ), $slug );
			}
		}

		/**
		 * @return void
		 */
		public function test_a_product_without_a_description_borrows_its_short_description(): void {
			Functions\when( 'is_singular' )->alias( static fn( $type = '' ): bool => 'product' === $type );
			Functions\when( 'get_queried_object_id' )->justReturn( 55 );
			Functions\when( 'has_excerpt' )->justReturn( true );
			Functions\when( 'get_the_excerpt' )->justReturn( '<p>Mekani   peškir sa alkom.</p>' );

			$this->assertSame( 'Mekani peškir sa alkom.', ( new SeoFallbacks() )->description( '' ) );
		}

		/**
		 * @return void
		 */
		public function test_anywhere_else_the_description_stays_empty_and_the_title_untouched(): void {
			$this->assertSame( '', ( new SeoFallbacks() )->description( '' ) );
			$this->assertSame( 'Naslov', ( new SeoFallbacks() )->title( 'Naslov' ) );
		}

		/**
		 * @return void
		 */
		public function test_the_sample_post_and_page_are_noindexed(): void {
			$fallbacks = new SeoFallbacks();

			Functions\when( 'is_single' )->alias( static fn( $slug = '' ): bool => 'hello-world' === $slug );
			$this->assertSame( 'noindex, follow', $fallbacks->robots( 'index, follow' ) );

			Functions\when( 'is_single' )->justReturn( false );
			Functions\when( 'is_page' )->alias( static fn( $slug = '' ): bool => 'sample-page' === $slug );
			$this->assertSame( 'noindex, follow', $fallbacks->robots( 'index, follow' ) );
		}

		/**
		 * @return void
		 */
		public function test_the_package_tiers_and_their_category_are_noindexed_but_other_products_are_not(): void {
			$this->with_package_tiers();
			Functions\when( 'is_singular' )->alias( static fn( $type = '' ): bool => 'product' === $type );
			$fallbacks = new SeoFallbacks();

			Functions\when( 'get_queried_object_id' )->justReturn( 12 );
			$this->assertSame( 'noindex, follow', $fallbacks->robots( 'index, follow' ) );

			Functions\when( 'get_queried_object_id' )->justReturn( 99 );
			$this->assertSame( 'index, follow', $fallbacks->robots( 'index, follow' ) );

			Functions\when( 'is_singular' )->justReturn( false );
			$this->on_category( $this->term( 'paketi', 'Paketi' ) );
			$this->assertSame( 'noindex, follow', $fallbacks->robots( 'index, follow' ) );

			$this->on_category( $this->term( 'zivotinjice', 'Životinjice' ) );
			$this->assertSame( 'index, follow', $fallbacks->robots( 'index, follow' ) );
		}

		/**
		 * @return void
		 */
		public function test_the_sitemap_drops_the_sample_content_and_the_package_tiers(): void {
			$this->with_package_tiers();
			Functions\when( 'get_page_by_path' )->alias(
				static fn( string $path ): object => (object) array( 'ID' => 'hello-world' === $path ? 1 : 2 )
			);

			$this->assertSame( array( 5, 1, 2, 11, 12 ), ( new SeoFallbacks() )->exclude_from_sitemap( array( 5 ) ) );
		}
	}
}
