<?php
/**
 * CheckoutSetup — the shop's payment and shipping configuration.
 *
 * CosyPaw takes no card payments: the buyer pays cash, either to the courier
 * or in person at pickup. That is a two-line WooCommerce configuration, but
 * WooCommerce ships with every gateway disabled and no shipping zone, and an
 * install in that state answers checkout with "no payment methods available".
 * Leaving it to be clicked in wp-admin means every fresh install (and the
 * production deploy) starts broken, so the configuration is written here and
 * applied by the seeder.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

namespace Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CheckoutSetup.
 *
 * Instance side: runtime filters that translate the stored labels.
 * Static side:   configure(), the one-shot idempotent write. See ProductSeeder.
 */
final class CheckoutSetup {

	/**
	 * Option key: marks the zone as already built, so a later re-seed does not
	 * recreate a zone the shop has since edited or deleted on purpose.
	 *
	 * @var string
	 */
	public const ZONE_OPTION = 'cosypaw_shipping_zone_id';

	/**
	 * Package whose price sets the free-delivery threshold. The landing page
	 * promises free shipping on the Trio and nothing below it.
	 *
	 * @var string
	 */
	private const FREE_SHIPPING_PACKAGE = 'trio';

	/**
	 * Constructor — registers the runtime label filters.
	 */
	public function __construct() {
		add_filter( 'woocommerce_gateway_title', array( $this, 'translate_gateway_title' ), 10, 2 );
		add_filter( 'woocommerce_gateway_description', array( $this, 'translate_gateway_description' ), 10, 2 );
		add_filter( 'woocommerce_shipping_rate_label', array( $this, 'translate_shipping_label' ), 20 );

		// The thank-you page and the order emails print the gateway's
		// "instructions" setting, which is stored text like the rest.
		add_filter( 'woocommerce_thankyou_order_received_text', array( $this, 'translate_stored_text' ) );
		add_filter( 'woocommerce_get_privacy_policy_text', array( $this, 'translate_stored_text' ) );
		// Front end only. The payment settings screen reads this same option and
		// writes back whatever it read, so filtering it in wp-admin would let an
		// administrator save the translation over the stored source string —
		// freezing one language into the setting for every visitor.
		if ( ! is_admin() ) {
			add_filter( 'option_woocommerce_cod_settings', array( $this, 'translate_cod_settings' ) );
		}

		// Over the free-shipping threshold both the courier rate and the free
		// rate are zero, and offering the same price twice reads as a bug.
		add_filter( 'woocommerce_package_rates', array( $this, 'hide_paid_delivery_when_free' ), 10 );
	}

	/**
	 * Translate a gateway title stored in the source language.
	 *
	 * Gateway settings live in the DB as plain text, which would freeze whatever
	 * language the admin used at setup into every visitor's checkout. configure()
	 * stores the Serbian strings, which are msgids in /languages, and they are
	 * resolved per request here — the same arrangement the seeded product titles
	 * use in WooCommerce::product_name().
	 *
	 * @param string $title Stored title.
	 * @param string $id    Gateway id.
	 * @return string
	 */
	public function translate_gateway_title( $title, $id ): string {
		return 'cod' === $id ? $this->translate( (string) $title ) : (string) $title;
	}

	/**
	 * Translate a gateway description stored in the source language.
	 *
	 * @param string $description Stored description.
	 * @param string $id          Gateway id.
	 * @return string
	 */
	public function translate_gateway_description( $description, $id ): string {
		return 'cod' === $id ? $this->translate( (string) $description ) : (string) $description;
	}

	/**
	 * Translate a shipping rate label stored in the source language.
	 *
	 * @param string $label Stored label.
	 * @return string
	 */
	public function translate_shipping_label( $label ): string {
		return $this->translate( (string) $label );
	}

	/**
	 * Translate a stored setting that WooCommerce prints verbatim.
	 *
	 * @param string $text Stored text.
	 * @return string
	 */
	public function translate_stored_text( $text ): string {
		return $this->translate( (string) $text );
	}

	/**
	 * Translate the cash-on-delivery instructions as the settings are read.
	 *
	 * The instructions reach the thank-you page and the customer email through
	 * the gateway object rather than a filter of their own, so the only seam
	 * before that is the option itself.
	 *
	 * @param mixed $settings Stored gateway settings.
	 * @return mixed
	 */
	public function translate_cod_settings( $settings ) {
		if ( is_array( $settings ) && ! empty( $settings['instructions'] ) ) {
			$settings['instructions'] = $this->translate( (string) $settings['instructions'] );
		}

		return $settings;
	}

	/**
	 * Run a stored string through the theme text domain.
	 *
	 * Unknown strings — anything the shop typed in wp-admin — come back
	 * unchanged, which is what an untranslated msgid does anyway.
	 *
	 * @param string $text Stored string.
	 * @return string
	 */
	private function translate( string $text ): string {
		if ( '' === $text ) {
			return $text;
		}

		// translators: dynamic label stored by CheckoutSetup::configure().
		return __( $text, 'cosypaw' ); // phpcs:ignore WordPress.WP.I18n
	}

	/**
	 * Drop the paid courier rate from a package that already qualifies for free
	 * delivery.
	 *
	 * Pickup is left alone: it is a different way to receive the order, not a
	 * cheaper one.
	 *
	 * @param array<string,object> $rates Shipping rates (\WC_Shipping_Rate at runtime).
	 * @return array<string,object>
	 */
	public function hide_paid_delivery_when_free( array $rates ): array {
		$has_free = false;
		foreach ( $rates as $rate ) {
			if ( 'free_shipping' === $rate->get_method_id() ) {
				$has_free = true;
				break;
			}
		}

		if ( ! $has_free ) {
			return $rates;
		}

		foreach ( $rates as $key => $rate ) {
			if ( 'flat_rate' === $rate->get_method_id() ) {
				unset( $rates[ $key ] );
			}
		}

		return $rates;
	}

	/**
	 * Enable cash on delivery and build the Serbia shipping zone. Idempotent.
	 *
	 * Both halves are skipped once they exist, so re-running the seeder never
	 * overwrites a price or a label the shop has since changed in wp-admin.
	 *
	 * @return string[] Human-readable list of what this run changed.
	 */
	public static function configure(): array {
		$done = array();

		if ( self::enable_cod() ) {
			$done[] = 'cod';
		}

		if ( self::create_shipping_zone() ) {
			$done[] = 'shipping';
		}

		return $done;
	}

	/**
	 * Turn on the cash-on-delivery gateway.
	 *
	 * enable_for_methods stays empty on purpose: cash is how both the courier
	 * delivery and the in-person pickup are paid, so the gateway must offer
	 * itself against every shipping method.
	 *
	 * @return bool True when this call enabled it.
	 */
	private static function enable_cod(): bool {
		$settings = get_option( 'woocommerce_cod_settings' );

		if ( is_array( $settings ) && ! empty( $settings['enabled'] ) ) {
			return false;
		}

		update_option(
			'woocommerce_cod_settings',
			array(
				'enabled'            => 'yes',
				'title'              => 'Plaćanje pouzećem',
				'description'        => 'Plaćaš gotovinom pri preuzimanju — kuriru na vratima ili nama lično.',
				'instructions'       => 'Pripremi iznos u gotovini za trenutak preuzimanja.',
				'enable_for_methods' => array(),
				'enable_for_virtual' => 'yes',
			)
		);

		return true;
	}

	/**
	 * Create the Serbia zone with pickup, courier delivery and free delivery.
	 *
	 * The courier rate carries no cost. The shop does not quote postage up
	 * front — the buyer settles it with the courier — so a number here would be
	 * a number the order cannot honour.
	 *
	 * @return bool True when this call created the zone.
	 */
	private static function create_shipping_zone(): bool {
		if ( ! class_exists( '\WC_Shipping_Zone' ) ) {
			return false;
		}

		$existing = (int) get_option( self::ZONE_OPTION, 0 );
		if ( $existing > 0 && null !== \WC_Shipping_Zones::get_zone( $existing ) ) {
			return false;
		}

		$zone = new \WC_Shipping_Zone();
		$zone->set_zone_name( 'Srbija' );
		$zone->set_zone_order( 1 );
		$zone->add_location( 'RS', 'country' );
		$zone->save();

		$zone_id = (int) $zone->get_id();
		if ( $zone_id < 1 ) {
			return false;
		}

		self::add_method(
			$zone,
			'local_pickup',
			array(
				'title'      => 'Lično preuzimanje',
				'tax_status' => 'none',
				'cost'       => '',
			)
		);

		self::add_method(
			$zone,
			'flat_rate',
			array(
				'title'      => 'Dostava kurirskom službom (poštarina se plaća kuriru)',
				'tax_status' => 'none',
				'cost'       => '0',
			)
		);

		$threshold = self::free_shipping_threshold();
		if ( $threshold > 0 ) {
			self::add_method(
				$zone,
				'free_shipping',
				array(
					'title'            => 'Besplatna dostava',
					'requires'         => 'min_amount',
					'min_amount'       => (string) $threshold,
					'ignore_discounts' => 'no',
				)
			);
		}

		update_option( self::ZONE_OPTION, $zone_id );

		return true;
	}

	/**
	 * The cart subtotal from which delivery is on the shop.
	 *
	 * Read from the live Trio product rather than from Catalog: Catalog holds
	 * the seed price, and WooCommerce owns the price the moment the product
	 * exists — the shop has already moved these once. A threshold copied from
	 * the seed would sooner or later sit under the Duo and hand out free
	 * delivery the landing page never promised.
	 *
	 * @return int Threshold in store currency, or 0 when it cannot be resolved.
	 */
	private static function free_shipping_threshold(): int {
		$map = (array) get_option( WooCommerce::PACKAGE_MAP_OPTION, array() );
		$id  = isset( $map[ self::FREE_SHIPPING_PACKAGE ] ) ? (int) $map[ self::FREE_SHIPPING_PACKAGE ] : 0;

		if ( $id < 1 || ! function_exists( 'wc_get_product' ) ) {
			return 0;
		}

		$product = wc_get_product( $id );
		if ( ! $product instanceof \WC_Product ) {
			return 0;
		}

		$price = $product->get_price();

		return is_numeric( $price ) ? (int) round( (float) $price ) : 0;
	}

	/**
	 * Attach one shipping method to a zone and write its settings.
	 *
	 * WC_Shipping_Zone::add_shipping_method() only creates the instance row; the
	 * instance's own settings live in a separate per-instance option that the
	 * method reads on load.
	 *
	 * @param \WC_Shipping_Zone   $zone     Target zone.
	 * @param string              $method   Shipping method id.
	 * @param array<string,mixed> $settings Instance settings.
	 * @return void
	 */
	private static function add_method( \WC_Shipping_Zone $zone, string $method, array $settings ): void {
		$instance_id = (int) $zone->add_shipping_method( $method );

		if ( $instance_id > 0 ) {
			update_option( sprintf( 'woocommerce_%s_%d_settings', $method, $instance_id ), $settings );
		}
	}
}
