<?php
/**
 * ProductLine — a family of simple products the theme creates and sells
 * outside the towel packages: the magične krpice, the Cuddle Puff pillows and
 * the long towels.
 *
 * A line is a registry (ITEMS) plus the machinery every line shares:
 *
 *  - Creation. The products are created once per registry version, the first
 *    time an administrator opens wp-admin — the same way \Theme\Pages makes
 *    the landing pages, because a deploy ships the theme and never the
 *    database. Photographs ship under IMAGE_DIR and are imported into the
 *    media library with descriptive alt text; each product gets its Yoast
 *    title and description. A product that already exists (mapped, or made
 *    by hand under the same slug) is adopted and never edited: price, text
 *    and stock are the shop's to change in wp-admin afterwards.
 *  - Storefront rows. Only what is published and purchasable is listed, so a
 *    card never offers a button that cannot sell.
 *  - Translation. Names and copy are stored in Serbian and double as msgids,
 *    so the storefront translates them for as long as they are left as
 *    created.
 *  - A spec list under the product page summary, in the towel pages' markup.
 *
 * A line is never filed under the towel category (WooCommerce::TOWEL_CATEGORY):
 * a product there is picked up by register_towel() and becomes a motif — in the
 * gallery, in the builder, and counted by BundlePricing towards a 2+1 package.
 * Each line lives in its own category and none of that touches it.
 *
 * Subclasses declare these constants (PHP has no abstract constants, so they
 * are read through static:: and a line that forgets one fails loudly):
 *
 *  - VERSION        int    Bump when an item is added to ITEMS.
 *  - OPTION         string Option recording the version this install is at.
 *  - MAP_OPTION     string Option: ITEMS key => product id.
 *  - CATEGORY       string Product category slug.
 *  - CATEGORY_NAME  string Its name, a Serbian database value.
 *  - PRICE          int    Launch price (RSD), written once at creation.
 *  - IMAGE_DIR      string Theme directory the photographs ship in.
 *  - DESCRIPTION    string Long description shared by the line's items.
 *  - ITEMS          array  Key => item, see item() for the shape.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

namespace Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ProductLine.
 */
abstract class ProductLine {

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

	/**
	 * The rows of the spec list for one of this line's products.
	 *
	 * @param string $key ITEMS key of the product on screen.
	 * @return array<string,string> Translated label => translated value.
	 */
	abstract protected function spec_rows( string $key ): array;

	/* ---------------------------------------------------------------------
	 * Creation
	 * ------------------------------------------------------------------ */

	/**
	 * Run ensure() unless this install is already at the current version.
	 *
	 * @return void
	 */
	public function maybe_ensure(): void {
		if ( (int) get_option( static::OPTION, 0 ) >= static::VERSION
			|| ! class_exists( '\WC_Product_Simple' )
			|| ! current_user_can( 'publish_products' ) ) {
			return;
		}

		$this->ensure();
		update_option( static::OPTION, static::VERSION );
	}

	/**
	 * Create the category and every product that does not exist yet.
	 *
	 * Item keys (all strings unless noted):
	 *  - name, slug, short, seo_title, seo_description: the product's own.
	 *  - image, alt: its photograph under IMAGE_DIR and that photo's alt text.
	 *  - price (int, optional): launch price when it is not the line's PRICE.
	 *  - gallery (optional): file => alt of further photographs, e.g. the
	 *    whole family together. A file shared by several items is imported
	 *    once and the same attachment reused.
	 *  - holds (optional): ITEMS keys of the products a set contains. A set
	 *    imports nothing; the photographs of what it holds become its image
	 *    and gallery, so those items have to come before it in ITEMS.
	 *
	 * @return int Number of products created.
	 */
	public function ensure(): int {
		$category = $this->category_id();
		if ( $category < 1 ) {
			return 0;
		}

		$map      = (array) get_option( static::MAP_OPTION, array() );
		$images   = array();
		$imported = array();
		$created  = 0;

		foreach ( static::ITEMS as $key => $item ) {
			// Mapped and still there, or already made by hand under the same
			// slug: adopt it, change nothing.
			$existing = (int) ( $map[ $key ] ?? 0 );
			if ( $existing < 1 || ! get_post( $existing ) ) {
				$found    = get_page_by_path( $item['slug'], OBJECT, 'product' );
				$existing = $found instanceof \WP_Post ? (int) $found->ID : 0;
			}

			if ( $existing > 0 ) {
				$map[ $key ]    = $existing;
				$images[ $key ] = (int) get_post_thumbnail_id( $existing );
				continue;
			}

			$product = new \WC_Product_Simple();
			$product->set_name( $item['name'] );
			$product->set_slug( $item['slug'] );
			$product->set_status( 'publish' );
			$product->set_catalog_visibility( 'visible' );
			$product->set_regular_price( (string) ( $item['price'] ?? static::PRICE ) );
			$product->set_short_description( $item['short'] );
			$product->set_description( static::DESCRIPTION );
			$product->set_category_ids( array( $category ) );
			$id = (int) $product->save();

			if ( $id < 1 ) {
				continue;
			}

			// A set shows the photographs of what it holds, the first as its
			// image and the rest in its gallery, instead of importing a
			// second copy of the same file.
			$held = array_values( array_filter( array_map( static fn( string $held ): int => $images[ $held ] ?? 0, $item['holds'] ?? array() ) ) );
			if ( $held ) {
				$image   = array_shift( $held );
				$gallery = $held;
			} else {
				$image   = $this->import_once( $imported, $item['image'], $id, $item['name'], $item['alt'] );
				$gallery = array();
				foreach ( $item['gallery'] ?? array() as $file => $alt ) {
					$gallery[] = $this->import_once( $imported, (string) $file, $id, $item['name'], (string) $alt );
				}
				$gallery = array_values( array_filter( $gallery ) );
			}

			if ( $image > 0 ) {
				$product->set_image_id( $image );
				if ( $gallery ) {
					$product->set_gallery_image_ids( $gallery );
				}
				$product->save();
			}
			$images[ $key ] = $image;

			// Yoast's own post meta, written once like the landing pages'.
			update_post_meta( $id, '_yoast_wpseo_title', $item['seo_title'] );
			update_post_meta( $id, '_yoast_wpseo_metadesc', $item['seo_description'] );

			$map[ $key ] = $id;
			++$created;
		}

		update_option( static::MAP_OPTION, $map );

		return $created;
	}

	/**
	 * Term id of the line's category, creating it when missing.
	 *
	 * @return int
	 */
	private function category_id(): int {
		$term = get_term_by( 'slug', static::CATEGORY, 'product_cat' );
		if ( $term ) {
			return (int) $term->term_id;
		}

		$created = wp_insert_term( static::CATEGORY_NAME, 'product_cat', array( 'slug' => static::CATEGORY ) );

		return is_wp_error( $created ) ? 0 : (int) $created['term_id'];
	}

	/**
	 * import_image(), remembering what this run already imported so a file
	 * shared by several items lands in the media library once.
	 *
	 * @param array<string,int> $imported  File => attachment id, this run.
	 * @param string            $file      File name under IMAGE_DIR.
	 * @param int               $parent_id Product it is first attached to.
	 * @param string            $title     Attachment title.
	 * @param string            $alt       Alt text.
	 * @return int Attachment id, or 0.
	 */
	private function import_once( array &$imported, string $file, int $parent_id, string $title, string $alt ): int {
		if ( ! isset( $imported[ $file ] ) ) {
			$imported[ $file ] = $this->import_image( $file, $parent_id, $title, $alt );
		}

		return $imported[ $file ];
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
		$path = get_template_directory() . '/' . static::IMAGE_DIR . $file;
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
	 * The line's single products on sale, in ITEMS order.
	 *
	 * Empty until ensure() has run, and without anything unpublished or
	 * unpurchasable — a section built on it is not printed at all then,
	 * rather than offering a button that cannot sell. Sets (items that hold
	 * others) are left out; a line that sells one answers for it itself.
	 *
	 * @return array<int,array{key:string,id:int,name:string,price:int,permalink:string,add_to_cart_url:string,image_id:int,gallery_ids:array<int,int>}>
	 */
	public static function products(): array {
		$out = array();

		foreach ( static::ITEMS as $key => $item ) {
			if ( ! empty( $item['holds'] ) ) {
				continue;
			}

			$row = static::row( (string) $key );
			if ( null !== $row ) {
				$out[] = $row;
			}
		}

		return $out;
	}

	/**
	 * Products on sale, grouped for the colour-swatch cards.
	 *
	 * For lines whose items are colours of one design: each item names its
	 * `group` (an ITEMS-wide key), its `color` (a Serbian msgid) and its
	 * `swatch` (a CSS colour). Groups come in the order their first item
	 * appears in ITEMS; a group with nothing on sale is left out, so no card
	 * is printed for it.
	 *
	 * @param array<int,array<string,mixed>> $rows products() output.
	 * @return array<int,array{key:string,variants:array<int,array<string,mixed>>}> Each variant is a products() row plus `color` (translated) and `swatch`.
	 */
	public static function groups( array $rows ): array {
		$groups = array();

		foreach ( $rows as $row ) {
			$item = static::ITEMS[ $row['key'] ] ?? null;
			if ( null === $item || empty( $item['group'] ) ) {
				continue;
			}

			$group = (string) $item['group'];
			if ( ! isset( $groups[ $group ] ) ) {
				$groups[ $group ] = array(
					'key'      => $group,
					'variants' => array(),
				);
			}

			$row['color']                   = self::translate( (string) ( $item['color'] ?? '' ) );
			$row['swatch']                  = (string) ( $item['swatch'] ?? '' );
			$groups[ $group ]['variants'][] = $row;
		}

		return array_values( $groups );
	}

	/**
	 * One ITEMS entry as a storefront row, or null while it cannot be sold.
	 *
	 * @param string $key ITEMS key.
	 * @return array{key:string,id:int,name:string,price:int,permalink:string,add_to_cart_url:string,image_id:int,gallery_ids:array<int,int>}|null
	 */
	protected static function row( string $key ): ?array {
		if ( ! function_exists( 'wc_get_product' ) ) {
			return null;
		}

		$map     = (array) get_option( static::MAP_OPTION, array() );
		$id      = (int) ( $map[ $key ] ?? 0 );
		$product = $id > 0 ? wc_get_product( $id ) : null;

		if ( ! $product instanceof \WC_Product || 'publish' !== $product->get_status() || ! $product->is_purchasable() ) {
			return null;
		}

		return array(
			'key'             => $key,
			'id'              => $id,
			'name'            => (string) $product->get_name(),
			'price'           => (int) round( (float) $product->get_price() ),
			'permalink'       => (string) get_permalink( $id ),
			'add_to_cart_url' => (string) $product->add_to_cart_url(),
			'image_id'        => (int) $product->get_image_id(),
			'gallery_ids'     => is_callable( array( $product, 'get_gallery_image_ids' ) ) ? array_map( 'intval', (array) $product->get_gallery_image_ids() ) : array(),
		);
	}

	/**
	 * Cheapest product on sale, for "od X" copy. Falls back to the launch price.
	 *
	 * @param array<int,array<string,mixed>> $rows products() output.
	 * @return int
	 */
	public static function from_price( array $rows ): int {
		$prices = array_filter( array_map( 'intval', array_column( $rows, 'price' ) ) );

		return $prices ? (int) min( $prices ) : static::PRICE;
	}

	/**
	 * Whether a product belongs to this line.
	 *
	 * @param int $product_id Product id.
	 * @return bool
	 */
	public static function owns( int $product_id ): bool {
		return '' !== static::key_of( $product_id );
	}

	/**
	 * ITEMS key a product id is mapped to, or ''.
	 *
	 * @param int $product_id Product id.
	 * @return string
	 */
	protected static function key_of( int $product_id ): string {
		if ( $product_id < 1 ) {
			return '';
		}

		$key = array_search( $product_id, array_map( 'intval', (array) get_option( static::MAP_OPTION, array() ) ), true );

		return false === $key ? '' : (string) $key;
	}

	/* ---------------------------------------------------------------------
	 * Translation
	 * ------------------------------------------------------------------ */

	/**
	 * Run a product's stored name or description through gettext on the
	 * storefront.
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

		if ( ! $product instanceof \WC_Product || ! static::owns( (int) $product->get_id() ) ) {
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
		if ( ! is_string( $title ) || ! $this->storefront_request() || ! static::owns( (int) $post_id ) ) {
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
	protected static function translate( string $text ): string {
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
	 * The spec list under one of the line's products, in the towel pages'
	 * own markup.
	 *
	 * @return void
	 */
	public function specs(): void {
		$product = function_exists( 'wc_get_product' ) ? wc_get_product() : null;
		$key     = $product instanceof \WC_Product ? static::key_of( (int) $product->get_id() ) : '';

		if ( '' === $key ) {
			return;
		}

		echo '<dl class="cosypaw-specs">';
		foreach ( $this->spec_rows( $key ) as $label => $value ) {
			printf(
				'<div class="cosypaw-specs__row"><dt>%1$s</dt><dd>%2$s</dd></div>',
				esc_html( (string) $label ),
				esc_html( (string) $value )
			);
		}
		echo '</dl>';
	}
}
