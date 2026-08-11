<?php
/**
 * SEO — meta description, social cards, hreflang and structured data.
 *
 * The theme previously emitted none of this. `title-tag` support was the only
 * SEO surface in the codebase, which left three concrete gaps:
 *
 *  1. No meta description, so search engines wrote their own snippet from
 *     whatever text they found first.
 *  2. No Open Graph or Twitter tags. The shop sells through Instagram DMs, so
 *     every shared link rendered as a bare URL with no image or description.
 *  3. No hreflang, despite the site serving three languages off the same URLs
 *     via ?lang=. Search engines saw three different pages at one address with
 *     nothing to say they were translations of each other.
 *
 * Deliberately narrow: this is not a replacement for an SEO plugin. If Yoast or
 * Rank Math is ever activated, `cosypaw_seo_enabled` can be filtered to false
 * to stand this down rather than emit competing tags.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

namespace Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Seo.
 */
final class Seo {

	/**
	 * Longest a meta description should run before search engines truncate it.
	 */
	private const DESCRIPTION_MAX = 160;

	/**
	 * Catalog, for pricing in the front-page structured data.
	 *
	 * @var Catalog
	 */
	private Catalog $catalog;

	/**
	 * Constructor.
	 *
	 * @param Catalog $catalog Catalog data object.
	 */
	public function __construct( Catalog $catalog ) {
		$this->catalog = $catalog;

		add_action( 'wp_head', array( $this, 'render' ), 5 );
	}

	/**
	 * Emit every tag this module owns.
	 *
	 * @return void
	 */
	public function render(): void {
		/**
		 * Allow the whole module to stand down, e.g. when a dedicated SEO
		 * plugin is activated and would otherwise emit competing tags.
		 *
		 * @param bool $enabled Whether to output theme SEO tags.
		 */
		if ( ! apply_filters( 'cosypaw_seo_enabled', true ) ) {
			return;
		}

		$this->render_description();
		$this->render_social();
		$this->render_hreflang();
		$this->render_schema();
	}

	/* ---------------------------------------------------------------------
	 * Description
	 * ------------------------------------------------------------------ */

	/**
	 * The description for the current view.
	 *
	 * @return string Plain text, already trimmed to length. Empty if none.
	 */
	private function description(): string {
		$text = '';

		if ( is_front_page() ) {
			$text = __( 'Ručno šiveni ukrasni peškirići za kupatilo — mekana mikrofibra, alka za kačenje i preko 20 motiva. Sastavi svoj paket, plaćanje pouzećem, dostava 2–4 dana širom Srbije.', 'cosypaw' );
		} elseif ( is_singular() ) {
			$post = get_queried_object();
			if ( $post instanceof \WP_Post ) {
				$text = has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_strip_all_tags( (string) $post->post_content );
			}
		} elseif ( is_category() || is_tag() || is_tax() ) {
			$text = wp_strip_all_tags( (string) term_description() );
		} elseif ( is_search() ) {
			/* translators: %s: search term. */
			$text = sprintf( __( 'Rezultati pretrage za „%s“.', 'cosypaw' ), get_search_query() );
		}

		return $this->trim_to_length( $text );
	}

	/**
	 * Collapse whitespace and cut on a word boundary near the limit.
	 *
	 * @param string $text Raw text.
	 * @return string
	 */
	private function trim_to_length( string $text ): string {
		$text = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $text ) ) ?? '' );

		if ( '' === $text || mb_strlen( $text ) <= self::DESCRIPTION_MAX ) {
			return $text;
		}

		$cut   = mb_substr( $text, 0, self::DESCRIPTION_MAX );
		$space = mb_strrpos( $cut, ' ' );

		return ( false === $space ? $cut : mb_substr( $cut, 0, $space ) ) . '…';
	}

	/**
	 * Output the meta description.
	 *
	 * @return void
	 */
	private function render_description(): void {
		$description = $this->description();
		if ( '' === $description ) {
			return;
		}

		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $description ) );
	}

	/* ---------------------------------------------------------------------
	 * Social cards
	 * ------------------------------------------------------------------ */

	/**
	 * Open Graph + Twitter tags.
	 *
	 * The shop's traffic arrives through Instagram, so a shared link that
	 * unfurls with a photograph and a sentence is worth more here than on a
	 * typical brochure site.
	 *
	 * @return void
	 */
	private function render_social(): void {
		$title       = wp_get_document_title();
		$description = $this->description();
		$image       = $this->social_image();
		$url         = $this->current_url();

		$tags = array(
			'og:type'       => is_singular() && ! is_front_page() ? 'article' : 'website',
			'og:site_name'  => get_bloginfo( 'name' ),
			'og:title'      => $title,
			'og:url'        => $url,
			'og:locale'     => get_locale(),
		);

		if ( '' !== $description ) {
			$tags['og:description'] = $description;
		}

		if ( '' !== $image ) {
			$tags['og:image']     = $image;
			$tags['og:image:alt'] = get_bloginfo( 'name' );
		}

		foreach ( $tags as $property => $content ) {
			printf(
				'<meta property="%1$s" content="%2$s">' . "\n",
				esc_attr( $property ),
				esc_attr( $content )
			);
		}

		// Twitter reads og:* for most fields; card type and image are its own.
		printf(
			'<meta name="twitter:card" content="%s">' . "\n",
			'' !== $image ? 'summary_large_image' : 'summary'
		);
	}

	/**
	 * Best available sharing image for the current view.
	 *
	 * @return string Absolute URL, or an empty string.
	 */
	private function social_image(): string {
		if ( is_singular() && has_post_thumbnail() ) {
			$src = wp_get_attachment_image_src( (int) get_post_thumbnail_id(), 'large' );
			if ( is_array( $src ) && ! empty( $src[0] ) ) {
				return (string) $src[0];
			}
		}

		// Fall back to a lifestyle shot: the product photography is the whole
		// pitch, and a logo unfurl would waste the card.
		return get_template_directory_uri() . '/assets/lifestyle1.avif';
	}

	/**
	 * Canonical-ish URL for the current request, language parameter preserved.
	 *
	 * @return string
	 */
	private function current_url(): string {
		$permalink = is_singular() ? get_permalink() : home_url( add_query_arg( array() ) );

		return is_string( $permalink ) && '' !== $permalink ? $permalink : home_url( '/' );
	}

	/* ---------------------------------------------------------------------
	 * hreflang
	 * ------------------------------------------------------------------ */

	/**
	 * Declare the sr/en/ru variants of the current URL as translations.
	 *
	 * Without these, three languages served off one URL look to a crawler like
	 * one page whose content keeps changing.
	 *
	 * @return void
	 */
	private function render_hreflang(): void {
		if ( ! function_exists( 'cosypaw_language' ) ) {
			return;
		}

		$language = cosypaw_language();
		$base     = $this->current_url();

		foreach ( $language->codes() as $code ) {
			// BCP-47 wants a hyphen; WordPress locales use an underscore.
			$tag = str_replace( '_', '-', $language->locale_for( $code ) );

			printf(
				'<link rel="alternate" hreflang="%1$s" href="%2$s">' . "\n",
				esc_attr( $tag ),
				esc_url( add_query_arg( 'lang', $code, $base ) )
			);
		}

		// The unparameterised URL serves the default language.
		printf(
			'<link rel="alternate" hreflang="x-default" href="%s">' . "\n",
			esc_url( remove_query_arg( 'lang', $base ) )
		);
	}

	/* ---------------------------------------------------------------------
	 * Structured data
	 * ------------------------------------------------------------------ */

	/**
	 * JSON-LD for the current view.
	 *
	 * @return void
	 */
	private function render_schema(): void {
		$graph = array( $this->schema_organization() );

		if ( is_front_page() ) {
			$graph[] = $this->schema_website();
			$graph[] = $this->schema_product_group();

			$faq = $this->schema_faq();
			if ( null !== $faq ) {
				$graph[] = $faq;
			}
		}

		$payload = array(
			'@context' => 'https://schema.org',
			'@graph'   => $graph,
		);

		printf(
			'<script type="application/ld+json">%s</script>' . "\n",
			wp_json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
		);
	}

	/**
	 * Organization node.
	 *
	 * @return array<string,mixed>
	 */
	private function schema_organization(): array {
		$node = array(
			'@type'  => 'Organization',
			'@id'    => home_url( '/#organization' ),
			'name'   => get_bloginfo( 'name' ),
			'url'    => home_url( '/' ),
			'sameAs' => array( 'https://instagram.com/cosypaw' ),
		);

		$logo_id = (int) get_theme_mod( 'custom_logo' );
		if ( $logo_id > 0 ) {
			$src = wp_get_attachment_image_src( $logo_id, 'full' );
			if ( is_array( $src ) && ! empty( $src[0] ) ) {
				$node['logo'] = (string) $src[0];
			}
		}

		return $node;
	}

	/**
	 * WebSite node, including the search action so Google can offer a sitelinks
	 * search box.
	 *
	 * @return array<string,mixed>
	 */
	private function schema_website(): array {
		return array(
			'@type'           => 'WebSite',
			'@id'             => home_url( '/#website' ),
			'url'             => home_url( '/' ),
			'name'            => get_bloginfo( 'name' ),
			'publisher'       => array( '@id' => home_url( '/#organization' ) ),
			'inLanguage'      => get_locale(),
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => array(
					'@type'       => 'EntryPoint',
					'urlTemplate' => home_url( '/?s={search_term_string}' ),
				),
				'query-input' => 'required name=search_term_string',
			),
		);
	}

	/**
	 * The motif range as a single ProductGroup with a price range, rather than
	 * 20 loose Product nodes the front page does not individually address.
	 *
	 * @return array<string,mixed>
	 */
	private function schema_product_group(): array {
		$products = array_values(
			array_filter(
				$this->catalog->products(),
				static fn( array $row ): bool => (bool) ( $row['available'] ?? true )
			)
		);

		$prices = array_map( 'intval', array_column( $products, 'price' ) );
		$low    = $prices ? min( $prices ) : Catalog::UNIT_PRICE;
		$high   = $prices ? max( $prices ) : Catalog::UNIT_PRICE;

		return array(
			'@type'       => 'ProductGroup',
			'@id'         => home_url( '/#motifs' ),
			'name'        => __( 'CosyPaw ukrasni peškirići', 'cosypaw' ),
			'description' => __( 'Ručno šiveni ukrasni peškirići od plišane mikrofibre, sa alkom za kačenje.', 'cosypaw' ),
			'brand'       => array( '@id' => home_url( '/#organization' ) ),
			'offers'      => array(
				'@type'         => 'AggregateOffer',
				'priceCurrency' => 'RSD',
				'lowPrice'      => $low,
				'highPrice'     => $high,
				'offerCount'    => count( $products ),
				'availability'  => 'https://schema.org/InStock',
			),
		);
	}

	/**
	 * FAQPage node built from the same source as the rendered FAQ.
	 *
	 * The front page already answers the five questions buyers ask; marking
	 * them up makes them eligible for the FAQ rich result.
	 *
	 * @return array<string,mixed>|null Null when no questions are registered.
	 */
	private function schema_faq(): ?array {
		$faqs = self::faqs();
		if ( ! $faqs ) {
			return null;
		}

		return array(
			'@type'      => 'FAQPage',
			'@id'        => home_url( '/#faq' ),
			'mainEntity' => array_map(
				static fn( array $faq ): array => array(
					'@type'          => 'Question',
					'name'           => $faq['q'],
					'acceptedAnswer' => array(
						'@type' => 'Answer',
						'text'  => $faq['a'],
					),
				),
				$faqs
			),
		);
	}

	/**
	 * The FAQ content.
	 *
	 * Lives here rather than inline in front-page.php so the rendered accordion
	 * and the FAQPage structured data cannot drift apart — Google penalises
	 * markup that does not match visible content.
	 *
	 * @return array<int,array{q:string,a:string}>
	 */
	public static function faqs(): array {
		return array(
			array(
				'q' => __( 'Od čega su peškirići napravljeni?', 'cosypaw' ),
				'a' => __( 'Od plišane mikrofibre — mekane, lagane i jako upijajuće. Prijatna je i nežnoj dečjoj koži.', 'cosypaw' ),
			),
			array(
				'q' => __( 'Kako se peru?', 'cosypaw' ),
				'a' => __( 'Mašinsko pranje na 40°C, bez omekšivača da ostanu upijajući. Suše se brzo i ne gube oblik.', 'cosypaw' ),
			),
			array(
				'q' => __( 'Koliko traje dostava?', 'cosypaw' ),
				'a' => __( 'Dostava je 2–4 radna dana na teritoriji cele Srbije. Trio paket stiže uz besplatnu dostavu.', 'cosypaw' ),
			),
			array(
				'q' => __( 'Kako mogu da platim?', 'cosypaw' ),
				'a' => __( 'Plaćanje je pouzećem — platiš kuriru pri preuzimanju paketa.', 'cosypaw' ),
			),
			array(
				'q' => __( 'Mogu li da vratim proizvod?', 'cosypaw' ),
				'a' => __( 'Naravno. Imaš 14 dana da vratiš nekorišćen peškirić uz povraćaj novca.', 'cosypaw' ),
			),
		);
	}
}
