<?php
/**
 * Catalog — plain data provider for the CosyPaw landing page.
 *
 * Mirrors the product/package data from the design source (CosyPaw.dc.html).
 * This is a pure data object (no side effects, no hooks) so templates may
 * instantiate it directly with `new \Theme\Catalog()`. When real WooCommerce
 * products exist, map IDs via the `cosypaw_catalog_*` filters.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

namespace Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Catalog.
 */
final class Catalog {

	/**
	 * Per-towel unit price (RSD).
	 *
	 * @var int
	 */
	public const UNIT_PRICE = 790;

	/**
	 * Directory (under the theme root) holding the motif photography.
	 *
	 * @var string
	 */
	private const IMAGE_DIR = 'assets/motifs/';

	/**
	 * Towel motifs.
	 *
	 * Each id resolves to three AVIFs in assets/motifs/ — `<id>.avif` (1086x1448,
	 * the untouched camera original and the seeder's sideload source),
	 * `<id>-md.avif` (600x800, hero + motif cards) and `<id>-sm.avif` (360x360
	 * square, the bundle-builder picker). Serving the pre-cropped variants keeps
	 * the motif grid off the full-size originals.
	 *
	 * AVIF is safe for the front end (the images are referenced by URL, and
	 * browser support is universal), but see ProductSeeder::avif_supported() for
	 * the WordPress-side caveat when sideloading these into the media library.
	 *
	 * The price is the seed value only: once a motif is mapped to a real
	 * WooCommerce product, WooCommerce::inject_product_ids() replaces it with
	 * whatever the shop charges.
	 *
	 * @return array<int,array{id:string,name:string,price:int,image:string,image_md:string,image_sm:string}>
	 */
	public function products(): array {
		$motifs = array(
			array( 'id' => 'zirafa',   'name' => __( 'Žirafa', 'cosypaw' ) ),
			array( 'id' => 'koala',    'name' => __( 'Koala', 'cosypaw' ) ),
			array( 'id' => 'pingvin',  'name' => __( 'Pingvin', 'cosypaw' ) ),
			array( 'id' => 'sova',     'name' => __( 'Sova', 'cosypaw' ) ),
			array( 'id' => 'panda',    'name' => __( 'Panda', 'cosypaw' ) ),
			array( 'id' => 'meda',     'name' => __( 'Meda', 'cosypaw' ) ),
			array( 'id' => 'kapibara', 'name' => __( 'Kapibara', 'cosypaw' ) ),
			array( 'id' => 'maca',     'name' => __( 'Maca', 'cosypaw' ) ),
			array( 'id' => 'kucence',  'name' => __( 'Kucence', 'cosypaw' ) ),
			array( 'id' => 'zeka',     'name' => __( 'Zeka', 'cosypaw' ) ),
			array( 'id' => 'avokado',  'name' => __( 'Avokado', 'cosypaw' ) ),
			array( 'id' => 'ananas',   'name' => __( 'Ananas', 'cosypaw' ) ),
			array( 'id' => 'tresnja',  'name' => __( 'Trešnja', 'cosypaw' ) ),
			array( 'id' => 'sir',      'name' => __( 'Sir', 'cosypaw' ) ),
			array( 'id' => 'krofna',   'name' => __( 'Krofna', 'cosypaw' ) ),
			array( 'id' => 'biskvit',  'name' => __( 'Biskvit', 'cosypaw' ) ),
			array( 'id' => 'keks',     'name' => __( 'Čokoladni keks', 'cosypaw' ) ),
			array( 'id' => 'tost',     'name' => __( 'Tost', 'cosypaw' ) ),
			array( 'id' => 'lala',     'name' => __( 'Lala', 'cosypaw' ) ),
			array( 'id' => 'list',     'name' => __( 'Javorov list', 'cosypaw' ) ),
		);

		$base = get_template_directory_uri() . '/' . self::IMAGE_DIR;
		$out  = array();
		foreach ( $motifs as $m ) {
			$out[] = array(
				'id'       => $m['id'],
				'name'     => $m['name'],
				'price'    => self::UNIT_PRICE,
				'image'    => $base . $m['id'] . '.avif',
				'image_md' => $base . $m['id'] . '-md.avif',
				'image_sm' => $base . $m['id'] . '-sm.avif',
			);
		}

		/**
		 * Filter the towel motif list (e.g. to map real WC product IDs).
		 *
		 * @param array $out The motif data.
		 */
		return (array) apply_filters( 'cosypaw_catalog_products', $out );
	}

	/**
	 * Purchase packages (bundle pricing).
	 *
	 * The crossed-out `old` price is what the same number of towels costs bought
	 * one at a time, so it is always UNIT_PRICE * qty, and `badge_saving` marks
	 * the badge as the difference between the two rather than free text. Both
	 * are recomputed from the live WooCommerce prices in
	 * WooCommerce::inject_package_ids() — the values below are only the seed.
	 *
	 * @return array<int,array{id:string,name:string,qty:int,price:int,old:?int,per:int,badge:?string,badge_saving:bool,best:bool,free_ship:bool,desc:string}>
	 */
	public function packages(): array {
		$packages = array(
			array(
				'id'           => 'solo',
				'name'         => __( 'Pojedinačno', 'cosypaw' ),
				'qty'          => 1,
				'price'        => self::UNIT_PRICE,
				'old'          => null,
				'per'          => self::UNIT_PRICE,
				'badge'        => null,
				'badge_saving' => false,
				'best'         => false,
				'free_ship'    => false,
				'desc'         => __( 'Jedan omiljeni motiv', 'cosypaw' ),
			),
			array(
				'id'           => 'duo',
				'name'         => __( 'Duo paket', 'cosypaw' ),
				'qty'          => 2,
				'price'        => 1200,
				'old'          => self::UNIT_PRICE * 2,
				'per'          => 600,
				/* translators: %s: formatted amount saved, e.g. "380 RSD". */
				'badge'        => sprintf( __( 'Ušteda %s', 'cosypaw' ), self::format_price( self::UNIT_PRICE * 2 - 1200 ) ),
				'badge_saving' => true,
				'best'         => false,
				'free_ship'    => false,
				'desc'         => __( 'Dva motiva po izboru', 'cosypaw' ),
			),
			array(
				'id'           => 'trio',
				'name'         => __( 'Trio paket', 'cosypaw' ),
				'qty'          => 3,
				'price'        => 1600,
				'old'          => self::UNIT_PRICE * 3,
				'per'          => 534,
				'badge'        => __( 'Najpopularnije', 'cosypaw' ),
				'badge_saving' => false,
				'best'         => true,
				'free_ship'    => true,
				'desc'         => __( 'Tri motiva po izboru', 'cosypaw' ),
			),
		);

		/**
		 * Filter the package list.
		 *
		 * @param array $packages The package data.
		 */
		return (array) apply_filters( 'cosypaw_catalog_packages', $packages );
	}

	/**
	 * Default-selected package id.
	 *
	 * @return string
	 */
	public function default_package(): string {
		return (string) apply_filters( 'cosypaw_catalog_default_package', 'trio' );
	}

	/**
	 * Format a price for display (de-DE grouping, as in the design) + RSD suffix.
	 *
	 * @param int $amount Amount in RSD.
	 * @return string
	 */
	public static function format_price( int $amount ): string {
		return number_format( $amount, 0, ',', '.' ) . ' RSD';
	}

	/**
	 * Map of motif id => { name, image } for fast lookups (cart thumbnails etc.).
	 * Names are translated for the current locale (from products()).
	 *
	 * @return array<string,array{id:string,name:string,image:string,image_md:string,image_sm:string}>
	 */
	public function motif_map(): array {
		$map = array();
		foreach ( $this->products() as $product ) {
			$map[ $product['id'] ] = $product;
		}

		return $map;
	}

	/**
	 * The motif ids featured in the hero carousel.
	 *
	 * @return string[]
	 */
	public function featured_ids(): array {
		return array( 'zirafa', 'kapibara', 'panda', 'tresnja', 'list', 'pingvin' );
	}

	/**
	 * Featured products for the hero carousel, in order.
	 *
	 * @return array<int,array{id:string,name:string,image:string,image_md:string,image_sm:string}>
	 */
	public function featured(): array {
		$by_id = array();
		foreach ( $this->products() as $p ) {
			$by_id[ $p['id'] ] = $p;
		}

		$out = array();
		foreach ( $this->featured_ids() as $id ) {
			if ( isset( $by_id[ $id ] ) ) {
				$out[] = $by_id[ $id ];
			}
		}

		return $out;
	}
}
