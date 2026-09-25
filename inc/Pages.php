<?php
/**
 * Pages — the landing pages the theme renders, and the WordPress pages they
 * hang off.
 *
 * The SEO plan asks for pages at fixed addresses ("/deciji-peskiri-sa-motivima-
 * zivotinja/"). Their content is written in the theme, in three languages, like
 * the front page — but WordPress routes a URL to a template only through a
 * post, and Yoast can only give a page its own title, description, canonical
 * and sitemap entry when there is a post to attach them to. So each one is a
 * real page with an empty body, rendered by `page-{slug}.php`.
 *
 * Deploys ship the theme and never the database, so the pages cannot be made
 * by hand on one install and expected on the other. This creates any that are
 * missing, once per registry version, the first time an administrator opens
 * wp-admin. A page that already exists is never touched: its title, slug and
 * Yoast fields belong to whoever edited them last.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

namespace Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pages.
 */
final class Pages {

	/**
	 * Bump when a page is added to REGISTRY, so installs that already ran
	 * ensure() look again.
	 */
	public const VERSION = 1;

	/**
	 * Option recording which registry version this install has been brought up to.
	 */
	private const OPTION = 'cosypaw_pages_version';

	/**
	 * Key => page definition.
	 *
	 * Titles and SEO fields are Serbian database values, not msgids: Yoast
	 * stores and prints them as typed, and the shop edits them in wp-admin.
	 *
	 * @var array<string,array{slug:string,title:string,seo_title:string,seo_description:string}>
	 */
	public const REGISTRY = array(
		'motivi' => array(
			'slug'            => 'deciji-peskiri-sa-motivima-zivotinja',
			'title'           => 'Dečiji peškiri sa motivima životinja',
			'seo_title'       => 'Dečiji Peškiri sa Motivima Životinja | Najlepši Peškiri za Decu',
			'seo_description' => 'Dečiji peškiri sa motivima životinja čine pranje ruku zabavnim! Peškir za ruke zeka, sovica ili panda, ručno rađen od mikrofibre. Pogledaj kolekciju!',
		),
	);

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'maybe_ensure' ) );
	}

	/**
	 * Run ensure() unless this install is already at the current version.
	 *
	 * @return void
	 */
	public function maybe_ensure(): void {
		if ( (int) get_option( self::OPTION, 0 ) >= self::VERSION || ! current_user_can( 'publish_pages' ) ) {
			return;
		}

		$this->ensure();
		update_option( self::OPTION, self::VERSION );
	}

	/**
	 * Create every registered page that does not exist yet.
	 *
	 * @return int Number of pages created.
	 */
	public function ensure(): int {
		$created = 0;

		foreach ( self::REGISTRY as $page ) {
			if ( get_page_by_path( $page['slug'] ) ) {
				continue;
			}

			$id = wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => $page['title'],
					'post_name'    => $page['slug'],
					'post_content' => '',
					// The theme's own meta description reads the excerpt when
					// no SEO plugin is active.
					'post_excerpt' => $page['seo_description'],
				),
				true
			);

			if ( is_wp_error( $id ) || $id < 1 ) {
				continue;
			}

			// Yoast's own post meta. Written once, at creation — edits made
			// in the Yoast box afterwards are the shop's and stay.
			update_post_meta( $id, '_yoast_wpseo_title', $page['seo_title'] );
			update_post_meta( $id, '_yoast_wpseo_metadesc', $page['seo_description'] );

			++$created;
		}

		return $created;
	}

	/**
	 * URL of a registered page, or '' while it does not exist or is not published.
	 *
	 * @param string $key REGISTRY key.
	 * @return string
	 */
	public static function url( string $key ): string {
		$slug = self::REGISTRY[ $key ]['slug'] ?? '';
		if ( '' === $slug ) {
			return '';
		}

		$page = get_page_by_path( $slug );
		if ( ! $page instanceof \WP_Post || 'publish' !== $page->post_status ) {
			return '';
		}

		$url = get_permalink( $page );

		return is_string( $url ) ? $url : '';
	}

	/**
	 * Whether the current view sells from the catalogue — the front page or one
	 * of the registered landing pages — and so needs the landing assets.
	 *
	 * @return bool
	 */
	public static function is_storefront(): bool {
		return is_front_page() || is_page( array_column( self::REGISTRY, 'slug' ) );
	}
}
