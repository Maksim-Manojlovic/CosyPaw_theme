<?php
/**
 * Unit tests for the post-delivery review request.
 *
 * The two things worth pinning down: an order never gets asked twice, and a
 * package order asks about the motifs inside it rather than about the package.
 *
 * @package CosyPaw\Tests
 */

declare(strict_types=1);

namespace Theme\Tests;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use Theme\ReviewRequest;

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}
if ( ! defined( 'DAY_IN_SECONDS' ) ) {
	define( 'DAY_IN_SECONDS', 86400 );
}

require_once __DIR__ . '/stubs.php';
require_once dirname( __DIR__ ) . '/inc/WooCommerce.php';
require_once dirname( __DIR__ ) . '/inc/ReviewRequest.php';

final class ReviewRequestTest extends TestCase {

	/**
	 * Events queued through wp_schedule_single_event during a test.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	private array $scheduled = array();

	/**
	 * The mailer spy behind the WC() stub, rebuilt per test so sent messages
	 * never leak from one case into the next.
	 *
	 * @var \WC_Mock_Mailer
	 */
	private \WC_Mock_Mailer $mailer;

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		$this->scheduled = array();

		Functions\stubs(
			array(
				'add_action'             => true,
				'add_filter'             => true,
				'apply_filters'          => static fn( $hook, $value = null ) => $value,
				'is_email'               => static fn( $email ) => (bool) filter_var( (string) $email, FILTER_VALIDATE_EMAIL ),
				'esc_url'                => static fn( $url ) => $url,
				'esc_html'               => static fn( $text ) => $text,
				'esc_html__'             => static fn( $text ) => $text,
				'__'                     => static fn( $text ) => $text,
				'wp_next_scheduled'      => false,
				'wp_clear_scheduled_hook' => true,
				// Every seeded motif is on sale and open for reviews unless a
				// test says otherwise.
				'get_post_status'        => 'publish',
				'comments_open'          => true,
				'get_permalink'          => static fn( $id ) => 'http://example.test/product/' . $id . '/',
				'get_the_title'          => static fn( $id ) => 'Motiv ' . $id,
				// No Language instance in unit tests, so no locale switch.
				'switch_to_locale'       => false,
				'restore_previous_locale' => true,
			)
		);

		// WC() is stubbed through Brain\Monkey rather than declared in stubs.php:
		// a real declaration loads before Patchwork and locks every other test
		// out of redefining it.
		$this->mailer = new \WC_Mock_Mailer();
		$mailer       = $this->mailer;

		Functions\when( 'WC' )->justReturn(
			new class( $mailer ) {
				/**
				 * The mailer spy.
				 *
				 * @var \WC_Mock_Mailer
				 */
				private \WC_Mock_Mailer $mailer;

				/**
				 * Constructor.
				 *
				 * @param \WC_Mock_Mailer $mailer Mailer spy.
				 */
				public function __construct( \WC_Mock_Mailer $mailer ) {
					$this->mailer = $mailer;
				}

				/**
				 * The mailer.
				 *
				 * @return \WC_Mock_Mailer
				 */
				public function mailer(): \WC_Mock_Mailer {
					return $this->mailer;
				}
			}
		);

		Functions\when( 'wp_schedule_single_event' )->alias(
			function ( $timestamp, $hook, $args = array() ) {
				$this->scheduled[] = array(
					'timestamp' => $timestamp,
					'hook'      => $hook,
					'args'      => $args,
				);
				return true;
			}
		);
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * A completed order queues exactly one ask, a week out, carrying its own id.
	 */
	public function test_schedule_queues_the_ask_a_week_after_completion(): void {
		Functions\when( 'wc_get_order' )->justReturn( new \WC_Mock_Order() );

		( new ReviewRequest( 'cosypaw' ) )->schedule( 77 );

		$this->assertCount( 1, $this->scheduled );
		$this->assertSame( ReviewRequest::CRON_HOOK, $this->scheduled[0]['hook'] );
		$this->assertSame( array( 77 ), $this->scheduled[0]['args'] );
		$this->assertEqualsWithDelta( time() + 7 * DAY_IN_SECONDS, $this->scheduled[0]['timestamp'], 5 );
	}

	/**
	 * An order already asked once is never queued again, however many times its
	 * status is flipped back to completed in wp-admin.
	 */
	public function test_schedule_skips_an_order_already_asked(): void {
		$order = new \WC_Mock_Order( 'completed', array(), array( '_cosypaw_review_request_sent' => 1750000000 ) );
		Functions\when( 'wc_get_order' )->justReturn( $order );

		( new ReviewRequest( 'cosypaw' ) )->schedule( 77 );

		$this->assertSame( array(), $this->scheduled );
	}

	/**
	 * A package line is expanded into the motifs it held: the email links to the
	 * towels the customer can have an opinion about, not to the bundle SKU.
	 */
	public function test_send_asks_about_the_motifs_inside_a_package(): void {
		Functions\when( 'get_option' )->justReturn(
			array(
				'zirafa' => 11,
				'koala'  => 12,
			)
		);

		$package = new \WC_Mock_Order_Item( 900, array( 'cosypaw_motifs' => 'zirafa,koala' ) );
		$order   = new \WC_Mock_Order( 'completed', array( $package ) );
		Functions\when( 'wc_get_order' )->justReturn( $order );

		( new ReviewRequest( 'cosypaw' ) )->send( 77 );

		$sent = $this->mailer->sent;
		$this->assertCount( 1, $sent );
		$this->assertSame( 'kupac@example.test', $sent[0]['to'] );
		$this->assertStringContainsString( 'http://example.test/product/11/#reviews', $sent[0]['body'] );
		$this->assertStringContainsString( 'http://example.test/product/12/#reviews', $sent[0]['body'] );
		// The package product itself is not something to review.
		$this->assertStringNotContainsString( '/product/900/', $sent[0]['body'] );

		$this->assertSame( 1, $order->saves, 'The ask must be marked sent.' );
		$this->assertNotSame( '', $order->get_meta( '_cosypaw_review_request_sent' ) );
	}

	/**
	 * An order refunded during the week between scheduling and sending gets no
	 * email, and keeps no sent marker either — nothing happened.
	 */
	public function test_send_stays_silent_on_an_order_no_longer_completed(): void {
		Functions\when( 'get_option' )->justReturn( array( 'zirafa' => 11 ) );

		$order = new \WC_Mock_Order( 'refunded', array( new \WC_Mock_Order_Item( 11 ) ) );
		Functions\when( 'wc_get_order' )->justReturn( $order );

		( new ReviewRequest( 'cosypaw' ) )->send( 77 );

		$this->assertSame( array(), $this->mailer->sent );
		$this->assertSame( 0, $order->saves );
	}

	/**
	 * A motif retired since the order was placed has no page to link to, so an
	 * order made only of retired motifs produces no email at all.
	 */
	public function test_send_stays_silent_when_nothing_can_be_reviewed(): void {
		Functions\when( 'get_option' )->justReturn( array( 'zirafa' => 11 ) );
		Functions\when( 'comments_open' )->justReturn( false );

		$order = new \WC_Mock_Order( 'completed', array( new \WC_Mock_Order_Item( 11 ) ) );
		Functions\when( 'wc_get_order' )->justReturn( $order );

		( new ReviewRequest( 'cosypaw' ) )->send( 77 );

		$this->assertSame( array(), $this->mailer->sent );
		$this->assertSame( 0, $order->saves );
	}
}
