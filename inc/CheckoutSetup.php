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
	 * Option key: the threshold this theme last wrote into the shipping zone.
	 *
	 * The zone is the shop's to edit, so the sync fires on a change to
	 * FREE_SHIPPING_MIN and never on a difference from it. Without the marker
	 * the two rules are indistinguishable, and every page load would undo an
	 * amount an administrator had deliberately typed in wp-admin.
	 *
	 * @var string
	 */
	public const APPLIED_OPTION = 'cosypaw_free_shipping_applied';

	/**
	 * Bumped whenever the sync itself changes what it is able to do.
	 *
	 * The marker records a version alongside the amount, so a shop that stored
	 * "the threshold is applied" under an older, weaker sync re-runs once under
	 * the new one. Version 1 trusted ZONE_OPTION and wrote the marker even when
	 * it had found no zone — which on a shop whose zone it had never recorded
	 * meant one silent failure disabled every later attempt, permanently.
	 *
	 * @var int
	 */
	private const SYNC_VERSION = 2;

	/**
	 * Cart subtotal from which delivery is on the shop (RSD).
	 *
	 * A flat amount, not a package price. It used to be read off the live Trio
	 * product, which tied the promise to one bundle: reprice the Trio and the
	 * threshold moved under it, and two towels bought loose for more than the
	 * Trio still paid postage. The offer is now the same for every basket —
	 * spend this much, however you reach it, and delivery is free — and the
	 * Trio card only claims free shipping while its own price clears the bar
	 * (Catalog::packages() derives that, it is no longer authored).
	 *
	 * @var int
	 */
	public const FREE_SHIPPING_MIN = 2000;

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

		// WooCommerce's own delivery block does not belong on this cart. The
		// shop quotes no postage — it is settled with the courier — so the
		// block had nothing to say beyond a rate of zero, an instruction to
		// come back at checkout, and a "calculate shipping" form asking for an
		// address the cart has no use for. All three sat directly above the
		// theme's own free-delivery row, which states the one fact that
		// matters. Cart only: the checkout still shows the method being
		// chosen, which is where the choice is actually made.
		add_filter( 'woocommerce_cart_ready_to_calc_shipping', array( $this, 'hide_cart_delivery_block' ), 20 );

		// ...and with the block gone, cart-totals.php falls through to the
		// shipping calculator instead. Off for the same reason.
		add_filter( 'option_woocommerce_enable_shipping_calc', array( $this, 'hide_cart_shipping_calculator' ), 20 );

		// A threshold change used to reach checkout only through the seeder,
		// which meant every page of the site could advertise a bar the cart did
		// not enforce until someone remembered to click Tools → CosyPaw Seeder.
		// The copy and the charge have to move together, so the zone catches up
		// on its own. One option read per request; a write only on the request
		// that first sees a new amount.
		add_action( 'init', array( $this, 'maybe_sync_free_shipping' ), 20 );
	}

	/**
	 * Keep WooCommerce's delivery block off the cart page.
	 *
	 * @param bool $ready Whether WooCommerce would show and calculate delivery.
	 * @return bool
	 */
	public function hide_cart_delivery_block( $ready ): bool {
		return $this->on_cart() ? false : (bool) $ready;
	}

	/**
	 * Keep the "calculate shipping" form off the cart page.
	 *
	 * Filters the stored option rather than the setting itself, so wp-admin
	 * still reads and writes the shop's real choice — the same reasoning that
	 * keeps translate_cod_settings() off the admin screens.
	 *
	 * @param mixed $enabled Stored 'yes'/'no'.
	 * @return mixed
	 */
	public function hide_cart_shipping_calculator( $enabled ) {
		return $this->on_cart() ? 'no' : $enabled;
	}

	/**
	 * Whether this is the cart page being rendered for a visitor.
	 *
	 * @return bool
	 */
	private function on_cart(): bool {
		return ! is_admin() && function_exists( 'is_cart' ) && is_cart();
	}

	/**
	 * Push a changed threshold into the shipping zone, once.
	 *
	 * Fires on a change to FREE_SHIPPING_MIN, never on a mere difference from
	 * it: an amount an administrator edited in wp-admin stays edited, because
	 * what is compared is the last value *this theme* wrote. See APPLIED_OPTION
	 * and SYNC_VERSION.
	 *
	 * @return void
	 */
	public function maybe_sync_free_shipping(): void {
		$threshold = self::free_shipping_threshold();
		$marker    = self::SYNC_VERSION . ':' . $threshold;

		if ( (string) get_option( self::APPLIED_OPTION, '' ) === $marker ) {
			return;
		}

		// Only on success. Writing it regardless is what let one failed run —
		// a shop whose zone this theme had never recorded — mark the threshold
		// as applied and never look again, leaving every page advertising a
		// bar WooCommerce granted nowhere. A failure is a handful of option
		// reads, and it stops the moment there is a zone to attach to.
		if ( self::sync_free_shipping() ) {
			update_option( self::APPLIED_OPTION, $marker );
		}
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
		} elseif ( self::sync_free_shipping() ) {
			// The zone survives a re-seed, so a threshold changed in the code
			// only reaches checkout through here.
			$done[] = 'shipping-threshold';
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
	 * Public because it is quoted, not just enforced: the announcement bar, the
	 * gift banner, the FAQ and the package cards all print this number, and a
	 * threshold the copy states from one source while the shipping zone charges
	 * from another is the exact bug this replaced.
	 *
	 * @return int Threshold in store currency; 0 disables free delivery.
	 */
	public static function free_shipping_threshold(): int {
		/**
		 * Filter the free-delivery threshold.
		 *
		 * @param int $min Cart subtotal from which delivery is free, in RSD.
		 */
		$min = (int) apply_filters( 'cosypaw_free_shipping_min', self::FREE_SHIPPING_MIN );

		return max( 0, $min );
	}

	/**
	 * Point an existing zone's free-delivery rate at the current threshold.
	 *
	 * create_shipping_zone() is a one-shot: it writes min_amount when it builds
	 * the zone and then never touches it again, so the amount stayed at
	 * whatever the shop was charging on the day it was seeded. Raising the
	 * threshold in the code alone would have moved every sentence on the site
	 * and none of the arithmetic at checkout. This re-runs with the seeder and
	 * closes that gap.
	 *
	 * A zone carrying no free-delivery method at all gets one. That is not a
	 * rebuild: create_shipping_zone() skips the method whenever the threshold
	 * cannot be resolved, which is how a shop ends up with a Serbia zone, a
	 * courier rate, and every page promising free delivery that nothing in
	 * WooCommerce grants. The zone itself is resolved by delivery_zone(),
	 * which no longer depends on this theme having built it.
	 *
	 * Otherwise only min_amount is written; the title and the `requires` mode
	 * are the shop's to edit in wp-admin.
	 *
	 * @return bool True when this call changed the zone.
	 */
	private static function sync_free_shipping(): bool {
		if ( ! class_exists( '\WC_Shipping_Zones' ) ) {
			return false;
		}

		$zone = self::delivery_zone();
		if ( null === $zone || ! is_callable( array( $zone, 'get_shipping_methods' ) ) ) {
			return false;
		}

		$threshold = self::free_shipping_threshold();
		$changed   = false;
		$found     = false;

		foreach ( (array) $zone->get_shipping_methods() as $instance_id => $method ) {
			if ( 'free_shipping' !== ( $method->id ?? '' ) ) {
				continue;
			}

			$found    = true;
			$key      = sprintf( 'woocommerce_free_shipping_%d_settings', (int) $instance_id );
			$settings = (array) get_option( $key, array() );

			if ( (string) ( $settings['min_amount'] ?? '' ) === (string) $threshold ) {
				continue;
			}

			$settings['min_amount'] = (string) $threshold;
			$settings['requires']   = 'min_amount';
			update_option( $key, $settings );
			$changed = true;
		}

		if ( ! $found && $threshold > 0 ) {
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
			$changed = true;
		}

		return $changed;
	}

	/**
	 * The zone that will actually rate an order to the shop's own country.
	 *
	 * ZONE_OPTION first, because that is the zone this theme built. It is not
	 * enough on its own: a shop whose zone was made by hand in wp-admin — or
	 * seeded before the option existed — records nothing there, and the sync
	 * then found no zone, attached no free-delivery method, and left the whole
	 * site advertising a threshold WooCommerce could not honour. That is the
	 * state cosypaw.rs was in.
	 *
	 * The fallback asks WooCommerce the same question it asks itself when it
	 * rates a cart: which zone covers this destination. The destination is the
	 * store's base country, since that is who the shop ships to. Zone 0, "rest
	 * of the world", is the documented answer when nothing else matches and is
	 * a perfectly good place to hang the method.
	 *
	 * A zone found this way is recorded, so the lookup happens once and the
	 * shop keeps one zone rather than growing another on the next run.
	 *
	 * @return \WC_Shipping_Zone|null
	 */
	private static function delivery_zone() {
		$zone_id = (int) get_option( self::ZONE_OPTION, 0 );

		if ( $zone_id > 0 ) {
			$zone = \WC_Shipping_Zones::get_zone( $zone_id );

			if ( null !== $zone ) {
				return $zone;
			}
		}

		if ( ! is_callable( array( '\WC_Shipping_Zones', 'get_zone_matching_package' ) ) ) {
			return null;
		}

		$country = function_exists( 'wc_get_base_location' ) ? (array) wc_get_base_location() : array();

		$zone = \WC_Shipping_Zones::get_zone_matching_package(
			array(
				'destination' => array(
					'country'  => (string) ( $country['country'] ?? '' ),
					'state'    => (string) ( $country['state'] ?? '' ),
					'postcode' => '',
				),
			)
		);

		if ( null === $zone || ! is_callable( array( $zone, 'get_id' ) ) ) {
			return $zone;
		}

		$found = (int) $zone->get_id();
		if ( $found > 0 ) {
			update_option( self::ZONE_OPTION, $found );
		}

		return $zone;
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
