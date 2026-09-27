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
 * The products are created by the theme, once per registry version, the first
 * time an administrator opens wp-admin — the same way \Theme\Pages makes the
 * landing pages, because a deploy ships the theme and never the database. The
 * photographs ship in assets/cloths/ and are imported into the media library
 * then. A product that already exists is never touched: price, text and
 * stock are the shop's to edit in wp-admin afterwards.
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
final class Cloths {

	/**
	 * Bump when a product is added to ITEMS.
	 */
	public const VERSION = 1;

	/**
	 * Option recording which registry version this install has been brought up to.
	 */
	private const OPTION = 'cosypaw_cloths_version';

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
	private const CATEGORY_NAME = 'Magične krpe';

	/**
	 * Launch price (RSD). Written once, at creation; wp-admin owns it after.
	 */
	public const PRICE = 299;

	/**
	 * Theme directory the photographs ship in.
	 */
	private const IMAGE_DIR = 'assets/cloths/';

	/**
	 * Key => product definition. Names and copy are Serbian database values
	 * that also serve as msgids, so the storefront can translate them while
	 * they are unedited (see translate()).
	 *
	 * @var array<string,array{name:string,slug:string,image:string,alt:string,short:string,seo_title:string,seo_description:string}>
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
	);

	/**
	 * Long description, shared by both colours.
	 */
	private const DESCRIPTION = 'Rebrasta mikrofibra upija vodu i hvata prašinu umesto da je razmazuje, pa je ista krpa jednako dobra kao kuhinjska krpa za radne površine i sudove i kao krpa za staklo, ogledala i tuš kabine. Briše bez tragova i dlačica, bez agresivnih sredstava. Višekratna je i pere se u mašini na 40°C, bez omekšivača, da zadrži upijanje — i zamenjuje gomilu papirnih ubrusa.';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'maybe_ensure' ) );

		add_filter( 'woocommerce_product_get_name', array( $this, 'translate_product_text' ), 10, 2 );
		add_filter( 'woocommerce_product_get_short_description', array( $this, 'translate_product_text' ), 10, 2 );
		add_filter( 'woocommerce_product_get_description', array( $this, 'translate_product_text' ), 10, 2 );
		add_filter( 'the_title', array( $this, 'translate_title' ), 10, 2 );

		add_action( 'woocommerce_single_product_summary', array( $this, 'specs' ), 45 );
	}

	/* ---------------------------------------------------------------------
	 * Creation
	 * ------------------------------------------------------------------ */

	/**
	 * Run ensure() unless this install is already at the current version.
	 *
	 * @return void
	 */
	public function maybe_ensure(): void {
		if ( (int) get_option( self::OPTION, 0 ) >= self::VERSION
			|| ! class_exists( '\WC_Product_Simple' )
			|| ! current_user_can( 'publish_products' ) ) {
			return;
		}

		$this->ensure();
		update_option( self::OPTION, self::VERSION );
	}

	/**
	 * Create the category and every product that does not exist yet.
	 *
	 * @return int Number of products created.
	 */
	public function ensure(): int {
		$category = $this->category_id();
		if ( $category < 1 ) {
			return 0;
		}

		$map     = (array) get_option( self::MAP_OPTION, array() );
		$created = 0;

		foreach ( self::ITEMS as $key => $item ) {
			// Mapped and still there, or already made by hand under the same
			// slug: adopt it, change nothing.
			$existing = (int) ( $map[ $key ] ?? 0 );
			if ( $existing < 1 || ! get_post( $existing ) ) {
				$found    = get_page_by_path( $item['slug'], OBJECT, 'product' );
				$existing = $found instanceof \WP_Post ? (int) $found->ID : 0;
			}

			if ( $existing > 0 ) {
				$map[ $key ] = $existing;
				continue;
			}

			$product = new \WC_Product_Simple();
			$product->set_name( $item['name'] );
			$product->set_slug( $item['slug'] );
			$product->set_status( 'publish' );
			$product->set_catalog_visibility( 'visible' );
			$product->set_regular_price( (string) self::PRICE );
			$product->set_short_description( $item['short'] );
			$product->set_description( self::DESCRIPTION );
			$product->set_category_ids( array( $category ) );
			$id = (int) $product->save();

			if ( $id < 1 ) {
				continue;
			}

			$image = $this->import_image( $item['image'], $id, $item['name'], $item['alt'] );
			if ( $image > 0 ) {
				$product->set_image_id( $image );
				$product->save();
			}

			// Yoast's own post meta, written once like the landing pages'.
			update_post_meta( $id, '_yoast_wpseo_title', $item['seo_title'] );
			update_post_meta( $id, '_yoast_wpseo_metadesc', $item['seo_description'] );

			$map[ $key ] = $id;
			++$created;
		}

		update_option( self::MAP_OPTION, $map );

		return $created;
	}

	/**
	 * Term id of the cloth category, creating it when missing.
	 *
	 * @return int
	 */
	private function category_id(): int {
		$term = get_term_by( 'slug', self::CATEGORY, 'product_cat' );
		if ( $term ) {
			return (int) $term->term_id;
		}

		$created = wp_insert_term( self::CATEGORY_NAME, 'product_cat', array( 'slug' => self::CATEGORY ) );

		return is_wp_error( $created ) ? 0 : (int) $created['term_id'];
	}

	/**
	 * Copy a photograph from the theme into the media library.
	 *
	 * @param string $file      File name under IMAGE_DIR.
	 * @param int    $parent_id Product it belongs to.
	 * @param string $title     Attachment title.
	 * @param string $alt       Alt text.
	 * @return int Attachment id, or 0.
	 */
	private function import_image( string $file, int $parent_id, string $title, string $alt ): int {
		$path = get_template_directory() . '/' . self::IMAGE_DIR . $file;
		if ( ! is_readable( $path ) ) {
			return 0;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		// media_handle_sideload() moves the file it is given, so it gets a copy.
		$tmp = wp_tempnam( $file );
		if ( ! $tmp || ! copy( $path, $tmp ) ) {
			return 0;
		}

		$id = media_handle_sideload(
			array(
				'name'     => $file,
				'tmp_name' => $tmp,
			),
			$parent_id,
			$title
		);

		if ( is_wp_error( $id ) ) {
			if ( file_exists( $tmp ) ) {
				wp_delete_file( $tmp );
			}
			return 0;
		}

		update_post_meta( (int) $id, '_wp_attachment_image_alt', $alt );

		return (int) $id;
	}

	/* ---------------------------------------------------------------------
	 * Storefront
	 * ------------------------------------------------------------------ */

	/**
	 * The cloths on sale, for the landing section and the cloth page.
	 *
	 * Empty until ensure() has run, and without anything unpublished or
	 * unpurchasable — the section is not printed at all then, rather than
	 * offering a button that cannot sell.
	 *
	 * @return array<int,array{key:string,id:int,name:string,price:int,permalink:string,add_to_cart_url:string,image_id:int}>
	 */
	public static function products(): array {
		if ( ! function_exists( 'wc_get_product' ) ) {
			return array();
		}

		$map = (array) get_option( self::MAP_OPTION, array() );
		$out = array();

		foreach ( array_keys( self::ITEMS ) as $key ) {
			$id      = (int) ( $map[ $key ] ?? 0 );
			$product = $id > 0 ? wc_get_product( $id ) : null;

			if ( ! $product instanceof \WC_Product || 'publish' !== $product->get_status() || ! $product->is_purchasable() ) {
				continue;
			}

			$out[] = array(
				'key'             => $key,
				'id'              => $id,
				'name'            => (string) $product->get_name(),
				'price'           => (int) round( (float) $product->get_price() ),
				'permalink'       => (string) get_permalink( $id ),
				'add_to_cart_url' => (string) $product->add_to_cart_url(),
				'image_id'        => (int) $product->get_image_id(),
			);
		}

		return $out;
	}

	/**
	 * Cheapest cloth on sale, for "od X" copy. Falls back to the launch price.
	 *
	 * @param array<int,array<string,mixed>> $rows products() output.
	 * @return int
	 */
	public static function from_price( array $rows ): int {
		$prices = array_filter( array_map( 'intval', array_column( $rows, 'price' ) ) );

		return $prices ? (int) min( $prices ) : self::PRICE;
	}

	/**
	 * Whether a product is one of the cloths.
	 *
	 * @param int $product_id Product id.
	 * @return bool
	 */
	public static function is_cloth( int $product_id ): bool {
		return $product_id > 0 && in_array( $product_id, array_map( 'intval', (array) get_option( self::MAP_OPTION, array() ) ), true );
	}

	/* ---------------------------------------------------------------------
	 * Translation
	 * ------------------------------------------------------------------ */

	/**
	 * Run a cloth's stored name or description through gettext on the storefront.
	 *
	 * The stored text is Serbian and doubles as a msgid, so it translates for
	 * as long as the shop leaves it as seeded; edited text simply has no
	 * translation and prints as typed. Never in wp-admin or a REST request,
	 * where the value may be about to be saved back.
	 *
	 * @param mixed       $value   Stored value.
	 * @param \WC_Product $product Product.
	 * @return mixed
	 */
	public function translate_product_text( $value, $product ) {
		if ( ! is_string( $value ) || '' === $value || ! $this->storefront_request() ) {
			return $value;
		}

		if ( ! $product instanceof \WC_Product || ! self::is_cloth( (int) $product->get_id() ) ) {
			return $value;
		}

		return self::translate( $value );
	}

	/**
	 * Same, for the product title printed through the_title().
	 *
	 * @param mixed $title   Title.
	 * @param mixed $post_id Post id.
	 * @return mixed
	 */
	public function translate_title( $title, $post_id = 0 ) {
		if ( ! is_string( $title ) || ! $this->storefront_request() || ! self::is_cloth( (int) $post_id ) ) {
			return $title;
		}

		return self::translate( $title );
	}

	/**
	 * Gettext lookup for a stored string.
	 *
	 * @param string $text Stored text.
	 * @return string
	 */
	private static function translate( string $text ): string {
		// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- stored copy is its own msgid.
		return __( $text, 'cosypaw' );
	}

	/**
	 * Whether this request renders the storefront (not wp-admin, not REST).
	 *
	 * @return bool
	 */
	private function storefront_request(): bool {
		return ! is_admin() && ! ( defined( 'REST_REQUEST' ) && REST_REQUEST );
	}

	/* ---------------------------------------------------------------------
	 * Product page
	 * ------------------------------------------------------------------ */

	/**
	 * The spec list under a cloth's summary, in the towel pages' own markup.
	 *
	 * @return void
	 */
	public function specs(): void {
		$product = function_exists( 'wc_get_product' ) ? wc_get_product() : null;

		if ( ! $product instanceof \WC_Product || ! self::is_cloth( (int) $product->get_id() ) ) {
			return;
		}

		$specs = array(
			__( 'Materijal', 'cosypaw' )  => __( 'Rebrasta mikrofibra — upija vodu i hvata prašinu.', 'cosypaw' ),
			__( 'Namena', 'cosypaw' )     => __( 'Kuhinja, staklo, ogledala, tuš kabine i radne površine.', 'cosypaw' ),
			__( 'Održavanje', 'cosypaw' ) => __( 'Mašinsko pranje na 40°C, bez omekšivača.', 'cosypaw' ),
			__( 'Dostava', 'cosypaw' )    => __( 'Plaćanje pouzećem, isporuka 2–4 dana širom Srbije.', 'cosypaw' ),
		);

		echo '<dl class="cosypaw-specs">';
		foreach ( $specs as $label => $value ) {
			printf(
				'<div class="cosypaw-specs__row"><dt>%1$s</dt><dd>%2$s</dd></div>',
				esc_html( (string) $label ),
				esc_html( (string) $value )
			);
		}
		echo '</dl>';
	}
}
