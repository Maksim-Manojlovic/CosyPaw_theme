<?php
/**
 * Unit tests for the theme's SEO head tags.
 *
 * @package CosyPaw\Tests
 */

declare(strict_types=1);

namespace Theme\Tests;

use Brain\Monkey;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use Theme\Catalog;
use Theme\Seo;

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once dirname( __DIR__ ) . '/inc/Catalog.php';
require_once dirname( __DIR__ ) . '/inc/Seo.php';

final class SeoTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		Functions\stubTranslationFunctions();
		Functions\stubEscapeFunctions();
		Functions\stubs(
			array(
				'add_action'                   => true,
				'is_front_page'                => true,
				'is_singular'                  => false,
				'is_category'                  => false,
				'is_tag'                       => false,
				'is_tax'                       => false,
				'is_search'                    => false,
				'get_bloginfo'                 => 'CosyPaw',
				'get_locale'                   => 'sr_RS',
				'get_theme_mod'                => 0,
				'get_template_directory_uri'   => 'https://cosypaw.test/wp-content/themes/cosypaw',
				'wp_get_document_title'        => 'CosyPaw',
				'wp_strip_all_tags'            => static fn( string $text ): string => strip_tags( $text ),
				'wp_json_encode'               => static fn( $data, int $flags = 0 ) => json_encode( $data, $flags ),
				'home_url'                     => static fn( string $path = '' ): string => 'https://cosypaw.test' . $path,
				'add_query_arg'                => static fn(): string => '/',
				'remove_query_arg'             => static fn( string $key, string $url ): string => $url,
			)
		);
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Render the head the way wp_head would.
	 *
	 * @return string
	 */
	private function head(): string {
		ob_start();
		( new Seo( new Catalog() ) )->render();

		return (string) ob_get_clean();
	}

	/**
	 * The JSON-LD graph node types, in order.
	 *
	 * @param string $html Rendered head.
	 * @return string[]
	 */
	private function schema_types( string $html ): array {
		if ( ! preg_match( '#<script type="application/ld\+json">(.*?)</script>#s', $html, $m ) ) {
			return array();
		}

		return array_column( json_decode( $m[1], true )['@graph'], '@type' );
	}

	/**
	 * With no SEO plugin the theme is the only thing writing the head, so it
	 * writes all of it.
	 *
	 * @return void
	 */
	public function test_without_a_plugin_it_emits_every_tag(): void {
		$html = $this->head();

		$this->assertStringContainsString( '<meta name="description"', $html );
		$this->assertStringContainsString( 'property="og:title"', $html );
		$this->assertStringContainsString( 'name="twitter:card"', $html );
		$this->assertSame(
			array( 'Organization', 'WebSite', 'ProductGroup', 'FAQPage' ),
			$this->schema_types( $html )
		);
	}

	/**
	 * With Yoast active the live front page carried two descriptions, two sets
	 * of og:* and two Organization/WebSite nodes. The theme now leaves those to
	 * the plugin and adds only the nodes the plugin does not produce.
	 *
	 * @return void
	 */
	public function test_with_a_plugin_it_leaves_the_description_social_and_site_nodes_to_it(): void {
		Filters\expectApplied( 'cosypaw_seo_plugin_active' )->andReturn( true );

		$html = $this->head();

		$this->assertStringNotContainsString( '<meta name="description"', $html );
		$this->assertStringNotContainsString( 'og:', $html );
		$this->assertStringNotContainsString( 'twitter:', $html );
		$this->assertSame( array( 'ProductGroup', 'FAQPage' ), $this->schema_types( $html ) );
	}

	/**
	 * The ProductGroup keeps pointing at #organization, which is the id Yoast
	 * gives its own Organization node, so the brand link still resolves.
	 *
	 * @return void
	 */
	public function test_with_a_plugin_the_brand_still_points_at_the_organization_id(): void {
		Filters\expectApplied( 'cosypaw_seo_plugin_active' )->andReturn( true );

		$this->assertStringContainsString(
			'"brand":{"@id":"https://cosypaw.test/#organization"}',
			$this->head()
		);
	}

	/**
	 * Off the front page there is nothing left for the theme to add, and an
	 * empty @graph is not printed.
	 *
	 * @return void
	 */
	public function test_with_a_plugin_other_pages_get_no_json_ld_at_all(): void {
		Filters\expectApplied( 'cosypaw_seo_plugin_active' )->andReturn( true );
		Functions\when( 'is_front_page' )->justReturn( false );

		$this->assertStringNotContainsString( 'application/ld+json', $this->head() );
	}

	/**
	 * hreflang is the one thing Yoast (free) never writes, and the site serves
	 * three languages off every URL — it has to survive the plugin.
	 *
	 * @return void
	 */
	public function test_with_a_plugin_hreflang_is_still_emitted(): void {
		Filters\expectApplied( 'cosypaw_seo_plugin_active' )->andReturn( true );

		$language = new class() {
			/** @return string[] */
			public function codes(): array {
				return array( 'sr', 'en', 'ru' );
			}

			public function locale_for( string $code ): string {
				return array( 'sr' => 'sr_RS', 'en' => 'en_US', 'ru' => 'ru_RU' )[ $code ];
			}
		};
		Functions\when( 'cosypaw_language' )->justReturn( $language );

		$html = $this->head();

		foreach ( array( 'sr-RS', 'en-US', 'ru-RU', 'x-default' ) as $tag ) {
			$this->assertStringContainsString( 'hreflang="' . $tag . '"', $html );
		}
	}

	/**
	 * The master switch still silences everything, hreflang included.
	 *
	 * @return void
	 */
	public function test_the_master_switch_still_silences_everything(): void {
		Filters\expectApplied( 'cosypaw_seo_enabled' )->andReturn( false );

		$this->assertSame( '', $this->head() );
	}
}
