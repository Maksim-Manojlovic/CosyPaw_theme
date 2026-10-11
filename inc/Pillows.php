<?php
/**
 * Pillows — the Cuddle Puff jastuci.
 *
 * Plush pillows in three shapes. The round, wavy-edged "Kružić" (five colours)
 * and the square "Kockasti" (three) have embroidered eyes, a little yellow
 * beak and a felt leaf; the "Cvetić" (four) is a flower instead — petals around
 * a centre in another colour, no face — so it carries its own description and
 * spec rows. Each colour is a simple product of its own, so it has its own
 * page, photograph, alt text and Yoast fields; the storefront folds the
 * colours of one shape back into a single card with colour swatches
 * (ProductLine::groups()).
 *
 * Sold on their own at their own price, outside the towel packages, in their
 * own category. Creation, storefront rows, translation and the spec list are
 * \Theme\ProductLine's; the photographs ship in assets/pillows/. Each shape's
 * group photograph is the second picture in the gallery of every colour of
 * that shape, imported once.
 *
 * The copy describes what the photographs show and nothing else: no size and
 * no fibre content until the shop has them to state.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

namespace Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pillows.
 */
final class Pillows extends ProductLine {

	/**
	 * Bump when a product is added to ITEMS.
	 */
	public const VERSION = 2;

	/**
	 * Option recording which registry version this install has been brought up to.
	 */
	protected const OPTION = 'cosypaw_pillows_version';

	/**
	 * Option: ITEMS key => WooCommerce product id.
	 */
	public const MAP_OPTION = 'cosypaw_pillow_map';

	/**
	 * Product category the pillows are filed under.
	 */
	public const CATEGORY = 'cuddle-puff-jastuci';

	/**
	 * Its name — a database value, stored in the source language.
	 */
	protected const CATEGORY_NAME = 'Cuddle Puff jastuci';

	/**
	 * Launch price (RSD) of one pillow, any shape. Written once, at
	 * creation; wp-admin owns it after.
	 */
	public const PRICE = 1599;

	/**
	 * Theme directory the photographs ship in.
	 */
	protected const IMAGE_DIR = 'assets/pillows/';

	/**
	 * Group keys.
	 */
	public const ROUND  = 'kruzic';
	public const SQUARE = 'kockasti';
	public const FLOWER = 'cvetic';

	/**
	 * The shapes' group photographs, file => alt.
	 */
	private const ROUND_FAMILY  = array( 'cuddle-puff-jastuci-kruzici-svih-boja.webp' => 'Šest Cuddle Puff jastuka u obliku kružića, u braon, sivoj, žutoj, roze i zelenoj boji, poređani na travi' );
	private const SQUARE_FAMILY = array( 'cuddle-puff-jastuci-kockasti-svih-boja.webp' => 'Tri kockasta Cuddle Puff jastuka, roze, braon i sivi, na krem sofi' );
	private const FLOWER_FAMILY = array( 'cuddle-puff-jastuci-cvetici-svih-boja.webp' => 'Šest Cuddle Puff jastuka u obliku cvetića, u mint zelenoj, žutoj, sivoj i roze boji, poređani na travi' );

	/**
	 * Long description of the flowers, which have none of what DESCRIPTION
	 * lists: no eyes, beak, leaf, trimmed edge or hanging loop.
	 */
	private const FLOWER_DESCRIPTION = 'Cuddle Puff jastuk Cvetić je punašan, mekan plišani jastuk u obliku cveta, sa prošivenim laticama i okruglom sredinom u drugoj boji. Jednako lepo stoji kao jastuk za stolicu ili fotelju, kao ukrasni jastuk na sofi ili krevetu i kao plišani drug u dečijoj sobi — i unosi boju i osmeh u svaki kutak doma.';

	/**
	 * Key => product definition. Names, colours and copy are Serbian database
	 * values that also serve as msgids, so the storefront can translate them
	 * while they are unedited.
	 *
	 * @var array<string,array<string,mixed>>
	 */
	public const ITEMS = array(
		'kruzic-braon'    => array(
			'name'            => 'Cuddle Puff jastuk Kružić, braon',
			'slug'            => 'cuddle-puff-jastuk-kruzic-braon',
			'group'           => self::ROUND,
			'color'           => 'Braon',
			'swatch'          => '#b08a62',
			'image'           => 'cuddle-puff-jastuk-kruzic-braon.webp',
			'alt'             => 'Braon Cuddle Puff jastuk u obliku kružića sa vezenim okicama, žutim kljunićem i zelenim listićem, na travi',
			'gallery'         => self::ROUND_FAMILY,
			'short'           => 'Mekani plišani jastuk u obliku kružića, u toploj braon boji, sa vezenim okicama, kljunićem i zelenim listićem. Za stolicu, fotelju, sofu ili dečiju sobu.',
			'seo_title'       => 'Cuddle Puff Jastuk Kružić, Braon | Plišani Jastuk za Sedenje',
			'seo_description' => 'Braon Cuddle Puff jastuk u obliku kružića, sa vezenim okicama i listićem. Mekan jastuk za stolicu, fotelju ili dečiju sobu. Plaćanje pouzećem!',
		),
		'kruzic-sivi'     => array(
			'name'            => 'Cuddle Puff jastuk Kružić, sivi',
			'slug'            => 'cuddle-puff-jastuk-kruzic-sivi',
			'group'           => self::ROUND,
			'color'           => 'Siva',
			'swatch'          => '#9b9a97',
			'image'           => 'cuddle-puff-jastuk-kruzic-sivi.webp',
			'alt'             => 'Sivi Cuddle Puff jastuk u obliku kružića sa vezenim okicama, žutim kljunićem i zelenim listićem, na travi',
			'gallery'         => self::ROUND_FAMILY,
			'short'           => 'Mekani plišani jastuk u obliku kružića, u nežnoj sivoj boji, sa vezenim okicama, kljunićem i zelenim listićem. Za stolicu, fotelju, sofu ili dečiju sobu.',
			'seo_title'       => 'Cuddle Puff Jastuk Kružić, Sivi | Plišani Jastuk za Sedenje',
			'seo_description' => 'Sivi Cuddle Puff jastuk u obliku kružića, sa vezenim okicama i listićem. Mekan jastuk za stolicu, fotelju ili dečiju sobu. Plaćanje pouzećem!',
		),
		'kruzic-zuti'     => array(
			'name'            => 'Cuddle Puff jastuk Kružić, žuti',
			'slug'            => 'cuddle-puff-jastuk-kruzic-zuti',
			'group'           => self::ROUND,
			'color'           => 'Žuta',
			'swatch'          => '#f0b417',
			'image'           => 'cuddle-puff-jastuk-kruzic-zuti.webp',
			'alt'             => 'Žuti Cuddle Puff jastuk u obliku kružića sa vezenim okicama, kljunićem i zelenim listićem, na travi',
			'gallery'         => self::ROUND_FAMILY,
			'short'           => 'Mekani plišani jastuk u obliku kružića, u veseloj žutoj boji, sa vezenim okicama, kljunićem i zelenim listićem. Za stolicu, fotelju, sofu ili dečiju sobu.',
			'seo_title'       => 'Cuddle Puff Jastuk Kružić, Žuti | Plišani Jastuk za Sedenje',
			'seo_description' => 'Žuti Cuddle Puff jastuk u obliku kružića, sa vezenim okicama i listićem. Mekan jastuk za stolicu, fotelju ili dečiju sobu. Plaćanje pouzećem!',
		),
		'kruzic-zeleni'   => array(
			'name'            => 'Cuddle Puff jastuk Kružić, zeleni',
			'slug'            => 'cuddle-puff-jastuk-kruzic-zeleni',
			'group'           => self::ROUND,
			'color'           => 'Zelena',
			'swatch'          => '#a8bc55',
			'image'           => 'cuddle-puff-jastuk-kruzic-zeleni.webp',
			'alt'             => 'Svetlozeleni Cuddle Puff jastuk u obliku kružića sa vezenim okicama, žutim kljunićem i listićem, na travi',
			'gallery'         => self::ROUND_FAMILY,
			'short'           => 'Mekani plišani jastuk u obliku kružića, u svetloj zelenoj boji, sa vezenim okicama, kljunićem i listićem. Za stolicu, fotelju, sofu ili dečiju sobu.',
			'seo_title'       => 'Cuddle Puff Jastuk Kružić, Zeleni | Plišani Jastuk za Sedenje',
			'seo_description' => 'Zeleni Cuddle Puff jastuk u obliku kružića, sa vezenim okicama i listićem. Mekan jastuk za stolicu, fotelju ili dečiju sobu. Plaćanje pouzećem!',
		),
		'kruzic-roze'     => array(
			'name'            => 'Cuddle Puff jastuk Kružić, roze',
			'slug'            => 'cuddle-puff-jastuk-kruzic-roze',
			'group'           => self::ROUND,
			'color'           => 'Roze',
			'swatch'          => '#e7a3a6',
			'image'           => 'cuddle-puff-jastuk-kruzic-roze.webp',
			'alt'             => 'Roze Cuddle Puff jastuk u obliku kružića sa vezenim okicama, žutim kljunićem i zelenim listićem, na travi',
			'gallery'         => self::ROUND_FAMILY,
			'short'           => 'Mekani plišani jastuk u obliku kružića, u nežnoj roze boji, sa vezenim okicama, kljunićem i zelenim listićem. Za stolicu, fotelju, sofu ili dečiju sobu.',
			'seo_title'       => 'Cuddle Puff Jastuk Kružić, Roze | Plišani Jastuk za Sedenje',
			'seo_description' => 'Roze Cuddle Puff jastuk u obliku kružića, sa vezenim okicama i listićem. Mekan jastuk za stolicu, fotelju ili dečiju sobu. Plaćanje pouzećem!',
		),
		'kockasti-roze'   => array(
			'name'            => 'Cuddle Puff jastuk Kockasti, roze',
			'slug'            => 'cuddle-puff-jastuk-kockasti-roze',
			'group'           => self::SQUARE,
			'color'           => 'Roze',
			'swatch'          => '#d690a4',
			'image'           => 'cuddle-puff-jastuk-kockasti-roze.webp',
			'alt'             => 'Roze kockasti Cuddle Puff jastuk sa talasastim ivicama, vezenim okicama i zelenim listićem, na krem fotelji',
			'gallery'         => self::SQUARE_FAMILY,
			'short'           => 'Mekani plišani kockasti jastuk sa talasastim ivicama, u roze boji, sa vezenim okicama, kljunićem i zelenim listićem. Jastuk za stolicu, fotelju ili sofu.',
			'seo_title'       => 'Cuddle Puff Kockasti Jastuk, Roze | Jastuk za Stolicu i Sofu',
			'seo_description' => 'Roze kockasti Cuddle Puff jastuk sa vezenim okicama i listićem. Mekan ukrasni jastuk za stolicu, fotelju ili sofu. Plaćanje pouzećem!',
		),
		'kockasti-braon'  => array(
			'name'            => 'Cuddle Puff jastuk Kockasti, braon',
			'slug'            => 'cuddle-puff-jastuk-kockasti-braon',
			'group'           => self::SQUARE,
			'color'           => 'Braon',
			'swatch'          => '#b89a7c',
			'image'           => 'cuddle-puff-jastuk-kockasti-braon.webp',
			'alt'             => 'Svetlobraon kockasti Cuddle Puff jastuk sa talasastim ivicama, vezenim okicama i zelenim listićem, na krem fotelji',
			'gallery'         => self::SQUARE_FAMILY,
			'short'           => 'Mekani plišani kockasti jastuk sa talasastim ivicama, u svetloj braon boji, sa vezenim okicama, kljunićem i zelenim listićem. Jastuk za stolicu, fotelju ili sofu.',
			'seo_title'       => 'Cuddle Puff Kockasti Jastuk, Braon | Jastuk za Stolicu i Sofu',
			'seo_description' => 'Braon kockasti Cuddle Puff jastuk sa vezenim okicama i listićem. Mekan ukrasni jastuk za stolicu, fotelju ili sofu. Plaćanje pouzećem!',
		),
		'kockasti-sivi'   => array(
			'name'            => 'Cuddle Puff jastuk Kockasti, sivi',
			'slug'            => 'cuddle-puff-jastuk-kockasti-sivi',
			'group'           => self::SQUARE,
			'color'           => 'Siva',
			'swatch'          => '#9a9a9a',
			'image'           => 'cuddle-puff-jastuk-kockasti-sivi.webp',
			'alt'             => 'Sivi kockasti Cuddle Puff jastuk sa talasastim ivicama, vezenim okicama i zelenim listićem, na krem fotelji',
			'gallery'         => self::SQUARE_FAMILY,
			'short'           => 'Mekani plišani kockasti jastuk sa talasastim ivicama, u sivoj boji, sa vezenim okicama, kljunićem i zelenim listićem. Jastuk za stolicu, fotelju ili sofu.',
			'seo_title'       => 'Cuddle Puff Kockasti Jastuk, Sivi | Jastuk za Stolicu i Sofu',
			'seo_description' => 'Sivi kockasti Cuddle Puff jastuk sa vezenim okicama i listićem. Mekan ukrasni jastuk za stolicu, fotelju ili sofu. Plaćanje pouzećem!',
		),
		'cvetic-zuti'     => array(
			'name'            => 'Cuddle Puff jastuk Cvetić, žuti',
			'slug'            => 'cuddle-puff-jastuk-cvetic-zuti',
			'group'           => self::FLOWER,
			'color'           => 'Žuta',
			'swatch'          => '#efd76a',
			'image'           => 'cuddle-puff-jastuk-cvetic-zuti.webp',
			'alt'             => 'Žuti Cuddle Puff jastuk u obliku cvetića sa lila sredinom, na travi',
			'gallery'         => self::FLOWER_FAMILY,
			'short'           => 'Mekani plišani jastuk u obliku cvetića, u nežnoj žutoj boji, sa zaobljenim laticama i lila sredinom. Za stolicu, fotelju, sofu ili dečiju sobu.',
			'description'     => self::FLOWER_DESCRIPTION,
			'seo_title'       => 'Cuddle Puff Jastuk Cvetić, Žuti | Jastuk u Obliku Cveta',
			'seo_description' => 'Žuti Cuddle Puff jastuk u obliku cveta, sa lila sredinom. Mekan plišani jastuk za stolicu, fotelju ili dečiju sobu. Plaćanje pouzećem!',
		),
		'cvetic-roze'     => array(
			'name'            => 'Cuddle Puff jastuk Cvetić, roze',
			'slug'            => 'cuddle-puff-jastuk-cvetic-roze',
			'group'           => self::FLOWER,
			'color'           => 'Roze',
			'swatch'          => '#de9a93',
			'image'           => 'cuddle-puff-jastuk-cvetic-roze.webp',
			'alt'             => 'Roze Cuddle Puff jastuk u obliku cvetića od rebrastog pliša, sa krem sredinom, na travi',
			'gallery'         => self::FLOWER_FAMILY,
			'short'           => 'Mekani jastuk u obliku cvetića od rebrastog pliša, u toploj roze boji, sa zaobljenim laticama i krem sredinom. Za stolicu, fotelju, sofu ili dečiju sobu.',
			'description'     => self::FLOWER_DESCRIPTION,
			'seo_title'       => 'Cuddle Puff Jastuk Cvetić, Roze | Jastuk u Obliku Cveta',
			'seo_description' => 'Roze Cuddle Puff jastuk u obliku cveta, od rebrastog pliša, sa krem sredinom. Mekan jastuk za stolicu, fotelju ili dečiju sobu. Plaćanje pouzećem!',
		),
		'cvetic-zeleni'   => array(
			'name'            => 'Cuddle Puff jastuk Cvetić, zeleni',
			'slug'            => 'cuddle-puff-jastuk-cvetic-zeleni',
			'group'           => self::FLOWER,
			'color'           => 'Zelena',
			'swatch'          => '#a9e6c1',
			'image'           => 'cuddle-puff-jastuk-cvetic-zeleni.webp',
			'alt'             => 'Mint zeleni Cuddle Puff jastuk u obliku cvetića sa žutom sredinom, na travi',
			'gallery'         => self::FLOWER_FAMILY,
			'short'           => 'Mekani plišani jastuk u obliku cvetića, u nežnoj mint zelenoj boji, sa izduženim laticama i žutom sredinom. Za stolicu, fotelju, sofu ili dečiju sobu.',
			'description'     => self::FLOWER_DESCRIPTION,
			'seo_title'       => 'Cuddle Puff Jastuk Cvetić, Zeleni | Jastuk u Obliku Cveta',
			'seo_description' => 'Mint zeleni Cuddle Puff jastuk u obliku cveta, sa žutom sredinom. Mekan plišani jastuk za stolicu, fotelju ili dečiju sobu. Plaćanje pouzećem!',
		),
		'cvetic-sivi'     => array(
			'name'            => 'Cuddle Puff jastuk Cvetić, sivi',
			'slug'            => 'cuddle-puff-jastuk-cvetic-sivi',
			'group'           => self::FLOWER,
			'color'           => 'Siva',
			'swatch'          => '#b3b5b1',
			'image'           => 'cuddle-puff-jastuk-cvetic-sivi.webp',
			'alt'             => 'Sivi Cuddle Puff jastuk u obliku cvetića sa belom sredinom, na travi',
			'gallery'         => self::FLOWER_FAMILY,
			'short'           => 'Mekani plišani jastuk u obliku cvetića, u nežnoj sivoj boji, sa izduženim laticama i belom sredinom. Za stolicu, fotelju, sofu ili dečiju sobu.',
			'description'     => self::FLOWER_DESCRIPTION,
			'seo_title'       => 'Cuddle Puff Jastuk Cvetić, Sivi | Jastuk u Obliku Cveta',
			'seo_description' => 'Sivi Cuddle Puff jastuk u obliku cveta, sa belom sredinom. Mekan plišani jastuk za stolicu, fotelju ili dečiju sobu. Plaćanje pouzećem!',
		),
	);

	/**
	 * Long description, shared by every pillow that does not bring its own.
	 */
	protected const DESCRIPTION = 'Cuddle Puff jastuci su punašni, mekani jastuci od rebraste plišane tkanine, sa vezenim okicama, žutim kljunićem i plišanim zelenim listićem na vrhu. Talasaste ivice obrubljene su svetlom, čupavom tkaninom, a sa strane je omčica za kačenje. Jednako lepo stoje kao jastuk za stolicu ili fotelju, kao ukrasni jastuk na sofi i kao plišani drug u dečijoj sobi — i unose osmeh u svaki kutak doma.';

	/**
	 * The spec list under a pillow's summary.
	 *
	 * @param string $key ITEMS key of the product on screen.
	 * @return array<string,string>
	 */
	protected function spec_rows( string $key ): array {
		$item = self::ITEMS[ $key ];

		return array(
			__( 'Oblik', 'cosypaw' )   => match ( $item['group'] ) {
				self::SQUARE => __( 'Kockasti, sa talasastim ivicama.', 'cosypaw' ),
				self::FLOWER => __( 'Cvetić, sa punašnim laticama.', 'cosypaw' ),
				default      => __( 'Kružić, okrugao sa talasastim ivicama.', 'cosypaw' ),
			},
			__( 'Boja', 'cosypaw' )    => self::translate( (string) $item['color'] ),
			__( 'Detalji', 'cosypaw' ) => self::FLOWER === $item['group']
				? __( 'Prošivene latice i sredina u drugoj boji.', 'cosypaw' )
				: __( 'Vezene okice i kljunić, plišani listić, omčica za kačenje.', 'cosypaw' ),
			__( 'Namena', 'cosypaw' )  => __( 'Stolica, fotelja, sofa i dečija soba.', 'cosypaw' ),
			__( 'Dostava', 'cosypaw' ) => __( 'Plaćanje pouzećem, isporuka 2–4 dana širom Srbije.', 'cosypaw' ),
		);
	}
}
