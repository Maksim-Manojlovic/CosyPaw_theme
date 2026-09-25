<?php
/**
 * Unit tests for the landing-page registry.
 *
 * @package CosyPaw\Tests
 */

declare(strict_types=1);

namespace Theme\Tests;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use Theme\Pages;

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once dirname( __DIR__ ) . '/inc/Pages.php';

final class PagesTest extends TestCase {

	/**
	 * Pages the fake database holds, slug => (object) post.
	 *
	 * @var array<string,object>
	 */
	private array $pages = array();

	/**
	 * Post meta written, id => key => value.
	 *
	 * @var array<int,array<string,string>>
	 */
	private array $meta = array();

	/**
	 * Options, name => value.
	 *
	 * @var array<string,mixed>
	 */
	private array $options = array();

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		$this->pages   = array();
		$this->meta    = array();
		$this->options = array();

		Functions\stubs(
			array(
				'add_action'       => true,
				'current_user_can' => true,
				'is_wp_error'      => false,
			)
		);

		Functions\when( 'get_page_by_path' )->alias( fn( string $slug ) => $this->pages[ $slug ] ?? null );
		Functions\when( 'wp_insert_post' )->alias(
			function ( array $post ): int {
				$id = 100 + count( $this->pages );

				$this->pages[ $post['post_name'] ] = (object) array_merge( $post, array( 'ID' => $id ) );

				return $id;
			}
		);
		Functions\when( 'update_post_meta' )->alias(
			function ( int $id, string $key, string $value ): bool {
				$this->meta[ $id ][ $key ] = $value;

				return true;
			}
		);
		Functions\when( 'get_option' )->alias( fn( string $name, $fallback = false ) => $this->options[ $name ] ?? $fallback );
		Functions\when( 'update_option' )->alias(
			function ( string $name, $value ): bool {
				$this->options[ $name ] = $value;

				return true;
			}
		);
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * A missing page is created, published, empty, with the plan's Yoast
	 * title and description.
	 *
	 * @return void
	 */
	public function test_it_creates_the_collection_page_with_its_seo_fields(): void {
		$this->assertSame( 1, ( new Pages() )->ensure() );

		$page = $this->pages['deciji-peskiri-sa-motivima-zivotinja'];
		$this->assertSame( 'page', $page->post_type );
		$this->assertSame( 'publish', $page->post_status );
		$this->assertSame( '', $page->post_content );

		$def = Pages::REGISTRY['motivi'];
		$this->assertSame( $def['seo_title'], $this->meta[ $page->ID ]['_yoast_wpseo_title'] );
		$this->assertSame( $def['seo_description'], $this->meta[ $page->ID ]['_yoast_wpseo_metadesc'] );
		$this->assertSame( $def['seo_description'], $page->post_excerpt );
	}

	/**
	 * An existing page — possibly edited in wp-admin — is left alone.
	 *
	 * @return void
	 */
	public function test_it_leaves_an_existing_page_alone(): void {
		$this->pages['deciji-peskiri-sa-motivima-zivotinja'] = (object) array( 'ID' => 7, 'post_title' => 'Naš naslov' );

		$this->assertSame( 0, ( new Pages() )->ensure() );
		$this->assertSame( 'Naš naslov', $this->pages['deciji-peskiri-sa-motivima-zivotinja']->post_title );
		$this->assertArrayNotHasKey( 7, $this->meta );
	}

	/**
	 * Once an install is at the current version, a page deleted afterwards
	 * stays deleted — the shop's call, not the theme's.
	 *
	 * @return void
	 */
	public function test_it_runs_once_per_registry_version(): void {
		$pages = new Pages();

		$pages->maybe_ensure();
		$this->assertSame( Pages::VERSION, $this->options['cosypaw_pages_version'] );

		$this->pages = array();
		$pages->maybe_ensure();

		$this->assertSame( array(), $this->pages );
	}

	/**
	 * Someone without the right to publish pages does not create them, and
	 * does not mark the install done either.
	 *
	 * @return void
	 */
	public function test_it_waits_for_a_user_who_can_publish_pages(): void {
		Functions\when( 'current_user_can' )->justReturn( false );

		( new Pages() )->maybe_ensure();

		$this->assertSame( array(), $this->pages );
		$this->assertArrayNotHasKey( 'cosypaw_pages_version', $this->options );
	}

	/**
	 * No URL for a page that does not exist or is not published, so callers
	 * fall back rather than link to a 404.
	 *
	 * @return void
	 */
	public function test_url_is_empty_until_the_page_is_published(): void {
		$this->assertSame( '', Pages::url( 'motivi' ) );
		$this->assertSame( '', Pages::url( 'nepostojeca' ) );
	}
}
