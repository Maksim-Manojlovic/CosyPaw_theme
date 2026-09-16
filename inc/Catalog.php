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
	 * Towels in the one package the shop sells, and what it costs (RSD).
	 *
	 * Three for the price of two, near enough that gratis_count() will print
	 * the claim: 1.490 is under two singles, so the third towel really is
	 * given away. The pair used to be sold as a Duo alongside a dearer Trio,
	 * which nobody had reason to buy once the Trio was priced as 2+1 — one
	 * package is the whole ladder now, and two towels are simply two singles.
	 *
	 * Seed values, like every other price here: WooCommerce::inject_package_ids()
	 * replaces them with what the shop charges the moment the package is mapped.
	 *
	 * @var int
	 */
	public const PACK_QTY   = 3;
	public const PACK_PRICE = 1490;

	/**
	 * Directory (under the theme root) holding the motif photography.
	 *
	 * @var string
	 */
	private const IMAGE_DIR = 'assets/motifs/';

	/**
	 * Towel motifs.
	 *
	 * Each id resolves to six AVIFs in assets/motifs/ — `<id>.avif` (1086x1448,
	 * the untouched camera original and the seeder's sideload source),
	 * `<id>-lg.avif` (900x1200) and `<id>-md.avif` (600x800) for the hero and
	 * motif cards at 2x-3x and 1x-2x respectively, and three squares cut from
	 * `<id>-sm.avif` (360x360, the hand-picked crop the bundle-builder picker
	 * uses): `<id>-th.avif` (192px, the benefit cards) and `<id>-xs.avif`
	 * (96px, the sprites that fall behind the hero copy on a phone). Serving
	 * the pre-cropped variants keeps the motif grid off the full-size
	 * originals; regenerate the derived ones with
	 * `node tools/build-images.mjs`.
	 *
	 * AVIF is safe for the front end (the images are referenced by URL, and
	 * browser support is universal), but see ProductSeeder::avif_supported() for
	 * the WordPress-side caveat when sideloading these into the media library.
	 *
	 * The price is the seed value only: once a motif is mapped to a real
	 * WooCommerce product, WooCommerce::inject_product_ids() replaces it with
	 * whatever the shop charges.
	 *
	 * `alt` describes what the photograph shows, for a reader who cannot see it,
	 * and `caption` is the display line the shop writes under it. They live here
	 * rather than only in the media library because an attachment takes its meta
	 * with it when it is deleted — which is how the live shop lost the copy for
	 * seven motifs. ProductSeeder writes them into any field still empty and
	 * never over an edit made in wp-admin.
	 *
	 * These are only the motifs the theme was built with. A towel the shop adds
	 * in wp-admin afterwards is appended by WooCommerce::append_shop_motifs()
	 * through the filter below, with its pictures taken from the media library.
	 *
	 * @return array<int,array{id:string,name:string,price:int,alt:string,caption:string,image:string,image_lg:string,image_md:string,image_sm:string,image_th:string,image_xs:string}>
	 */
	public function products(): array {
		$base = get_template_directory_uri() . '/' . self::IMAGE_DIR;
		$out  = array();
		foreach ( $this->seed_motifs() as $m ) {
			$out[] = array(
				'id'       => $m['id'],
				'name'     => $m['name'],
				'price'    => self::UNIT_PRICE,
				'alt'      => $m['alt'],
				'caption'  => $m['caption'],
				'image'    => $base . $m['id'] . '.avif',
				'image_lg' => $base . $m['id'] . '-lg.avif',
				'image_md' => $base . $m['id'] . '-md.avif',
				'image_sm' => $base . $m['id'] . '-sm.avif',
				'image_th' => $base . $m['id'] . '-th.avif',
				'image_xs' => $base . $m['id'] . '-xs.avif',
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
	 * Ids of the motifs this file ships, before any filter adds to them.
	 *
	 * WooCommerce::register_towel() needs these to keep a product added in
	 * wp-admin from claiming an id the theme already uses. products() cannot
	 * answer that: its filter is where the shop's own products are appended.
	 *
	 * @return string[]
	 */
	public function seed_ids(): array {
		return array_column( $this->seed_motifs(), 'id' );
	}

	/**
	 * The motifs photographed for the theme itself, with their copy.
	 *
	 * @return array<int,array{id:string,name:string,alt:string,caption:string}>
	 */
	private function seed_motifs(): array {
		return array(
			array(
				'id'      => 'zirafa',
				'name'    => __( 'Žirafa', 'cosypaw' ),
				'alt'     => __( 'Ručno šiven ukrasni peškirić u obliku žirafe sa roškićima, umotane u zeleno ćebence', 'cosypaw' ),
				'caption' => __( 'Žirafa — najviši gost u kupatilu.', 'cosypaw' ),
			),
			array(
				'id'      => 'koala',
				'name'    => __( 'Koala', 'cosypaw' ),
				'alt'     => __( 'Ručno šiven ukrasni peškirić u obliku sive koale u žutom džemperu', 'cosypaw' ),
				'caption' => __( 'Koala — spava dok se ti umivaš.', 'cosypaw' ),
			),
			array(
				'id'      => 'pingvin',
				'name'    => __( 'Pingvin', 'cosypaw' ),
				'alt'     => __( 'Ručno šiven ukrasni peškirić u obliku plavog pingvina sa sklopljenim očima i belim stopalima', 'cosypaw' ),
				'caption' => __( 'Pingvin — mali frak pored lavaboa.', 'cosypaw' ),
			),
			array(
				'id'      => 'sova',
				'name'    => __( 'Sova', 'cosypaw' ),
				'alt'     => __( 'Ručno šiven ukrasni peškirić u obliku zelenkaste sove sa krupnim očima', 'cosypaw' ),
				'caption' => __( 'Sova — budna i kad ti nisi.', 'cosypaw' ),
			),
			array(
				'id'      => 'panda',
				'name'    => __( 'Panda', 'cosypaw' ),
				'alt'     => __( 'Ručno šiven ukrasni peškirić u obliku pande sa žutim cvetom, na plavoj podlozi', 'cosypaw' ),
				'caption' => __( 'Panda — donosi cvet svako jutro.', 'cosypaw' ),
			),
			array(
				'id'      => 'meda',
				'name'    => __( 'Meda', 'cosypaw' ),
				'alt'     => __( 'Ručno šiven ukrasni peškirić u obliku braon medveda', 'cosypaw' ),
				'caption' => __( 'Meda — zagrljaj na kuki.', 'cosypaw' ),
			),
			array(
				'id'      => 'kapibara',
				'name'    => __( 'Kapibara', 'cosypaw' ),
				'alt'     => __( 'Ručno šiven ukrasni peškirić u obliku kapibare umotane u plavo ćebence', 'cosypaw' ),
				'caption' => __( 'Kapibara — smirena duša tvog kupatila.', 'cosypaw' ),
			),
			array(
				'id'      => 'maca',
				'name'    => __( 'Maca', 'cosypaw' ),
				'alt'     => __( 'Ručno šiven ukrasni peškirić u obliku bele mace sa žutom ogrlicom i zvončićem', 'cosypaw' ),
				'caption' => __( 'Maca — tiho sedi i čeka.', 'cosypaw' ),
			),
			array(
				'id'      => 'kucence',
				'name'    => __( 'Kucence', 'cosypaw' ),
				'alt'     => __( 'Ručno šiven ukrasni peškirić u obliku belog kucenceta sa plavim detaljem', 'cosypaw' ),
				'caption' => __( 'Kucence — verni čuvar kupatila.', 'cosypaw' ),
			),
			array(
				'id'      => 'zeka',
				'name'    => __( 'Zeka', 'cosypaw' ),
				'alt'     => __( 'Ručno šiven ukrasni peškirić u obliku roze-bele zeke sa dugim ušima', 'cosypaw' ),
				'caption' => __( 'Zeka — najmekši stanar kupatila.', 'cosypaw' ),
			),
			array(
				'id'      => 'avokado',
				'name'    => __( 'Avokado', 'cosypaw' ),
				'alt'     => __( 'Ručno šiven ukrasni peškirić u obliku prepolovljenog avokada sa krupnom košticom', 'cosypaw' ),
				'caption' => __( 'Avokado — zeleno i zrelo, bez roka trajanja.', 'cosypaw' ),
			),
			array(
				'id'      => 'ananas',
				'name'    => __( 'Ananas', 'cosypaw' ),
				'alt'     => __( 'Ručno šiven ukrasni peškirić u obliku žutog ananasa sa zelenom krunom', 'cosypaw' ),
				'caption' => __( 'Ananas — leto na kuki, cele godine.', 'cosypaw' ),
			),
			array(
				'id'      => 'tresnja',
				'name'    => __( 'Trešnja', 'cosypaw' ),
				'alt'     => __( 'Ručno šiven ukrasni peškirić sa dve tamnocrvene trešnje i roze mašnom', 'cosypaw' ),
				'caption' => __( 'Trešnja — mašna na vrhu dana.', 'cosypaw' ),
			),
			array(
				'id'      => 'sir',
				'name'    => __( 'Sir', 'cosypaw' ),
				'alt'     => __( 'Ručno šiven ukrasni peškirić u obliku žutog parčeta sira sa nasmejanim licem', 'cosypaw' ),
				'caption' => __( 'Sir — parče koje se uvek smeši.', 'cosypaw' ),
			),
			array(
				'id'      => 'krofna',
				'name'    => __( 'Krofna', 'cosypaw' ),
				'alt'     => __( 'Ručno šiven ukrasni peškirić u obliku krofne sa tirkiznom glazurom i šarenim mrvicama', 'cosypaw' ),
				'caption' => __( 'Krofna — slatkiš bez kalorija.', 'cosypaw' ),
			),
			array(
				'id'      => 'biskvit',
				'name'    => __( 'Biskvit', 'cosypaw' ),
				'alt'     => __( 'Ručno šiven ukrasni peškirić u obliku četvrtastog biskvita sa nasmejanim licem', 'cosypaw' ),
				'caption' => __( 'Biskvit — uz kafu, ali se ne mrvi.', 'cosypaw' ),
			),
			array(
				'id'      => 'keks',
				'name'    => __( 'Čokoladni keks', 'cosypaw' ),
				'alt'     => __( 'Ručno šiven ukrasni peškirić u obliku okruglog keksa sa komadićima čokolade', 'cosypaw' ),
				'caption' => __( 'Čokoladni keks — namigne kad ga uzmeš.', 'cosypaw' ),
			),
			array(
				'id'      => 'tost',
				'name'    => __( 'Tost', 'cosypaw' ),
				'alt'     => __( 'Ručno šiven ukrasni peškirić u obliku parčeta tosta sa nasmejanim licem', 'cosypaw' ),
				'caption' => __( 'Tost — dobro jutro u obliku peškirića.', 'cosypaw' ),
			),
			array(
				'id'      => 'lala',
				'name'    => __( 'Lala', 'cosypaw' ),
				'alt'     => __( 'Ručno šiven ukrasni peškirić sa roze lalom na svetložutoj podlozi', 'cosypaw' ),
				'caption' => __( 'Lala — proleće pored ogledala.', 'cosypaw' ),
			),
			array(
				'id'      => 'list',
				'name'    => __( 'Javorov list', 'cosypaw' ),
				'alt'     => __( 'Ručno šiven ukrasni peškirić u obliku tamnozelenog javorovog lista, sa alkom za kačenje', 'cosypaw' ),
				'caption' => __( 'Javorov list — komadić jeseni pored lavaboa.', 'cosypaw' ),
			),
		);
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
	 * `gratis` is how many towels the saving actually pays for — the "2+1
	 * GRATIS" claim on the card. It is derived, never authored, so it can only
	 * appear while the arithmetic holds; see gratis_count().
	 *
	 * @return array<int,array{id:string,name:string,qty:int,price:int,old:?int,per:int,badge:?string,badge_saving:bool,best:bool,free_ship:bool,gratis:int,desc:string}>
	 */
	public function packages(): array {
		$packages = array(
			array(
				'id'           => 'solo',
				'name'         => __( 'Single', 'cosypaw' ),
				'qty'          => 1,
				'price'        => self::UNIT_PRICE,
				'old'          => null,
				'per'          => self::UNIT_PRICE,
				'badge'        => null,
				'badge_saving' => false,
				'best'         => false,
				'desc'         => __( 'Jedan omiljeni peškirić', 'cosypaw' ),
			),
			// Priced under two towels so the third is genuinely free — the
			// card's "2+1 GRATIS" only renders while that holds, and anything
			// from 1.580 up is enough to lose the claim.
			//
			// The id stays 'duo': it is the key cosypaw_package_map stores the
			// WooCommerce product under, and renaming it would orphan the
			// package a live shop is already selling. Only the offer changed.
			array(
				'id'           => 'duo',
				'name'         => __( '2+1 paket', 'cosypaw' ),
				'qty'          => self::PACK_QTY,
				'price'        => self::PACK_PRICE,
				'old'          => self::UNIT_PRICE * self::PACK_QTY,
				'per'          => (int) round( self::PACK_PRICE / self::PACK_QTY ),
				/* translators: %s: formatted amount saved, e.g. "880 RSD". */
				'badge'        => sprintf( __( 'Ušteda %s', 'cosypaw' ), self::format_price( self::UNIT_PRICE * self::PACK_QTY - self::PACK_PRICE ) ),
				'badge_saving' => true,
				'best'         => true,
				'desc'         => __( 'Tri peškirića po izboru', 'cosypaw' ),
			),
		);

		$free_ship_min = self::free_shipping_min();

		foreach ( $packages as &$package ) {
			$package['gratis'] = self::gratis_count( $package['qty'], $package['price'], self::UNIT_PRICE );

			// Free delivery is won by what the basket costs, not by which
			// bundle it is, so the pill is arithmetic against the threshold.
			// Authored per package, it survived a reprice that moved the
			// package under the bar and kept promising delivery the cart would
			// charge for. WooCommerce::inject_package_ids() recomputes it once
			// the live prices are in.
			$package['free_ship'] = $free_ship_min > 0 && $package['price'] >= $free_ship_min;
		}
		unset( $package );

		/**
		 * Filter the package list.
		 *
		 * @param array $packages The package data.
		 */
		return (array) apply_filters( 'cosypaw_catalog_packages', $packages );
	}

	/**
	 * How many towels in a bundle are covered by its own discount.
	 *
	 * The number behind "2+1 GRATIS": three towels for the price of two is one
	 * free, so `gratis` is the quantity less the whole towels the bundle price
	 * pays for. Rounding is deliberately against the claim — ceil() means a
	 * bundle priced 10 RSD over two towels pays for three, returns zero, and
	 * the card says nothing rather than advertising a towel it does not give
	 * away. A price claim that is 99% true is a false price claim.
	 *
	 * @param int $qty   Towels in the bundle.
	 * @param int $price What the bundle costs.
	 * @param int $unit  What one towel costs on its own.
	 * @return int Towels given free, 0 when the bundle is not that generous.
	 */
	public static function gratis_count( int $qty, int $price, int $unit ): int {
		if ( $qty < 2 || $price < 1 || $unit < 1 ) {
			return 0;
		}

		return max( 0, $qty - (int) ceil( $price / $unit ) );
	}

	/**
	 * Default-selected package id.
	 *
	 * @return string
	 */
	public function default_package(): string {
		return (string) apply_filters( 'cosypaw_catalog_default_package', 'duo' );
	}

	/**
	 * Cart subtotal from which delivery is free, or 0 when there is no offer.
	 *
	 * Delegates to CheckoutSetup, which is what actually configures the
	 * shipping zone — one number behind both the claim and the charge. Guarded
	 * so the Catalog stays usable (tests, a theme loaded without the rest of
	 * inc/) when that class is not present.
	 *
	 * @return int
	 */
	public static function free_shipping_min(): int {
		return class_exists( CheckoutSetup::class ) ? CheckoutSetup::free_shipping_threshold() : 0;
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
	 * @return array<string,array{id:string,name:string,image:string,image_lg:string,image_md:string,image_sm:string,image_th:string,image_xs:string}>
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
	 * @return array<int,array{id:string,name:string,image:string,image_lg:string,image_md:string,image_sm:string,image_th:string,image_xs:string}>
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
