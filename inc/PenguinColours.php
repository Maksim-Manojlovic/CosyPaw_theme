<?php
/**
 * PenguinColours — the Pingvin towel in six colours, as one product with a
 * colour choice.
 *
 * The motif towels are one product each. The penguin now comes in six colours
 * (the original blue and five new ones), and unlike the pillows, which are a
 * product per colour folded into one card, it is a real WooCommerce variable
 * product: one page, one colour field, a photograph per colour that replaces
 * the main picture when the colour is chosen.
 *
 * The product exists already — the shop sells it today as a simple towel — so
 * nothing is created. The first time an administrator opens wp-admin, this
 * turns that product into a variable one: a "Boja" attribute, a variation per
 * colour, the blue variation keeping the product's own photograph and price,
 * the new ones importing theirs from assets/penguins/. It is done once per
 * VERSION and is safe to repeat: a colour that already has a variation is
 * left as it is, so a price or photograph changed in wp-admin stays.
 *
 * The parent stays the towel the rest of the theme knows (the same product
 * id in the motif map), so the grid, the builder and the 2+1 package pricing
 * treat any colour as a penguin. Where a tile or an upsell link cannot choose
 * a colour, WooCommerce sends the shopper to the product page to pick one.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

namespace Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PenguinColours.
 */
final class PenguinColours {

	/**
	 * Bump when a colour is added to COLOURS.
	 */
	public const VERSION = 1;

	/**
	 * Option recording which version this install has been brought up to.
	 */
	private const OPTION = 'cosypaw_penguin_colours_version';

	/**
	 * Motif id of the penguin in the product map.
	 */
	public const MOTIF = 'pingvin';

	/**
	 * The attribute's label, a Serbian msgid, and its key on a variation
	 * (what WooCommerce makes of the label: sanitize_title()).
	 */
	public const ATTRIBUTE     = 'Boja';
	public const ATTRIBUTE_KEY = 'boja';

	/**
	 * Theme directory the photographs ship in.
	 */
	private const IMAGE_DIR = 'assets/penguins/';

	/**
	 * Colour on the product today, which keeps the product's own photograph.
	 */
	public const ORIGINAL = 'Plava';

	/**
	 * The colours, in the order the field lists them. Names are Serbian
	 * msgids; `swatch` is the colour of its dot, `image` the photograph under IMAGE_DIR (none for the original
	 * colour) and `alt` what it shows.
	 *
	 * @var array<string,array{swatch:string,image:string,alt:string}>
	 */
	public const COLOURS = array(
		'Plava'       => array(
			'swatch' => '#5f86c9',
			'image' => '',
			'alt'   => '',
		),
		'Braon'       => array(
			'swatch' => '#b08a62',
			'image' => 'pingvin-braon.webp',
			'alt'   => 'Ručno šiven ukrasni peškir u obliku braon pingvina sa belim stopalima i žutim kljunom, na krem podlozi',
		),
		'Roze'        => array(
			'swatch' => '#efb9b9',
			'image' => 'pingvin-roze.webp',
			'alt'   => 'Ručno šiven ukrasni peškir u obliku roze pingvina sa belim stopalima i žutim kljunom, na krem podlozi',
		),
		'Siva'        => array(
			'swatch' => '#9b9594',
			'image' => 'pingvin-sivi.webp',
			'alt'   => 'Ručno šiven ukrasni peškir u obliku sivog pingvina sa belim stopalima i žutim kljunom, na krem podlozi',
		),
		'Tamno plava' => array(
			'swatch' => '#35507f',
			'image' => 'pingvin-tamno-plavi.webp',
			'alt'   => 'Ručno šiven ukrasni peškir u obliku tamno plavog pingvina sa belim stopalima i žutim kljunom, na krem podlozi',
		),
		'Tirkizna'    => array(
			'swatch' => '#a9cdb9',
			'image' => 'pingvin-tirkizni.webp',
			'alt'   => 'Ručno šiven ukrasni peškir u obliku tirkiznog pingvina sa belim stopalima i žutim kljunom, na krem podlozi',
		),
	);

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'maybe_run' ) );

		// The colour names and the field's label are stored in Serbian; the
		// storefront translates them the way it does the rest of the copy.
		add_filter( 'woocommerce_variation_option_name', array( $this, 'translate' ) );
		add_filter( 'woocommerce_attribute_label', array( $this, 'translate_label' ) );

		// The colour dots: on the product page in place of the dropdown, and on
		// the penguin's card in the motif grid.
		add_filter( 'woocommerce_dropdown_variation_attribute_options_html', array( $this, 'swatch_field' ), 10, 2 );
		add_filter( 'cosypaw_catalog_products', array( $this, 'card_colours' ), 20 );
	}

	/**
	 * Translate a colour name on the storefront.
	 *
	 * @param mixed $name Option name.
	 * @return mixed
	 */
	public function translate( $name ) {
		if ( ! is_string( $name ) || ! isset( self::COLOURS[ $name ] ) || ( function_exists( 'is_admin' ) && is_admin() ) ) {
			return $name;
		}

		// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- the stored colour is its own msgid.
		return __( $name, 'cosypaw' );
	}

	/**
	 * Translate the attribute's label on the storefront.
	 *
	 * @param mixed $label Attribute label.
	 * @return mixed
	 */
	public function translate_label( $label ) {
		if ( self::ATTRIBUTE !== $label || ( function_exists( 'is_admin' ) && is_admin() ) ) {
			return $label;
		}

		return __( 'Boja', 'cosypaw' );
	}

	/**
	 * The product page's colour field: dots that drive the dropdown, which
	 * stays in the markup (WooCommerce reads it) and is what the page shows
	 * until the script has run.
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
			if ( isset( self::COLOURS[ $value ] ) ) {
				$options[] = array(
					'value'  => $value,
					'label'  => (string) $this->translate( $value ),
					'swatch' => self::COLOURS[ $value ]['swatch'],
				);
			}
		}

		if ( count( $options ) < 2 ) {
			return $html;
		}

		$select = (string) ( $args['id'] ?? self::ATTRIBUTE_KEY );

		return Swatches::radios( $options, (string) ( $args['selected'] ?? '' ), $select )
			. '<div class="colour-swatches__select">' . $html . '</div>';
	}

	/**
	 * Give the penguin's catalogue row its colours, for the dots on its card.
	 *
	 * Each colour carries what the card needs to switch to it without a page
	 * load: the photograph, and the variation to put in the cart. A colour that
	 * cannot be bought is left out.
	 *
	 * @param mixed $products Catalogue rows.
	 * @return mixed
	 */
	public function card_colours( $products ) {
		if ( ! is_array( $products ) || ! function_exists( 'wc_get_product' ) ) {
			return $products;
		}

		foreach ( $products as $index => $row ) {
			if ( ! is_array( $row ) || self::MOTIF !== ( $row['id'] ?? '' ) || empty( $row['product_id'] ) ) {
				continue;
			}

			$parent = wc_get_product( (int) $row['product_id'] );
			if ( ! $parent instanceof \WC_Product || ! $parent->is_type( 'variable' ) ) {
				continue;
			}

			$colours = array();
			foreach ( (array) $parent->get_children() as $child_id ) {
				$colour = $this->card_colour( $parent, (int) $child_id );
				if ( null !== $colour ) {
					$colours[ $colour['value'] ] = $colour;
				}
			}

			// In the order the product page lists them.
			$ordered = array();
			foreach ( array_keys( self::COLOURS ) as $name ) {
				if ( isset( $colours[ $name ] ) ) {
					$ordered[] = $colours[ $name ];
				}
			}

			if ( count( $ordered ) > 1 ) {
				$products[ $index ]['colours'] = $ordered;
			}
		}

		return $products;
	}

	/**
	 * One variation as a card colour, or null when it cannot be sold.
	 *
	 * @param \WC_Product $parent The penguin.
	 * @param int         $id     Variation id.
	 * @return array<string,mixed>|null
	 */
	private function card_colour( $parent, int $id ): ?array {
		$variation = wc_get_product( $id );
		if ( ! $variation instanceof \WC_Product || ! $variation->is_purchasable() || ! $variation->is_in_stock() ) {
			return null;
		}

		$attributes = (array) $variation->get_attributes();
		$value      = (string) ( $attributes[ self::ATTRIBUTE_KEY ] ?? '' );
		if ( ! isset( self::COLOURS[ $value ] ) ) {
			return null;
		}

		$image  = (int) $variation->get_image_id();
		$source = $image > 0 ? wp_get_attachment_image_src( $image, 'woocommerce_single' ) : false;

		return array(
			'value'        => $value,
			'label'        => (string) $this->translate( $value ),
			'swatch'       => self::COLOURS[ $value ]['swatch'],
			'variation_id' => $id,
			'image'        => is_array( $source ) ? (string) $source[0] : '',
			'srcset'       => $image > 0 ? (string) wp_get_attachment_image_srcset( $image, 'woocommerce_single' ) : '',
			'url'          => esc_url_raw(
				add_query_arg(
					array(
						'add-to-cart'  => $parent->get_id(),
						'variation_id' => $id,
						'attribute_' . self::ATTRIBUTE_KEY => $value,
					)
				)
			),
		);
	}

	/**
	 * Run once per VERSION, for someone allowed to edit the products.
	 *
	 * @return void
	 */
	public function maybe_run(): void {
		if ( (int) get_option( self::OPTION, 0 ) >= self::VERSION
			|| ! class_exists( '\WC_Product_Variable' )
			|| ! current_user_can( 'edit_products' ) ) {
			return;
		}

		// A product that is not there yet is not an error: the version is left
		// alone, so the first admin page after it is created runs this.
		if ( $this->convert() ) {
			update_option( self::OPTION, self::VERSION );
		}
	}

	/**
	 * Make the penguin a variable product with a variation per colour.
	 *
	 * @return bool Whether the penguin product was found and brought up to date.
	 */
	public function convert(): bool {
		$map = (array) get_option( WooCommerce::PRODUCT_MAP_OPTION, array() );
		$id  = (int) ( $map[ self::MOTIF ] ?? 0 );

		$product = $id > 0 ? wc_get_product( $id ) : null;
		if ( ! $product instanceof \WC_Product ) {
			return false;
		}

		// What the simple product is today, before it becomes the parent.
		$price    = (string) $product->get_regular_price();
		$image    = (int) $product->get_image_id();
		$in_stock = $product->is_in_stock();

		if ( ! $product->is_type( 'variable' ) ) {
			wp_set_object_terms( $id, 'variable', 'product_type' );
			$product = new \WC_Product_Variable( $id );
		}

		$this->set_attribute( $product );
		$product->save();

		$existing = $this->variation_colours( $product );
		$imported = array();

		foreach ( self::COLOURS as $colour => $data ) {
			if ( isset( $existing[ $colour ] ) ) {
				continue;
			}

			$original = self::ORIGINAL === $colour;
			$variant  = new \WC_Product_Variation();
			$variant->set_parent_id( $id );
			$variant->set_status( 'publish' );
			$variant->set_attributes( array( self::ATTRIBUTE_KEY => $colour ) );
			$variant->set_regular_price( $price );
			$variant->set_manage_stock( false );
			$variant->set_stock_status( ( $original && ! $in_stock ) ? 'outofstock' : 'instock' );

			$attachment = $original ? $image : $this->import( $data['image'], $id, $colour, $data['alt'], $imported );
			if ( $attachment > 0 ) {
				$variant->set_image_id( $attachment );
			}

			$variant->save();
		}

		// Re-sync the parent's price range and stock from its variations.
		if ( is_callable( array( \WC_Product_Variable::class, 'sync' ) ) ) {
			\WC_Product_Variable::sync( $id );
		}

		return true;
	}

	/**
	 * Give the parent its "Boja" attribute, keeping any other attribute the
	 * shop added, and open it on the colour the product has always been.
	 *
	 * @param \WC_Product $product The parent.
	 * @return void
	 */
	private function set_attribute( $product ): void {
		$attributes = array();
		foreach ( (array) $product->get_attributes() as $key => $attribute ) {
			if ( self::ATTRIBUTE_KEY !== (string) $key ) {
				$attributes[ $key ] = $attribute;
			}
		}

		$attribute = new \WC_Product_Attribute();
		$attribute->set_id( 0 );
		$attribute->set_name( self::ATTRIBUTE );
		$attribute->set_options( array_keys( self::COLOURS ) );
		$attribute->set_position( count( $attributes ) );
		$attribute->set_visible( true );
		$attribute->set_variation( true );

		$attributes[ self::ATTRIBUTE_KEY ] = $attribute;
		$product->set_attributes( $attributes );

		$defaults = (array) $product->get_default_attributes();
		if ( empty( $defaults[ self::ATTRIBUTE_KEY ] ) ) {
			$defaults[ self::ATTRIBUTE_KEY ] = self::ORIGINAL;
			$product->set_default_attributes( $defaults );
		}
	}

	/**
	 * Colours that already have a variation.
	 *
	 * @param \WC_Product $product The parent.
	 * @return array<string,int> Colour => variation id.
	 */
	private function variation_colours( $product ): array {
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
			$colour     = (string) ( $attributes[ self::ATTRIBUTE_KEY ] ?? '' );
			if ( '' !== $colour ) {
				$found[ $colour ] = (int) $child_id;
			}
		}

		return $found;
	}

	/**
	 * Copy a photograph from the theme into the media library.
	 *
	 * @param string             $file     File name under IMAGE_DIR.
	 * @param int                $parent   Product it belongs to.
	 * @param string             $colour   Colour, for the attachment title.
	 * @param string             $alt      Alt text.
	 * @param array<string,int>  $imported File => attachment id, this run.
	 * @return int Attachment id, or 0.
	 */
	private function import( string $file, int $parent, string $colour, string $alt, array &$imported ): int {
		if ( isset( $imported[ $file ] ) ) {
			return $imported[ $file ];
		}

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
			$parent,
			'Pingvin, ' . mb_strtolower( $colour )
		);

		if ( is_wp_error( $id ) ) {
			if ( file_exists( $tmp ) ) {
				wp_delete_file( $tmp );
			}
			return 0;
		}

		update_post_meta( (int) $id, '_wp_attachment_image_alt', $alt );
		$imported[ $file ] = (int) $id;

		return (int) $id;
	}
}
