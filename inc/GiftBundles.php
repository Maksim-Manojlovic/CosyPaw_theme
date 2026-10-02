<?php
/**
 * GiftBundles — the poklon paketi: fixed combinations of the shop's products,
 * each sold as one product at one price.
 *
 * A bundle is a plain simple product rather than a cart rule, like the cloth
 * set: it has its own page, photo and Yoast fields, and the free-delivery
 * threshold sees the price it is really sold at. Its contents are fixed —
 * named towels, colours and the cloth set — so the shop knows what to pack
 * from the order line alone, and `contains` below is the packing list.
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
	public const VERSION = 1;

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
	 *  - contains: the packing list, [line, key] pairs. A towel is its motif
	 *    id (Catalog), the others their own line's ITEMS key; the cloths are
	 *    the set of both.
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
			'alt'             => 'Krem i plavi dugi peškir sa vezenim medom i dečiji peškiri u obliku žirafe i kapibare',
			'includes'        => '2 duga peškira Meda (krem i plavi) i 2 dečija peškira (Žirafa, Kapibara)',
			'short'           => 'Peškiri za celu porodicu: dva duga peškira sa vezenim medom za odrasle i dva dečija peškira sa alkom za mališane. Za novo ili osveženo kupatilo.',
			'seo_title'       => 'Peškiri za Celu Porodicu | Dugi i Dečiji Peškiri u Paketu',
			'seo_description' => 'Dva duga peškira sa vezenim medom i dva dečija peškira u obliku životinja — kupatilo za celu porodicu u jednom paketu. Besplatna dostava!',
			'contains'        => array( array( self::LONG, 'meda-krem' ), array( self::LONG, 'meda-plavi' ), array( self::TOWEL, 'zirafa' ), array( self::TOWEL, 'kapibara' ) ),
		),
		'novi-dom'             => array(
			'name'            => 'Poklon paket Novi dom',
			'slug'            => 'poklon-paket-novi-dom',
			'occasion'        => 'dom',
			'price'           => 2490,
			'image'           => 'poklon-paket-novi-dom.webp',
			'alt'             => 'Braon kockasti Cuddle Puff jastuk, krem i bež dugi peškir sa vezenim medom i dve magične krpice sa avokadom',
			'includes'        => 'Cuddle Puff jastuk Kockasti, braon, 2 duga peškira Meda (krem i bež) i obe magične krpice Avokado',
			'short'           => 'Poklon za useljenje: mekani kockasti jastuk za sofu, dva duga peškira za kupatilo i dve magične krpice za kuhinju — po nešto za svaku sobu novog doma.',
			'seo_title'       => 'Poklon za Useljenje | Paket Jastuk, Peškiri i Krpice',
			'seo_description' => 'Poklon za useljenje: Cuddle Puff jastuk, dva duga peškira i dve magične krpice — po nešto za svaku sobu novog doma. Besplatna dostava!',
			'contains'        => array( array( self::PILLOW, 'kockasti-braon' ), array( self::LONG, 'meda-krem' ), array( self::LONG, 'meda-bez' ), array( self::CLOTHS, Cloths::SET ) ),
		),
		'za-dvoje'             => array(
			'name'            => 'Poklon paket Za dvoje',
			'slug'            => 'poklon-paket-za-dvoje',
			'occasion'        => 'dom',
			'price'           => 3490,
			'image'           => 'poklon-paket-za-dvoje.webp',
			'alt'             => 'Roze i sivi kockasti Cuddle Puff jastuk i roze i plavi dugi peškir sa vezenim medom',
			'includes'        => '2 Cuddle Puff jastuka Kockasti (roze i sivi) i 2 duga peškira Meda (roze i plavi)',
			'short'           => 'Za njega i za nju: dva kockasta Cuddle Puff jastuka i dva duga peškira sa vezenim medom, u paru boja. Poklon za mladence, godišnjicu ili zajednički stan.',
			'seo_title'       => 'Poklon za Mladence i Parove | Jastuci i Peškiri za Dvoje',
			'seo_description' => 'Dva Cuddle Puff jastuka i dva duga peškira u paru boja — poklon za mladence, godišnjicu ili zajednički stan. Plaćanje pouzećem, besplatna dostava!',
			'contains'        => array( array( self::PILLOW, 'kockasti-roze' ), array( self::PILLOW, 'kockasti-sivi' ), array( self::LONG, 'meda-roze' ), array( self::LONG, 'meda-plavi' ) ),
		),
		'mali-znak-paznje'     => array(
			'name'            => 'Poklon paket Mali znak pažnje',
			'slug'            => 'poklon-paket-mali-znak-paznje',
			'occasion'        => 'paznja',
			'price'           => 1390,
			'image'           => 'poklon-paket-mali-znak-paznje.webp',
			'alt'             => 'Peškir u obliku lale, roze dugi peškir sa vezenim medom i dve magične krpice sa avokadom',
			'includes'        => 'peškir Lala, dugi peškir Meda, roze, i obe magične krpice Avokado',
			'short'           => 'Mali poklon koji se koristi svaki dan: peškir u obliku lale, roze dugi peškir sa vezenim medom i dve magične krpice. Za vaspitačicu, učiteljicu ili koleginicu.',
			'seo_title'       => 'Poklon za Vaspitačicu i Učiteljicu | Mali Znak Pažnje',
			'seo_description' => 'Peškir u obliku lale, dugi peškir i dve magične krpice — mali, praktičan poklon za vaspitačicu, učiteljicu, 8. mart ili kraj godine. Pouzećem!',
			'contains'        => array( array( self::TOWEL, 'lala' ), array( self::LONG, 'meda-roze' ), array( self::CLOTHS, Cloths::SET ) ),
		),
		'praznicna-kutija'     => array(
			'name'            => 'Poklon paket Praznična kutija mekoće',
			'slug'            => 'poklon-paket-praznicna-kutija-mekoce',
			'occasion'        => 'praznici',
			'price'           => 3390,
			'image'           => 'poklon-paket-praznicna-kutija-mekoce.webp',
			'alt'             => 'Braon Cuddle Puff jastuk Kružić, bež dugi peškir, dečiji peškiri u obliku pingvina, mede i keksa i dve magične krpice',
			'includes'        => '3 dečija peškira (Pingvin, Meda, Čokoladni keks), Cuddle Puff jastuk Kružić, braon, dugi peškir Meda, bež, i obe magične krpice Avokado',
			'short'           => 'Najveći CosyPaw poklon: tri dečija peškira, Cuddle Puff jastuk, dugi peškir i dve magične krpice. Novogodišnji paketić za celu porodicu.',
			'seo_title'       => 'Novogodišnji Poklon Paket | Praznična Kutija Mekoće',
			'seo_description' => 'Novogodišnji paketić za celu porodicu: tri dečija peškira, Cuddle Puff jastuk, dugi peškir i magične krpice. Plaćanje pouzećem, besplatna dostava!',
			'contains'        => array( array( self::TOWEL, 'pingvin' ), array( self::TOWEL, 'meda' ), array( self::TOWEL, 'keks' ), array( self::PILLOW, 'kruzic-braon' ), array( self::LONG, 'meda-bez' ), array( self::CLOTHS, Cloths::SET ) ),
		),
	);

	/**
	 * Long description, shared by every bundle.
	 */
	protected const DESCRIPTION = 'Poklon paket CosyPaw: sve iz paketa stiže zajedno, u jednoj porudžbini, uz plaćanje pouzećem i dostavu 2–4 dana širom Srbije. Svaki deo je isti kao kad ga kupuješ posebno — dečiji peškiri sa alkom za kačenje, dugi peškiri sa vezenim medom, Cuddle Puff jastuci sa okicama i magične krpice od mikrofibera — samo složen za jednu priliku i jeftiniji u paketu.';

	/**
	 * Per-request memo of towel tiers.
	 *
	 * @var array<int,array{id:string,name:string,qty:int,price:int}>|null
	 */
	private static ?array $tiers = null;

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
	 * Forget the memoised tiers (tests price several scenarios in one run).
	 *
	 * @return void
	 */
	public static function flush(): void {
		self::$tiers = null;
	}

	/* ---------------------------------------------------------------------
	 * Product page
	 * ------------------------------------------------------------------ */

	/**
	 * The spec list under a bundle's summary: the packing list first.
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

		return array(
			__( 'U paketu', 'cosypaw' )  => self::translate( (string) $item['includes'] ),
			__( 'Prilika', 'cosypaw' )   => self::translate( self::OCCASIONS[ $item['occasion'] ] ),
			__( 'Dostava', 'cosypaw' )   => $free
				? __( 'Besplatna dostava, plaćanje pouzećem, isporuka 2–4 dana širom Srbije.', 'cosypaw' )
				: __( 'Plaćanje pouzećem, isporuka 2–4 dana širom Srbije.', 'cosypaw' ),
		);
	}
}
