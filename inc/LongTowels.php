<?php
/**
 * LongTowels — the dugi peškiri.
 *
 * A longer towel with an embroidered teddy, a paw and "GOODLUCK" on its woven
 * border, in four colours, each a simple product of its own.
 *
 * Deliberately NOT a towel in the shop's sense: it is not filed under
 * WooCommerce::TOWEL_CATEGORY, so it never becomes a motif, never enters the
 * package builder and never counts towards a 2+1 — a 499 RSD towel counted as
 * one of three 790 RSD ones would hand out the wrong one for free. It lives in
 * its own category at its own price, like the cloths.
 *
 * Creation, storefront rows, translation and the spec list are
 * \Theme\ProductLine's; the photographs ship in assets/long-towels/. The copy
 * describes what the photographs show and nothing else: no size and no fibre
 * content until the shop has them to state.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

namespace Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LongTowels.
 */
final class LongTowels extends ProductLine {

	/**
	 * Bump when a product is added to ITEMS.
	 */
	public const VERSION = 1;

	/**
	 * Option recording which registry version this install has been brought up to.
	 */
	protected const OPTION = 'cosypaw_long_towels_version';

	/**
	 * Option: ITEMS key => WooCommerce product id.
	 */
	public const MAP_OPTION = 'cosypaw_long_towel_map';

	/**
	 * Product category the long towels are filed under.
	 */
	public const CATEGORY = 'dugi-peskiri';

	/**
	 * Its name — a database value, stored in the source language.
	 */
	protected const CATEGORY_NAME = 'Dugi peškiri';

	/**
	 * Launch price (RSD). Written once, at creation; wp-admin owns it after.
	 */
	public const PRICE = 499;

	/**
	 * Theme directory the photographs ship in.
	 */
	protected const IMAGE_DIR = 'assets/long-towels/';

	/**
	 * Group key: all four colours are the one design.
	 */
	public const TEDDY = 'meda';

	/**
	 * Key => product definition. Names, colours and copy are Serbian database
	 * values that also serve as msgids, so the storefront can translate them
	 * while they are unedited.
	 *
	 * @var array<string,array<string,mixed>>
	 */
	public const ITEMS = array(
		'meda-krem'  => array(
			'name'            => 'Dugi peškir Meda, krem',
			'slug'            => 'dugi-peskir-meda-krem',
			'group'           => self::TEDDY,
			'color'           => 'Krem',
			'swatch'          => '#f3ede0',
			'image'           => 'dugi-peskir-meda-krem.webp',
			'alt'             => 'Krem dugi peškir sa vezenim medom, šapicom i natpisom GOODLUCK, okačen na hromirani držač u mermernom kupatilu',
			'short'           => 'Mekani dugi peškir u krem boji, sa vezenim medom, šapicom i natpisom GOODLUCK na borduri. Za lice, ruke i kosu.',
			'seo_title'       => 'Dugi Peškir Meda, Krem | Mekani Peškir za Kupatilo',
			'seo_description' => 'Krem dugi peškir sa vezenim medom i šapicom: mekan i upijajuć, za lice, ruke i kosu. Lep detalj za svako kupatilo. Plaćanje pouzećem!',
		),
		'meda-roze'  => array(
			'name'            => 'Dugi peškir Meda, roze',
			'slug'            => 'dugi-peskir-meda-roze',
			'group'           => self::TEDDY,
			'color'           => 'Roze',
			'swatch'          => '#e8cdc8',
			'image'           => 'dugi-peskir-meda-roze.webp',
			'alt'             => 'Roze dugi peškir sa vezenim medom, šapicom i natpisom GOODLUCK, prebačen preko drvenih merdevina za peškire u kupatilu',
			'short'           => 'Mekani dugi peškir u nežnoj roze boji, sa vezenim medom, šapicom i natpisom GOODLUCK na borduri. Za lice, ruke i kosu.',
			'seo_title'       => 'Dugi Peškir Meda, Roze | Mekani Peškir za Kupatilo',
			'seo_description' => 'Roze dugi peškir sa vezenim medom i šapicom: mekan i upijajuć, za lice, ruke i kosu. Lep detalj za svako kupatilo. Plaćanje pouzećem!',
		),
		'meda-plavi' => array(
			'name'            => 'Dugi peškir Meda, plavi',
			'slug'            => 'dugi-peskir-meda-plavi',
			'group'           => self::TEDDY,
			'color'           => 'Plava',
			'swatch'          => '#8fa6be',
			'image'           => 'dugi-peskir-meda-plavi.webp',
			'alt'             => 'Plavi dugi peškir sa vezenim medom, šapicom i natpisom GOODLUCK, okačen na hromirani držač u mermernom kupatilu',
			'short'           => 'Mekani dugi peškir u nežnoj plavoj boji, sa vezenim medom, šapicom i natpisom GOODLUCK na borduri. Za lice, ruke i kosu.',
			'seo_title'       => 'Dugi Peškir Meda, Plavi | Mekani Peškir za Kupatilo',
			'seo_description' => 'Plavi dugi peškir sa vezenim medom i šapicom: mekan i upijajuć, za lice, ruke i kosu. Lep detalj za svako kupatilo. Plaćanje pouzećem!',
		),
		'meda-bez'   => array(
			'name'            => 'Dugi peškir Meda, bež',
			'slug'            => 'dugi-peskir-meda-bez',
			'group'           => self::TEDDY,
			'color'           => 'Bež',
			'swatch'          => '#cdbba6',
			'image'           => 'dugi-peskir-meda-bez.webp',
			'alt'             => 'Bež dugi peškir sa vezenim medom, šapicom i natpisom GOODLUCK, okačen na držač u mermernom kupatilu',
			'short'           => 'Mekani dugi peškir u toploj bež boji, sa vezenim medom, šapicom i natpisom GOODLUCK na borduri. Za lice, ruke i kosu.',
			'seo_title'       => 'Dugi Peškir Meda, Bež | Mekani Peškir za Kupatilo',
			'seo_description' => 'Bež dugi peškir sa vezenim medom i šapicom: mekan i upijajuć, za lice, ruke i kosu. Lep detalj za svako kupatilo. Plaćanje pouzećem!',
		),
	);

	/**
	 * Long description, shared by every colour.
	 */
	protected const DESCRIPTION = 'Dugi peškir od mekane, upijajuće tkanine, sa vezenim medom, šapicom i natpisom GOODLUCK na tkanoj borduri. Duži je od peškirića za ruke, pa je zgodan i za lice i kosu — na držaču pored lavaboa ili prebačen preko merdevina za peškire. Nežne boje lako se uklope u svako kupatilo, a stiže kao i ostali CosyPaw proizvodi: uz plaćanje pouzećem i dostavu širom Srbije.';

	/**
	 * The spec list under a long towel's summary.
	 *
	 * @param string $key ITEMS key of the product on screen.
	 * @return array<string,string>
	 */
	protected function spec_rows( string $key ): array {
		return array(
			__( 'Boja', 'cosypaw' )    => self::translate( (string) self::ITEMS[ $key ]['color'] ),
			__( 'Detalji', 'cosypaw' ) => __( 'Vezeni meda, šapica i natpis GOODLUCK.', 'cosypaw' ),
			__( 'Namena', 'cosypaw' )  => __( 'Lice, ruke i kosa — za celu porodicu.', 'cosypaw' ),
			__( 'Dostava', 'cosypaw' ) => __( 'Plaćanje pouzećem, isporuka 2–4 dana širom Srbije.', 'cosypaw' ),
		);
	}
}
