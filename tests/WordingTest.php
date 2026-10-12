<?php
/**
 * Unit tests for carrying "peškirići" → "peškiri", and "dugi peškiri" →
 * "peškiri", into seeded database text.
 *
 * @package CosyPaw\Tests
 */

declare(strict_types=1);

namespace Theme\Tests;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use Theme\GiftBundles;
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
require_once dirname( __DIR__ ) . '/inc/GiftBundles.php';
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
		Functions\when( 'get_post_thumbnail_id' )->alias( static fn( int $id ): int => array( 1 => 11, 2 => 12, 3 => 13 )[ $id ] ?? 0 );
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

	/**
	 * "dugi" goes in every case form, and only where it names the towel.
	 *
	 * @return void
	 */
	public function test_unlong_drops_the_word_in_front_of_the_towel(): void {
		$this->assertSame( 'Peškir Meda, krem', Wording::unlong( 'Dugi peškir Meda, krem' ) );
		$this->assertSame( 'Peškir Meda, Krem | Mekani Peškir za Kupatilo', Wording::unlong( 'Dugi Peškir Meda, Krem | Mekani Peškir za Kupatilo' ) );
		$this->assertSame( 'Krem peškir sa vezenim medom', Wording::unlong( 'Krem dugi peškir sa vezenim medom' ) );
		$this->assertSame( 'dva peškira za kupatilo', Wording::unlong( 'dva duga peškira za kupatilo' ) );
		$this->assertSame( 'poput peškira sa vezenim medom', Wording::unlong( 'poput dugih peškira sa vezenim medom' ) );
		$this->assertSame( 'sa jastucima, peškirima i krpicama', Wording::unlong( 'sa jastucima, dugim peškirima i krpicama' ) );

		// Beside another towel a bare "peškir" would not say which one.
		$this->assertSame( 'tri dečija peškira, jastuk, peškir sa medom i dve krpice', Wording::unlong( 'tri dečija peškira, jastuk, dugi peškir i dve krpice' ) );
		$this->assertSame( 'Peškiri za Odrasle i Decu u Paketu', Wording::unlong( 'Dugi i Dečiji Peškiri u Paketu' ) );

		// A fact about the towel, not its name — and other long things.
		$this->assertSame( 'Duži je od peškira za ruke.', Wording::unlong( 'Duži je od peškira za ruke.' ) );
		$this->assertSame( 'Žirafa duga vrata.', Wording::unlong( 'Žirafa duga vrata.' ) );
	}

	/**
	 * What the swap makes of the old seeded copy is what the registries say
	 * now — the stored text doubles as a msgid, so one stray word would cost
	 * the product its translation.
	 *
	 * @return void
	 */
	public function test_unlong_lands_on_the_registry_copy(): void {
		$item = LongTowels::ITEMS['meda-roze'];

		$this->assertSame( $item['name'], Wording::unlong( 'Dugi peškir Meda, roze' ) );
		$this->assertSame( $item['short'], Wording::unlong( 'Mekani dugi peškir u nežnoj roze boji, sa vezenim medom, šapicom i natpisom GOODLUCK na borduri. Za lice, ruke i kosu.' ) );
		$this->assertSame( $item['seo_title'], Wording::unlong( 'Dugi Peškir Meda, Roze | Mekani Peškir za Kupatilo' ) );

		$bundle = GiftBundles::ITEMS['praznicna-kutija'];

		$this->assertSame( $bundle['short'], Wording::unlong( 'Najveći CosyPaw poklon: tri dečija peškira, Cuddle Puff jastuk, dugi peškir i dve magične krpice. Novogodišnji paketić za celu porodicu.' ) );
		$this->assertSame( GiftBundles::ITEMS['kupatilo-za-porodicu']['seo_title'], Wording::unlong( 'Peškiri za Celu Porodicu | Dugi i Dečiji Peškiri u Paketu' ) );
	}

	/**
	 * An install already past the first swap gets only the second: the long
	 * towels and the bundles that hold one lose "dugi" in everything the
	 * theme wrote, and what the shop wrote itself is left as it is.
	 *
	 * @return void
	 */
	public function test_long_towels_lose_the_word_in_what_was_seeded(): void {
		$this->options['cosypaw_wording_version'] = 1;
		$this->options[ LongTowels::MAP_OPTION ]  = array( 'meda-krem' => 2 );
		$this->options[ GiftBundles::MAP_OPTION ] = array( 'novi-dom' => 3 );

		$this->posts[2]  = (object) array(
			'post_title'   => 'Dugi peškir Meda, krem',
			'post_excerpt' => 'Mekani dugi peškir u krem boji.',
			'post_content' => 'Dugi peškir od mekane tkanine. Duži je od peškirića za ruke.',
		);
		$this->meta[2]   = array(
			'_yoast_wpseo_title'    => 'Dugi Peškir Meda, Krem | Mekani Peškir za Kupatilo',
			'_yoast_wpseo_metadesc' => 'Naš opis, bez te reči.',
		);
		$this->posts[12] = (object) array( 'post_title' => 'Dugi peškir Meda, krem' );
		$this->meta[12]  = array( '_wp_attachment_image_alt' => 'Krem dugi peškir sa vezenim medom' );

		$this->posts[3]  = (object) array(
			'post_title'   => 'Poklon paket Novi dom',
			'post_excerpt' => 'Poklon za useljenje: jastuk, dva duga peškira i dve magične krpice.',
			'post_content' => 'Napisao vlasnik.',
		);
		$this->meta[13]  = array( '_wp_attachment_image_alt' => 'Braon jastuk, krem i bež dugi peškir sa vezenim medom' );

		( new Wording() )->maybe_run();

		$this->assertSame( 'Peškir Meda, krem', $this->posts[2]->post_title );
		$this->assertSame( 'Mekani peškir u krem boji.', $this->posts[2]->post_excerpt );
		// The first swap is behind this install, so its word is not looked for.
		$this->assertSame( 'Peškir od mekane tkanine. Duži je od peškirića za ruke.', $this->posts[2]->post_content );
		$this->assertSame( 'Peškir Meda, Krem | Mekani Peškir za Kupatilo', $this->meta[2]['_yoast_wpseo_title'] );
		$this->assertSame( 'Naš opis, bez te reči.', $this->meta[2]['_yoast_wpseo_metadesc'] );
		$this->assertSame( 'Peškir Meda, krem', $this->posts[12]->post_title );
		$this->assertSame( 'Krem peškir sa vezenim medom', $this->meta[12]['_wp_attachment_image_alt'] );

		$this->assertSame( 'Poklon paket Novi dom', $this->posts[3]->post_title );
		$this->assertSame( 'Poklon za useljenje: jastuk, dva peškira i dve magične krpice.', $this->posts[3]->post_excerpt );
		$this->assertSame( 'Napisao vlasnik.', $this->posts[3]->post_content );
		$this->assertSame( 'Braon jastuk, krem i bež peškir sa vezenim medom', $this->meta[13]['_wp_attachment_image_alt'] );

		$this->assertSame( Wording::VERSION, $this->options['cosypaw_wording_version'] );
	}
}
