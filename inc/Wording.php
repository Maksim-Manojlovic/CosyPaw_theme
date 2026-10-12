<?php
/**
 * Wording — carries a change of the shop's vocabulary into text the theme
 * has already written to the database.
 *
 * The towels used to be "peškirići"; the copy now calls them "peškiri". The
 * theme's own strings change with the code, but some of that copy was
 * written once into the database and is never written again: the alt text,
 * caption and description of every towel photograph (ProductSeeder) and the
 * long towels' description (LongTowels). Those keep the old word until
 * someone edits them by hand.
 *
 * This swaps the word in exactly those fields, once per VERSION, the first
 * time an administrator opens wp-admin. It changes nothing but the word —
 * declined to match ("peškirićem" becomes "peškirom") — so a field the shop
 * has rewritten keeps everything it says, minus the diminutive.
 *
 * The second swap is the long towels' name. They were sold as "dugi peškiri"
 * and are plain "peškiri" now, and here the name itself is database text:
 * the product title, both descriptions, the Yoast fields and the
 * photograph's title and alt (LongTowels), and the same fields of the gift
 * bundles that hold one (GiftBundles). The slugs keep the old word, so no
 * address moves. The page's own title is \Theme\Pages' to reword.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

namespace Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wording.
 */
final class Wording {

	/**
	 * Bump when a new swap is added.
	 */
	public const VERSION = 2;

	/**
	 * Option recording which version this install has been brought up to.
	 */
	private const OPTION = 'cosypaw_wording_version';

	/**
	 * Phrases the long-towel swap rewrites whole, before the word is dropped.
	 *
	 * Beside another towel a bare "peškir" would not say which one is meant,
	 * so there the long towel is named by its teddy, or by who it is for.
	 *
	 * @var array<string,string>
	 */
	private const LONG_PHRASES = array(
		'Dugi i Dečiji Peškiri' => 'Peškiri za Odrasle i Decu',
		'dugi peškir i '        => 'peškir sa medom i ',
	);

	/**
	 * Yoast's title and description, as post meta.
	 */
	private const YOAST = array( '_yoast_wpseo_title', '_yoast_wpseo_metadesc' );

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'maybe_run' ) );
	}

	/**
	 * Run once per VERSION, for someone allowed to edit the products.
	 *
	 * @return void
	 */
	public function maybe_run(): void {
		$done = (int) get_option( self::OPTION, 0 );
		if ( $done >= self::VERSION || ! current_user_can( 'edit_products' ) ) {
			return;
		}

		$this->run( $done );
		update_option( self::OPTION, self::VERSION );
	}

	/**
	 * Run every swap this install has not had yet.
	 *
	 * @param int $done Version the install is at.
	 * @return int Number of fields changed.
	 */
	public function run( int $done = 0 ): int {
		$changed = 0;

		if ( $done < 1 ) {
			$changed += $this->drop_diminutive();
		}
		if ( $done < 2 ) {
			$changed += $this->drop_long();
		}

		return $changed;
	}

	/**
	 * "peškirići" → "peškiri", in every field the theme seeded with it.
	 *
	 * @return int Number of fields changed.
	 */
	private function drop_diminutive(): int {
		$changed = 0;
		$swap    = array( self::class, 'swap' );

		// The towel photographs: alt text, caption and description.
		foreach ( (array) get_option( WooCommerce::PRODUCT_MAP_OPTION, array() ) as $product_id ) {
			$image = (int) get_post_thumbnail_id( (int) $product_id );
			if ( $image < 1 ) {
				continue;
			}

			$changed += $this->swap_meta( $image, array( '_wp_attachment_image_alt' ), $swap );
			$changed += $this->swap_post( $image, array( 'post_excerpt', 'post_content' ), $swap );
		}

		// The long towels' description.
		foreach ( (array) get_option( LongTowels::MAP_OPTION, array() ) as $product_id ) {
			$changed += $this->swap_post( (int) $product_id, array( 'post_content' ), $swap );
		}

		return $changed;
	}

	/**
	 * "dugi peškir" → "peškir": the long towels, and the gift bundles that
	 * hold one.
	 *
	 * @return int Number of fields changed.
	 */
	private function drop_long(): int {
		$changed = 0;
		$swap    = array( self::class, 'unlong' );

		$products = array_merge(
			array_values( (array) get_option( LongTowels::MAP_OPTION, array() ) ),
			array_values( (array) get_option( GiftBundles::MAP_OPTION, array() ) )
		);

		foreach ( $products as $product_id ) {
			$id = (int) $product_id;

			// Name, short description, description.
			$changed += $this->swap_post( $id, array( 'post_title', 'post_excerpt', 'post_content' ), $swap );
			$changed += $this->swap_meta( $id, self::YOAST, $swap );

			// The photograph was titled after the product when it was imported.
			$image = (int) get_post_thumbnail_id( $id );
			if ( $image > 0 ) {
				$changed += $this->swap_post( $image, array( 'post_title' ), $swap );
				$changed += $this->swap_meta( $image, array( '_wp_attachment_image_alt' ), $swap );
			}
		}

		return $changed;
	}

	/**
	 * Swap the word in some of a post's meta, saving only what changed.
	 *
	 * @param int               $id   Post id.
	 * @param array<int,string> $keys Meta keys to look at.
	 * @param callable          $swap The swap: text in, text out.
	 * @return int Number of fields changed.
	 */
	private function swap_meta( int $id, array $keys, callable $swap ): int {
		$changed = 0;

		foreach ( $keys as $key ) {
			$text = (string) get_post_meta( $id, $key, true );
			$new  = (string) $swap( $text );
			if ( $new !== $text ) {
				update_post_meta( $id, $key, $new );
				++$changed;
			}
		}

		return $changed;
	}

	/**
	 * Swap the word in some of a post's fields, saving only what changed.
	 *
	 * @param int               $id     Post id.
	 * @param array<int,string> $fields Post fields to look at.
	 * @param callable          $swap   The swap: text in, text out.
	 * @return int Number of fields changed.
	 */
	private function swap_post( int $id, array $fields, callable $swap ): int {
		$post = $id > 0 ? get_post( $id ) : null;
		if ( ! is_object( $post ) ) {
			return 0;
		}

		$update = array();
		foreach ( $fields as $field ) {
			$text = (string) ( $post->$field ?? '' );
			$new  = (string) $swap( $text );
			if ( $new !== $text ) {
				$update[ $field ] = $new;
			}
		}

		if ( $update ) {
			wp_update_post( array( 'ID' => $id ) + $update );
		}

		return count( $update );
	}

	/**
	 * "peškirić" in any case form becomes "peškir" in the same case form.
	 *
	 * Every ending carries over unchanged except the instrumental singular:
	 * peškirići → peškiri, peškirića → peškira, peškiriće → peškire, but
	 * peškirićem → peškirom.
	 *
	 * @param string $text Text.
	 * @return string
	 */
	public static function swap( string $text ): string {
		return (string) preg_replace_callback(
			'/([Pp])eškirić(em)?/u',
			static fn( array $m ): string => $m[1] . 'eškir' . ( empty( $m[2] ) ? '' : 'om' ),
			$text
		);
	}

	/**
	 * "dugi peškir" in any case form becomes "peškir" in the same case form.
	 *
	 * Only the adjective goes, and only in front of the noun: "Duži je od
	 * peškira za ruke" is a fact about the towel, not its name, and stays.
	 * Where the adjective opened the sentence or the title, the noun takes
	 * its capital.
	 *
	 * @param string $text Text.
	 * @return string
	 */
	public static function unlong( string $text ): string {
		return (string) preg_replace_callback(
			'/\b([Dd])ug(?:og|om|im|ih|i|a|e|u)\s+([Pp])eškir/u',
			static fn( array $m ): string => ( 'D' === $m[1] ? 'P' : $m[2] ) . 'eškir',
			strtr( $text, self::LONG_PHRASES )
		);
	}
}
