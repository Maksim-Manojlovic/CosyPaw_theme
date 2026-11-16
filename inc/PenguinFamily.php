<?php
/**
 * PenguinFamily — the "Porodica pingvina" package: all six penguin colours in
 * one box, at one price.
 *
 * A plain simple product, like the cloth set and the gift bundles: its own
 * page, photographs and Yoast fields. It is not filed under the towel
 * category, so BundlePricing never counts it towards a 2+1; the price is the
 * price of six towels bought as two 3-for-2 packages, and what the box holds
 * is fixed, one of each colour (see PenguinColours::COLOURS).
 *
 * Creation, storefront rows, translation and the spec list are
 * \Theme\ProductLine's; the photographs ship in assets/penguins/. It sits in
 * the gift packages' category so it is found next to them.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

namespace Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PenguinFamily.
 */
final class PenguinFamily extends ProductLine {

	/**
	 * Bump when a product is added to ITEMS.
	 */
	public const VERSION = 1;

	/**
	 * Option recording which registry version this install has been brought up to.
	 */
	protected const OPTION = 'cosypaw_penguin_family_version';

	/**
	 * Option: ITEMS key => WooCommerce product id.
	 */
	public const MAP_OPTION = 'cosypaw_penguin_family_map';

	/**
	 * Product category: the gift packages'. The term is found by its slug, so
	 * the existing one is reused.
	 */
	public const CATEGORY = 'poklon-paketi';

	/**
	 * Its name — used only if the category has to be created.
	 */
	protected const CATEGORY_NAME = 'Poklon paketi';

	/**
	 * Price (RSD) of the box: six towels as two 3-for-2 packages. Written
	 * once, at creation; wp-admin owns it after.
	 */
	public const PRICE = 2980;

	/**
	 * Theme directory the photographs ship in.
	 */
	protected const IMAGE_DIR = 'assets/penguins/';

	/**
	 * The one product.
	 *
	 * @var array<string,array<string,mixed>>
	 */
	public const ITEMS = array(
		'porodica-pingvina' => array(
			'name'            => 'Paket Porodica pingvina',
			'slug'            => 'paket-porodica-pingvina',
			'image'           => 'porodica-pingvina-svih-boja.webp',
			'alt'             => 'Šest peškira u obliku pingvina u tamno plavoj, plavoj, roze, zelenkastoj, braon i sivoj boji, u pletenoj korpi pored drvene dečije ogradice',
			'gallery'         => array( 'porodica-pingvina-pet-novih-boja.webp' => 'Pet peškira u obliku pingvina u zelenkastoj, tamno plavoj, braon, roze i sivoj boji, poređani na bež podlozi sa suvim travama' ),
			'short'           => 'Svih šest pingvina u jednom paketu: plava, tamno plava, braon, roze, siva i tirkizna. Šest mekanih peškira sa alkom za kačenje, po jedan u svakoj boji.',
			'seo_title'       => 'Porodica Pingvina | Paket 6 Peškira u Obliku Pingvina',
			'seo_description' => 'Paket od šest peškira u obliku pingvina, po jedan u svakoj boji: plava, tamno plava, braon, roze, siva i tirkizna. Plaćanje pouzećem!',
			'price'           => self::PRICE,
		),
	);

	/**
	 * Long description.
	 */
	protected const DESCRIPTION = 'Porodica pingvina je šest mekanih peškira u obliku pingvina, po jedan u svakoj boji: plava, tamno plava, braon, roze, siva i tirkizna. Svaki pingvin ima vezeno lice sa žutim kljunom i belim stopalima, i alku za kačenje pored lavaboa, kade ili kuhinje. Odličan su poklon za dečiju sobu, za porodicu i za svakoga ko voli da u kupatilu ima malo osmeha — jer svaki član porodice dobija svog pingvina.';

	/**
	 * The spec list under the package's summary.
	 *
	 * @param string $key ITEMS key of the product on screen.
	 * @return array<string,string>
	 */
	protected function spec_rows( string $key ): array {
		unset( $key );

		return array(
			__( 'U paketu', 'cosypaw' )   => __( '6 pingvina: plava, tamno plava, braon, roze, siva i tirkizna.', 'cosypaw' ),
			__( 'Materijal', 'cosypaw' )  => __( 'Plišana mikrofibra — mekana, lagana i jako upijajuća.', 'cosypaw' ),
			__( 'Dimenzije', 'cosypaw' )  => __( 'Prosečno 22 cm x 18 cm — tačna veličina zavisi od oblika i motiva.', 'cosypaw' ),
			__( 'Kačenje', 'cosypaw' )    => __( 'Alka za kačenje, da peškir uvek stoji na svom mestu.', 'cosypaw' ),
			__( 'Održavanje', 'cosypaw' ) => __( 'Mašinsko pranje na 40°C, bez omekšivača. Suši se brzo i ne gubi oblik.', 'cosypaw' ),
			__( 'Dostava', 'cosypaw' )    => __( 'Plaćanje pouzećem, isporuka 2–4 dana širom Srbije.', 'cosypaw' ),
		);
	}
}
