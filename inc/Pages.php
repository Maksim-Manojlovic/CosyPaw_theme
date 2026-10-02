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
	 * Bump when a page is added to REGISTRY or reworded (REWORDED), so
	 * installs that already ran ensure() look again.
	 */
	public const VERSION = 6;

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
			'title'           => 'Dečiji peškiri u obliku životinja',
			'seo_title'       => 'Dečiji Peškiri u Obliku Životinja | Najlepši Peškiri za Decu',
			'seo_description' => 'Dečiji peškiri u obliku životinja čine pranje ruku zabavnim! Peškir za ruke zeka, sovica ili panda, ručno rađen od mikrofibre. Pogledaj kolekciju!',
		),
		'pokloni' => array(
			'slug'            => 'poklon-setovi-za-bebu-i-decu',
			'title'           => 'Poklon setovi peškira za bebu i decu',
			'seo_title'       => 'Poklon Setovi Peškira za Bebu i Decu | Unikatni Pokloni Srbija',
			'seo_description' => 'Naši poklon setovi peškira za bebu i decu su ručno rađeni, lepo upakovani i idealni za krštenje, rođenje ili baby shower. Naruči unikatni poklon danas!',
		),
		'krpe'    => array(
			'slug'            => 'magicne-krpe',
			'title'           => 'Magične krpe',
			'seo_title'       => 'Magična Krpa od Mikrofibera | Najbolja Krpa za Staklo i Kuhinju',
			'seo_description' => 'Magična krpa od mikrofibera za čišćenje bez tragova — kuhinjska krpa i krpa za staklo u jednom. Višekratna, pere se u mašini. Poruči odmah!',
		),
		'jastuci' => array(
			'slug'            => 'cuddle-puff-jastuci',
			'title'           => 'Cuddle Puff jastuci',
			'seo_title'       => 'Cuddle Puff Jastuci | Plišani Jastuci za Stolicu i Sofu',
			'seo_description' => 'Cuddle Puff jastuci sa vezenim okicama, u obliku kružića ili kockasti. Mekani ukrasni jastuci za stolicu, fotelju i dečiju sobu. Poruči pouzećem!',
		),
		'dugi'    => array(
			'slug'            => 'dugi-peskiri',
			'title'           => 'Dugi peškiri',
			'seo_title'       => 'Dugi Peškiri sa Vezenim Medom | Mekani Peškiri za Kupatilo',
			'seo_description' => 'Dugi peškiri sa vezenim medom i šapicom, u krem, roze, plavoj i bež boji. Mekani i upijajući, za lice, ruke i kosu. Poruči uz plaćanje pouzećem!',
		),
		'paketi'  => array(
			'slug'            => 'poklon-paketi',
			'title'           => 'Poklon paketi',
			'seo_title'       => 'Poklon Paketi | Pokloni za Bebu, Useljenje i Praznike',
			'seo_description' => 'Gotovi poklon paketi peškira, jastuka i krpica: za bebu, vrtić, useljenje, mladence, vaspitačicu i Novu godinu. Jeftinije u paketu, pouzećem!',
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
		$this->reword();
		update_option( self::OPTION, self::VERSION );
	}

	/**
	 * What a registered page said before its REGISTRY copy was reworded:
	 * key => the title, Yoast title and description the theme wrote then.
	 *
	 * The collection's towels are "oblici", no longer "motivi"; its address
	 * stays, so it keeps what it ranks for.
	 *
	 * @var array<string,array{title:string,seo_title:string,seo_description:string}>
	 */
	private const REWORDED = array(
		'motivi' => array(
			'title'           => 'Dečiji peškiri sa motivima životinja',
			'seo_title'       => 'Dečiji Peškiri sa Motivima Životinja | Najlepši Peškiri za Decu',
			'seo_description' => 'Dečiji peškiri sa motivima životinja čine pranje ruku zabavnim! Peškir za ruke zeka, sovica ili panda, ručno rađen od mikrofibre. Pogledaj kolekciju!',
		),
	);

	/**
	 * Bring existing pages up to their reworded REGISTRY copy.
	 *
	 * The one exception to "a page that exists is never touched", and a
	 * narrow one: a field is replaced only while it still holds exactly what
	 * the theme wrote (REWORDED). Anything the shop has edited since is its
	 * own and stays. Runs once, with the version bump that carried it.
	 *
	 * @return int Number of fields updated.
	 */
	public function reword(): int {
		$updated = 0;

		foreach ( self::REWORDED as $key => $old ) {
			$new  = self::REGISTRY[ $key ];
			$page = get_page_by_path( $new['slug'] );
			if ( ! is_object( $page ) || empty( $page->ID ) ) {
				continue;
			}

			$id   = (int) $page->ID;
			$post = array();

			if ( $old['title'] === (string) ( $page->post_title ?? '' ) ) {
				$post['post_title'] = $new['title'];
			}
			// The excerpt carries the description for the theme's own meta tag.
			if ( $old['seo_description'] === (string) ( $page->post_excerpt ?? '' ) ) {
				$post['post_excerpt'] = $new['seo_description'];
			}
			if ( $post ) {
				wp_update_post( array( 'ID' => $id ) + $post );
				$updated += count( $post );
			}

			$yoast = array(
				'_yoast_wpseo_title'    => 'seo_title',
				'_yoast_wpseo_metadesc' => 'seo_description',
			);
			foreach ( $yoast as $meta => $field ) {
				if ( $old[ $field ] === (string) get_post_meta( $id, $meta, true ) ) {
					update_post_meta( $id, $meta, $new[ $field ] );
					++$updated;
				}
			}
		}

		return $updated;
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
