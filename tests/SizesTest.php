<?php
/**
 * Unit tests for carrying the stated sizes into seeded long descriptions.
 *
 * @package CosyPaw\Tests
 */

declare(strict_types=1);

namespace Theme\Tests;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use Theme\Cloths;
use Theme\LongTowels;
use Theme\Pillows;
use Theme\Sizes;

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once __DIR__ . '/stubs.php';
require_once dirname( __DIR__ ) . '/inc/ProductLine.php';
require_once dirname( __DIR__ ) . '/inc/Cloths.php';
require_once dirname( __DIR__ ) . '/inc/LongTowels.php';
require_once dirname( __DIR__ ) . '/inc/Pillows.php';
require_once dirname( __DIR__ ) . '/inc/Sizes.php';

final class SizesTest extends TestCase {

	/** @var array<string,mixed> */
	private array $options = array();

	/** @var array<int,object> */
	private array $posts = array();

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		$this->options = array();
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
	 * A registry description, as it read before its size sentence.
	 *
	 * @param string $class    Class holding the constant.
	 * @param string $constant Constant name.
	 * @param string $sentence The size sentence to take out.
	 * @return array{0:string,1:string} Old text, new (registry) text.
	 */
	private function pair( string $class, string $constant, string $sentence ): array {
		$new = (string) ( new \ReflectionClassConstant( $class, $constant ) )->getValue();
		$this->assertStringContainsString( $sentence, $new );

		return array( str_replace( $sentence, '', $new ), $new );
	}

	/**
	 * The migration turns the old stored text into exactly what the registry
	 * now seeds, so a migrated product reads like a new one and keeps its
	 * translation.
	 *
	 * @return void
	 */
	public function test_it_lands_on_the_registry_copy(): void {
		list( $cloth_old, $cloth_new )   = $this->pair( Cloths::class, 'DESCRIPTION', 'Dimenzije su 29 cm x 21 cm. ' );
		list( $towel_old, $towel_new )   = $this->pair( LongTowels::class, 'DESCRIPTION', 'Dimenzije peškira su 50 cm x 25 cm. ' );
		list( $round_old, $round_new )   = $this->pair( Pillows::class, 'DESCRIPTION', 'Dimenzije kružića su 41 cm x 45 cm. ' );
		list( $square_old, $square_new ) = $this->pair( Pillows::class, 'SQUARE_DESCRIPTION', 'Dimenzije kockastog jastuka su 41 cm x 41 cm. ' );

		$this->options[ Cloths::MAP_OPTION ]      = array( 'avokado-limeta' => 1, Cloths::SET => 2 );
		$this->options[ LongTowels::MAP_OPTION ]  = array( 'meda-krem' => 3 );
		$this->options[ Pillows::MAP_OPTION ]     = array(
			'kruzic-braon'  => 4,
			'kockasti-roze' => 5,
			'cvetic-zuti'   => 6,
		);

		$flower = 'Cvetić nema dimenzije. omčica za kačenje. ';

		$this->posts[1] = (object) array( 'post_content' => $cloth_old );
		$this->posts[2] = (object) array( 'post_content' => $cloth_old );
		$this->posts[3] = (object) array( 'post_content' => $towel_old );
		$this->posts[4] = (object) array( 'post_content' => $round_old );
		$this->posts[5] = (object) array( 'post_content' => $square_old );
		$this->posts[6] = (object) array( 'post_content' => $flower );

		( new Sizes() )->maybe_run();

		$this->assertSame( $cloth_new, $this->posts[1]->post_content );
		$this->assertSame( $cloth_new, $this->posts[2]->post_content );
		$this->assertSame( $towel_new, $this->posts[3]->post_content );
		$this->assertSame( $round_new, $this->posts[4]->post_content );
		$this->assertSame( $square_new, $this->posts[5]->post_content );
		$this->assertSame( $flower, $this->posts[6]->post_content, 'flowers have no stated size' );
	}

	/**
	 * Rewritten text is left alone, and nothing is added twice.
	 *
	 * @return void
	 */
	public function test_it_leaves_edited_text_and_never_doubles(): void {
		list( $old, $new ) = $this->pair( Cloths::class, 'DESCRIPTION', 'Dimenzije su 29 cm x 21 cm. ' );

		$this->options[ Cloths::MAP_OPTION ] = array( 'avokado-limeta' => 1, 'avokado-zalfija' => 2, Cloths::SET => 3 );
		$this->posts[1]                      = (object) array( 'post_content' => 'Moj sopstveni opis.' );
		$this->posts[2]                      = (object) array( 'post_content' => $new );
		$this->posts[3]                      = (object) array( 'post_content' => $old );

		$sizes = new Sizes();
		$this->assertSame( 1, $sizes->run() );
		$this->assertSame( 'Moj sopstveni opis.', $this->posts[1]->post_content );
		$this->assertSame( $new, $this->posts[2]->post_content );
		$this->assertSame( $new, $this->posts[3]->post_content );

		// Second pass: nothing left to do.
		$this->assertSame( 0, $sizes->run() );
	}

	/**
	 * Once per version: text typed back in later is not touched.
	 *
	 * @return void
	 */
	public function test_it_runs_once(): void {
		list( $old ) = $this->pair( Cloths::class, 'DESCRIPTION', 'Dimenzije su 29 cm x 21 cm. ' );

		$this->options[ Cloths::MAP_OPTION ] = array( 'avokado-limeta' => 1 );
		$this->posts[1]                      = (object) array( 'post_content' => 'x' );

		$sizes = new Sizes();
		$sizes->maybe_run();

		$this->posts[1] = (object) array( 'post_content' => $old );
		$sizes->maybe_run();

		$this->assertSame( $old, $this->posts[1]->post_content );
	}
}
