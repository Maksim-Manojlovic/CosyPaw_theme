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
	public const VERSION = 1;

	/**
	 * Option recording which version this install has been brought up to.
	 */
	private const OPTION = 'cosypaw_wording_version';

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
		if ( (int) get_option( self::OPTION, 0 ) >= self::VERSION || ! current_user_can( 'edit_products' ) ) {
			return;
		}

		$this->run();
		update_option( self::OPTION, self::VERSION );
	}

	/**
	 * Swap the word in every field the theme seeded.
	 *
	 * @return int Number of fields changed.
	 */
	public function run(): int {
		$changed = 0;

		// The towel photographs: alt text, caption and description.
		foreach ( (array) get_option( WooCommerce::PRODUCT_MAP_OPTION, array() ) as $product_id ) {
			$image = (int) get_post_thumbnail_id( (int) $product_id );
			if ( $image < 1 ) {
				continue;
			}

			$alt = (string) get_post_meta( $image, '_wp_attachment_image_alt', true );
			if ( self::swap( $alt ) !== $alt ) {
				update_post_meta( $image, '_wp_attachment_image_alt', self::swap( $alt ) );
				++$changed;
			}

			$changed += $this->swap_post( $image, array( 'post_excerpt', 'post_content' ) );
		}

		// The long towels' description.
		foreach ( (array) get_option( LongTowels::MAP_OPTION, array() ) as $product_id ) {
			$changed += $this->swap_post( (int) $product_id, array( 'post_content' ) );
		}

		return $changed;
	}

	/**
	 * Swap the word in some of a post's fields, saving only what changed.
	 *
	 * @param int                $id     Post id.
	 * @param array<int,string> $fields Post fields to look at.
	 * @return int Number of fields changed.
	 */
	private function swap_post( int $id, array $fields ): int {
		$post = $id > 0 ? get_post( $id ) : null;
		if ( ! is_object( $post ) ) {
			return 0;
		}

		$update = array();
		foreach ( $fields as $field ) {
			$text = (string) ( $post->$field ?? '' );
			if ( self::swap( $text ) !== $text ) {
				$update[ $field ] = self::swap( $text );
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
}
