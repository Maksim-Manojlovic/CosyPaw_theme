<?php
/**
 * Sizes — carries the sizes the shop has stated into the long descriptions
 * the theme already wrote to the database.
 *
 * A product's long description is written once, when the product is created,
 * and is wp-admin's after that, so a sentence added to the registry copy
 * (Cloths, Pillows, LongTowels) never reaches a product that exists. The
 * spec list is code and shows the size at once; this adds the same size, as
 * one sentence, to the stored description.
 *
 * It runs once per VERSION, the first time an administrator opens wp-admin,
 * and it is careful with what the shop may have rewritten: a description is
 * touched only where it still holds the seeded sentence the new one goes
 * after, and never twice.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

namespace Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sizes.
 */
final class Sizes {

	/**
	 * Bump when a size is added.
	 */
	public const VERSION = 1;

	/**
	 * Option recording which version this install has been brought up to.
	 */
	private const OPTION = 'cosypaw_sizes_version';

	/**
	 * Seeded sentence the size goes after, and the size's own sentence — the
	 * same words the registries carry, so a new install and a migrated one
	 * read alike.
	 */
	private const CLOTH_AFTER  = 'bez agresivnih sredstava. ';
	private const CLOTH_SIZE   = 'Dimenzije su 29 cm x 21 cm. ';
	private const PILLOW_AFTER = 'omčica za kačenje. ';
	private const SQUARE_SIZE  = 'Dimenzije kockastog jastuka su 41 cm x 41 cm. ';
	private const ROUND_SIZE   = 'Dimenzije kružića su 41 cm x 45 cm. ';
	private const TOWEL_AFTER  = 'na tkanoj borduri. ';
	private const TOWEL_SIZE   = 'Dimenzije peškira su 50 cm x 25 cm. ';

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
	 * Add each size to the description it belongs to.
	 *
	 * @return int Number of descriptions changed.
	 */
	public function run(): int {
		$changed = 0;

		foreach ( $this->targets() as $id => $target ) {
			$post = $id > 0 ? get_post( $id ) : null;
			if ( ! is_object( $post ) ) {
				continue;
			}

			$text = (string) ( $post->post_content ?? '' );
			$new  = self::add( $text, $target[0], $target[1] );
			if ( $new !== $text ) {
				wp_update_post(
					array(
						'ID'           => $id,
						'post_content' => $new,
					)
				);
				++$changed;
			}
		}

		return $changed;
	}

	/**
	 * Product id => [ sentence to follow, size sentence ].
	 *
	 * @return array<int,array{0:string,1:string}>
	 */
	private function targets(): array {
		$targets = array();

		foreach ( (array) get_option( Cloths::MAP_OPTION, array() ) as $id ) {
			$targets[ (int) $id ] = array( self::CLOTH_AFTER, self::CLOTH_SIZE );
		}

		foreach ( (array) get_option( LongTowels::MAP_OPTION, array() ) as $id ) {
			$targets[ (int) $id ] = array( self::TOWEL_AFTER, self::TOWEL_SIZE );
		}

		foreach ( (array) get_option( Pillows::MAP_OPTION, array() ) as $key => $id ) {
			$group = Pillows::ITEMS[ $key ]['group'] ?? '';
			if ( Pillows::SQUARE === $group ) {
				$targets[ (int) $id ] = array( self::PILLOW_AFTER, self::SQUARE_SIZE );
			} elseif ( Pillows::ROUND === $group ) {
				$targets[ (int) $id ] = array( self::PILLOW_AFTER, self::ROUND_SIZE );
			}
		}

		return $targets;
	}

	/**
	 * Put the size sentence right after the seeded one.
	 *
	 * A description that no longer holds the seeded sentence (rewritten in
	 * wp-admin) or already holds the size comes back as it was.
	 *
	 * @param string $text  Description.
	 * @param string $after Seeded sentence, with its trailing space.
	 * @param string $size  Size sentence, with its trailing space.
	 * @return string
	 */
	public static function add( string $text, string $after, string $size ): string {
		if ( str_contains( $text, $size ) || 1 !== substr_count( $text, $after ) ) {
			return $text;
		}

		return str_replace( $after, $after . $size, $text );
	}
}
