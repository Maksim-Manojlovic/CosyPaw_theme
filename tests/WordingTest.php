<?php
/**
 * Unit tests for carrying "peškirići" → "peškiri" into seeded database text.
 *
 * @package CosyPaw\Tests
 */

declare(strict_types=1);

namespace Theme\Tests;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use Theme\LongTowels;
use Theme\WooCommerce;
use Theme\Wording;

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once __DIR__ . '/stubs.php';
require_once dirname( __DIR__ ) . '/inc/Catalog.php';
require_once dirname( __DIR__ ) . '/inc/WooCommerce.php';
require_once dirname( __DIR__ ) . '/inc/ProductLine.php';
require_once dirname( __DIR__ ) . '/inc/LongTowels.php';
require_once dirname( __DIR__ ) . '/inc/Wording.php';

final class WordingTest extends TestCase {

	/** @var array<string,mixed> */
	private array $options = array();

	/** @var array<int,array<string,string>> */
	private array $meta = array();

	/** @var array<int,object> */
	private array $posts = array();

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		$this->options = array();
		$this->meta    = array();
		$this->posts   = array();

		Functions\stubs(
			array(
				'add_action'       => true,
				'current_user_can' => true,
			)
		);

		Functions\when( 'get_option' )->alias( fn( string $name, $fallback = false ) => $this->options[ $name ] ?? $fallback );
		Functions\when( 'update_option' )->alias(
			function ( string $name, $value ): bool {
				$this->options[ $name ] = $value;
				return true;
			}
		);
		Functions\when( 'get_post_thumbnail_id' )->alias( static fn( int $id ): int => array( 1 => 11 )[ $id ] ?? 0 );
		Functions\when( 'get_post_meta' )->alias( fn( int $id, string $key ) => $this->meta[ $id ][ $key ] ?? '' );
		Functions\when( 'update_post_meta' )->alias(
			function ( int $id, string $key, $value ): bool {
				$this->meta[ $id ][ $key ] = (string) $value;
				return true;
			}
		);
		Functions\when( 'get_post' )->alias( fn( int $id ) => $this->posts[ $id ] ?? null );
		Functions\when( 'wp_update_post' )->alias(
			function ( array $post ): int {
				$this->posts[ $post['ID'] ] = (object) array_merge( (array) $this->posts[ $post['ID'] ], $post );
				return $post['ID'];
			}
		);
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Every case form keeps its ending, except the instrumental.
	 *
	 * @return void
	 */
	public function test_swap_declines_the_word(): void {
		$this->assertSame( 'Peškiri', Wording::swap( 'Peškirići' ) );
		$this->assertSame( 'Mekani svet peškira', Wording::swap( 'Mekani svet peškirića' ) );
		$this->assertSame( 'ubaci omiljene peškire', Wording::swap( 'ubaci omiljene peškiriće' ) );
		$this->assertSame( 'sa svakim sledećim peškirom', Wording::swap( 'sa svakim sledećim peškirićem' ) );
		$this->assertSame( 'o svojim peškirima', Wording::swap( 'o svojim peškirićima' ) );
		$this->assertSame( 'Dugi peškir', Wording::swap( 'Dugi peškir' ) );
	}

	/**
	 * The towel photographs' alt, caption and description, and the long
	 * towels' description, lose the diminutive; nothing else moves.
	 *
	 * @return void
	 */
	public function test_it_swaps_the_seeded_fields_once(): void {
		$this->options[ WooCommerce::PRODUCT_MAP_OPTION ] = array( 'zeka' => 1 );
		$this->options[ LongTowels::MAP_OPTION ] = array( 'meda-krem' => 2 );

		$this->meta[11]['_wp_attachment_image_alt'] = 'Ručno šiven ukrasni peškirić u obliku zeke';
		$this->posts[11] = (object) array(
			'post_excerpt' => 'Tost — dobro jutro u obliku peškirića.',
			'post_content' => 'Napisao vlasnik, bez te reči.',
		);
		$this->posts[2] = (object) array( 'post_content' => 'Duži je od peškirića za ruke.' );

		$wording = new Wording();
		$wording->maybe_run();

		$this->assertSame( 'Ručno šiven ukrasni peškir u obliku zeke', $this->meta[11]['_wp_attachment_image_alt'] );
		$this->assertSame( 'Tost — dobro jutro u obliku peškira.', $this->posts[11]->post_excerpt );
		$this->assertSame( 'Napisao vlasnik, bez te reči.', $this->posts[11]->post_content );
		$this->assertSame( 'Duži je od peškira za ruke.', $this->posts[2]->post_content );

		// Done for this version: a word typed back in later stays.
		$this->meta[11]['_wp_attachment_image_alt'] = 'peškirić';
		$wording->maybe_run();
		$this->assertSame( 'peškirić', $this->meta[11]['_wp_attachment_image_alt'] );
	}
}
