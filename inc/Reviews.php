<?php
/**
 * Reviews — the shop's real reviews, pooled across every motif.
 *
 * Twenty motifs split a small pile of reviews twenty ways, so a product page
 * showing only its own can read as deserted while the shop as a whole is doing
 * fine. This gathers the newest approved ones from every product into a single
 * list the landing page can show, which lets one review work in two places: on
 * the towel it was written about, and as proof on the way in.
 *
 * Rows are cached raw — ids, text, rating — and never with a product name
 * baked in. The name is translated per request, and a cache holding a Serbian
 * title would serve it to a Russian visitor.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

namespace Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reviews.
 *
 * Instantiated by Bootstrap ONLY when class_exists('WooCommerce') is true — the
 * instance exists to invalidate the cache. Reading is static, so a template can
 * ask for the list without reaching into Bootstrap.
 */
final class Reviews {

	/**
	 * Transient key for the pooled review list.
	 *
	 * @var string
	 */
	private const CACHE_KEY = 'cosypaw_latest_reviews';

	/**
	 * Cache lifetime in seconds (6 hours).
	 *
	 * @var int
	 */
	private const CACHE_TTL = 6 * HOUR_IN_SECONDS;

	/**
	 * How many rows are cached. The template asks for fewer; caching a deeper
	 * pool means changing the section's length costs no extra query.
	 *
	 * @var int
	 */
	private const POOL_SIZE = 24;

	/**
	 * Constructor — registers cache invalidation.
	 */
	public function __construct() {
		// Every route a review can take in or out of "approved".
		add_action( 'comment_post', array( $this, 'flush' ) );
		add_action( 'edit_comment', array( $this, 'flush' ) );
		add_action( 'wp_set_comment_status', array( $this, 'flush' ) );
		add_action( 'trashed_comment', array( $this, 'flush' ) );
		add_action( 'untrashed_comment', array( $this, 'flush' ) );
		add_action( 'deleted_comment', array( $this, 'flush' ) );
	}

	/**
	 * Delete the cached list.
	 *
	 * @return void
	 */
	public function flush(): void {
		delete_transient( self::CACHE_KEY );
	}

	/**
	 * The newest approved reviews across the whole catalog.
	 *
	 * Each row: product_id, rating, author, quote, permalink.
	 *
	 * @param int $limit How many to return.
	 * @return array<int,array<string,mixed>>
	 */
	public static function latest( int $limit = 6 ): array {
		if ( $limit < 1 ) {
			return array();
		}

		$pool = get_transient( self::CACHE_KEY );

		if ( ! is_array( $pool ) ) {
			$pool = self::gather();
			set_transient( self::CACHE_KEY, $pool, self::CACHE_TTL );
		}

		return array_slice( self::render( $pool ), 0, $limit );
	}

	/**
	 * Query the reviews worth showing and reduce them to cacheable rows.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	private static function gather(): array {
		if ( ! function_exists( 'get_comments' ) ) {
			return array();
		}

		$product_ids = array_map(
			'intval',
			array_merge(
				array_values( (array) get_option( WooCommerce::PRODUCT_MAP_OPTION, array() ) ),
				array_values( (array) get_option( WooCommerce::PACKAGE_MAP_OPTION, array() ) )
			)
		);

		$product_ids = array_values( array_filter( $product_ids ) );

		if ( ! $product_ids ) {
			return array();
		}

		/**
		 * Lowest rating the landing page will quote.
		 *
		 * The section is the shop's pitch, not its review archive — a three-star
		 * review belongs on the product page, where it sits beside the rest and
		 * can be read in context. Nothing is hidden: every approved review still
		 * shows in full on the towel it was written about.
		 *
		 * @param int $min Minimum rating, 1-5.
		 */
		$min_rating = (int) apply_filters( 'cosypaw_landing_review_min_rating', 4 );

		$comments = get_comments(
			array(
				'post__in'   => $product_ids,
				'post_type'  => 'product',
				'status'     => 'approve',
				'type'       => 'review',
				'orderby'    => 'comment_date_gmt',
				'order'      => 'DESC',
				// Over-fetch: the rating filter below drops an unknown share.
				'number'     => self::POOL_SIZE * 3,
				'no_found_rows' => true,
			)
		);

		$rows = array();

		foreach ( (array) $comments as $comment ) {
			if ( ! is_object( $comment ) || ! isset( $comment->comment_ID ) ) {
				continue;
			}

			$rating = (int) get_comment_meta( (int) $comment->comment_ID, 'rating', true );
			$text   = trim( (string) $comment->comment_content );

			if ( $rating < $min_rating || '' === $text ) {
				continue;
			}

			$rows[] = array(
				'id'         => (int) $comment->comment_ID,
				'product_id' => (int) $comment->comment_post_ID,
				'rating'     => min( 5, $rating ),
				'author'     => trim( (string) $comment->comment_author ),
				'text'       => $text,
			);

			if ( count( $rows ) >= self::POOL_SIZE ) {
				break;
			}
		}

		return $rows;
	}

	/**
	 * Turn cached rows into what a template prints: trimmed quote, display name,
	 * translated product name and a link to the review in place.
	 *
	 * @param array<int,array<string,mixed>> $pool Cached rows.
	 * @return array<int,array<string,mixed>>
	 */
	private static function render( array $pool ): array {
		$out = array();

		foreach ( $pool as $row ) {
			$product_id = (int) ( $row['product_id'] ?? 0 );

			// A motif retired since the review was written has no page left to
			// link to, and quoting it would advertise something unbuyable.
			if ( ! $product_id || 'publish' !== get_post_status( $product_id ) ) {
				continue;
			}

			$permalink = get_permalink( $product_id );
			$author    = (string) ( $row['author'] ?? '' );

			$out[] = array(
				'quote'     => wp_trim_words( (string) ( $row['text'] ?? '' ), 34, '…' ),
				// An anonymous review still deserves a byline, and "Kupac" is
				// truer than inventing initials for someone who left none.
				'name'      => '' !== $author ? $author : __( 'Kupac', 'cosypaw' ),
				'meta'      => (string) get_the_title( $product_id ),
				'rating'    => (int) ( $row['rating'] ?? 5 ),
				'permalink' => is_string( $permalink ) ? $permalink . '#comment-' . (int) ( $row['id'] ?? 0 ) : '',
			);
		}

		return $out;
	}
}
