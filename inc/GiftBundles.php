<?php
/**
 * GiftBundles — the poklon paketi: fixed combinations of the shop's products,
 * each sold as one product at one price.
 *
 * A bundle is a plain simple product rather than a cart rule, like the cloth
 * set: it has its own page, photo and Yoast fields, and the free-delivery
 * threshold sees the price it is really sold at. `contains` below is the
 * suggested packing list, one slot per part, and each slot can be swapped
 * like for like on the product page (slots(), \Theme\GiftBundlePicker): a
 * pillow for another colour of the same shape, a towel for any other shape,
 * a long towel for another colour — only ever for something on sale at the
 * same price, so the bundle's price and its saving hold whatever is chosen.
 * The order line carries what was chosen, so the shop still knows what to
 * pack from it alone.
 *
 * What a bundle saves is never a number typed in. It is worked out against
 * the cheapest way the shop itself would sell the same things today: towels
 * through BundlePricing::plan() (so three towels are the 2+1 package, not
 * three singles), the cloths at the set's price, everything else at its live
 * price. A bundle with any part off sale, or no longer cheaper than its parts,
 * is not offered at all — the card never shows a saving the cart would not
 * give.
 *
 * Not filed under the towel category, so BundlePricing never counts a bundle
 * towards a 2+1 package. Creation, translation and the spec list are
 * \Theme\ProductLine's; the photographs are collages of the contents, built
 * by tools/build-bundle-collages.py into assets/bundles/.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

namespace Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GiftBundles.
 */
final class GiftBundles extends ProductLine {

	/**
	 * Bump when a bundle is added to ITEMS.
	 */
	public const VERSION = 2;

	/**
	 * Option recording which registry version this install has been brought up to.
	 */
	protected const OPTION = 'cosypaw_gift_bundles_version';

	/**
	 * Option: ITEMS key => WooCommerce product id.
	 */
	public const MAP_OPTION = 'cosypaw_gift_bundle_map';

	/**
	 * Product category the bundles are filed under.
	 */
	public const CATEGORY = 'poklon-paketi';

	/**
	 * Its name — a database value, stored in the source language.
	 */
	protected const CATEGORY_NAME = 'Poklon paketi';

	/**
	 * Fallback launch price; every bundle names its own.
	 */
	public const PRICE = 990;

	/**
	 * Theme directory the photographs ship in.
	 */
	protected const IMAGE_DIR = 'assets/bundles/';

	/**
	 * Occasions, for grouping on the bundle page. Key => Serbian msgid.
	 */
	public const OCCASIONS = array(
		'deca'     => 'Za bebu i decu',
		'dom'      => 'Za dom i dvoje',
		'paznja'   => 'Mali pokloni',
		'praznici' => 'Za praznike',
	);

	/**
	 * Component lines.
	 */
	private const TOWEL  = 'towel';
	private const LONG   = 'long';
	private const PILLOW = 'pillow';
	private const CLOTHS = 'cloths';

	/**
	 * Key => bundle. Besides ProductLine's keys:
	 *  - occasion: OCCASIONS key.
	 *  - includes: the contents in words (a Serbian msgid), for the card and
	 *    the product page.
	 *  - contains: the suggested packing list, [line, key] pairs, one per slot.
	 *    A towel is its motif id (Catalog), the others their own line's ITEMS
	 *    key; the cloths are the set of both. Cards and offers() describe and
	 *    price exactly this; the product page lets each slot be swapped.
	 *
	 * @var array<string,array<string,mixed>>
	 */
	public const ITEMS = array(
		'dobrodoslica-za-bebu' => array(
			'name'            => 'Poklon paket Dobrodošlica za bebu',
			'slug'            => 'poklon-paket-dobrodoslica-za-bebu',
			'occasion'        => 'deca',
			'price'           => 2790,
			'image'           => 'poklon-paket-dobrodoslica-za-bebu.webp',
			'alt'             => 'Poklon paket za bebu: roze Cuddle Puff jastuk u obliku kružića i dečiji peškiri u obliku zeke, mede i pande',
			'includes'        => '3 dečija peškira (Zeka, Meda, Panda) i roze Cuddle Puff jastuk Kružić',
			'short'           => 'Poklon za rođenje, krštenje ili baby shower: tri mekana dečija peškira u obliku životinja sa alkom za kačenje i roze Cuddle Puff jastuk za dečiju sobu.',
			'seo_title'       => 'Poklon Paket za Bebu | Peškiri i Jastuk za Rođenje i Krštenje',
			'seo_description' => 'Poklon za bebu: tri dečija peškira u obliku životinja i mekani Cuddle Puff jastuk. Idealno za rođenje, krštenje i baby shower. Besplatna dostava!',
			'contains'        => array( array( self::TOWEL, 'zeka' ), array( self::TOWEL, 'meda' ), array( self::TOWEL, 'panda' ), array( self::PILLOW, 'kruzic-roze' ) ),
		),
		'kutak-za-decju-sobu'  => array(
			'name'            => 'Poklon paket Kutak za dečiju sobu',
			'slug'            => 'poklon-paket-kutak-za-decju-sobu',
			'occasion'        => 'deca',
			'price'           => 3290,
			'image'           => 'poklon-paket-kutak-za-decju-sobu.webp',
			'alt'             => 'Poklon paket za dečiju sobu: žuti Cuddle Puff jastuk Kružić, sivi kockasti jastuk i dečiji peškir u obliku sove',
			'includes'        => 'Cuddle Puff jastuk Kružić, žuti, Cuddle Puff jastuk Kockasti, sivi, i dečiji peškir Sova',
			'short'           => 'Dva Cuddle Puff jastuka sa okicama — žuti kružić i sivi kockasti — i dečiji peškir Sova sa alkom za kačenje. Za uređenje dečije sobe ili rođendanski poklon.',
			'seo_title'       => 'Poklon Paket za Dečiju Sobu | Jastuci i Peškir za Decu',
			'seo_description' => 'Dva mekana Cuddle Puff jastuka i dečiji peškir u obliku sove — poklon za rođendan ili novu dečiju sobu. Plaćanje pouzećem, besplatna dostava!',
			'contains'        => array( array( self::PILLOW, 'kruzic-zuti' ), array( self::PILLOW, 'kockasti-sivi' ), array( self::TOWEL, 'sova' ) ),
		),
		'za-vrtic'             => array(
			'name'            => 'Poklon paket Za vrtić',
			'slug'            => 'poklon-paket-za-vrtic',
			'occasion'        => 'deca',
			'price'           => 2090,
			'image'           => 'poklon-paket-za-vrtic.webp',
			'alt'             => 'Četiri dečija peškira sa alkom za kačenje, u obliku kuce, mace, pingvina i koale',
			'includes'        => '4 dečija peškira sa alkom (Kucence, Maca, Pingvin, Koala)',
			'short'           => 'Četiri dečija peškira sa alkom za kačenje: dva za kuku u vrtiću, dva za kuću. Dete svoj peškir prepozna na prvi pogled, a alka ga drži na mestu.',
			'seo_title'       => 'Peškir za Vrtić | Paket od 4 Dečija Peškira sa Alkom',
			'seo_description' => 'Četiri dečija peškira sa alkom za kačenje — dva za vrtić, dva za kuću. Dete svoj peškir lako prepozna. Plaćanje pouzećem, besplatna dostava!',
			'contains'        => array( array( self::TOWEL, 'kucence' ), array( self::TOWEL, 'maca' ), array( self::TOWEL, 'pingvin' ), array( self::TOWEL, 'koala' ) ),
		),
		'avokado-ljubav'       => array(
			'name'            => 'Poklon paket Avokado ljubav',
			'slug'            => 'poklon-paket-avokado-ljubav',
			'occasion'        => 'paznja',
			'price'           => 990,
			'image'           => 'poklon-paket-avokado-ljubav.webp',
			'alt'             => 'Dečiji peškir u obliku avokada i dve magične krpice od mikrofibera sa vezenim avokadom, limeta i žalfija',
			'includes'        => 'dečiji peškir Avokado i obe magične krpice Avokado (limeta i žalfija)',
			'short'           => 'Za ljubitelje avokada: peškir u obliku prepolovljenog avokada i obe magične krpice sa vezenim avokadom. Mali poklon koji se koristi svaki dan.',
			'seo_title'       => 'Poklon Paket Avokado | Peškir i Magične Krpice',
			'seo_description' => 'Peškir u obliku avokada i dve magične krpice sa vezenim avokadom — mali, praktičan poklon za ljubitelje avokada. Plaćanje pouzećem!',
			'contains'        => array( array( self::TOWEL, 'avokado' ), array( self::CLOTHS, Cloths::SET ) ),
		),
		'dorucak-u-kuhinji'    => array(
			'name'            => 'Poklon paket Doručak u kuhinji',
			'slug'            => 'poklon-paket-dorucak-u-kuhinji',
			'occasion'        => 'paznja',
			'price'           => 1690,
			'image'           => 'poklon-paket-dorucak-u-kuhinji.webp',
			'alt'             => 'Peškiri u obliku tosta, čokoladnog keksa i krofne i dve magične krpice sa vezenim avokadom',
			'includes'        => '3 peškira (Tost, Čokoladni keks, Krofna) i obe magične krpice Avokado',
			'short'           => 'Doručak koji se ne jede: peškiri u obliku tosta, keksa i krofne za ruke u kuhinji i obe magične krpice za radne površine. Šaljiv i praktičan poklon.',
			'seo_title'       => 'Poklon Paket za Kuhinju | Peškiri Tost, Keks i Krofna',
			'seo_description' => 'Peškiri u obliku tosta, keksa i krofne i dve magične krpice — šaljiv i praktičan poklon za kuhinju, kolegu ili domaćicu. Plaćanje pouzećem!',
			'contains'        => array( array( self::TOWEL, 'tost' ), array( self::TOWEL, 'keks' ), array( self::TOWEL, 'krofna' ), array( self::CLOTHS, Cloths::SET ) ),
		),
		'kupatilo-za-porodicu' => array(
			'name'            => 'Poklon paket Kupatilo za celu porodicu',
			'slug'            => 'poklon-paket-kupatilo-za-celu-porodicu',
			'occasion'        => 'dom',
			'price'           => 2190,
			'image'           => 'poklon-paket-kupatilo-za-celu-porodicu.webp',
			'alt'             => 'Krem i plavi peškir sa vezenim medom i dečiji peškiri u obliku žirafe i kapibare',
			'includes'        => '2 peškira Meda (krem i plavi) i 2 dečija peškira (Žirafa, Kapibara)',
			'short'           => 'Peškiri za celu porodicu: dva peškira sa vezenim medom za odrasle i dva dečija peškira sa alkom za mališane. Za novo ili osveženo kupatilo.',
			'seo_title'       => 'Peškiri za Celu Porodicu | Peškiri za Odrasle i Decu u Paketu',
			'seo_description' => 'Dva peškira sa vezenim medom i dva dečija peškira u obliku životinja — kupatilo za celu porodicu u jednom paketu. Besplatna dostava!',
			'contains'        => array( array( self::LONG, 'meda-krem' ), array( self::LONG, 'meda-plavi' ), array( self::TOWEL, 'zirafa' ), array( self::TOWEL, 'kapibara' ) ),
		),
		'novi-dom'             => array(
			'name'            => 'Poklon paket Novi dom',
			'slug'            => 'poklon-paket-novi-dom',
			'occasion'        => 'dom',
			'price'           => 2490,
			'image'           => 'poklon-paket-novi-dom.webp',
			'alt'             => 'Braon kockasti Cuddle Puff jastuk, krem i bež peškir sa vezenim medom i dve magične krpice sa avokadom',
			'includes'        => 'Cuddle Puff jastuk Kockasti, braon, 2 peškira Meda (krem i bež) i obe magične krpice Avokado',
			'short'           => 'Poklon za useljenje: mekani kockasti jastuk za sofu, dva peškira za kupatilo i dve magične krpice za kuhinju — po nešto za svaku sobu novog doma.',
			'seo_title'       => 'Poklon za Useljenje | Paket Jastuk, Peškiri i Krpice',
			'seo_description' => 'Poklon za useljenje: Cuddle Puff jastuk, dva peškira i dve magične krpice — po nešto za svaku sobu novog doma. Besplatna dostava!',
			'contains'        => array( array( self::PILLOW, 'kockasti-braon' ), array( self::LONG, 'meda-krem' ), array( self::LONG, 'meda-bez' ), array( self::CLOTHS, Cloths::SET ) ),
		),
		'za-dvoje'             => array(
			'name'            => 'Poklon paket Za dvoje',
			'slug'            => 'poklon-paket-za-dvoje',
			'occasion'        => 'dom',
			'price'           => 3490,
			'image'           => 'poklon-paket-za-dvoje.webp',
			'alt'             => 'Roze i sivi kockasti Cuddle Puff jastuk i roze i plavi peškir sa vezenim medom',
			'includes'        => '2 Cuddle Puff jastuka Kockasti (roze i sivi) i 2 peškira Meda (roze i plavi)',
			'short'           => 'Za njega i za nju: dva kockasta Cuddle Puff jastuka i dva peškira sa vezenim medom, u paru boja. Poklon za mladence, godišnjicu ili zajednički stan.',
			'seo_title'       => 'Poklon za Mladence i Parove | Jastuci i Peškiri za Dvoje',
			'seo_description' => 'Dva Cuddle Puff jastuka i dva peškira u paru boja — poklon za mladence, godišnjicu ili zajednički stan. Plaćanje pouzećem, besplatna dostava!',
			'contains'        => array( array( self::PILLOW, 'kockasti-roze' ), array( self::PILLOW, 'kockasti-sivi' ), array( self::LONG, 'meda-roze' ), array( self::LONG, 'meda-plavi' ) ),
		),
		'mali-znak-paznje'     => array(
			'name'            => 'Poklon paket Mali znak pažnje',
			'slug'            => 'poklon-paket-mali-znak-paznje',
			'occasion'        => 'paznja',
			'price'           => 1390,
			'image'           => 'poklon-paket-mali-znak-paznje.webp',
			'alt'             => 'Peškir u obliku lale, roze peškir sa vezenim medom i dve magične krpice sa avokadom',
			'includes'        => 'peškir Lala, peškir Meda, roze, i obe magične krpice Avokado',
			'short'           => 'Mali poklon koji se koristi svaki dan: peškir u obliku lale, roze peškir sa vezenim medom i dve magične krpice. Za vaspitačicu, učiteljicu ili koleginicu.',
			'seo_title'       => 'Poklon za Vaspitačicu i Učiteljicu | Mali Znak Pažnje',
			'seo_description' => 'Peškir u obliku lale, peškir sa medom i dve magične krpice — mali, praktičan poklon za vaspitačicu, učiteljicu, 8. mart ili kraj godine. Pouzećem!',
			'contains'        => array( array( self::TOWEL, 'lala' ), array( self::LONG, 'meda-roze' ), array( self::CLOTHS, Cloths::SET ) ),
		),
		'praznicna-kutija'     => array(
			'name'            => 'Poklon paket Praznična kutija mekoće',
			'slug'            => 'poklon-paket-praznicna-kutija-mekoce',
			'occasion'        => 'praznici',
			'price'           => 3390,
			'image'           => 'poklon-paket-praznicna-kutija-mekoce.webp',
			'alt'             => 'Braon Cuddle Puff jastuk Kružić, bež peškir, dečiji peškiri u obliku pingvina, mede i keksa i dve magične krpice',
			'includes'        => '3 dečija peškira (Pingvin, Meda, Čokoladni keks), Cuddle Puff jastuk Kružić, braon, peškir Meda, bež, i obe magične krpice Avokado',
			'short'           => 'Najveći CosyPaw poklon: tri dečija peškira, Cuddle Puff jastuk, peškir sa medom i dve magične krpice. Novogodišnji paketić za celu porodicu.',
			'seo_title'       => 'Novogodišnji Poklon Paket | Praznična Kutija Mekoće',
			'seo_description' => 'Novogodišnji paketić za celu porodicu: tri dečija peškira, Cuddle Puff jastuk, peškir sa medom i magične krpice. Plaćanje pouzećem, besplatna dostava!',
			'contains'        => array( array( self::TOWEL, 'pingvin' ), array( self::TOWEL, 'meda' ), array( self::TOWEL, 'keks' ), array( self::PILLOW, 'kruzic-braon' ), array( self::LONG, 'meda-bez' ), array( self::CLOTHS, Cloths::SET ) ),
		),

		/*
		 * Version 2: two more for children, for small gifts and for the
		 * holidays, so each of those occasions has three to choose from. A
		 * bundle is withdrawn the day one of its towels is retired, and the
		 * shop had retired enough of them to leave those occasions with one
		 * bundle each. These hold only towels the shop sells today, and name
		 * them as the shop does ("u torbi", "Sovica", "Krofnica").
		 */
		'drugari-u-torbi'      => array(
			'name'            => 'Poklon paket Drugari u torbi',
			'slug'            => 'poklon-paket-drugari-u-torbi',
			'occasion'        => 'deca',
			'price'           => 2090,
			'image'           => 'poklon-paket-drugari-u-torbi.webp',
			'alt'             => 'Četiri dečija peškira sa alkom za kačenje: žirafa, koala, kapibara i zeka, svaka viri iz svoje torbe',
			'includes'        => '4 dečija peškira u torbi (Žirafa, Koala, Kapibarica, Zeka)',
			'short'           => 'Četiri drugara koji vire iz torbe: žirafa, koala, kapibarica i zeka, svaki u svojoj boji i sa alkom za kačenje. Poklon za rođendan, polazak u vrtić ili za braću i sestre.',
			'seo_title'       => 'Dečiji Peškiri u Torbi | Poklon Paket od 4 Peškira sa Alkom',
			'seo_description' => 'Četiri dečija peškira u torbi — žirafa, koala, kapibarica i zeka, sa alkom za kačenje. Poklon za rođendan ili vrtić. Pouzećem, besplatna dostava!',
			'contains'        => array( array( self::TOWEL, 'zirafa' ), array( self::TOWEL, 'koala' ), array( self::TOWEL, 'kapibara' ), array( self::TOWEL, 'zeka' ) ),
		),
		'prvi-rodjendan'       => array(
			'name'            => 'Poklon paket Prvi rođendan',
			'slug'            => 'poklon-paket-prvi-rodjendan',
			'occasion'        => 'deca',
			'price'           => 2790,
			'image'           => 'poklon-paket-prvi-rodjendan.webp',
			'alt'             => 'Žuti Cuddle Puff jastuk u obliku cveta i dečiji peškiri u obliku pingvina, sove i mede',
			'includes'        => 'Cuddle Puff jastuk Cvetić, žuti, i 3 dečija peškira (Pingvin, Sovica, Meda)',
			'short'           => 'Za prvu svećicu: žuti Cuddle Puff jastuk u obliku cveta za dečiju sobu i tri dečija peškira sa alkom — pingvin, sovica i meda. Poklon za prvi rođendan, krštenje ili rođenje.',
			'seo_title'       => 'Poklon za Prvi Rođendan | Jastuk Cvetić i 3 Dečija Peškira',
			'seo_description' => 'Poklon za prvi rođendan ili krštenje: Cuddle Puff jastuk u obliku cveta i tri dečija peškira u obliku životinja. Plaćanje pouzećem, besplatna dostava!',
			'contains'        => array( array( self::PILLOW, 'cvetic-zuti' ), array( self::TOWEL, 'pingvin' ), array( self::TOWEL, 'sova' ), array( self::TOWEL, 'meda' ) ),
		),
		'slatki-zalogaji'      => array(
			'name'            => 'Poklon paket Slatki zalogaji',
			'slug'            => 'poklon-paket-slatki-zalogaji',
			'occasion'        => 'paznja',
			'price'           => 1590,
			'image'           => 'poklon-paket-slatki-zalogaji.webp',
			'alt'             => 'Peškiri u obliku krofne, čokoladnog keksa i ananasa i dve magične krpice sa vezenim avokadom',
			'includes'        => '3 peškira (Krofnica, Keksić, Ananas) i obe magične krpice Avokado',
			'short'           => 'Slatkiši koji ne goje: peškiri u obliku krofne, keksa i ananasa za ruke u kuhinji i obe magične krpice za radne površine. Veseo poklon za koleginicu ili domaćicu.',
			'seo_title'       => 'Poklon za Koleginicu | Peškiri Krofna, Keks i Ananas',
			'seo_description' => 'Peškiri u obliku krofne, keksa i ananasa i dve magične krpice — veseo, praktičan poklon za koleginicu, domaćicu ili kuhinju. Plaćanje pouzećem!',
			'contains'        => array( array( self::TOWEL, 'krofna' ), array( self::TOWEL, 'keks' ), array( self::TOWEL, 'ananas' ), array( self::CLOTHS, Cloths::SET ) ),
		),
		'hvala-od-srca'        => array(
			'name'            => 'Poklon paket Hvala od srca',
			'slug'            => 'poklon-paket-hvala-od-srca',
			'occasion'        => 'paznja',
			'price'           => 1290,
			'image'           => 'poklon-paket-hvala-od-srca.webp',
			'alt'             => 'Peškir u obliku javorovog lista, krem peškir sa vezenim medom i dve magične krpice sa avokadom',
			'includes'        => 'peškir List, peškir Meda, krem, i obe magične krpice Avokado',
			'short'           => 'Mali poklon da kažeš hvala: peškir u obliku javorovog lista, krem peškir sa vezenim medom i dve magične krpice. Za vaspitačicu, učiteljicu ili kraj školske godine.',
			'seo_title'       => 'Poklon za Učiteljicu i Vaspitačicu | Paket Hvala od Srca',
			'seo_description' => 'Peškir u obliku lista, peškir sa medom i dve magične krpice — mali poklon za učiteljicu, vaspitačicu, kraj školske godine ili 8. mart. Pouzećem!',
			'contains'        => array( array( self::TOWEL, 'list' ), array( self::LONG, 'meda-krem' ), array( self::CLOTHS, Cloths::SET ) ),
		),
		'novogodisnji-paketic' => array(
			'name'            => 'Poklon paket Novogodišnji paketić',
			'slug'            => 'poklon-paket-novogodisnji-paketic',
			'occasion'        => 'praznici',
			'price'           => 2090,
			'image'           => 'poklon-paket-novogodisnji-paketic.webp',
			'alt'             => 'Četiri dečija peškira sa alkom za kačenje, u obliku pingvina, kuce, krofne i čokoladnog keksa',
			'includes'        => '4 dečija peškira (Pingvin, Kuca pecaroš, Krofnica, Keksić)',
			'short'           => 'Paketić pod jelku koji se ne pojede za jedan dan: četiri dečija peškira sa alkom — pingvin, kuca, krofnica i keksić. Novogodišnji poklon za decu, bez šećera.',
			'seo_title'       => 'Novogodišnji Paketić za Decu | 4 Dečija Peškira sa Alkom',
			'seo_description' => 'Novogodišnji paketić za decu: četiri dečija peškira u obliku pingvina, kuce, krofne i keksa. Poklon pod jelku bez šećera. Pouzećem, besplatna dostava!',
			'contains'        => array( array( self::TOWEL, 'pingvin' ), array( self::TOWEL, 'kucence' ), array( self::TOWEL, 'krofna' ), array( self::TOWEL, 'keks' ) ),
		),
		'topla-zima'           => array(
			'name'            => 'Poklon paket Topla zima',
			'slug'            => 'poklon-paket-topla-zima',
			'occasion'        => 'praznici',
			'price'           => 2790,
			'image'           => 'poklon-paket-topla-zima.webp',
			'alt'             => 'Zeleni Cuddle Puff jastuk Kružić, krem i bež peškir sa vezenim medom i dečiji peškir u obliku pingvina',
			'includes'        => 'Cuddle Puff jastuk Kružić, zeleni, 2 peškira Meda (krem i bež) i dečiji peškir Pingvin',
			'short'           => 'Za duge zimske večeri: zeleni Cuddle Puff jastuk za fotelju, dva mekana peškira sa vezenim medom i pingvin za najmlađe. Novogodišnji ili božićni poklon za dom.',
			'seo_title'       => 'Božićni i Novogodišnji Poklon za Dom | Paket Topla Zima',
			'seo_description' => 'Novogodišnji poklon za dom: Cuddle Puff jastuk, dva peškira sa vezenim medom i dečiji peškir Pingvin. Plaćanje pouzećem, besplatna dostava!',
			'contains'        => array( array( self::PILLOW, 'kruzic-zeleni' ), array( self::LONG, 'meda-krem' ), array( self::LONG, 'meda-bez' ), array( self::TOWEL, 'pingvin' ) ),
		),
	);

	/**
	 * Long description, shared by every bundle.
	 */
	protected const DESCRIPTION = 'Poklon paket CosyPaw: sve iz paketa stiže zajedno, u jednoj porudžbini, uz plaćanje pouzećem i dostavu 2–4 dana širom Srbije. Svaki deo je isti kao kad ga kupuješ posebno — dečiji peškiri sa alkom za kačenje, peškiri sa vezenim medom, Cuddle Puff jastuci sa okicama i magične krpice od mikrofibera — samo složen za jednu priliku i jeftiniji u paketu.';

	/**
	 * Per-request memo of towel tiers.
	 *
	 * @var array<int,array{id:string,name:string,qty:int,price:int}>|null
	 */
	private static ?array $tiers = null;

	/**
	 * Per-request memo of the towel motifs, sold or not, by id.
	 *
	 * @var array<string,array<string,mixed>>|null
	 */
	private static ?array $motifs = null;

	/**
	 * Per-request memo of the towels on sale, as slot choices.
	 *
	 * @var array<string,array{key:string,name:string,swatch:string,image:string,price:int}>|null
	 */
	private static ?array $towels = null;

	/**
	 * Per-request memo of slots(), by bundle key.
	 *
	 * @var array<string,array<int,array<string,mixed>>>
	 */
	private static array $slots = array();

	/* ---------------------------------------------------------------------
	 * Storefront
	 * ------------------------------------------------------------------ */

	/**
	 * Bundles on offer, each with what it saves.
	 *
	 * A products() row plus `occasion`, `includes` (translated), `separately`
	 * (what the same things cost bought the cheapest way the shop sells them
	 * today) and `saving`. A bundle is left out while it, or anything it
	 * holds, cannot be bought, or while it is not cheaper than its parts.
	 *
	 * @param array<int,string> $keys Only these bundles, in this order; all when empty.
	 * @return array<int,array<string,mixed>>
	 */
	public static function offers( array $keys = array() ): array {
		$out = array();

		foreach ( $keys ? $keys : array_keys( self::ITEMS ) as $key ) {
			$item = self::ITEMS[ $key ] ?? null;
			$row  = null !== $item ? self::row( (string) $key ) : null;
			if ( null === $row ) {
				continue;
			}

			$separately = self::separately( $item['contains'] );
			if ( null === $separately || $row['price'] < 1 || $separately <= $row['price'] ) {
				continue;
			}

			$row['occasion']   = $item['occasion'];
			$row['includes']   = self::translate( $item['includes'] );
			$row['separately'] = $separately;
			$row['saving']     = $separately - $row['price'];
			$out[]             = $row;
		}

		return $out;
	}

	/**
	 * Offers grouped by occasion, in OCCASIONS order. Empty groups left out.
	 *
	 * @param array<int,array<string,mixed>> $offers offers() output.
	 * @return array<string,array{title:string,offers:array<int,array<string,mixed>>}>
	 */
	public static function by_occasion( array $offers ): array {
		$groups = array();

		foreach ( self::OCCASIONS as $key => $title ) {
			$in = array_values( array_filter( $offers, static fn( array $o ): bool => $key === $o['occasion'] ) );
			if ( $in ) {
				$groups[ $key ] = array(
					'title'  => self::translate( $title ),
					'offers' => $in,
				);
			}
		}

		return $groups;
	}

	/**
	 * What a packing list costs bought the cheapest way the shop sells it,
	 * or null while any part of it cannot be bought.
	 *
	 * @param array<int,array{0:string,1:string}> $contains Packing list.
	 * @return int|null
	 */
	public static function separately( array $contains ): ?int {
		$towels = 0;
		$loose  = 0;
		$total  = 0;

		foreach ( $contains as $part ) {
			list( $line, $key ) = $part;

			if ( self::TOWEL === $line ) {
				$price = self::towel_price( $key );
				if ( null === $price ) {
					return null;
				}
				++$towels;
				$loose += $price;
				continue;
			}

			$row = match ( $line ) {
				self::LONG   => LongTowels::row( $key ),
				self::PILLOW => Pillows::row( $key ),
				self::CLOTHS => Cloths::row( $key ),
				default      => null,
			};
			if ( null === $row || $row['price'] < 1 ) {
				return null;
			}
			$total += $row['price'];
		}

		if ( $towels > 0 ) {
			// The towels at what the cart would charge for that many — the 2+1
			// package where it applies — and at their own prices when there
			// are no packages to price them against.
			$tiers   = self::towel_tiers();
			$planned = $tiers ? BundlePricing::plan( $towels, $tiers )['total'] : 0;
			$total  += $planned > 0 ? min( $planned, $loose ) : $loose;
		}

		return $total;
	}

	/**
	 * Live price of a towel motif, or null while it cannot be bought.
	 *
	 * @param string $motif Catalog motif id.
	 * @return int|null
	 */
	private static function towel_price( string $motif ): ?int {
		if ( ! function_exists( 'wc_get_product' ) ) {
			return null;
		}

		$id      = (int) ( ( (array) get_option( WooCommerce::PRODUCT_MAP_OPTION, array() ) )[ $motif ] ?? 0 );
		$product = $id > 0 ? wc_get_product( $id ) : null;

		if ( ! $product instanceof \WC_Product || 'publish' !== $product->get_status() || ! $product->is_purchasable() ) {
			return null;
		}

		$price = (int) round( (float) $product->get_price() );

		return $price > 0 ? $price : null;
	}

	/**
	 * The towel packages, as BundlePricing prices a cart with them.
	 *
	 * @return array<int,array{id:string,name:string,qty:int,price:int}>
	 */
	private static function towel_tiers(): array {
		if ( null === self::$tiers ) {
			self::$tiers = BundlePricing::tiers_from( ( new Catalog() )->packages() );
		}

		return self::$tiers;
	}

	/**
	 * Forget the memoised tiers, motifs and slots (tests price several
	 * scenarios in one run).
	 *
	 * @return void
	 */
	public static function flush(): void {
		self::$tiers  = null;
		self::$motifs = null;
		self::$towels = null;
		self::$slots  = array();
	}

	/* ---------------------------------------------------------------------
	 * Slots: what each part of a bundle may be swapped for
	 * ------------------------------------------------------------------ */

	/**
	 * The bundle's parts, each with what it may be swapped for.
	 *
	 * One slot per `contains` entry, in that order. `choices` are the things
	 * on sale today that can take the slot: another colour of the same pillow
	 * shape, any towel shape, another colour of the long towel; the cloth set
	 * is the only thing that fills its own slot. A choice has to sell at the
	 * price the suggested part does (its launch price, while that part is off
	 * sale), so a swap never changes what the bundle is worth — the price and
	 * the saving offers() states hold for every combination. A colour the shop
	 * reprices on its own simply drops out of the bundle until the line is
	 * level again.
	 *
	 * @param string $key ITEMS key.
	 * @return array<int,array{line:string,default:string,label:string,choices:array<string,array{key:string,name:string,swatch:string,image:string,price:int}>}> Empty for an unknown key.
	 */
	public static function slots( string $key ): array {
		if ( isset( self::$slots[ $key ] ) ) {
			return self::$slots[ $key ];
		}

		$item = self::ITEMS[ $key ] ?? null;
		if ( null === $item ) {
			return array();
		}

		$slots = array();
		foreach ( $item['contains'] as $part ) {
			list( $line, $default ) = $part;

			$slots[] = array(
				'line'    => $line,
				'default' => $default,
				'label'   => self::slot_label( $line, $default ),
				'choices' => self::choices( $line, $default ),
			);
		}

		self::$slots[ $key ] = $slots;

		return $slots;
	}

	/**
	 * Whether every slot of the bundle has something on sale to fill it.
	 *
	 * @param string $key ITEMS key.
	 * @return bool
	 */
	public static function packable( string $key ): bool {
		$slots = self::slots( $key );

		foreach ( $slots as $slot ) {
			if ( ! $slot['choices'] ) {
				return false;
			}
		}

		return (bool) $slots;
	}

	/**
	 * The packing list for a bundle, with the shopper's picks in it.
	 *
	 * A slot not picked keeps its suggested part — which is what an add from a
	 * bundle card, carrying no picks at all, gets. A pick that is not one of
	 * the slot's choices, or a suggestion no longer on sale, makes the whole
	 * list unpackable rather than quietly packing something else.
	 *
	 * @param string            $key    ITEMS key.
	 * @param array<int,string> $picked Slot index => chosen key.
	 * @return array<int,array{0:string,1:string}>|null [line, key] pairs, or null.
	 */
	public static function resolve( string $key, array $picked = array() ): ?array {
		$slots = self::slots( $key );
		if ( ! $slots ) {
			return null;
		}

		$parts = array();
		foreach ( $slots as $i => $slot ) {
			$choice = isset( $picked[ $i ] ) ? (string) $picked[ $i ] : $slot['default'];
			if ( ! isset( $slot['choices'][ $choice ] ) ) {
				return null;
			}
			$parts[] = array( $slot['line'], $choice );
		}

		return $parts;
	}

	/**
	 * A packing list as one string for the cart item and the order line:
	 * "pillow:kruzic-zuti,towel:sova". Language-neutral, like the builder's
	 * motif ids, so the order reads in whatever language it is viewed in.
	 *
	 * @param array<int,array{0:string,1:string}> $parts [line, key] pairs.
	 * @return string
	 */
	public static function encode( array $parts ): string {
		return implode( ',', array_map( static fn( array $part ): string => $part[0] . ':' . $part[1], $parts ) );
	}

	/**
	 * encode() reversed. Anything that is not a known line and a key is
	 * dropped.
	 *
	 * @param string $stored Stored packing list.
	 * @return array<int,array{0:string,1:string}>
	 */
	public static function decode( string $stored ): array {
		$lines = array( self::TOWEL, self::LONG, self::PILLOW, self::CLOTHS );
		$parts = array();

		foreach ( explode( ',', $stored ) as $pair ) {
			$pair = explode( ':', trim( $pair ), 2 );
			if ( 2 === count( $pair ) && in_array( $pair[0], $lines, true ) && '' !== $pair[1] ) {
				$parts[] = array( $pair[0], $pair[1] );
			}
		}

		return $parts;
	}

	/**
	 * A packing list in words, a row per part: "Jastuk Kružić" / "Žuta".
	 *
	 * Resolved from the registries rather than the live products, so an order
	 * still names a part long after it has gone off sale.
	 *
	 * @param array<int,array{0:string,1:string}> $parts [line, key] pairs.
	 * @return array<int,array{label:string,name:string}>
	 */
	public static function describe( array $parts ): array {
		$out = array();

		foreach ( $parts as $part ) {
			list( $line, $key ) = $part;

			$out[] = array(
				'label' => self::slot_label( $line, $key ),
				'name'  => self::part_name( $line, $key ),
			);
		}

		return $out;
	}

	/**
	 * ITEMS key a product is mapped to, or '' for anything that is not a
	 * bundle.
	 *
	 * @param int $product_id Product id.
	 * @return string
	 */
	public static function key_for( int $product_id ): string {
		return self::key_of( $product_id );
	}

	/**
	 * What a slot is, in words: "Peškir", "Jastuk Kockasti". The long towel
	 * is "Peškir Meda", after its product, so it does not read as a second
	 * shaped-towel slot.
	 *
	 * @param string $line Component line.
	 * @param string $key  The part in the slot.
	 * @return string
	 */
	private static function slot_label( string $line, string $key ): string {
		return match ( $line ) {
			self::TOWEL  => __( 'Peškir', 'cosypaw' ),
			self::PILLOW => match ( Pillows::ITEMS[ $key ]['group'] ?? '' ) {
				Pillows::SQUARE => __( 'Jastuk Kockasti', 'cosypaw' ),
				Pillows::FLOWER => __( 'Jastuk Cvetić', 'cosypaw' ),
				default         => __( 'Jastuk Kružić', 'cosypaw' ),
			},
			self::LONG   => __( 'Peškir Meda', 'cosypaw' ),
			default      => __( 'Magične krpice', 'cosypaw' ),
		};
	}

	/**
	 * Which one a part is, in words: the towel's shape, the colour of the
	 * others. Its key when the registry no longer knows it.
	 *
	 * @param string $line Component line.
	 * @param string $key  Part key.
	 * @return string
	 */
	private static function part_name( string $line, string $key ): string {
		$color = match ( $line ) {
			self::PILLOW => Pillows::ITEMS[ $key ]['color'] ?? '',
			self::LONG   => LongTowels::ITEMS[ $key ]['color'] ?? '',
			default      => '',
		};

		if ( '' !== $color ) {
			return self::translate( (string) $color );
		}

		if ( self::TOWEL === $line ) {
			return (string) ( self::motifs()[ $key ]['name'] ?? $key );
		}

		if ( self::CLOTHS === $line && isset( Cloths::ITEMS[ $key ] ) ) {
			return __( 'Avokado, limeta i žalfija', 'cosypaw' );
		}

		return $key;
	}

	/**
	 * What may take a slot: the candidates on sale at the suggested part's
	 * price, in registry order.
	 *
	 * @param string $line    Component line.
	 * @param string $default The suggested part.
	 * @return array<string,array{key:string,name:string,swatch:string,image:string,price:int}>
	 */
	private static function choices( string $line, string $default ): array {
		$candidates = match ( $line ) {
			self::TOWEL  => self::towel_choices(),
			self::PILLOW => self::line_choices( $line, Pillows::class, self::same_group( Pillows::ITEMS, $default ) ),
			self::LONG   => self::line_choices( $line, LongTowels::class, self::same_group( LongTowels::ITEMS, $default ) ),
			self::CLOTHS => self::line_choices( $line, Cloths::class, static fn( string $key, array $item ): bool => $key === $default ),
			default      => array(),
		};

		$price = $candidates[ $default ]['price'] ?? self::launch_price( $line, $default );

		return array_filter( $candidates, static fn( array $choice ): bool => $choice['price'] === $price );
	}

	/**
	 * Every towel on sale, as slot choices. Its photo is the 192px square
	 * the benefit cards use.
	 *
	 * @return array<string,array{key:string,name:string,swatch:string,image:string,price:int}>
	 */
	private static function towel_choices(): array {
		if ( null !== self::$towels ) {
			return self::$towels;
		}

		$out = array();
		foreach ( self::motifs() as $id => $motif ) {
			$price = self::towel_price( (string) $id );
			if ( null === $price ) {
				continue;
			}

			$out[ (string) $id ] = array(
				'key'    => (string) $id,
				'name'   => (string) ( $motif['name'] ?? $id ),
				'swatch' => '',
				'image'  => (string) ( $motif['image_th'] ?? '' ),
				'price'  => $price,
			);
		}

		self::$towels = $out;

		return $out;
	}

	/**
	 * A product line's items on sale that a predicate admits, as slot choices.
	 *
	 * @param string                       $line   Component line.
	 * @param class-string<ProductLine>    $class  The line's class.
	 * @param callable(string,array):bool $wanted Whether an ITEMS entry may take the slot.
	 * @return array<string,array{key:string,name:string,swatch:string,image:string,price:int}>
	 */
	private static function line_choices( string $line, string $class, callable $wanted ): array {
		$out = array();

		foreach ( $class::ITEMS as $key => $item ) {
			$key = (string) $key;
			if ( ! $wanted( $key, $item ) ) {
				continue;
			}

			$row = $class::row( $key );
			if ( null === $row || $row['price'] < 1 ) {
				continue;
			}

			$out[ $key ] = array(
				'key'    => $key,
				'name'   => self::part_name( $line, $key ),
				'swatch' => (string) ( $item['swatch'] ?? '' ),
				'image'  => $row['image_id'] > 0 ? (string) wp_get_attachment_image_url( $row['image_id'], 'woocommerce_thumbnail' ) : '',
				'price'  => $row['price'],
			);
		}

		return $out;
	}

	/**
	 * Predicate for line_choices(): the items of the same design as one item —
	 * the colours of one pillow shape, the colours of the long towel.
	 *
	 * @param array<string,array<string,mixed>> $items A line's ITEMS.
	 * @param string                            $key   The suggested item.
	 * @return callable(string,array):bool
	 */
	private static function same_group( array $items, string $key ): callable {
		$group = $items[ $key ]['group'] ?? null;

		return static fn( string $candidate, array $item ): bool => null !== $group && ( $item['group'] ?? null ) === $group;
	}

	/**
	 * What a part was launched at, for a slot whose suggestion is off sale.
	 *
	 * @param string $line Component line.
	 * @param string $key  Part key.
	 * @return int
	 */
	private static function launch_price( string $line, string $key ): int {
		return (int) match ( $line ) {
			self::PILLOW => Pillows::ITEMS[ $key ]['price'] ?? Pillows::PRICE,
			self::LONG   => LongTowels::ITEMS[ $key ]['price'] ?? LongTowels::PRICE,
			self::CLOTHS => Cloths::ITEMS[ $key ]['price'] ?? Cloths::PRICE,
			default      => Catalog::UNIT_PRICE,
		};
	}

	/**
	 * Every towel motif by id, sold or not — orders have to name retired ones.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	private static function motifs(): array {
		if ( null === self::$motifs ) {
			self::$motifs = ( new Catalog() )->motif_map();
		}

		return self::$motifs;
	}

	/* ---------------------------------------------------------------------
	 * Product page
	 * ------------------------------------------------------------------ */

	/**
	 * The spec list under a bundle's summary.
	 *
	 * The suggested packing list leads it only where the picker above the
	 * button is not printed: there the picker already lists every part, and
	 * would have the list here contradicting it after the first swap.
	 *
	 * @param string $key ITEMS key of the product on screen.
	 * @return array<string,string>
	 */
	protected function spec_rows( string $key ): array {
		$item     = self::ITEMS[ $key ];
		$row      = self::row( $key );
		$free_min = Catalog::free_shipping_min();
		// The live price, not the launch one: wp-admin may have moved it.
		$free = $free_min > 0 && null !== $row && $row['price'] >= $free_min;

		$specs = array();
		if ( null === $row || ! self::packable( $key ) ) {
			$specs[ __( 'U paketu', 'cosypaw' ) ] = self::translate( (string) $item['includes'] );
		}

		return $specs + array(
			__( 'Prilika', 'cosypaw' )   => self::translate( self::OCCASIONS[ $item['occasion'] ] ),
			__( 'Dostava', 'cosypaw' )   => $free
				? __( 'Besplatna dostava, plaćanje pouzećem, isporuka 2–4 dana širom Srbije.', 'cosypaw' )
				: __( 'Plaćanje pouzećem, isporuka 2–4 dana širom Srbije.', 'cosypaw' ),
		);
	}
}
