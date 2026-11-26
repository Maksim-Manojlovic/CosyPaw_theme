<?php
/**
 * Dugonja — the Cuddle Puff long pillow, 100 cm, as one product with four
 * plush friends to choose from.
 *
 * The other pillows are a product per colour folded into one card. The
 * Dugonja is a real WooCommerce variable product, like the penguin: one page,
 * one "Drugar" field, a photograph per friend that replaces the main picture
 * when the friend is chosen. The choice is made by clicking the friends'
 * heads — small round crops of their own photographs (HEAD_DIR) — on the
 * product page in place of the dropdown, and on its card on the pillow page.
 *
 * The product is brought up to date once per VERSION, the first time an
 * administrator opens wp-admin, in the pillows' category: the parent with the
 * group photograph as its image, a variation per friend at PRICE with its own
 * photograph, every photograph in the gallery. No friend is preselected, so
 * the page opens on the group photograph. A product that already exists
 * (recorded, or made by hand under the same slug) is adopted and only given
 * what it is missing: price, text and stock are the shop's in wp-admin.
 *
 * The copy says what the photographs show and what the shop has confirmed:
 * 100 cm, antiallergenic plush, keeps its shape. Nothing about washing.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

namespace Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dugonja.
 */
final class Dugonja {

	/**
	 * Bump when a friend is added to FRIENDS, or the product needs bringing up
	 * to date again (2: the first run stopped after the parent, before the
	 * photographs and the variations).
	 */
	public const VERSION = 2;

	/**
	 * Option recording which version this install has been brought up to.
	 */
	private const OPTION = 'cosypaw_dugonja_version';

	/**
	 * Option: the product's id.
	 */
	public const ID_OPTION = 'cosypaw_dugonja_product';

	/**
	 * The product. Name and copy are Serbian database values that double as
	 * msgids, so the storefront translates them while they are unedited.
	 */
	public const NAME = 'Cuddle Puff dugi jastuk Dugonja';
	public const SLUG = 'cuddle-puff-dugi-jastuk-dugonja';

	/**
	 * Launch price (RSD) of every friend. Written once, at creation; wp-admin
	 * owns it after.
	 */
	public const PRICE = 4490;

	/**
	 * Group key of its card among the pillows' cards.
	 */
	public const GROUP = 'dugonja';

	/**
	 * The attribute's label, a Serbian msgid, and its key on a variation (what
	 * WooCommerce makes of the label: sanitize_title()).
	 */
	public const ATTRIBUTE     = 'Drugar';
	public const ATTRIBUTE_KEY = 'drugar';

	/**
	 * Theme directories the photographs and the heads ship in.
	 */
	public const IMAGE_DIR = 'assets/pillows/';
	public const HEAD_DIR  = 'assets/pillows/glave/';

	/**
	 * The product's own photograph (all four friends together), file => alt.
	 */
	public const IMAGE = array( 'cuddle-puff-dugi-jastuk-dugonja-ekipa.webp' => 'Nasmejana devojka na krevetu sa četiri duga plišana jastuka Dugonja od 100 cm: lav, žuti mačak, sivi mačak i roze prasence' );

	/**
	 * Further photographs, after the friends' own in the gallery, file => alt.
	 */
	public const GALLERY = array( 'cuddle-puff-dugi-jastuk-dugonja-izaberi-drugara.webp' => 'Tri duga plišana jastuka Dugonja jedan pored drugog: žuti mačak, sivi mačak i roze prasence' );

	/**
	 * The friends, in the order the field lists them. Names are Serbian
	 * msgids and the variation's attribute value; `swatch` is a CSS colour for
	 * where a head cannot be shown, `image` the photograph under IMAGE_DIR,
	 * `head` its round crop under HEAD_DIR, `alt` what the photograph shows.
	 *
	 * @var array<string,array{swatch:string,image:string,head:string,alt:string}>
	 */
	public const FRIENDS = array(
		'Lavko'   => array(
			'swatch' => '#c9965a',
			'image'  => 'cuddle-puff-dugi-jastuk-dugonja-lavko.webp',
			'head'   => 'dugonja-lavko.webp',
			'alt'    => 'Dugi plišani jastuk Dugonja Lavko u obliku lava sa braon grivom, belom njuškom i vezenim natpisom Sweet na stomaku, na beloj pozadini',
		),
		'Žuća'    => array(
			'swatch' => '#d9a066',
			'image'  => 'cuddle-puff-dugi-jastuk-dugonja-zuca.webp',
			'head'   => 'dugonja-zuca.webp',
			'alt'    => 'Dugi plišani jastuk Dugonja Žuća u obliku žutog mačka sa belim stomakom i podignutim šapicama, na beloj pozadini',
		),
		'Sivko'   => array(
			'swatch' => '#a9a6a4',
			'image'  => 'cuddle-puff-dugi-jastuk-dugonja-sivko.webp',
			'head'   => 'dugonja-sivko.webp',
			'alt'    => 'Dugi plišani jastuk Dugonja Sivko u obliku sivog mačka sa belim stomakom i podignutim šapicama, na beloj pozadini',
		),
		'Slatkiš' => array(
			'swatch' => '#f1bcc0',
			'image'  => 'cuddle-puff-dugi-jastuk-dugonja-slatkis.webp',
			'head'   => 'dugonja-slatkis.webp',
			'alt'    => 'Dugi plišani jastuk Dugonja Slatkiš u obliku roze prasenceta sa vezenim natpisom Sweet na stomaku, na beloj pozadini',
		),
	);

	/**
	 * Short description.
	 */
	public const SHORT = 'Dugi plišani jastuk od 100 cm za spavanje, maženje i odmor. Izaberi svog drugara: lava Lavka, žutog mačka Žuću, sivog mačka Sivka ili prasence Slatkiša.';

	/**
	 * Long description.
	 */
	public const DESCRIPTION = 'Cuddle Puff dugi jastuk Dugonja je plišani drugar dugačak 100 cm — za grljenje, spavanje na boku i odmor. Dolazi u četiri lika: Lavko je lav sa braon grivom i belom njuškom, Žuća je žuti mačak sa prugicama na glavi, Sivko je sivi mačak, a Slatkiš je roze prasence. Mačići imaju podignute šapice sa roze jastučićima, a Lavko i Slatkiš na stomaku vezen natpis „Sweet". Mekani antialergijski pliš nežan je za kožu, a jastuk se ne gužva i zadržava oblik. Lepo stoji na krevetu ili sofi, kao oslonac za leđa dok čitaš ili gledaš TV i kao plišani drug u dečijoj sobi — a odličan je i kao poklon za decu, tinejdžere i odrasle.';

	/**
	 * Yoast fields, written once at creation.
	 */
	public const SEO_TITLE       = 'Dugonja | Dugi Plišani Jastuk 100 cm za Spavanje i Maženje';
	public const SEO_DESCRIPTION = 'Dugi plišani jastuk Dugonja od 100 cm u obliku lava, mačke ili prasenceta. Antialergijski pliš, za spavanje i maženje. Plaćanje pouzećem!';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'maybe_run' ) );

		// Name, copy, the field's label and the friends' names are stored in
		// Serbian; the storefront translates them like the rest of the copy.
		add_filter( 'woocommerce_product_get_name', array( $this, 'translate_product_text' ), 10, 2 );
		add_filter( 'woocommerce_product_get_short_description', array( $this, 'translate_product_text' ), 10, 2 );
		add_filter( 'woocommerce_product_get_description', array( $this, 'translate_product_text' ), 10, 2 );
		add_filter( 'woocommerce_product_variation_get_name', array( $this, 'translate_variation_name' ), 10, 2 );
		add_filter( 'the_title', array( $this, 'translate_title' ), 10, 2 );
		add_filter( 'woocommerce_variation_option_name', array( $this, 'translate_friend' ) );
		add_filter( 'woocommerce_attribute_label', array( $this, 'translate_label' ) );

		// The heads in place of the dropdown, and the spec list.
		add_filter( 'woocommerce_dropdown_variation_attribute_options_html', array( $this, 'swatch_field' ), 10, 2 );
		add_action( 'woocommerce_single_product_summary', array( $this, 'specs' ), 45 );
	}

	/* ---------------------------------------------------------------------
	 * Lookups
	 * ------------------------------------------------------------------ */

	/**
	 * The product's id, or 0 before it has been created.
	 *
	 * @return int
	 */
	public static function product_id(): int {
		return (int) get_option( self::ID_OPTION, 0 );
	}

	/**
	 * Whether a product id is the Dugonja.
	 *
	 * @param int $product_id Product id.
	 * @return bool
	 */
	public static function owns( int $product_id ): bool {
		return $product_id > 0 && self::product_id() === $product_id;
	}

	/**
	 * URL of a friend's head.
	 *
	 * @param string $friend FRIENDS key.
	 * @return string
	 */
	public static function head_url( string $friend ): string {
		$head = self::FRIENDS[ $friend ]['head'] ?? '';

		return '' === $head ? '' : get_template_directory_uri() . '/' . self::HEAD_DIR . $head;
	}

	/**
	 * The friends that have a variation.
	 *
	 * @param \WC_Product $product The parent.
	 * @return array<string,int> Friend => variation id.
	 */
	private static function variations( $product ): array {
		$found = array();

		if ( ! is_callable( array( $product, 'get_children' ) ) ) {
			return $found;
		}

		foreach ( (array) $product->get_children() as $child_id ) {
			$variation = wc_get_product( (int) $child_id );
			if ( ! $variation instanceof \WC_Product ) {
				continue;
			}

			$attributes = (array) $variation->get_attributes();
			$friend     = (string) ( $attributes[ self::ATTRIBUTE_KEY ] ?? '' );
			if ( '' !== $friend ) {
				$found[ $friend ] = (int) $child_id;
			}
		}

		return $found;
	}

	/* ---------------------------------------------------------------------
	 * Storefront
	 * ------------------------------------------------------------------ */

	/**
	 * The Dugonja as one more group for the pillows' swatch cards: a variant
	 * per friend that can be bought, each with its photograph, its own
	 * add-to-cart (the variation) and its head for the swatch.
	 *
	 * Null until the product exists, or while it is not published or has no
	 * friend to sell, so no card is printed for it.
	 *
	 * @return array{key:string,legend:string,variants:array<int,array<string,mixed>>}|null
	 */
	public static function group(): ?array {
		if ( ! function_exists( 'wc_get_product' ) ) {
			return null;
		}

		$id     = self::product_id();
		$parent = $id > 0 ? wc_get_product( $id ) : null;
		if ( ! $parent instanceof \WC_Product || 'publish' !== $parent->get_status() || ! $parent->is_type( 'variable' ) ) {
			return null;
		}

		$found     = self::variations( $parent );
		$name      = (string) $parent->get_name();
		$permalink = (string) get_permalink( $id );
		$variants  = array();

		foreach ( self::FRIENDS as $friend => $data ) {
			$variation = isset( $found[ $friend ] ) ? wc_get_product( $found[ $friend ] ) : null;
			if ( ! $variation instanceof \WC_Product || ! $variation->is_purchasable() ) {
				continue;
			}

			$label      = self::friend_label( $friend );
			$variants[] = array(
				'key'             => sanitize_title( $friend ),
				'id'              => (int) $found[ $friend ],
				/* translators: 1: product name, 2: the friend, e.g. "Lavko". */
				'name'            => sprintf( __( '%1$s, %2$s', 'cosypaw' ), $name, $label ),
				'price'           => (int) round( (float) $variation->get_price() ),
				// WooCommerce opens the page on the friend named in the query.
				'permalink'       => add_query_arg( 'attribute_' . self::ATTRIBUTE_KEY, rawurlencode( $friend ), $permalink ),
				'add_to_cart_url' => (string) $variation->add_to_cart_url(),
				'image_id'        => (int) $variation->get_image_id(),
				'gallery_ids'     => array(),
				'sold_out'        => Stock::sold_out( $variation ),
				'color'           => $label,
				'swatch'          => $data['swatch'],
				'head'            => self::head_url( $friend ),
			);
		}

		if ( ! $variants ) {
			return null;
		}

		return array(
			'key'      => self::GROUP,
			'legend'   => __( 'Izaberi drugara', 'cosypaw' ),
			'variants' => $variants,
		);
	}

	/**
	 * The product page's "Drugar" field: the heads, which drive the dropdown.
	 * The dropdown stays in the markup (WooCommerce reads it) and is what the
	 * page shows until the script has run.
	 *
	 * @param mixed $html Dropdown HTML.
	 * @param mixed $args Field arguments (attribute, options, selected, id).
	 * @return mixed
	 */
	public function swatch_field( $html, $args ) {
		if ( ! is_string( $html ) || ! is_array( $args ) || self::ATTRIBUTE !== ( $args['attribute'] ?? '' ) ) {
			return $html;
		}

		$options = array();
		foreach ( (array) ( $args['options'] ?? array() ) as $value ) {
			$value = (string) $value;
			if ( isset( self::FRIENDS[ $value ] ) ) {
				$options[] = array(
					'value'  => $value,
					'label'  => self::friend_label( $value ),
					'swatch' => self::FRIENDS[ $value ]['swatch'],
					'image'  => self::head_url( $value ),
				);
			}
		}

		if ( count( $options ) < 2 ) {
			return $html;
		}

		// WooCommerce passes the id empty and works it out itself.
		$select = ! empty( $args['id'] ) ? (string) $args['id'] : self::ATTRIBUTE_KEY;

		return Swatches::radios( $options, (string) ( $args['selected'] ?? '' ), $select, __( 'Izaberi drugara', 'cosypaw' ) )
			. '<div class="colour-swatches__select">' . $html . '</div>';
	}

	/**
	 * The spec list under the product's summary, in the towel pages' markup.
	 *
	 * @return void
	 */
	public function specs(): void {
		$product = function_exists( 'wc_get_product' ) ? wc_get_product() : null;
		if ( ! $product instanceof \WC_Product || ! self::owns( (int) $product->get_id() ) ) {
			return;
		}

		echo '<dl class="cosypaw-specs">';
		foreach ( self::spec_rows() as $label => $value ) {
			printf(
				'<div class="cosypaw-specs__row"><dt>%1$s</dt><dd>%2$s</dd></div>',
				esc_html( (string) $label ),
				esc_html( (string) $value )
			);
		}
		echo '</dl>';
	}

	/**
	 * The rows of the spec list.
	 *
	 * @return array<string,string> Translated label => translated value.
	 */
	public static function spec_rows(): array {
		return array(
			__( 'Drugari', 'cosypaw' )    => __( 'Lavko (lav), Žuća (žuti mačak), Sivko (sivi mačak) i Slatkiš (prasence).', 'cosypaw' ),
			__( 'Dužina', 'cosypaw' )     => __( '100 cm.', 'cosypaw' ),
			__( 'Materijal', 'cosypaw' )  => __( 'Mekani antialergijski pliš, nežan za kožu.', 'cosypaw' ),
			__( 'Oblik', 'cosypaw' )      => __( 'Ne gužva se i zadržava oblik.', 'cosypaw' ),
			__( 'Idealan za', 'cosypaw' ) => __( 'Spavanje na boku, maženje, čitanje, gledanje TV-a, dečiju sobu i poklon.', 'cosypaw' ),
			__( 'Dostava', 'cosypaw' )    => __( 'Plaćanje pouzećem, isporuka 2–4 dana širom Srbije.', 'cosypaw' ),
		);
	}

	/* ---------------------------------------------------------------------
	 * Translation
	 * ------------------------------------------------------------------ */

	/**
	 * A friend's name as the storefront shows it.
	 *
	 * @param string $friend FRIENDS key.
	 * @return string
	 */
	private static function friend_label( string $friend ): string {
		// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- the stored name is its own msgid.
		return isset( self::FRIENDS[ $friend ] ) ? __( $friend, 'cosypaw' ) : $friend;
	}

	/**
	 * Translate a friend's name in WooCommerce's own field and cart lines.
	 *
	 * @param mixed $name Option name.
	 * @return mixed
	 */
	public function translate_friend( $name ) {
		if ( ! is_string( $name ) || ! isset( self::FRIENDS[ $name ] ) || ! self::storefront() ) {
			return $name;
		}

		return self::friend_label( $name );
	}

	/**
	 * Translate the attribute's label.
	 *
	 * @param mixed $label Attribute label.
	 * @return mixed
	 */
	public function translate_label( $label ) {
		if ( self::ATTRIBUTE !== $label || ! self::storefront() ) {
			return $label;
		}

		return __( 'Drugar', 'cosypaw' );
	}

	/**
	 * Translate the product's stored name or copy.
	 *
	 * @param mixed       $value   Stored value.
	 * @param \WC_Product $product Product.
	 * @return mixed
	 */
	public function translate_product_text( $value, $product ) {
		if ( ! is_string( $value ) || '' === $value || ! self::storefront()
			|| ! $product instanceof \WC_Product || ! self::owns( (int) $product->get_id() ) ) {
			return $value;
		}

		// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- stored copy is its own msgid.
		return __( $value, 'cosypaw' );
	}

	/**
	 * A variation's name, "Cuddle Puff dugi jastuk Dugonja - Lavko" as
	 * WooCommerce generates it, translated in both its parts. A name the shop
	 * has changed is left as typed.
	 *
	 * @param mixed       $value   Stored name.
	 * @param \WC_Product $product Variation.
	 * @return mixed
	 */
	public function translate_variation_name( $value, $product ) {
		if ( ! is_string( $value ) || ! self::storefront() || ! $product instanceof \WC_Product
			|| ! is_callable( array( $product, 'get_parent_id' ) ) || ! self::owns( (int) $product->get_parent_id() ) ) {
			return $value;
		}

		$prefix = self::NAME . ' - ';
		$friend = 0 === strpos( $value, $prefix ) ? substr( $value, strlen( $prefix ) ) : '';
		if ( ! isset( self::FRIENDS[ $friend ] ) ) {
			return $value;
		}

		return __( self::NAME, 'cosypaw' ) . ' - ' . self::friend_label( $friend ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
	}

	/**
	 * Same, for the product title printed through the_title().
	 *
	 * @param mixed $title   Title.
	 * @param mixed $post_id Post id.
	 * @return mixed
	 */
	public function translate_title( $title, $post_id = 0 ) {
		if ( ! is_string( $title ) || ! self::storefront() || ! self::owns( (int) $post_id ) ) {
			return $title;
		}

		// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- stored title is its own msgid.
		return __( $title, 'cosypaw' );
	}

	/**
	 * Whether this request renders the storefront (not wp-admin, not REST),
	 * where a stored value is never about to be saved back.
	 *
	 * @return bool
	 */
	private static function storefront(): bool {
		return ! is_admin() && ! ( defined( 'REST_REQUEST' ) && REST_REQUEST );
	}

	/* ---------------------------------------------------------------------
	 * Creation
	 * ------------------------------------------------------------------ */

	/**
	 * Run once per VERSION, for someone allowed to publish products.
	 *
	 * @return void
	 */
	public function maybe_run(): void {
		if ( (int) get_option( self::OPTION, 0 ) >= self::VERSION
			|| ! class_exists( '\WC_Product_Variable' )
			|| ! current_user_can( 'publish_products' ) ) {
			return;
		}

		if ( $this->ensure() > 0 ) {
			update_option( self::OPTION, self::VERSION );
		}
	}

	/**
	 * Create the product, or adopt the one already there, and give it whatever
	 * it is missing.
	 *
	 * Safe to repeat. A product found (recorded, or under the same slug) is
	 * kept as it is; only what it does not have yet is added: a variation for
	 * a friend that has none, the main photograph, the gallery, the Yoast
	 * fields. A price, photograph or text changed in wp-admin stays.
	 *
	 * @return int The product's id, or 0 when it could not be made.
	 */
	public function ensure(): int {
		$id = self::product_id();
		if ( $id < 1 || ! get_post( $id ) ) {
			$found = get_page_by_path( self::SLUG, OBJECT, 'product' );
			$id    = $found instanceof \WP_Post ? (int) $found->ID : 0;
		}

		if ( $id < 1 ) {
			$id = $this->create();
			if ( $id < 1 ) {
				return 0;
			}
		}

		update_option( self::ID_OPTION, $id );

		// A product made by hand as a simple one is the shop's: left alone.
		$product = wc_get_product( $id );
		if ( $product instanceof \WC_Product && $product->is_type( 'variable' ) ) {
			$this->complete( $product );
		}

		return $id;
	}

	/**
	 * The parent product, with its copy, category and "Drugar" attribute.
	 *
	 * @return int Its id, or 0.
	 */
	private function create(): int {
		$category = $this->category_id();
		if ( $category < 1 ) {
			return 0;
		}

		$product = new \WC_Product_Variable();
		$product->set_name( self::NAME );
		$product->set_slug( self::SLUG );
		$product->set_status( 'publish' );
		$product->set_catalog_visibility( 'visible' );
		$product->set_short_description( self::SHORT );
		$product->set_description( self::DESCRIPTION );
		$product->set_category_ids( array( $category ) );
		$product->set_attributes( array( self::ATTRIBUTE_KEY => $this->attribute() ) );

		return (int) $product->save();
	}

	/**
	 * Add what the product is missing: a variation per friend at PRICE with its
	 * photograph, the group photograph as the main image, every photograph in
	 * the gallery, the Yoast fields.
	 *
	 * @param \WC_Product $product The parent.
	 * @return void
	 */
	private function complete( $product ): void {
		$id    = (int) $product->get_id();
		$found = self::variations( $product );

		if ( (int) $product->get_image_id() < 1 ) {
			$file  = (string) array_key_first( self::IMAGE );
			$image = $this->import( $file, $id, self::IMAGE[ $file ] );
			if ( $image > 0 ) {
				$product->set_image_id( $image );
			}
		}

		// The friends' photographs, in FRIENDS order: the ones already on a
		// variation, and the ones imported for a new variation.
		$photos = array();
		foreach ( self::FRIENDS as $friend => $data ) {
			if ( isset( $found[ $friend ] ) ) {
				$variation = wc_get_product( $found[ $friend ] );
				$photos[]  = $variation instanceof \WC_Product ? (int) $variation->get_image_id() : 0;
				continue;
			}

			$photo = $this->import( $data['image'], $id, $data['alt'] );

			$variation = new \WC_Product_Variation();
			$variation->set_parent_id( $id );
			$variation->set_status( 'publish' );
			$variation->set_attributes( array( self::ATTRIBUTE_KEY => $friend ) );
			$variation->set_regular_price( (string) self::PRICE );
			$variation->set_manage_stock( false );
			$variation->set_stock_status( 'instock' );
			if ( $photo > 0 ) {
				$variation->set_image_id( $photo );
			}
			$variation->save();

			$photos[] = $photo;
		}

		// A friend's photograph shows only once it is chosen, so in the gallery
		// they are thumbnails beside the group photograph from the start. A
		// gallery the shop has already arranged is left as it is.
		if ( ! $product->get_gallery_image_ids() ) {
			foreach ( self::GALLERY as $file => $alt ) {
				$photos[] = $this->import( (string) $file, $id, (string) $alt );
			}
			$product->set_gallery_image_ids( array_values( array_filter( $photos ) ) );
		}

		$product->save();

		// The parent's price and stock from its variations.
		if ( is_callable( array( \WC_Product_Variable::class, 'sync' ) ) ) {
			\WC_Product_Variable::sync( $id );
		}

		// Yoast's own post meta, written once like the other lines'.
		if ( '' === (string) get_post_meta( $id, '_yoast_wpseo_title', true ) ) {
			update_post_meta( $id, '_yoast_wpseo_title', self::SEO_TITLE );
		}
		if ( '' === (string) get_post_meta( $id, '_yoast_wpseo_metadesc', true ) ) {
			update_post_meta( $id, '_yoast_wpseo_metadesc', self::SEO_DESCRIPTION );
		}
	}

	/**
	 * The "Drugar" attribute: one option per friend, used for variations.
	 *
	 * @return \WC_Product_Attribute
	 */
	private function attribute() {
		$attribute = new \WC_Product_Attribute();
		$attribute->set_id( 0 );
		$attribute->set_name( self::ATTRIBUTE );
		$attribute->set_options( array_keys( self::FRIENDS ) );
		$attribute->set_position( 0 );
		$attribute->set_visible( true );
		$attribute->set_variation( true );

		return $attribute;
	}

	/**
	 * Term id of the pillows' category, creating it when missing.
	 *
	 * @return int
	 */
	private function category_id(): int {
		$term = get_term_by( 'slug', Pillows::CATEGORY, 'product_cat' );
		if ( $term ) {
			return (int) $term->term_id;
		}

		$created = wp_insert_term( 'Cuddle Puff jastuci', 'product_cat', array( 'slug' => Pillows::CATEGORY ) );

		return is_wp_error( $created ) ? 0 : (int) $created['term_id'];
	}

	/**
	 * Copy a photograph from the theme into the media library. Every one is
	 * titled with the product's name, as on the other lines.
	 *
	 * @param string $file      File name under IMAGE_DIR.
	 * @param int    $parent_id Product it belongs to.
	 * @param string $alt       Alt text.
	 * @return int Attachment id, or 0.
	 */
	private function import( string $file, int $parent_id, string $alt ): int {
		$path = get_template_directory() . '/' . self::IMAGE_DIR . $file;
		if ( '' === $file || ! is_readable( $path ) ) {
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
			self::NAME
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
}
