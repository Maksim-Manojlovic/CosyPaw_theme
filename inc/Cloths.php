<?php
/**
 * Cloths — the "magične krpice", the shop's first product that is not a towel.
 *
 * Sold on their own, at their own price, outside the towel packages. They are
 * deliberately NOT filed under the towel category (WooCommerce::TOWEL_CATEGORY):
 * a product there is picked up by register_towel() and becomes a motif — in the
 * gallery, in the builder, and counted by BundlePricing towards a 2+1 package.
 * A cloth lives in its own category and none of that touches it.
 *
 * Creation, storefront rows, translation and the product page spec list are
 * \Theme\ProductLine's; the photographs ship in assets/cloths/.
 *
 * Besides the two colours there is a set of both, a product of its own at its
 * own price (SET_PRICE). It is a plain simple product rather than a cart rule:
 * it has its own page and card, and — unlike a discount booked as a fee — the
 * free-delivery threshold sees the price it is actually sold at.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

namespace Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cloths.
 */
final class Cloths extends ProductLine {

	/**
	 * Bump when a product is added to ITEMS.
	 */
	public const VERSION = 2;

	/**
	 * Option recording which registry version this install has been brought up to.
	 */
	protected const OPTION = 'cosypaw_cloths_version';

	/**
	 * Option: ITEMS key => WooCommerce product id.
	 */
	public const MAP_OPTION = 'cosypaw_cloth_map';

	/**
	 * Product category the cloths are filed under.
	 */
	public const CATEGORY = 'magicne-krpe';

	/**
	 * Its name — a database value, stored in the source language like
	 * "Peškiri" and "Paketi".
	 */
	protected const CATEGORY_NAME = 'Magične krpe';

	/**
	 * Launch price (RSD). Written once, at creation; wp-admin owns it after.
	 */
	public const PRICE = 299;

	/**
	 * ITEMS key of the set of both colours.
	 */
	public const SET = 'set-avokado';

	/**
	 * Launch price of the set (RSD) — both cloths for the price of one.
	 * Written once, at creation, like PRICE.
	 */
	public const SET_PRICE = 299;

	/**
	 * Theme directory the photographs ship in.
	 */
	protected const IMAGE_DIR = 'assets/cloths/';

	/**
	 * Key => product definition. Names and copy are Serbian database values
	 * that also serve as msgids, so the storefront can translate them while
	 * they are unedited (see translate()).
	 *
	 * The set comes last: it has no photograph of its own and reuses the ones
	 * the cloths it holds (`holds`) were given, so they have to exist first.
	 *
	 * @var array<string,array{name:string,slug:string,image:string,alt:string,short:string,seo_title:string,seo_description:string,price?:int,holds?:array<int,string>}>
	 */
	public const ITEMS = array(
		'avokado-limeta'  => array(
			'name'            => 'Magična krpica Avokado, limeta',
			'slug'            => 'magicna-krpica-avokado-limeta',
			'image'           => 'krpica-avokado-limeta.webp',
			'alt'             => 'Magična krpa od mikrofibera u limeta zelenoj boji sa vezenim avokadom, na pultu u kupatilu',
			'short'           => 'Magična krpa od mikrofibera u jarkoj limeta zelenoj boji, sa vezenim avokadom. Upija vodu, hvata prašinu i briše staklo bez tragova — za kuhinju, kupatilo i ogledala.',
			'seo_title'       => 'Magična Krpica Avokado, Limeta | Krpa za Staklo i Kuhinju',
			'seo_description' => 'Magična krpa od mikrofibera sa vezenim avokadom: upija vodu, hvata prašinu i briše staklo bez tragova. Za kuhinju i kupatilo. Poruči odmah!',
		),
		'avokado-zalfija' => array(
			'name'            => 'Magična krpica Avokado, žalfija',
			'slug'            => 'magicna-krpica-avokado-zalfija',
			'image'           => 'krpica-avokado-zalfija.webp',
			'alt'             => 'Magična krpa od mikrofibera u nežnoj žalfija zelenoj boji sa vezenim nasmejanim avokadom, na pultu u kupatilu',
			'short'           => 'Magična krpa od mikrofibera u nežnoj žalfija zelenoj boji, sa vezenim nasmejanim avokadom. Upija vodu, hvata prašinu i briše staklo bez tragova — za kuhinju, kupatilo i ogledala.',
			'seo_title'       => 'Magična Krpica Avokado, Žalfija | Krpa za Staklo i Kuhinju',
			'seo_description' => 'Magična krpa od mikrofibera u žalfija zelenoj boji: upija vodu, hvata prašinu i briše staklo bez tragova. Za kuhinju i kupatilo. Poruči odmah!',
		),
		self::SET         => array(
			'name'            => 'Set od 2 magične krpice Avokado',
			'slug'            => 'set-2-magicne-krpice-avokado',
			'image'           => 'krpica-avokado-limeta.webp',
			'alt'             => 'Magična krpa od mikrofibera u limeta zelenoj boji sa vezenim avokadom, na pultu u kupatilu',
			'short'           => 'Obe magične krpice Avokado u jednom setu — limeta i žalfija zelena. Upijaju vodu, hvataju prašinu i brišu staklo bez tragova: jedna za kuhinju, druga za staklo i ogledala.',
			'seo_title'       => 'Set 2 Magične Krpice Avokado | Krpe za Staklo i Kuhinju',
			'seo_description' => 'Dve magične krpe od mikrofibera sa vezenim avokadom, limeta i žalfija, u jednom setu: upijaju vodu i brišu staklo bez tragova. Poruči set odmah!',
			'price'           => self::SET_PRICE,
			'holds'           => array( 'avokado-limeta', 'avokado-zalfija' ),
		),
	);

	/**
	 * Long description, shared by both colours.
	 */
	protected const DESCRIPTION = 'Rebrasta mikrofibra upija vodu i hvata prašinu umesto da je razmazuje, pa je ista krpa jednako dobra kao kuhinjska krpa za radne površine i sudove i kao krpa za staklo, ogledala i tuš kabine. Briše bez tragova i dlačica, bez agresivnih sredstava. Višekratna je i pere se u mašini na 40°C, bez omekšivača, da zadrži upijanje — i zamenjuje gomilu papirnih ubrusa.';

	/**
	 * The set of both colours, when it is on sale and actually saves something.
	 *
	 * `separately` is what the cloths it holds cost bought one by one, and
	 * `saving` the difference. A set that is not cheaper than its cloths —
	 * a price moved in wp-admin, or a cloth it holds no longer on sale — is
	 * not offered as a deal at all, so the card never shows a saving the cart
	 * would not give.
	 *
	 * @param array<int,array<string,mixed>> $cloths products() output.
	 * @return array{key:string,id:int,name:string,price:int,permalink:string,add_to_cart_url:string,image_id:int,gallery_ids:array<int,int>,separately:int,saving:int}|null
	 */
	public static function set( array $cloths ): ?array {
		$row = self::row( self::SET );
		if ( null === $row ) {
			return null;
		}

		$prices     = array_column( $cloths, 'price', 'key' );
		$separately = 0;
		foreach ( self::ITEMS[ self::SET ]['holds'] ?? array() as $held ) {
			if ( empty( $prices[ $held ] ) ) {
				return null;
			}
			$separately += (int) $prices[ $held ];
		}

		if ( $row['price'] < 1 || $separately <= $row['price'] ) {
			return null;
		}

		$row['separately'] = $separately;
		$row['saving']     = $separately - $row['price'];

		return $row;
	}

	/**
	 * Whether a product is one of the cloths.
	 *
	 * @param int $product_id Product id.
	 * @return bool
	 */
	public static function is_cloth( int $product_id ): bool {
		return self::owns( $product_id );
	}

	/**
	 * The spec list under a cloth's summary.
	 *
	 * @param string $key ITEMS key of the product on screen.
	 * @return array<string,string>
	 */
	protected function spec_rows( string $key ): array {
		$specs = array();

		if ( self::SET === $key ) {
			$specs[ __( 'U setu', 'cosypaw' ) ] = __( '2 krpice: limeta i žalfija zelena.', 'cosypaw' );
		}

		return $specs + array(
			__( 'Materijal', 'cosypaw' )  => __( 'Rebrasta mikrofibra — upija vodu i hvata prašinu.', 'cosypaw' ),
			__( 'Namena', 'cosypaw' )     => __( 'Kuhinja, staklo, ogledala, tuš kabine i radne površine.', 'cosypaw' ),
			__( 'Održavanje', 'cosypaw' ) => __( 'Mašinsko pranje na 40°C, bez omekšivača.', 'cosypaw' ),
			__( 'Dostava', 'cosypaw' )    => __( 'Plaćanje pouzećem, isporuka 2–4 dana širom Srbije.', 'cosypaw' ),
		);
	}
}
