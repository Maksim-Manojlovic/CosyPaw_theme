<?php
/**
 * Unit tests for the pooled landing-page review list.
 *
 * @package CosyPaw\Tests
 */

declare(strict_types=1);

namespace Theme\Tests;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use Theme\Reviews;

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}
if ( ! defined( 'HOUR_IN_SECONDS' ) ) {
	define( 'HOUR_IN_SECONDS', 3600 );
}

require_once __DIR__ . '/stubs.php';
require_once dirname( __DIR__ ) . '/inc/WooCommerce.php';
require_once dirname( __DIR__ ) . '/inc/Reviews.php';

final class ReviewsTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		Functions\stubs(
			array(
				'add_action'       => true,
				'add_filter'       => true,
				'apply_filters'    => static fn( $hook, $value = null ) => $value,
				'__'               => static fn( $text ) => $text,
				'get_transient'    => false,
				'set_transient'    => true,
				'delete_transient' => true,
				'get_post_status'  => 'publish',
				'get_permalink'    => static fn( $id ) => 'http://example.test/product/' . $id . '/',
				'get_the_title'    => static fn( $id ) => 'Motiv ' . $id,
				'wp_trim_words'    => static fn( $text ) => $text,
			)
		);
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Build a comment row the way WP_Comment_Query hands them over.
	 *
	 * @param int    $id      Comment ID.
	 * @param int    $post_id Product ID.
	 * @param string $author  Comment author.
	 * @param string $content Comment body.
	 * @return object
	 */
	private function comment( int $id, int $post_id, string $author = 'Ana', string $content = 'Mekani su.' ): object {
		return (object) array(
			'comment_ID'      => $id,
			'comment_post_ID' => $post_id,
			'comment_author'  => $author,
			'comment_content' => $content,
		);
	}

	/**
	 * Point the catalog at a set of seeded products.
	 *
	 * @param array<string,int> $map Motif id => product id.
	 * @return void
	 */
	private function seed( array $map ): void {
		Functions\when( 'get_option' )->alias(
			static fn( $option ) => \Theme\WooCommerce::PRODUCT_MAP_OPTION === $option ? $map : array()
		);
	}

	/**
	 * Ratings live in comment meta.
	 *
	 * @param array<int,int> $ratings Comment ID => rating.
	 * @return void
	 */
	private function ratings( array $ratings ): void {
		Functions\when( 'get_comment_meta' )->alias(
			static fn( $comment_id ) => $ratings[ (int) $comment_id ] ?? 0
		);
	}

	/**
	 * A review is quoted with its rating, its author and a link back to itself
	 * on the product page it was written on.
	 */
	public function test_latest_quotes_an_approved_review(): void {
		$this->seed( array( 'zirafa' => 11 ) );
		$this->ratings( array( 5 => 5 ) );
		Functions\when( 'get_comments' )->justReturn( array( $this->comment( 5, 11 ) ) );

		$out = Reviews::latest( 6 );

		$this->assertCount( 1, $out );
		$this->assertSame( 'Mekani su.', $out[0]['quote'] );
		$this->assertSame( 'Ana', $out[0]['name'] );
		$this->assertSame( 5, $out[0]['rating'] );
		// The meta line names the towel, not a city.
		$this->assertSame( 'Motiv 11', $out[0]['meta'] );
		$this->assertSame( 'http://example.test/product/11/#comment-5', $out[0]['permalink'] );
	}

	/**
	 * A three-star review belongs on the product page, where it can be read in
	 * context, not in the shop's own pitch.
	 */
	public function test_latest_leaves_low_ratings_to_the_product_page(): void {
		$this->seed( array( 'zirafa' => 11 ) );
		$this->ratings(
			array(
				5 => 3,
				6 => 4,
			)
		);
		Functions\when( 'get_comments' )->justReturn(
			array( $this->comment( 5, 11 ), $this->comment( 6, 11 ) )
		);

		$out = Reviews::latest( 6 );

		$this->assertCount( 1, $out );
		$this->assertSame( 4, $out[0]['rating'] );
	}

	/**
	 * A motif retired since the review was written has no page left to link to,
	 * so quoting it would advertise something that cannot be bought.
	 */
	public function test_latest_drops_reviews_of_retired_motifs(): void {
		$this->seed( array( 'zirafa' => 11 ) );
		$this->ratings( array( 5 => 5 ) );
		Functions\when( 'get_comments' )->justReturn( array( $this->comment( 5, 11 ) ) );
		Functions\when( 'get_post_status' )->justReturn( 'draft' );

		$this->assertSame( array(), Reviews::latest( 6 ) );
	}

	/**
	 * An anonymous review keeps its byline rather than inventing one.
	 */
	public function test_latest_names_an_anonymous_reviewer(): void {
		$this->seed( array( 'zirafa' => 11 ) );
		$this->ratings( array( 5 => 5 ) );
		Functions\when( 'get_comments' )->justReturn( array( $this->comment( 5, 11, '' ) ) );

		$out = Reviews::latest( 6 );

		$this->assertSame( 'Kupac', $out[0]['name'] );
	}

	/**
	 * Nothing seeded means nothing to quote — and no comment query either.
	 */
	public function test_latest_is_empty_before_the_catalog_is_seeded(): void {
		$this->seed( array() );
		Functions\when( 'get_comments' )->justReturn( array( $this->comment( 5, 11 ) ) );

		$this->assertSame( array(), Reviews::latest( 6 ) );
	}

	/**
	 * The template's limit wins over however deep the cached pool runs.
	 */
	public function test_latest_returns_no_more_than_asked_for(): void {
		$this->seed( array( 'zirafa' => 11 ) );
		$this->ratings( array( 1 => 5, 2 => 5, 3 => 5, 4 => 5 ) );
		Functions\when( 'get_comments' )->justReturn(
			array(
				$this->comment( 1, 11 ),
				$this->comment( 2, 11 ),
				$this->comment( 3, 11 ),
				$this->comment( 4, 11 ),
			)
		);

		$this->assertCount( 2, Reviews::latest( 2 ) );
		$this->assertSame( array(), Reviews::latest( 0 ) );
	}
}
