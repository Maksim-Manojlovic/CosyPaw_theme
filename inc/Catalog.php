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
	 * Towel motifs. Each maps to an image in the theme's /assets directory.
	 *
	 * @return array<int,array{id:string,name:string,image:string}>
	 */
	public function products(): array {
		$motifs = array(
			array( 'id' => 'zirafa',  'name' => __( 'Žirafa', 'cosypaw' ),         'file' => 'zirafa.png' ),
			array( 'id' => 'koala',   'name' => __( 'Koala', 'cosypaw' ),          'file' => 'koala.png' ),
			array( 'id' => 'pingvin', 'name' => __( 'Pingvin', 'cosypaw' ),        'file' => 'pingvin.png' ),
			array( 'id' => 'sova',    'name' => __( 'Sova', 'cosypaw' ),           'file' => 'sova.png' ),
			array( 'id' => 'avokado', 'name' => __( 'Avokado', 'cosypaw' ),        'file' => 'avokado.png' ),
			array( 'id' => 'ananas',  'name' => __( 'Ananas', 'cosypaw' ),         'file' => 'ananas.png' ),
			array( 'id' => 'biskvit', 'name' => __( 'Biskvit', 'cosypaw' ),        'file' => 'biskvit.png' ),
			array( 'id' => 'keks',    'name' => __( 'Čokoladni keks', 'cosypaw' ), 'file' => 'keks.png' ),
			array( 'id' => 'tost',    'name' => __( 'Tost', 'cosypaw' ),           'file' => 'tost.png' ),
		);

		$base = get_template_directory_uri() . '/assets/';
		$out  = array();
		foreach ( $motifs as $m ) {
			$out[] = array(
				'id'    => $m['id'],
				'name'  => $m['name'],
				'image' => $base . $m['file'],
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
	 * @return array<int,array{id:string,name:string,qty:int,price:int,old:?int,per:int,badge:?string,best:bool,free_ship:bool,desc:string}>
	 */
	public function packages(): array {
		$packages = array(
			array(
				'id'        => 'solo',
				'name'      => __( 'Pojedinačno', 'cosypaw' ),
				'qty'       => 1,
				'price'     => 790,
				'old'       => null,
				'per'       => 790,
				'badge'     => null,
				'best'      => false,
				'free_ship' => false,
				'desc'      => __( 'Jedan omiljeni motiv', 'cosypaw' ),
			),
			array(
				'id'        => 'duo',
				'name'      => __( 'Duo paket', 'cosypaw' ),
				'qty'       => 2,
				'price'     => 1200,
				'old'       => 1580,
				'per'       => 600,
				'badge'     => __( 'Ušteda 380 RSD', 'cosypaw' ),
				'best'      => false,
				'free_ship' => false,
				'desc'      => __( 'Dva motiva po izboru', 'cosypaw' ),
			),
			array(
				'id'        => 'trio',
				'name'      => __( 'Trio paket', 'cosypaw' ),
				'qty'       => 3,
				'price'     => 1600,
				'old'       => 2370,
				'per'       => 534,
				'badge'     => __( 'Najpopularnije', 'cosypaw' ),
				'best'      => true,
				'free_ship' => true,
				'desc'      => __( 'Tri motiva po izboru', 'cosypaw' ),
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
	 * @return array<string,array{id:string,name:string,image:string}>
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
		return array( 'zirafa', 'koala', 'pingvin', 'ananas', 'tost' );
	}

	/**
	 * Featured products for the hero carousel, in order.
	 *
	 * @return array<int,array{id:string,name:string,image:string}>
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
