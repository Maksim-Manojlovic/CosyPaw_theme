<?php
/**
 * BlackFriday — "Crni petak": a gift in the cart that grows with the order.
 *
 * Three steps, measured on the same number free delivery is measured on
 * (BundlePricing::payable_total(): line items, less coupons, less the package
 * saving), so the gift and the delivery can never disagree about one cart:
 *
 *   from 1.490 RSD  one magična krpica (the price of the 2+1 package, so the
 *                   shop's most common order already earns something);
 *   from 2.500 RSD  a Meda towel in a colour of the shopper's choice, OR the
 *                   set of two cloths — picked in the popup — plus free delivery;
 *   from 3.500 RSD  the Meda towel AND the set of two cloths.
 *
 * Each step replaces the one below it rather than adding to it, so the most a
 * cart can carry is one towel and one set.
 *
 * The gifts are real cart lines at a price of zero, flagged with ITEM_KEY.
 * Being real products they reduce stock when the order is placed, show on the
 * order screen the shop packs from, and are never given once sold out. Only
 * reconcile() puts them there: it runs after every totals calculation, works
 * out which gifts this cart has earned, and adds, removes or resets lines until
 * the cart holds exactly those. A gift line the shopper tampers with — a
 * quantity changed, a flag forged in a request — is corrected on the next
 * calculation, so the zero price is never attached to anything the cart has not
 * earned.
 *
 * The shopper's pick (towel or cloths, and the towel's colour) lives in the
 * WooCommerce session, set through a wc-ajax endpoint from the popup. Without a
 * pick the cart gives the towel, in the first colour still in stock.
 *
 * Outside START–END every gift line is removed and nothing is rendered. The
 * shop can also end it early by hand: WooCommerce → Podešavanja → Opšte →
 * "Crni petak" (ENABLED_OPTION), no deploy needed. A shop manager can see it
 * while it is off with ?crni-petak=1 (and stop seeing it with ?crni-petak=0);
 * nobody else can.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

namespace Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BlackFriday.
 */
final class BlackFriday {

	/**
	 * First moment of the promotion, Belgrade time.
	 */
	public const START = '2026-11-20 00:00:00';

	/**
	 * Last moment of the promotion, Belgrade time.
	 */
	public const END = '2026-12-15 23:59:59';

	/**
	 * Time zone START and END are written in.
	 */
	public const TIMEZONE = 'Europe/Belgrade';

	/**
	 * Option behind the switch in WooCommerce's general settings: "no" ends the
	 * promotion before END. A checkbox, so WooCommerce stores "yes" or "no".
	 */
	public const ENABLED_OPTION = 'cosypaw_black_friday_enabled';

	/**
	 * Step => least the order has to be worth for it (RSD).
	 *
	 * Step 1 is the 2+1 package's price on purpose; step 2 is the free-delivery
	 * threshold on purpose. Neither is read from those sources: a reprice of the
	 * package or a move of the delivery bar must not silently move a promise
	 * printed in a popup, so a change here is a decision of its own.
	 *
	 * @var array<int,int>
	 */
	public const TIERS = array(
		1 => 1490,
		2 => 2500,
		3 => 3500,
	);

	/**
	 * The step at which the shopper picks between the towel and the cloths.
	 */
	public const CHOICE_TIER = 2;

	/**
	 * Pick: the Meda towel.
	 */
	public const PICK_TOWEL = 'towel';

	/**
	 * Pick: the set of two cloths.
	 */
	public const PICK_CLOTHS = 'cloths';

	/**
	 * The single cloth given at step 1, in order of preference: the first one
	 * still in stock is the one given.
	 *
	 * @var array<int,string>
	 */
	public const CLOTHS = array( 'avokado-limeta', 'avokado-zalfija' );

	/**
	 * Gift slot prefixes: which product line a slot's key belongs to.
	 */
	public const SLOT_CLOTH = 'cloth';
	public const SLOT_SET   = 'set';
	public const SLOT_TOWEL = 'towel';

	/**
	 * Cart item data: the gift slot a line fills, e.g. "towel:meda-roze".
	 */
	public const ITEM_KEY = 'cosypaw_gift';

	/**
	 * Cart item data: what the gift would cost, for the struck-through price.
	 */
	public const VALUE_KEY = 'cosypaw_gift_value';

	/**
	 * Session key holding the shopper's pick.
	 */
	public const SESSION_KEY = 'cosypaw_gift_choice';

	/**
	 * wc-ajax action the popup posts its pick to.
	 */
	public const AJAX_ACTION = 'cosypaw_gift';

	/**
	 * Query parameter that switches a shop manager's preview on (1) or off (0).
	 */
	public const PREVIEW_PARAM = 'crni-petak';

	/**
	 * Cookie that carries a shop manager's preview from page to page.
	 */
	public const PREVIEW_COOKIE = 'cosypaw_bf_preview';

	/**
	 * Hidden order item meta: the slot a gift line filled.
	 */
	public const ORDER_META = '_cosypaw_gift';

	/**
	 * Identifies this promotion in the visitor's browser, so "already seen the
	 * popup" from one campaign does not silence the next one.
	 */
	public const PROMO_ID = 'crni-petak-2026';

	/**
	 * Package pricing — the source of the number every step is measured on.
	 *
	 * @var BundlePricing
	 */
	private BundlePricing $pricing;

	/**
	 * Set while reconcile() is changing the cart, so the recalculation it
	 * triggers does not reconcile again.
	 *
	 * @var bool
	 */
	private bool $busy = false;

	/**
	 * Constructor.
	 *
	 * @param BundlePricing $pricing Package pricing module.
	 */
	public function __construct( BundlePricing $pricing ) {
		$this->pricing = $pricing;

		add_action( 'init', array( $this, 'maybe_set_preview' ) );
		add_filter( 'woocommerce_general_settings', array( $this, 'settings' ) );

		// The zero price, on every calculation and on every line restored from
		// the session (which is what the cart table prints before any
		// calculation has run on this request).
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'price_gifts' ), 5 );
		add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'restore_gift' ), 10, 2 );

		// After the totals, because the step is read off them.
		add_action( 'woocommerce_after_calculate_totals', array( $this, 'reconcile' ), 20 );

		add_action( 'wc_ajax_' . self::AJAX_ACTION, array( $this, 'ajax_choose' ) );

		// The gift lines in the cart and the checkout.
		add_filter( 'woocommerce_cart_item_price', array( $this, 'line_price' ), 20, 2 );
		add_filter( 'woocommerce_cart_item_subtotal', array( $this, 'line_price' ), 20, 2 );
		add_filter( 'woocommerce_cart_item_quantity', array( $this, 'line_quantity' ), 20, 3 );
		add_filter( 'woocommerce_cart_item_remove_link', array( $this, 'line_remove_link' ), 20, 2 );
		add_filter( 'woocommerce_get_item_data', array( $this, 'line_rows' ), 20, 2 );
		add_action( 'woocommerce_after_cart_item_name', array( $this, 'line_change_button' ), 20, 2 );
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'save_order_item' ), 20, 3 );

		// Where the shopper stands, inside the cart form (so WooCommerce's own
		// refresh of that form refreshes it too) and above the checkout.
		add_action( 'woocommerce_before_cart_table', array( $this, 'cart_ladder' ) );
		add_action( 'woocommerce_before_checkout_form', array( $this, 'checkout_ladder' ), 4 );

		add_action( 'wp_footer', array( $this, 'render_popup' ) );
	}

	/* ---------------------------------------------------------------------
	 * When.
	 * ------------------------------------------------------------------- */

	/**
	 * Whether the promotion is on for this request.
	 *
	 * @param int|null $now Unix time to judge; now by default.
	 * @return bool
	 */
	public static function active( ?int $now = null ): bool {
		if ( self::in_window( $now ?? time() ) && self::enabled() ) {
			/**
			 * Switch the promotion off inside its window from code. The shop's
			 * own switch is ENABLED_OPTION.
			 *
			 * @param bool $active Whether the gifts are given.
			 */
			return (bool) apply_filters( 'cosypaw_black_friday_active', true );
		}

		return self::previewing();
	}

	/**
	 * Whether a moment falls between START and END, inclusive.
	 *
	 * @param int $now Unix time.
	 * @return bool
	 */
	public static function in_window( int $now ): bool {
		$zone  = new \DateTimeZone( self::TIMEZONE );
		$start = ( new \DateTimeImmutable( self::START, $zone ) )->getTimestamp();
		$end   = ( new \DateTimeImmutable( self::END, $zone ) )->getTimestamp();

		return $now >= $start && $now <= $end;
	}

	/**
	 * Whether the shop has left the switch on. On until it is switched off.
	 *
	 * @return bool
	 */
	public static function enabled(): bool {
		return 'no' !== get_option( self::ENABLED_OPTION, 'yes' );
	}

	/**
	 * The switch, under WooCommerce → Podešavanja → Opšte.
	 *
	 * @param array<int,array<string,mixed>> $settings General settings fields.
	 * @return array<int,array<string,mixed>>
	 */
	public function settings( $settings ) {
		if ( ! is_array( $settings ) ) {
			return $settings;
		}

		$end = ( new \DateTimeImmutable( self::END, new \DateTimeZone( self::TIMEZONE ) ) )->format( 'j.n.Y.' );

		$settings[] = array(
			'title' => __( 'Crni petak', 'cosypaw' ),
			'type'  => 'title',
			/* translators: %s: last day of the promotion, e.g. "15.12.2026.". */
			'desc'  => sprintf( __( 'Pokloni uz porudžbinu od 1.490, 2.500 i 3.500 RSD. Akcija se sama završava %s; ovde je možete ugasiti ranije.', 'cosypaw' ), $end ),
			'id'    => 'cosypaw_black_friday',
		);
		$settings[] = array(
			'title'   => __( 'Akcija uključena', 'cosypaw' ),
			'desc'    => __( 'Prikazuj popup i dodaj poklone u korpu', 'cosypaw' ),
			'id'      => self::ENABLED_OPTION,
			'type'    => 'checkbox',
			'default' => 'yes',
		);
		$settings[] = array(
			'type' => 'sectionend',
			'id'   => 'cosypaw_black_friday',
		);

		return $settings;
	}

	/**
	 * Whether a shop manager is previewing the promotion outside its window.
	 *
	 * @return bool
	 */
	private static function previewing(): bool {
		return ! empty( $_COOKIE[ self::PREVIEW_COOKIE ] )
			&& function_exists( 'current_user_can' )
			&& current_user_can( 'manage_woocommerce' );
	}

	/**
	 * Turn a shop manager's preview on or off from the query string.
	 *
	 * @return void
	 */
	public function maybe_set_preview(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- a read-only display toggle, honoured for shop managers only.
		if ( ! isset( $_GET[ self::PREVIEW_PARAM ] ) || ! current_user_can( 'manage_woocommerce' ) || headers_sent() ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$on = '1' === sanitize_text_field( wp_unslash( $_GET[ self::PREVIEW_PARAM ] ) );

		setcookie( self::PREVIEW_COOKIE, $on ? '1' : '', $on ? time() + 7 * DAY_IN_SECONDS : time() - 3600, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true );

		if ( $on ) {
			$_COOKIE[ self::PREVIEW_COOKIE ] = '1';
		} else {
			unset( $_COOKIE[ self::PREVIEW_COOKIE ] );
		}
	}

	/* ---------------------------------------------------------------------
	 * What a cart earns.
	 * ------------------------------------------------------------------- */

	/**
	 * The step an order of this worth reaches; 0 for none.
	 *
	 * @param float $amount What the order is worth (RSD).
	 * @return int
	 */
	public static function tier_for( float $amount ): int {
		$tier = 0;

		foreach ( self::TIERS as $step => $min ) {
			if ( $amount >= (float) $min ) {
				$tier = (int) $step;
			}
		}

		return $tier;
	}

	/**
	 * How much more the order needs to reach the next step; 0 at the top.
	 *
	 * @param float $amount What the order is worth (RSD).
	 * @return int
	 */
	public static function gap( float $amount ): int {
		$next = self::tier_for( $amount ) + 1;

		return isset( self::TIERS[ $next ] ) ? (int) ceil( self::TIERS[ $next ] - $amount ) : 0;
	}

	/**
	 * The gift slots a step earns, given the shopper's pick and what is in stock.
	 *
	 * Pure: what is available is asked of $available, so every substitution can
	 * be tested without a shop. A sold-out pick falls back to the other gift of
	 * the same step rather than to nothing — the step was promised, the colour
	 * or the kind was a preference — and only when neither is left does the cart
	 * fall back to the step below.
	 *
	 * @param int                   $tier      Step reached (0–3).
	 * @param array<string,string>  $choice    Shopper's pick: `pick` and `colour`.
	 * @param callable(string):bool $available Whether a slot can be given now.
	 * @return array<int,string> Slots, e.g. array( 'towel:meda-roze' ).
	 */
	public static function gifts( int $tier, array $choice, callable $available ): array {
		if ( $tier < 1 ) {
			return array();
		}

		$cloth = self::first_available( self::cloth_slots(), $available );

		if ( $tier < self::CHOICE_TIER ) {
			return null === $cloth ? array() : array( $cloth );
		}

		$towel = self::first_available( self::towel_slots( (string) ( $choice['colour'] ?? '' ) ), $available );
		$set   = self::slot( self::SLOT_SET, Cloths::SET );
		$set   = $available( $set ) ? $set : null;

		if ( $tier > self::CHOICE_TIER ) {
			$both = array_values( array_filter( array( $towel, $set ?? $cloth ) ) );

			if ( $both ) {
				return $both;
			}
		}

		$order = self::PICK_CLOTHS === ( $choice['pick'] ?? '' ) ? array( $set, $towel ) : array( $towel, $set );

		foreach ( $order as $slot ) {
			if ( null !== $slot ) {
				return array( $slot );
			}
		}

		return null === $cloth ? array() : array( $cloth );
	}

	/**
	 * The Meda colours, in the order the shop lists them.
	 *
	 * @return array<int,string> LongTowels::ITEMS keys.
	 */
	public static function colours(): array {
		$keys = array();

		foreach ( LongTowels::ITEMS as $key => $item ) {
			if ( LongTowels::TEDDY === ( $item['group'] ?? '' ) ) {
				$keys[] = (string) $key;
			}
		}

		return $keys;
	}

	/**
	 * A pick as stored: an allowed kind and an allowed colour, or the defaults.
	 *
	 * @param array<string,mixed> $raw Pick as posted or as read from the session.
	 * @return array{pick:string,colour:string}
	 */
	public static function sanitize_choice( array $raw ): array {
		$pick    = (string) ( $raw['pick'] ?? '' );
		$colour  = (string) ( $raw['colour'] ?? '' );
		$colours = self::colours();

		return array(
			'pick'   => self::PICK_CLOTHS === $pick ? self::PICK_CLOTHS : self::PICK_TOWEL,
			'colour' => in_array( $colour, $colours, true ) ? $colour : (string) ( $colours[0] ?? '' ),
		);
	}

	/**
	 * Slot id for a product line key.
	 *
	 * @param string $line SLOT_* prefix.
	 * @param string $key  The line's ITEMS key.
	 * @return string
	 */
	public static function slot( string $line, string $key ): string {
		return $line . ':' . $key;
	}

	/**
	 * Every towel slot, the chosen colour first.
	 *
	 * @param string $colour Chosen colour key.
	 * @return array<int,string>
	 */
	private static function towel_slots( string $colour ): array {
		$keys = self::colours();

		if ( in_array( $colour, $keys, true ) ) {
			$keys = array_values( array_unique( array_merge( array( $colour ), $keys ) ) );
		}

		return array_map( static fn( string $key ): string => self::slot( self::SLOT_TOWEL, $key ), $keys );
	}

	/**
	 * Every single-cloth slot, in order of preference.
	 *
	 * @return array<int,string>
	 */
	private static function cloth_slots(): array {
		return array_map( static fn( string $key ): string => self::slot( self::SLOT_CLOTH, $key ), self::CLOTHS );
	}

	/**
	 * The first slot that can be given.
	 *
	 * @param array<int,string>     $slots     Candidates, best first.
	 * @param callable(string):bool $available Availability check.
	 * @return string|null
	 */
	private static function first_available( array $slots, callable $available ): ?string {
		foreach ( $slots as $slot ) {
			if ( $available( $slot ) ) {
				return $slot;
			}
		}

		return null;
	}

	/**
	 * The product row behind a slot, or null where it is not on sale.
	 *
	 * @param string $slot Slot id.
	 * @return array<string,mixed>|null ProductLine::row() output.
	 */
	public static function row_for( string $slot ): ?array {
		$parts = explode( ':', $slot, 2 );
		$key   = (string) ( $parts[1] ?? '' );

		switch ( $parts[0] ) {
			case self::SLOT_TOWEL:
				return in_array( $key, self::colours(), true ) ? LongTowels::row( $key ) : null;
			case self::SLOT_SET:
				return Cloths::SET === $key ? Cloths::row( $key ) : null;
			case self::SLOT_CLOTH:
				return in_array( $key, self::CLOTHS, true ) ? Cloths::row( $key ) : null;
		}

		return null;
	}

	/**
	 * Whether a slot can be given right now: on sale, in stock, and — where the
	 * shop counts stock — with one left beyond what the cart already holds.
	 *
	 * @param string $slot Slot id.
	 * @return bool
	 */
	public function available( string $slot ): bool {
		$row = self::row_for( $slot );

		if ( null === $row || ! empty( $row['sold_out'] ) ) {
			return false;
		}

		$product = function_exists( 'wc_get_product' ) ? wc_get_product( (int) $row['id'] ) : null;

		if ( ! is_object( $product ) || ! is_callable( array( $product, 'managing_stock' ) ) || ! $product->managing_stock() ) {
			return true;
		}

		$held = 0;
		$cart = $this->cart();

		if ( null !== $cart ) {
			foreach ( $cart->get_cart() as $item ) {
				// A gift line of this very slot is the one being kept, not a
				// competitor for the stock.
				if ( (int) ( $item['product_id'] ?? 0 ) === (int) $row['id'] && ( $item[ self::ITEM_KEY ] ?? '' ) !== $slot ) {
					$held += (int) ( $item['quantity'] ?? 0 );
				}
			}
		}

		return (int) $product->get_stock_quantity() > $held;
	}

	/**
	 * The shopper's pick, from the session.
	 *
	 * @return array{pick:string,colour:string}
	 */
	public function choice(): array {
		$session = function_exists( 'WC' ) && WC()->session ? WC()->session : null;
		$raw     = $session ? $session->get( self::SESSION_KEY ) : null;

		return self::sanitize_choice( is_array( $raw ) ? $raw : array() );
	}

	/**
	 * What the cart is worth for the steps: the free-delivery number.
	 *
	 * @param \WC_Cart $cart Cart.
	 * @return float
	 */
	public function amount( \WC_Cart $cart ): float {
		return $this->pricing->payable_total( $cart );
	}

	/* ---------------------------------------------------------------------
	 * The cart.
	 * ------------------------------------------------------------------- */

	/**
	 * Whether a cart line is a gift.
	 *
	 * @param array<string,mixed> $item Cart item.
	 * @return bool
	 */
	public static function is_gift( array $item ): bool {
		return ! empty( $item[ self::ITEM_KEY ] );
	}

	/**
	 * Zero the price of every gift line before the totals are worked out.
	 *
	 * @param \WC_Cart|null $cart Cart being calculated.
	 * @return void
	 */
	public function price_gifts( $cart = null ): void {
		if ( ! $cart instanceof \WC_Cart ) {
			return;
		}

		foreach ( $cart->get_cart() as $item ) {
			if ( self::is_gift( $item ) && isset( $item['data'] ) && is_callable( array( $item['data'], 'set_price' ) ) ) {
				$item['data']->set_price( 0 );
			}
		}
	}

	/**
	 * A gift line restored from the session keeps its zero price.
	 *
	 * @param array<string,mixed> $item   Cart item being restored.
	 * @param array<string,mixed> $values Its stored values.
	 * @return array<string,mixed>
	 */
	public function restore_gift( $item, $values ) {
		if ( ! is_array( $item ) || ! is_array( $values ) || ! self::is_gift( $values ) ) {
			return $item;
		}

		$item[ self::ITEM_KEY ]  = (string) $values[ self::ITEM_KEY ];
		$item[ self::VALUE_KEY ] = (int) ( $values[ self::VALUE_KEY ] ?? 0 );

		if ( isset( $item['data'] ) && is_callable( array( $item['data'], 'set_price' ) ) ) {
			$item['data']->set_price( 0 );
		}

		return $item;
	}

	/**
	 * Make the cart hold exactly the gifts it has earned.
	 *
	 * @param \WC_Cart|null $cart Cart that has just been calculated.
	 * @return void
	 */
	public function reconcile( $cart = null ): void {
		if ( $this->busy || ! $cart instanceof \WC_Cart ) {
			return;
		}

		// wp-admin builds order screens against a cart; AJAX is the storefront.
		if ( is_admin() && ! wp_doing_ajax() ) {
			return;
		}

		$this->busy = true;

		$wanted = self::active()
			? self::gifts( self::tier_for( $this->amount( $cart ) ), $this->choice(), array( $this, 'available' ) )
			: array();

		$changed = $this->apply( $cart, $wanted );

		if ( $changed ) {
			$cart->calculate_totals();
		}

		$this->busy = false;
	}

	/**
	 * Add, remove and reset gift lines until the cart holds exactly $wanted.
	 *
	 * @param \WC_Cart          $cart   Cart.
	 * @param array<int,string> $wanted Slots the cart has earned.
	 * @return bool Whether anything changed.
	 */
	public function apply( \WC_Cart $cart, array $wanted ): bool {
		$changed = false;
		$held    = array();

		foreach ( $cart->get_cart() as $key => $item ) {
			if ( ! self::is_gift( $item ) ) {
				continue;
			}

			$slot = (string) $item[ self::ITEM_KEY ];

			// Not earned, or a second line for a slot already kept.
			if ( ! in_array( $slot, $wanted, true ) || isset( $held[ $slot ] ) ) {
				$this->drop( $cart, (string) $key );
				$changed = true;
				continue;
			}

			$held[ $slot ] = true;

			if ( 1 !== (int) ( $item['quantity'] ?? 1 ) ) {
				$cart->set_quantity( (string) $key, 1, false );
				$changed = true;
			}
		}

		foreach ( $wanted as $slot ) {
			if ( isset( $held[ $slot ] ) ) {
				continue;
			}

			$row = self::row_for( $slot );

			if ( null === $row ) {
				continue;
			}

			$added = $cart->add_to_cart(
				(int) $row['id'],
				1,
				0,
				array(),
				array(
					self::ITEM_KEY  => $slot,
					self::VALUE_KEY => (int) $row['price'],
				)
			);

			$changed = $changed || false !== $added;
		}

		return $changed;
	}

	/**
	 * Take a gift line out of the cart without leaving it in "recently
	 * removed", where the cart's undo link and the upsell would find it.
	 *
	 * @param \WC_Cart $cart Cart.
	 * @param string   $key  Cart item key.
	 * @return void
	 */
	private function drop( \WC_Cart $cart, string $key ): void {
		$cart->remove_cart_item( $key );

		if ( is_callable( array( $cart, 'set_removed_cart_contents' ) ) ) {
			$removed = $cart->get_removed_cart_contents();
			unset( $removed[ $key ] );
			$cart->set_removed_cart_contents( $removed );
		}
	}

	/**
	 * Store the shopper's pick and answer with what it now is.
	 *
	 * No nonce: the request can only choose between two gifts the shopper is
	 * entitled to anyway, and a nonce in footer markup would break under page
	 * caching. Every value is checked against the allowlist.
	 *
	 * @return void
	 */
	public function ajax_choose(): void {
		if ( ! self::active() || ! function_exists( 'WC' ) || ! WC()->session ) {
			wp_send_json_error( array( 'message' => __( 'Akcija trenutno nije aktivna.', 'cosypaw' ) ), 400 );
		}

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- see the docblock.
		$choice = self::sanitize_choice(
			array(
				'pick'   => isset( $_POST['pick'] ) ? sanitize_key( wp_unslash( $_POST['pick'] ) ) : '',
				'colour' => isset( $_POST['colour'] ) ? sanitize_key( wp_unslash( $_POST['colour'] ) ) : '',
			)
		);
		// phpcs:enable

		$session = WC()->session;

		if ( is_callable( array( $session, 'has_session' ) ) && ! $session->has_session() ) {
			$session->set_customer_session_cookie( true );
		}

		$session->set( self::SESSION_KEY, $choice );

		$slot = self::PICK_CLOTHS === $choice['pick']
			? self::slot( self::SLOT_SET, Cloths::SET )
			: self::slot( self::SLOT_TOWEL, $choice['colour'] );
		$row  = self::row_for( $slot );
		$name = null !== $row ? (string) $row['name'] : '';

		wp_send_json_success(
			array(
				'pick'    => $choice['pick'],
				'colour'  => $choice['colour'],
				'message' => sprintf(
					/* translators: 1: the gift, e.g. "Peškir Meda, roze", 2: formatted order value, e.g. "2.500 RSD". */
					__( 'Sačuvano: %1$s. Stiže gratis uz porudžbinu od %2$s.', 'cosypaw' ),
					$name,
					Catalog::format_price( self::TIERS[ self::CHOICE_TIER ] )
				),
			)
		);
	}

	/**
	 * "Gratis", with what the gift would have cost struck through.
	 *
	 * @param string              $html Price HTML.
	 * @param array<string,mixed> $item Cart item.
	 * @return string
	 */
	public function line_price( $html, $item ) {
		if ( ! is_array( $item ) || ! self::is_gift( $item ) ) {
			return $html;
		}

		$value = (int) ( $item[ self::VALUE_KEY ] ?? 0 );

		return sprintf(
			'%1$s<strong class="gift-line__free">%2$s</strong>',
			$value > 0 ? '<del>' . esc_html( Catalog::format_price( $value ) ) . '</del> ' : '',
			esc_html__( 'Gratis', 'cosypaw' )
		);
	}

	/**
	 * A gift is one, and says so instead of offering a quantity field.
	 *
	 * @param string              $html Quantity HTML.
	 * @param string              $key  Cart item key.
	 * @param array<string,mixed> $item Cart item.
	 * @return string
	 */
	public function line_quantity( $html, $key = '', $item = array() ) {
		return is_array( $item ) && self::is_gift( $item ) ? '<span class="gift-line__qty">1</span>' : $html;
	}

	/**
	 * No remove link on a gift: the next calculation would only put it back.
	 *
	 * @param string $html Remove link HTML.
	 * @param string $key  Cart item key.
	 * @return string
	 */
	public function line_remove_link( $html, $key = '' ) {
		$cart = $this->cart();
		$item = null !== $cart && is_callable( array( $cart, 'get_cart_item' ) ) ? $cart->get_cart_item( (string) $key ) : array();

		return is_array( $item ) && self::is_gift( $item ) ? '' : $html;
	}

	/**
	 * "Poklon: Crni petak" under a gift's name.
	 *
	 * @param array<int,array<string,string>> $rows Item data rows.
	 * @param array<string,mixed>             $item Cart item.
	 * @return array<int,array<string,string>>
	 */
	public function line_rows( $rows, $item ) {
		if ( ! is_array( $rows ) || ! is_array( $item ) || ! self::is_gift( $item ) ) {
			return $rows;
		}

		$rows[] = array(
			'key'   => __( 'Poklon', 'cosypaw' ),
			'value' => __( 'Crni petak', 'cosypaw' ),
		);

		return $rows;
	}

	/**
	 * "Promeni poklon" under the gift the shopper picked, in the cart.
	 *
	 * @param array<string,mixed> $item Cart item.
	 * @param string              $key  Cart item key.
	 * @return void
	 */
	public function line_change_button( $item, $key = '' ): void {
		unset( $key );

		if ( ! is_array( $item ) || ! self::is_gift( $item ) ) {
			return;
		}

		$line = explode( ':', (string) $item[ self::ITEM_KEY ], 2 )[0];

		if ( self::SLOT_CLOTH === $line ) {
			return;
		}

		printf(
			'<button type="button" class="gift-line__change" data-gift-open>%s</button>',
			esc_html__( 'Promeni poklon', 'cosypaw' )
		);
	}

	/**
	 * Mark a gift on the order, for the shop that packs it.
	 *
	 * @param \WC_Order_Item_Product $order_item Order line being created.
	 * @param string                 $key        Cart item key.
	 * @param array<string,mixed>    $values     Cart item.
	 * @return void
	 */
	public function save_order_item( $order_item, $key, $values ): void {
		unset( $key );

		if ( ! is_array( $values ) || ! self::is_gift( $values ) || ! is_callable( array( $order_item, 'add_meta_data' ) ) ) {
			return;
		}

		$order_item->add_meta_data( self::ORDER_META, (string) $values[ self::ITEM_KEY ], true );
		// Stored in the shop's own language, like every other order note: it
		// is read by whoever packs the order, not by the shopper's locale.
		$order_item->add_meta_data( 'Poklon', 'Crni petak — gratis', true );
	}

	/* ---------------------------------------------------------------------
	 * Where the shopper stands.
	 * ------------------------------------------------------------------- */

	/**
	 * The ladder above the cart table.
	 *
	 * @return void
	 */
	public function cart_ladder(): void {
		echo $this->ladder(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ladder() escapes every part it interpolates.
	}

	/**
	 * The ladder above the checkout form.
	 *
	 * @return void
	 */
	public function checkout_ladder(): void {
		echo $this->ladder(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ladder() escapes every part it interpolates.
	}

	/**
	 * Three steps, a bar, and one sentence saying where this cart is.
	 *
	 * @return string
	 */
	public function ladder(): string {
		$cart = $this->cart();

		if ( null === $cart || ! self::active() || $cart->is_empty() ) {
			return '';
		}

		$amount = $this->amount( $cart );
		$tier   = self::tier_for( $amount );
		$gifts  = array();

		foreach ( $cart->get_cart() as $item ) {
			if ( self::is_gift( $item ) && isset( $item['data'] ) && is_callable( array( $item['data'], 'get_name' ) ) ) {
				$gifts[] = (string) $item['data']->get_name();
			}
		}

		$steps = '';

		foreach ( $this->step_labels() as $step => $label ) {
			$steps .= sprintf(
				'<li class="gift-ladder__step%1$s"><strong>%2$s</strong><span>%3$s</span></li>',
				$step <= $tier ? ' is-reached' : '',
				esc_html( Catalog::format_price( self::TIERS[ $step ] ) ),
				esc_html( $label )
			);
		}

		$button = sprintf(
			'<button type="button" class="gift-ladder__change" data-gift-open>%s</button>',
			$tier >= self::CHOICE_TIER ? esc_html__( 'Promeni poklon', 'cosypaw' ) : esc_html__( 'Izaberite poklon', 'cosypaw' )
		);

		return sprintf(
			'<section class="gift-ladder" aria-labelledby="gift-ladder-title">' .
				'<p class="gift-ladder__eyebrow" id="gift-ladder-title">%1$s</p>' .
				'<p class="gift-ladder__status">%2$s</p>' .
				'<span class="gift-ladder__bar" aria-hidden="true"><span class="gift-ladder__fill" style="width:%3$s%%"></span></span>' .
				'<ol class="gift-ladder__steps">%4$s</ol>' .
				'%5$s' .
			'</section>',
			esc_html__( 'Crni petak — pokloni uz porudžbinu', 'cosypaw' ),
			esc_html( $this->status( $tier, $amount, $gifts ) ),
			esc_attr( (string) self::progress( $amount ) ),
			$steps,
			$button
		);
	}

	/**
	 * How far along the three steps an order is, in percent of the bar.
	 *
	 * The bar is three equal thirds, one per step, matching the three columns
	 * under it; within a third the fill is proportional.
	 *
	 * @param float $amount Order worth.
	 * @return float
	 */
	public static function progress( float $amount ): float {
		$steps = array_values( self::TIERS );
		$count = count( $steps );
		$prev  = 0.0;

		foreach ( $steps as $i => $min ) {
			if ( $amount < $min ) {
				$part = ( $amount - $prev ) / max( 1.0, $min - $prev );

				return round( ( $i + max( 0.0, $part ) ) / $count * 100, 1 );
			}

			$prev = (float) $min;
		}

		return 100.0;
	}

	/**
	 * The sentence under the ladder's heading.
	 *
	 * @param int               $tier   Step reached.
	 * @param float             $amount Order worth.
	 * @param array<int,string> $gifts  Names of the gifts in the cart.
	 * @return string
	 */
	private function status( int $tier, float $amount, array $gifts ): string {
		$gap = Catalog::format_price( self::gap( $amount ) );

		switch ( $tier ) {
			case 0:
				/* translators: %s: formatted amount still to spend, e.g. "520 RSD". */
				return sprintf( __( 'Još %s do prvog poklona — magična krpa gratis.', 'cosypaw' ), $gap );

			case 1:
				return self::free_delivery_at_choice()
					/* translators: %s: formatted amount still to spend, e.g. "1.010 RSD". */
					? sprintf( __( 'Magična krpa je vaša. Još %s do peškira Meda i besplatne dostave.', 'cosypaw' ), $gap )
					/* translators: %s: formatted amount still to spend, e.g. "1.010 RSD". */
					: sprintf( __( 'Magična krpa je vaša. Još %s do peškira Meda.', 'cosypaw' ), $gap );

			case 2:
				return sprintf(
					/* translators: 1: the gift, e.g. "Peškir Meda, roze", 2: formatted amount still to spend. */
					__( 'Vaš poklon: %1$s. Još %2$s i dobijate i peškir Meda i set magičnih krpa.', 'cosypaw' ),
					implode( ', ', $gifts ),
					$gap
				);
		}

		/* translators: %s: the gifts, e.g. "Peškir Meda, roze + Set od 2 magične krpice Avokado". */
		return sprintf( __( 'Svi pokloni su vaši: %s.', 'cosypaw' ), implode( ' + ', $gifts ) );
	}

	/**
	 * What each step gives, in a few words.
	 *
	 * @return array<int,string>
	 */
	private function step_labels(): array {
		return array(
			1 => __( 'Magična krpa', 'cosypaw' ),
			2 => self::free_delivery_at_choice()
				? __( 'Peškir Meda ili set krpa + besplatna dostava', 'cosypaw' )
				: __( 'Peškir Meda ili set krpa', 'cosypaw' ),
			3 => __( 'Peškir Meda i set krpa', 'cosypaw' ),
		);
	}

	/**
	 * Whether the choice step also clears the free-delivery bar, so the copy
	 * may promise both. Read off the bar the checkout enforces.
	 *
	 * @return bool
	 */
	private static function free_delivery_at_choice(): bool {
		$min = Catalog::free_shipping_min();

		return $min > 0 && $min <= self::TIERS[ self::CHOICE_TIER ];
	}

	/* ---------------------------------------------------------------------
	 * The popup.
	 * ------------------------------------------------------------------- */

	/**
	 * Print the popup and its launcher at the end of the document.
	 *
	 * @return void
	 */
	public function render_popup(): void {
		echo $this->popup(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- popup() escapes every part it interpolates.
	}

	/**
	 * The popup: pick a towel (and its colour) or the cloths, and the three
	 * steps under them.
	 *
	 * The markup is the same for every visitor — the pick is restored in the
	 * browser — so a cached page cannot show one shopper another's choice.
	 *
	 * @return string
	 */
	public function popup(): string {
		if ( ! self::active() || ! class_exists( '\WC_AJAX' ) ) {
			return '';
		}

		$towel = $this->towel_card();
		$set   = $this->cloths_card();

		if ( '' === $towel && '' === $set ) {
			return '';
		}

		$on_till = ( function_exists( 'is_cart' ) && is_cart() ) || ( function_exists( 'is_checkout' ) && is_checkout() );

		$steps = '';
		foreach ( $this->step_labels() as $step => $label ) {
			$steps .= sprintf(
				'<li%1$s><strong>%2$s</strong> <span>%3$s</span></li>',
				self::CHOICE_TIER === $step ? ' class="is-main"' : '',
				esc_html(
					sprintf(
						/* translators: %s: formatted order value, e.g. "2.500 RSD". */
						__( 'od %s', 'cosypaw' ),
						Catalog::format_price( self::TIERS[ $step ] )
					)
				),
				esc_html( $label )
			);
		}

		$lead = self::free_delivery_at_choice()
			/* translators: %s: formatted order value, e.g. "2.500 RSD". */
			? __( 'Uz porudžbinu od %s birate jedan poklon — gratis, uz besplatnu dostavu.', 'cosypaw' )
			/* translators: %s: formatted order value, e.g. "2.500 RSD". */
			: __( 'Uz porudžbinu od %s birate jedan poklon — gratis.', 'cosypaw' );

		$launcher = $on_till ? '' : sprintf(
			'<button type="button" class="gift-fab" data-gift-open data-gift-fab>' .
				'<span class="gift-fab__icon" aria-hidden="true">%1$s</span>' .
				'<span class="gift-fab__text">%2$s</span>' .
			'</button>',
			$this->gift_icon(),
			esc_html__( 'Poklon', 'cosypaw' )
		);

		return $launcher . sprintf(
			'<dialog class="gift-popup" data-gift-popup data-gift-auto="%1$s" data-gift-promo="%2$s" data-gift-endpoint="%3$s" data-gift-chosen="%4$s" data-gift-error="%5$s" aria-labelledby="gift-popup-title">' .
				// Focus lands on the content, not on the close button: a
				// ring around the "×" is the first thing anyone would see.
				'<div class="gift-popup__inner" tabindex="-1" autofocus>' .
					'<form method="dialog" class="gift-popup__x"><button type="submit" aria-label="%6$s">&times;</button></form>' .
					'<p class="gift-popup__badge">%7$s</p>' .
					'<h2 class="gift-popup__title" id="gift-popup-title">%8$s</h2>' .
					'<p class="gift-popup__lead">%9$s</p>' .
					'<div class="gift-popup__cards">%10$s%11$s</div>' .
					'<p class="gift-popup__status" data-gift-status role="status" aria-live="polite"></p>' .
					'<div class="gift-popup__ladder"><p class="gift-popup__ladder-title">%12$s</p><ol>%13$s</ol></div>' .
					'<form method="dialog" class="gift-popup__later"><button type="submit">%14$s</button></form>' .
					'<p class="gift-popup__fine">%15$s</p>' .
				'</div>' .
			'</dialog>',
			$on_till ? '0' : '1',
			esc_attr( self::PROMO_ID ),
			esc_url( \WC_AJAX::get_endpoint( self::AJAX_ACTION ) ),
			esc_attr__( 'Izabrano ✓', 'cosypaw' ),
			esc_attr__( 'Čuvanje nije uspelo — pokušajte ponovo.', 'cosypaw' ),
			esc_attr__( 'Zatvori', 'cosypaw' ),
			esc_html__( 'Crni petak', 'cosypaw' ),
			esc_html__( 'Izaberite poklon', 'cosypaw' ),
			wp_kses( sprintf( $lead, '<strong>' . esc_html( Catalog::format_price( self::TIERS[ self::CHOICE_TIER ] ) ) . '</strong>' ), array( 'strong' => array() ) ),
			$towel,
			$set,
			esc_html__( 'Pokloni rastu sa korpom', 'cosypaw' ),
			$steps,
			esc_html__( 'Izabraću kasnije', 'cosypaw' ),
			esc_html(
				sprintf(
					/* translators: %s: last day of the promotion, e.g. "30.11.". */
					__( 'Akcija traje do %s, dok traju zalihe.', 'cosypaw' ),
					( new \DateTimeImmutable( self::END, new \DateTimeZone( self::TIMEZONE ) ) )->format( 'j.n.' )
				)
			)
		);
	}

	/**
	 * The Meda card: photograph, colour dots, price struck through, pick button.
	 *
	 * @return string Empty where no colour is on sale.
	 */
	private function towel_card(): string {
		$dots  = '';
		$first = null;
		$value = 0;

		foreach ( self::colours() as $key ) {
			$row = LongTowels::row( $key );

			if ( null === $row ) {
				continue;
			}

			$item     = LongTowels::ITEMS[ $key ];
			$sold_out = ! empty( $row['sold_out'] );
			$image    = $this->asset( 'assets/long-towels/' . $item['image'] );
			$value    = max( $value, (int) $row['price'] );

			if ( null === $first && ! $sold_out ) {
				$first = array(
					'image' => $image,
					'alt'   => (string) $item['alt'],
				);
			}

			$dots .= sprintf(
				'<label class="gift-card__dot%1$s" title="%2$s">' .
					'<input type="radio" name="gift-colour" value="%3$s" data-image="%4$s" data-alt="%5$s"%6$s%7$s>' .
					'<span style="--swatch:%8$s" aria-hidden="true"></span>' .
					'<span class="screen-reader-text">%2$s</span>' .
				'</label>',
				$sold_out ? ' is-sold-out' : '',
				// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- stored colour names are their own msgids (see ProductLine::translate()).
				esc_attr( __( (string) $item['color'], 'cosypaw' ) ),
				esc_attr( $key ),
				esc_url( $image ),
				// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
				esc_attr( __( (string) $item['alt'], 'cosypaw' ) ),
				$sold_out ? ' disabled' : '',
				null !== $first && $first['image'] === $image ? ' checked' : '',
				esc_attr( (string) $item['swatch'] )
			);
		}

		if ( null === $first ) {
			return '';
		}

		return sprintf(
			'<article class="gift-card" data-gift-card="%1$s">' .
				'<div class="gift-card__media"><img src="%2$s" alt="%3$s" width="600" height="800" loading="lazy" decoding="async" data-gift-image></div>' .
				'<h3 class="gift-card__name">%4$s</h3>' .
				'<p class="gift-card__price">%5$s<strong>%6$s</strong></p>' .
				'<fieldset class="gift-card__dots"><legend class="screen-reader-text">%7$s</legend>%8$s</fieldset>' .
				'<button type="button" class="gift-card__pick" data-gift-pick="%1$s" data-label="%9$s">%9$s</button>' .
			'</article>',
			esc_attr( self::PICK_TOWEL ),
			esc_url( $first['image'] ),
			// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
			esc_attr( __( $first['alt'], 'cosypaw' ) ),
			esc_html__( 'Peškir Meda', 'cosypaw' ),
			$value > 0 ? '<del>' . esc_html( Catalog::format_price( $value ) ) . '</del> ' : '',
			esc_html__( 'Gratis', 'cosypaw' ),
			esc_html__( 'Boja peškira', 'cosypaw' ),
			$dots,
			esc_html__( 'Izaberite peškir', 'cosypaw' )
		);
	}

	/**
	 * The cloths card: both cloths side by side, price struck through.
	 *
	 * @return string Empty where the set is not on sale or sold out.
	 */
	private function cloths_card(): string {
		$row = Cloths::row( Cloths::SET );

		if ( null === $row || ! empty( $row['sold_out'] ) ) {
			return '';
		}

		$photos = '';
		foreach ( Cloths::ITEMS[ Cloths::SET ]['holds'] ?? array() as $held ) {
			$photos .= sprintf(
				'<img src="%1$s" alt="%2$s" width="600" height="800" loading="lazy" decoding="async">',
				esc_url( $this->asset( 'assets/cloths/' . Cloths::ITEMS[ $held ]['image'] ) ),
				// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
				esc_attr( __( (string) Cloths::ITEMS[ $held ]['alt'], 'cosypaw' ) )
			);
		}

		return sprintf(
			'<article class="gift-card" data-gift-card="%1$s">' .
				'<div class="gift-card__media gift-card__media--pair">%2$s</div>' .
				'<h3 class="gift-card__name">%3$s</h3>' .
				'<p class="gift-card__price">%4$s<strong>%5$s</strong></p>' .
				'<p class="gift-card__note">%6$s</p>' .
				'<button type="button" class="gift-card__pick" data-gift-pick="%1$s" data-label="%7$s">%7$s</button>' .
			'</article>',
			esc_attr( self::PICK_CLOTHS ),
			$photos,
			esc_html__( 'Set od 2 magične krpe', 'cosypaw' ),
			(int) $row['price'] > 0 ? '<del>' . esc_html( Catalog::format_price( (int) $row['price'] ) ) . '</del> ' : '',
			esc_html__( 'Gratis', 'cosypaw' ),
			esc_html__( 'Limeta i žalfija zelena', 'cosypaw' ),
			esc_html__( 'Izaberite krpe', 'cosypaw' )
		);
	}

	/**
	 * URL of a file shipped with the theme.
	 *
	 * @param string $path Path inside the theme.
	 * @return string
	 */
	private function asset( string $path ): string {
		return trailingslashit( get_template_directory_uri() ) . $path;
	}

	/**
	 * A wrapped present, for the launcher.
	 *
	 * @return string
	 */
	private function gift_icon(): string {
		return '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="8" width="18" height="4" rx="1"/><path d="M5 12v8a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-8M12 8v13"/><path d="M12 8S10.5 3.5 7.8 4.1C6 4.5 6.3 7.2 8.4 8M12 8s1.5-4.5 4.2-3.9C18 4.5 17.7 7.2 15.6 8"/></svg>';
	}

	/**
	 * The session cart, where there is one.
	 *
	 * @return \WC_Cart|null
	 */
	private function cart(): ?\WC_Cart {
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return null;
		}

		return WC()->cart instanceof \WC_Cart ? WC()->cart : null;
	}
}
