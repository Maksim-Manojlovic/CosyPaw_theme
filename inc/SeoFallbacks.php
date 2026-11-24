<?php
/**
 * SeoFallbacks — what Yoast prints where nobody has written anything, and what
 * it should not print at all.
 *
 * Google's result for the shop showed three kinds of damage that all trace to
 * Yoast fields nobody filled in or pages that should not be in the index:
 *
 *  - The shop page, the product categories and some products have no meta
 *    description, so Google wrote one from the first text on the page: the
 *    first product card ("…1.599 RSD Dodaj u korpu"), or the footer.
 *  - WordPress's own sample content ("Hello world!", "Sample Page") is
 *    published and in the sitemap.
 *  - The 2+1 package products ("Pojedinačno", "Duo paket") are pricing tiers
 *    the cart works out, not things to land on, and their category is just
 *    those two.
 *
 * Fallbacks fill a gap and nothing else: a title or description typed into
 * Yoast always wins, and every product that has its own copy keeps it. The
 * copy is Serbian database-style text, not msgids — Yoast prints what it is
 * given, in whatever language the visitor chose, like \Theme\Pages' own.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

namespace Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SeoFallbacks.
 */
final class SeoFallbacks {

	/**
	 * Longest a description should run before Google cuts it.
	 */
	private const DESCRIPTION_MAX = 155;

	/**
	 * Taxonomy the category pages belong to.
	 */
	private const TAXONOMY = 'product_cat';

	/**
	 * The category that holds only the package tiers.
	 */
	private const PACKAGE_CATEGORY = 'paketi';

	/**
	 * Slugs of the sample content WordPress installs.
	 */
	private const SAMPLE_POST = 'hello-world';
	private const SAMPLE_PAGE = 'sample-page';

	/**
	 * The shop page.
	 *
	 * @var array{title:string,description:string}
	 */
	public const SHOP = array(
		'title'       => 'Prodavnica | Dečiji Peškiri, Jastuci i Poklon Paketi | CosyPaw',
		'description' => 'Svi CosyPaw proizvodi na jednom mestu: ručno rađeni dečiji peškiri, Cuddle Puff jastuci, magične krpe i poklon paketi. Plaćanje pouzećem, dostava 2–4 dana.',
	);

	/**
	 * Category slug => title and description.
	 *
	 * Worded differently from the landing pages that sell the same range
	 * (\Theme\Pages::REGISTRY), so a category and its landing page do not
	 * compete for one snippet.
	 *
	 * @var array<string,array{title:string,description:string}>
	 */
	public const CATEGORIES = array(
		'peskiri'            => array(
			'title'       => 'Dečiji Peškiri | Životinjice, Zalogajčići, Cvetići | CosyPaw',
			'description' => 'Svi CosyPaw peškiri za decu na jednom mestu: životinjice, zalogajčići, cvetići i crtani likovi. Ručno rađeni od mikrofibere, sa alkom za kačenje. Pouzećem!',
		),
		'zivotinjice'        => array(
			'title'       => 'Peškiri u Obliku Životinja | Zeka, Panda, Sovica | CosyPaw',
			'description' => 'Dečiji peškiri u obliku životinja: zeka, panda, sovica, koala i još mnogo njih. Ručno rađeni od mekane mikrofibere, sa alkom za kačenje. Poruči pouzećem!',
		),
		'zalogajcici'        => array(
			'title'       => 'Peškiri u Obliku Hrane | Krofnica, Keksić, Avokado | CosyPaw',
			'description' => 'Dečiji peškiri u obliku zalogajčića: krofnica, keksić, ananas i avokado. Ručno rađeni od mikrofibere, mekani i upijajući. Poruči pouzećem!',
		),
		'cvetici-i-listici'  => array(
			'title'       => 'Peškiri Cvetići i Listići | Dečiji Peškiri | CosyPaw',
			'description' => 'Dečiji peškiri u obliku cvetića i listića, ručno rađeni od mekane mikrofibere, sa alkom za kačenje. Poruči pouzećem, dostava 2–4 dana širom Srbije.',
		),
		'crtani-likovi'      => array(
			'title'       => 'Peškiri sa Crtanim Likovima za Decu | CosyPaw',
			'description' => 'Dečiji peškiri u obliku omiljenih crtanih likova, ručno rađeni od mekane mikrofibere, sa alkom za kačenje. Poruči pouzećem, dostava 2–4 dana.',
		),
		'peskiri-u-torbi'    => array(
			'title'       => 'Peškiri u Torbi | Dečiji Peškir sa Torbicom | CosyPaw',
			'description' => 'Dečiji peškiri u torbi: ručno rađeni od mekane mikrofibere, sa alkom za kačenje. Praktičan i slatki poklon za decu. Poruči pouzećem!',
		),
		'cuddle-puff-jastuci' => array(
			'title'       => 'Cuddle Puff Jastuci | Plišani Ukrasni Jastuci | CosyPaw',
			'description' => 'Cuddle Puff jastuci: mekani plišani ukrasni jastuci za stolicu, fotelju i dečiju sobu, ručno rađeni. Izaberi svoj oblik i boju. Poruči pouzećem!',
		),
		'dugi-peskiri'       => array(
			'title'       => 'Peškiri sa Vezenim Medom | Mekani Peškiri | CosyPaw',
			'description' => 'Peškiri sa vezenim medom i šapicom u više boja: mekani i upijajući, za lice, ruke i kosu. Ručno rađeni, plaćanje pouzećem, dostava 2–4 dana.',
		),
		'magicne-krpe'       => array(
			'title'       => 'Magične Krpe od Mikrofibera | Za Staklo i Kuhinju | CosyPaw',
			'description' => 'Magične krpe od mikrofibera za čišćenje bez tragova: kuhinja, staklo, površine. Višekratne, peru se u mašini. Poruči pouzećem, dostava 2–4 dana.',
		),
		'poklon-paketi'      => array(
			'title'       => 'Poklon Paketi | Pokloni za Bebu, Decu i Praznike | CosyPaw',
			'description' => 'Gotovi poklon paketi peškira, jastuka i krpica, lepo upakovani: za bebu, vrtić, useljenje i praznike. Jeftinije u paketu, plaćanje pouzećem.',
		),
	);

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'wpseo_title', array( $this, 'title' ), 20 );
		add_filter( 'wpseo_metadesc', array( $this, 'description' ), 20 );
		add_filter( 'wpseo_robots', array( $this, 'robots' ), 20 );
		add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', array( $this, 'exclude_from_sitemap' ), 20 );
	}

	/**
	 * Document title, where Yoast has none of its own for the view.
	 *
	 * @param mixed $title Title Yoast built.
	 * @return string
	 */
	public function title( $title ): string {
		$title = (string) $title;

		if ( $this->is_shop() ) {
			return $this->has_own_title( $this->shop_id() ) ? $title : self::SHOP['title'];
		}

		$term = $this->category();
		if ( $term instanceof \WP_Term ) {
			$copy = $this->category_copy( $term );

			return '' === $this->own_term_field( $term, 'title' ) ? $copy['title'] : $title;
		}

		return $title;
	}

	/**
	 * Meta description, where Yoast would print none.
	 *
	 * @param mixed $description Description Yoast built; empty when none.
	 * @return string
	 */
	public function description( $description ): string {
		$description = (string) $description;
		if ( '' !== trim( $description ) ) {
			return $description;
		}

		if ( $this->is_shop() ) {
			return self::SHOP['description'];
		}

		$term = $this->category();
		if ( $term instanceof \WP_Term ) {
			return $this->category_copy( $term )['description'];
		}

		if ( is_singular( 'product' ) ) {
			$id = (int) get_queried_object_id();

			return $id > 0
				? $this->trim_to_length( has_excerpt( $id ) ? get_the_excerpt( $id ) : (string) get_post_field( 'post_content', $id ) )
				: '';
		}

		return '';
	}

	/**
	 * Keep the sample content and the package tiers out of the index. They
	 * stay reachable — the cart links to the tiers — and "follow" lets the
	 * crawler carry on through them.
	 *
	 * @param mixed $robots Robots directive Yoast built.
	 * @return string
	 */
	public function robots( $robots ): string {
		$robots = (string) $robots;

		return $this->is_left_out() ? 'noindex, follow' : $robots;
	}

	/**
	 * Yoast's sitemap lists every published post that is not marked noindex in
	 * its own box; a filter-set noindex does not reach it.
	 *
	 * @param mixed $ids Post ids already excluded.
	 * @return int[]
	 */
	public function exclude_from_sitemap( $ids ): array {
		$ids = array_map( 'intval', (array) $ids );

		$sample = array(
			get_page_by_path( self::SAMPLE_POST, OBJECT, 'post' ),
			get_page_by_path( self::SAMPLE_PAGE ),
		);
		foreach ( $sample as $post ) {
			if ( is_object( $post ) && ! empty( $post->ID ) ) {
				$ids[] = (int) $post->ID;
			}
		}

		return array_values( array_unique( array_merge( $ids, $this->package_ids() ) ) );
	}

	/* ---------------------------------------------------------------------
	 * Internals
	 * ------------------------------------------------------------------ */

	/**
	 * Whether the current view is one to keep out of the index.
	 *
	 * @return bool
	 */
	private function is_left_out(): bool {
		if ( is_single( self::SAMPLE_POST ) || is_page( self::SAMPLE_PAGE ) ) {
			return true;
		}

		if ( is_singular( 'product' ) ) {
			return in_array( (int) get_queried_object_id(), $this->package_ids(), true );
		}

		return is_tax( self::TAXONOMY, self::PACKAGE_CATEGORY );
	}

	/**
	 * Ids of the 2+1 package products.
	 *
	 * @return int[]
	 */
	private function package_ids(): array {
		return array_values( array_map( 'intval', (array) get_option( WooCommerce::PACKAGE_MAP_OPTION, array() ) ) );
	}

	/**
	 * Whether the view is the shop page.
	 *
	 * @return bool
	 */
	private function is_shop(): bool {
		return function_exists( 'is_shop' ) && is_shop();
	}

	/**
	 * The shop page's post id, or 0.
	 *
	 * @return int
	 */
	private function shop_id(): int {
		return function_exists( 'wc_get_page_id' ) ? max( 0, (int) wc_get_page_id( 'shop' ) ) : 0;
	}

	/**
	 * Whether a post carries a Yoast title of its own.
	 *
	 * @param int $post_id Post id.
	 * @return bool
	 */
	private function has_own_title( int $post_id ): bool {
		return $post_id > 0 && '' !== trim( (string) get_post_meta( $post_id, '_yoast_wpseo_title', true ) );
	}

	/**
	 * The category the view is, or null.
	 *
	 * @return \WP_Term|null
	 */
	private function category(): ?\WP_Term {
		if ( ! is_tax( self::TAXONOMY ) ) {
			return null;
		}

		$term = get_queried_object();

		return $term instanceof \WP_Term ? $term : null;
	}

	/**
	 * A field Yoast stores for a term, as typed; empty when unset.
	 *
	 * @param \WP_Term $term  Term.
	 * @param string   $field 'title' or 'desc'.
	 * @return string
	 */
	private function own_term_field( \WP_Term $term, string $field ): string {
		if ( ! class_exists( '\WPSEO_Taxonomy_Meta' ) ) {
			return '';
		}

		$value = \WPSEO_Taxonomy_Meta::get_term_meta( $term, self::TAXONOMY, $field );

		return is_string( $value ) ? trim( $value ) : '';
	}

	/**
	 * Copy for a category: its own entry, else one built from its name.
	 *
	 * @param \WP_Term $term Term.
	 * @return array{title:string,description:string}
	 */
	private function category_copy( \WP_Term $term ): array {
		if ( isset( self::CATEGORIES[ $term->slug ] ) ) {
			return self::CATEGORIES[ $term->slug ];
		}

		return array(
			'title'       => $term->name . ' | CosyPaw',
			'description' => $this->trim_to_length(
				$term->name . ' — CosyPaw ručno rađeni peškiri i dodaci od mikrofibere. Poruči pouzećem, dostava 2–4 dana širom Srbije.'
			),
		);
	}

	/**
	 * Collapse whitespace and cut on a word boundary near the limit.
	 *
	 * @param string $text Raw text.
	 * @return string
	 */
	private function trim_to_length( string $text ): string {
		$text = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $text ) ) ?? '' );

		if ( mb_strlen( $text ) <= self::DESCRIPTION_MAX ) {
			return $text;
		}

		$cut   = mb_substr( $text, 0, self::DESCRIPTION_MAX );
		$space = mb_strrpos( $cut, ' ' );

		return ( false === $space ? $cut : mb_substr( $cut, 0, $space ) ) . '…';
	}
}
