<?php
/**
 * ReviewRequest — one email, a week after delivery, asking for a review.
 *
 * A review form nobody is pointed at stays empty. WooCommerce sends nothing of
 * the kind on its own, so this schedules a single message per order: seven days
 * after the order is marked completed, listing what was bought with a link
 * straight to each product's review form.
 *
 * Deliberately once per order and never again. There is no follow-up, no
 * reminder and no second chance — a shop this small cannot afford to read as a
 * mailing list, and a customer who ignored the first ask has answered.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

namespace Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ReviewRequest.
 *
 * Instantiated by Bootstrap ONLY when class_exists('WooCommerce') is true.
 */
final class ReviewRequest {

	/**
	 * Cron hook fired once per order, with the order id as its only argument.
	 *
	 * @var string
	 */
	public const CRON_HOOK = 'cosypaw_send_review_request';

	/**
	 * Order meta: unix timestamp of the moment the ask went out. Its presence
	 * is what makes a second send impossible, so it is written even when the
	 * mailer reports failure — a bounced ask is not worth a duplicate.
	 *
	 * @var string
	 */
	private const SENT_META = '_cosypaw_review_request_sent';

	/**
	 * Order meta: the language the customer checked out in. Cron runs without a
	 * request, so the cookie Language reads is long gone by the time the email
	 * is built; the order has to carry the answer itself.
	 *
	 * @var string
	 */
	private const LANG_META = '_cosypaw_lang';

	/**
	 * Theme text domain.
	 *
	 * @var string
	 */
	private string $text_domain;

	/**
	 * Constructor — registers the capture, the schedule and the send.
	 *
	 * @param string $text_domain Theme text domain.
	 */
	public function __construct( string $text_domain ) {
		$this->text_domain = $text_domain;

		// Checkout is the last moment the visitor's language is knowable.
		add_action( 'woocommerce_checkout_create_order', array( $this, 'remember_language' ), 10, 1 );

		add_action( 'woocommerce_order_status_completed', array( $this, 'schedule' ), 10, 1 );

		// A refund or a cancellation retires the ask. The send re-checks status
		// anyway, so this only keeps dead rows out of the cron table.
		add_action( 'woocommerce_order_status_refunded', array( $this, 'unschedule' ), 10, 1 );
		add_action( 'woocommerce_order_status_cancelled', array( $this, 'unschedule' ), 10, 1 );

		add_action( self::CRON_HOOK, array( $this, 'send' ), 10, 1 );
	}

	/**
	 * Store the checkout language on the order.
	 *
	 * @param object $order Order being created (\WC_Order at runtime).
	 * @return void
	 */
	public function remember_language( $order ): void {
		if ( ! is_callable( array( $order, 'update_meta_data' ) ) || ! function_exists( 'cosypaw_language' ) ) {
			return;
		}

		$order->update_meta_data( self::LANG_META, cosypaw_language()->current() );
	}

	/**
	 * Queue the ask for one week after the order completed.
	 *
	 * @param int $order_id Order ID.
	 * @return void
	 */
	public function schedule( $order_id ): void {
		$order_id = (int) $order_id;
		$args     = array( $order_id );

		if ( wp_next_scheduled( self::CRON_HOOK, $args ) ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order || $order->get_meta( self::SENT_META ) ) {
			return;
		}

		/**
		 * Delay between completion and the ask.
		 *
		 * A week is the window where the towels have been used a few times but
		 * the parcel is still a memory. Sooner and there is nothing to say.
		 *
		 * @param int $delay    Seconds.
		 * @param int $order_id Order ID.
		 */
		$delay = (int) apply_filters( 'cosypaw_review_request_delay', 7 * DAY_IN_SECONDS, $order_id );

		wp_schedule_single_event( time() + max( 0, $delay ), self::CRON_HOOK, $args );
	}

	/**
	 * Drop a queued ask (order refunded or cancelled).
	 *
	 * @param int $order_id Order ID.
	 * @return void
	 */
	public function unschedule( $order_id ): void {
		wp_clear_scheduled_hook( self::CRON_HOOK, array( (int) $order_id ) );
	}

	/**
	 * Build and send the ask. Runs on cron, with no request context.
	 *
	 * @param int $order_id Order ID.
	 * @return void
	 */
	public function send( $order_id ): void {
		$order = wc_get_order( (int) $order_id );

		if ( ! $order || $order->get_meta( self::SENT_META ) ) {
			return;
		}

		// The week between scheduling and sending is long enough for an order
		// to be refunded, cancelled or reopened. Only a still-completed one
		// earns the ask.
		if ( 'completed' !== $order->get_status() ) {
			return;
		}

		$email = (string) $order->get_billing_email();
		if ( '' === $email || ! is_email( $email ) ) {
			return;
		}

		$products = $this->reviewable_products( $order );
		if ( ! $products ) {
			return;
		}

		$switched = $this->switch_language( (string) $order->get_meta( self::LANG_META ) );

		$this->dispatch( $order, $email, $products );

		if ( $switched ) {
			restore_previous_locale();
		}

		$order->update_meta_data( self::SENT_META, time() );
		$order->save();
	}

	/**
	 * Product ids from an order that can actually take a review right now.
	 *
	 * A package line is expanded into the motifs it contained: the customer has
	 * opinions about the towels, not about the wrapper they were sold in.
	 *
	 * @param object $order Order (\WC_Order at runtime).
	 * @return int[] Unique product ids.
	 */
	private function reviewable_products( $order ): array {
		$motif_map = (array) get_option( WooCommerce::PRODUCT_MAP_OPTION, array() );
		$ids       = array();

		foreach ( $order->get_items() as $item ) {
			$motifs = is_callable( array( $item, 'get_meta' ) ) ? (string) $item->get_meta( 'cosypaw_motifs' ) : '';

			if ( '' !== $motifs ) {
				foreach ( explode( ',', $motifs ) as $motif_id ) {
					$motif_id = trim( $motif_id );
					if ( '' !== $motif_id && isset( $motif_map[ $motif_id ] ) ) {
						$ids[] = (int) $motif_map[ $motif_id ];
					}
				}
				continue;
			}

			if ( is_callable( array( $item, 'get_product_id' ) ) ) {
				$ids[] = (int) $item->get_product_id();
			}
		}

		$ids = array_values( array_unique( array_filter( $ids ) ) );

		// A motif retired since the order was placed has no page left to link
		// to, and one with reviews switched off has no form on the page it does
		// have. Either way there is nowhere to send the customer.
		return array_values(
			array_filter(
				$ids,
				static fn( int $id ): bool => 'publish' === get_post_status( $id ) && comments_open( $id )
			)
		);
	}

	/**
	 * Switch gettext to the language the order was placed in.
	 *
	 * @param string $lang Stored language code (sr|en|ru), possibly empty.
	 * @return bool Whether a switch happened and has to be restored.
	 */
	private function switch_language( string $lang ): bool {
		if ( '' === $lang || ! function_exists( 'cosypaw_language' ) ) {
			return false;
		}

		return switch_to_locale( cosypaw_language()->locale_for( $lang ) );
	}

	/**
	 * Compose the message and hand it to WooCommerce's mailer, so it arrives in
	 * the same shell (header, logo, footer) as every other shop email.
	 *
	 * @param object $order    Order (\WC_Order at runtime).
	 * @param string $email    Recipient.
	 * @param int[]  $products Product ids to ask about.
	 * @return void
	 */
	private function dispatch( $order, string $email, array $products ): void {
		if ( ! function_exists( 'WC' ) ) {
			return;
		}

		$mailer = WC()->mailer();
		if ( ! is_callable( array( $mailer, 'wrap_message' ) ) || ! is_callable( array( $mailer, 'send' ) ) ) {
			return;
		}

		$first_name = (string) $order->get_billing_first_name();

		$heading = '' !== $first_name
			/* translators: %s: customer's first name. */
			? sprintf( __( 'Kako su peškirići, %s?', 'cosypaw' ), $first_name )
			: __( 'Kako su peškirići?', 'cosypaw' );

		$subject = __( 'Reci nam par reči o svojim peškirićima', 'cosypaw' );

		$body  = '<p>' . esc_html__( 'Prošlo je nedelju dana otkad je paket stigao — taman dovoljno da se peškirići okače, isprobaju i operu bar jednom.', 'cosypaw' ) . '</p>';
		$body .= '<p>' . esc_html__( 'Ako imaš minut, ostavi kratku recenziju. Pomaže drugima da izaberu, a nama da znamo šta da pravimo sledeće.', 'cosypaw' ) . '</p>';
		$body .= '<ul>';

		foreach ( $products as $product_id ) {
			$link = get_permalink( $product_id );
			if ( ! is_string( $link ) || '' === $link ) {
				continue;
			}

			$body .= sprintf(
				'<li><a href="%1$s">%2$s</a></li>',
				esc_url( $link . '#reviews' ),
				esc_html( (string) get_the_title( $product_id ) )
			);
		}

		$body .= '</ul>';
		$body .= '<p>' . esc_html__( 'Hvala na poverenju — CosyPaw', 'cosypaw' ) . '</p>';

		$mailer->send( $email, $subject, $mailer->wrap_message( $heading, $body ), '', array() );
	}
}
