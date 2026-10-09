<?php
/**
 * GiftBundlePicker — "Složi svoj paket" on a gift bundle's page, and the
 * chosen contents from there into the cart and onto the order.
 *
 * A bundle suggests its contents (GiftBundles::ITEMS `contains`), and the
 * product page lets every part be swapped like for like: another colour of a
 * pillow, any other towel shape, another colour of a long towel. The picker
 * is plain radio buttons inside WooCommerce's own add-to-cart form, so it
 * posts with the form and works without script; BundlePicker.js only keeps
 * the name and photo beside each part in step with the pick.
 *
 * What was picked is checked against GiftBundles::slots() before anything
 * reaches the cart — never trusted from the request — and every bundle line
 * carries its packing list, suggested or picked, so the order says what to
 * pack. An add from a bundle card posts no picks and gets the suggestion.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

namespace Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GiftBundlePicker.
 */
final class GiftBundlePicker {

	/**
	 * Form field the picks are posted in: cosypaw_bundle[slot index] = key.
	 */
	public const FIELD = 'cosypaw_bundle';

	/**
	 * Cart item data key, and the order line meta key, of the packing list.
	 */
	public const ITEM_KEY = 'cosypaw_bundle';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'render' ) );

		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'validate' ), 10, 2 );
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_cart_item_data' ), 10, 2 );
		add_filter( 'woocommerce_get_item_data', array( $this, 'cart_item_rows' ), 10, 2 );

		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'save_order_item' ), 10, 3 );
		add_filter( 'woocommerce_order_item_display_meta_key', array( $this, 'order_meta_key' ), 10, 2 );
		add_filter( 'woocommerce_order_item_display_meta_value', array( $this, 'order_meta_value' ), 10, 2 );
	}

	/**
	 * The picker, inside the add-to-cart form of a bundle that can be packed.
	 *
	 * After a failed add the page comes back with the picks still posted, and
	 * they stay picked rather than resetting to the suggestion.
	 *
	 * @return void
	 */
	public function render(): void {
		$product = function_exists( 'wc_get_product' ) ? wc_get_product() : null;
		$key     = $product instanceof \WC_Product ? GiftBundles::key_for( (int) $product->get_id() ) : '';

		if ( '' === $key || ! GiftBundles::packable( $key ) ) {
			return;
		}

		get_template_part(
			'template-parts/bundle-picker',
			null,
			array(
				'slots'  => GiftBundles::slots( $key ),
				'picked' => $this->picked(),
			)
		);
	}

	/**
	 * Refuse a bundle whose picks cannot be packed.
	 *
	 * A colour can sell out between the page loading and the button being
	 * pressed; packing something else in its place would ship a gift nobody
	 * chose, so the add is refused with a note to pick again.
	 *
	 * @param bool|mixed $passed     Whether the add has passed so far.
	 * @param int|mixed  $product_id Product being added.
	 * @return bool|mixed
	 */
	public function validate( $passed, $product_id ) {
		if ( ! $passed ) {
			return $passed;
		}

		$key = GiftBundles::key_for( (int) $product_id );
		if ( '' === $key || null !== GiftBundles::resolve( $key, $this->picked() ) ) {
			return $passed;
		}

		wc_add_notice( __( 'Nešto iz paketa trenutno nije dostupno — izaberi drugu boju ili oblik.', 'cosypaw' ), 'error' );

		return false;
	}

	/**
	 * Carry the packing list on the cart item.
	 *
	 * Bundles packed differently stay separate cart lines (the data changes the
	 * cart item hash); the same packing stacks as quantity.
	 *
	 * @param array<string,mixed> $cart_item_data Existing cart item data.
	 * @param int|mixed           $product_id     Product being added.
	 * @return array<string,mixed>
	 */
	public function add_cart_item_data( array $cart_item_data, $product_id ): array {
		$key   = GiftBundles::key_for( (int) $product_id );
		$parts = '' !== $key ? GiftBundles::resolve( $key, $this->picked() ) : null;

		if ( null !== $parts ) {
			$cart_item_data[ self::ITEM_KEY ] = GiftBundles::encode( $parts );
		}

		return $cart_item_data;
	}

	/**
	 * The packing list under the bundle in the cart and the checkout, a row
	 * per part.
	 *
	 * @param array<int,array<string,string>> $item_data Existing item data rows.
	 * @param array<string,mixed>             $cart_item Cart item.
	 * @return array<int,array<string,string>>
	 */
	public function cart_item_rows( array $item_data, array $cart_item ): array {
		if ( empty( $cart_item[ self::ITEM_KEY ] ) ) {
			return $item_data;
		}

		foreach ( GiftBundles::describe( GiftBundles::decode( (string) $cart_item[ self::ITEM_KEY ] ) ) as $part ) {
			$item_data[] = array(
				'key'   => $part['label'],
				'value' => $part['name'],
			);
		}

		return $item_data;
	}

	/**
	 * Persist the packing list onto the order line, as keys: the label and the
	 * names are resolved when the order is viewed, in the viewer's language.
	 *
	 * @param object $item   Order line item (\WC_Order_Item_Product at runtime).
	 * @param string $key    Cart item key.
	 * @param array  $values Cart item values.
	 * @return void
	 */
	public function save_order_item( $item, $key, $values ): void {
		unset( $key );

		if ( ! empty( $values[ self::ITEM_KEY ] ) ) {
			$item->add_meta_data( self::ITEM_KEY, (string) $values[ self::ITEM_KEY ], true );
		}
	}

	/**
	 * Label the packing list meta row.
	 *
	 * @param string $display_key Default display key.
	 * @param object $meta        Meta row (\WC_Meta_Data at runtime).
	 * @return string
	 */
	public function order_meta_key( $display_key, $meta ): string {
		if ( $meta && self::ITEM_KEY === $meta->key ) {
			return __( 'U paketu', 'cosypaw' );
		}

		return (string) $display_key;
	}

	/**
	 * The packing list in words, a line per part — on the order screen, in the
	 * emails and on the thank-you page alike.
	 *
	 * @param string $display_value Default display value.
	 * @param object $meta          Meta row (\WC_Meta_Data at runtime).
	 * @return string
	 */
	public function order_meta_value( $display_value, $meta ): string {
		if ( ! $meta || self::ITEM_KEY !== $meta->key ) {
			return (string) $display_value;
		}

		$lines = array();
		foreach ( GiftBundles::describe( GiftBundles::decode( (string) $meta->value ) ) as $part ) {
			$lines[] = esc_html( $part['label'] . ': ' . $part['name'] );
		}

		return implode( '<br>', $lines );
	}

	/**
	 * The posted picks, slot index => key. Only shaped here: whether a pick
	 * is allowed is GiftBundles::resolve()'s to say.
	 *
	 * @return array<int,string>
	 */
	private function picked(): array {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- WooCommerce's add-to-cart carries no nonce; every pick is checked against the slot's choices.
		$raw = isset( $_REQUEST[ self::FIELD ] ) ? wp_unslash( $_REQUEST[ self::FIELD ] ) : array();

		if ( ! is_array( $raw ) ) {
			return array();
		}

		$picked = array();
		foreach ( $raw as $i => $key ) {
			if ( is_int( $i ) && is_string( $key ) ) {
				$picked[ $i ] = sanitize_text_field( $key );
			}
		}

		return $picked;
	}
}
