<?php
/**
 * Front page — the CosyPaw landing experience.
 *
 * Section order (hero, motif grid, packages, lifestyle, benefits, testimonials,
 * FAQ) is deliberate: the catalogue has to precede the bundle builder, because
 * step 2 of the builder asks the visitor to choose motifs and the gallery is
 * where they meet them. It used to run the other way round — "Upoznaj sve
 * peškiriće" sat after the section that made you pick three. Everything from
 * lifestyle down is objection handling, ordered softest first.
 *
 * Data comes from the plain \Theme\Catalog data object.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$catalog = new \Theme\Catalog();

// Products, packages, the per-piece ladder and the free-delivery bar come from
// the same place the motif collection page reads them, so the two pages cannot
// disagree about a price. See \Theme\Storefront::context().
$storefront     = \Theme\Storefront::context( $catalog );
$products       = $storefront['products'];
$from_price     = $storefront['from_price'];
$packages       = $storefront['packages'];
$default_pkg    = $storefront['default_pkg'];
$selected       = $storefront['selected'];
$cosypaw_ladder = $storefront['ladder'];
$free_min       = $storefront['free_min'];

$featured = array_values( array_filter( $catalog->featured(), array( \Theme\Storefront::class, 'in_stock' ) ) );

// The hero lead used to carry four facts — material, hanging loop, catalogue
// size and an instruction — two of which are repeated verbatim in the benefits
// section. It is one line now; the offer does the selling, from the ribbon and
// the button. The headline now names the product the way people search for it
// ("dečiji peškiri od mikrofibera"), so the brand's own line moved down here.
$tagline = sprintf(
	/* translators: %d: how many motifs are on sale. */
	__( 'Ručno rađeni peškiri sa alkom za kačenje — %d motiva koji grle tvoje kupatilo.', 'cosypaw' ),
	count( $products )
);

/*
 * Every offer claim in the hero is derived from the package the bundle builder
 * opens on, never authored. Reprice the package in wp-admin and the "2+1 GRATIS"
 * ribbon and the "plati 2" button stop making the promise rather than keep
 * making a false one — the same rule the package card follows, and the reason
 * `gratis` is computed rather than typed. See Catalog::gratis_count().
 *
 * Deriving it also keeps the button honest about where it lands: the price on
 * it is the price the builder will show, because both read $selected.
 */
$hero_qty    = (int) ( $selected['qty'] ?? 0 );
$hero_gratis = (int) ( $selected['gratis'] ?? 0 );
$hero_pay    = $hero_qty - $hero_gratis;
$hero_deal   = $hero_qty > 1 && $hero_gratis > 0 && $hero_pay > 0;

// The ribbon leads with the free towel and adds the shipping only where the
// package actually carries it.
$hero_ribbon = '';
if ( $hero_deal ) {
	$hero_ribbon = sprintf(
		/* translators: 1: towels paid for, 2: towels given free, e.g. "2+1 GRATIS". */
		__( '%1$d+%2$d GRATIS', 'cosypaw' ),
		$hero_pay,
		$hero_gratis
	);

	if ( ! empty( $selected['free_ship'] ) ) {
		$hero_ribbon .= ' · ' . __( 'besplatna dostava', 'cosypaw' );
	}
}
?>

<main id="primary" class="site-main" tabindex="-1">

	<!-- HERO -->
	<section id="top" class="hero">
		<?php
		/*
		 * Falling motifs, phones only.
		 *
		 * Below 880px .hero__art is display:none, which left the opening screen
		 * as text on an empty page — the one view where the product is never
		 * shown. These are the same motifs, at 96px and a sixth of full
		 * opacity, drifting behind the copy.
		 *
		 * The table is authored rather than randomised: a per-request shuffle
		 * would clump sprites and change the page between two loads of the same
		 * URL. Columns are horizontal position (%), size (px), how long one fall
		 * takes (s), how far into that fall the sprite starts (s, negative so
		 * the screen is already populated at first paint rather than empty for
		 * ten seconds) and how far it drifts sideways on the way down (px).
		 */
		$hero_fall = array(
			array( 3, 44, 14.0, -1.2, 12 ),
			array( 16, 32, 17.5, -8.0, -14 ),
			array( 28, 52, 12.5, -4.6, 8 ),
			array( 41, 36, 18.5, -13.0, -10 ),
			array( 54, 46, 13.5, -6.8, 16 ),
			array( 67, 30, 16.0, -2.4, -12 ),
			array( 79, 50, 15.0, -10.5, 9 ),
			array( 91, 34, 19.0, -5.2, -15 ),
			array( 9, 38, 16.8, -12.0, 11 ),
			array( 22, 48, 13.8, -9.4, -8 ),
			array( 35, 30, 17.8, -3.0, 14 ),
			array( 48, 40, 15.5, -14.2, -11 ),
			array( 62, 34, 18.2, -7.6, 10 ),
			array( 74, 44, 14.6, -11.8, -13 ),
			array( 86, 32, 16.4, -0.8, 12 ),
		);

		// Drawn from the same filtered list the grid uses, so a retired motif
		// stops falling the moment it stops being for sale.
		if ( $products ) :
			?>
			<div class="hero__fall" aria-hidden="true">
				<?php
				foreach ( $hero_fall as $cosypaw_n => $drop ) :
					$motif = $products[ $cosypaw_n % count( $products ) ];

					// One line, because fifteen indented style blocks put a few
					// kilobytes of whitespace into every front-page response.
					$style = sprintf(
						'--fall-x:%1$d%%;--fall-size:%2$dpx;--fall-dur:%3$ss;--fall-delay:%4$ss;--fall-drift:%5$dpx;--fall-img:url(%6$s)',
						(int) $drop[0],
						(int) $drop[1],
						(float) $drop[2],
						(float) $drop[3],
						(int) $drop[4],
						esc_url( $motif['image_xs'] )
					);
					?>
					<span class="hero__fall-item" style="<?php echo esc_attr( $style ); ?>"></span>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<div class="hero__copy">
			<?php
			// The ribbon used to read "Mekani svet peškirića", which is the
			// headline underneath it said twice — a spent line before the pitch
			// begins, and on a phone the hero is copy only (.hero__art is
			// display:none below 880px), so it was spent where there was least
			// room. It carries the offer now, and only while the offer holds.
			if ( '' !== $hero_ribbon ) :
				?>
				<span class="badge badge--offer">
					<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 12v9H4v-9M2 7h20v5H2zM12 21V7M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7zM12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>
					<?php echo esc_html( $hero_ribbon ); ?>
				</span>
			<?php endif; ?>
			<?php
			// The page's main search phrase, verbatim: the Yoast title and the
			// SEO plan both lead with it, and an H1 that says something else
			// splits the page's signal between two topics.
			?>
			<h1 class="hero__title">
				<?php
				printf(
					/* translators: %s: highlighted word "mikrofibera". */
					esc_html__( 'Dečiji peškiri od %s', 'cosypaw' ),
					'<em>' . esc_html__( 'mikrofibera', 'cosypaw' ) . '</em>'
				);
				?>
			</h1>
			<p class="hero__lead"><?php echo esc_html( $tagline ); ?></p>

			<?php
			// Two equal buttons that both meant "keep scrolling" gave a visitor
			// who arrived ready to buy nothing to press. One button now, and it
			// carries the offer and the price — the price especially, because
			// the "od X" tag lives on .hero__art, which no phone ever sees.
			// The gallery keeps its link, demoted to the weight it deserves.
			?>
			<div class="hero__cta">
				<?php
				// #napravi-paket, not #paketi: the section opens on its heading
				// and a full-width gift photo, so landing on the section left a
				// visitor who pressed a priced buy button looking at neither a
				// price nor a button. This lands on the builder itself.
				?>
				<a href="#napravi-paket" class="btn btn--primary hero__buy">
					<span class="hero__buy-label">
						<?php
						echo esc_html(
							$hero_deal
								? sprintf(
									/* translators: 1: towels in the package, 2: towels paid for, e.g. "Uzmi 3 — plati 2". */
									__( 'Uzmi %1$d — plati %2$d', 'cosypaw' ),
									$hero_qty,
									$hero_pay
								)
								: __( 'Izaberi paket', 'cosypaw' )
						);
						?>
					</span>
					<span class="hero__buy-price"><?php echo esc_html( \Theme\Catalog::format_price( (int) $selected['price'] ) ); ?></span>
				</a>

				<a href="#galerija" class="hero__link">
					<?php
					echo esc_html(
						sprintf(
							/* translators: %d: how many motifs are on sale. */
							__( 'ili pogledaj svih %d peškirića', 'cosypaw' ),
							count( $products )
						)
					);
					?>
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
				</a>
			</div>

			<?php
			// The strip no longer repeats the offer the ribbon and the button
			// already made. It answers what a first-time buyer asks next:
			// what does delivery cost, how do I pay, who made this.
			$trust_items = array();

			// Named the selected package until the offer stopped belonging to
			// one: delivery is free from a cart subtotal now, whichever way the
			// basket gets there, so the strip states the bar rather than a
			// bundle that may no longer clear it.
			if ( $free_min > 0 ) {
				$trust_items[] = array(
					'label' => sprintf(
						/* translators: %s: formatted free-delivery threshold, e.g. "2.000 RSD". */
						__( 'Besplatna dostava preko %s', 'cosypaw' ),
						\Theme\Catalog::format_price( $free_min )
					),
					'icon'  => '<path d="M3 7h11v8H3zM14 10h4l3 3v2h-7z"/><circle cx="7" cy="18" r="1.6"/><circle cx="17.5" cy="18" r="1.6"/>',
				);
			}

			$trust_items[] = array(
				'label' => __( 'Plaćanje pouzećem', 'cosypaw' ),
				'icon'  => '<rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2.6"/>',
			);
			$trust_items[] = array(
				'label' => __( 'Ručni rad', 'cosypaw' ),
				'icon'  => '<path d="M12 21s-7-4.6-9.3-9C1.2 9 2.6 5.5 6 5.5c2 0 3.2 1.2 4 2.4.8-1.2 2-2.4 4-2.4 3.4 0 4.8 3.5 3.3 6.5C19 16.4 12 21 12 21z"/>',
			);
			?>
			<ul class="trust">
				<?php foreach ( $trust_items as $item ) : ?>
					<li class="trust__item">
						<span class="trust__check">
							<?php
							echo wp_kses(
								'<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--ink)" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $item['icon'] . '</svg>',
								array(
									'svg'    => array( 'width' => array(), 'height' => array(), 'viewbox' => array(), 'fill' => array(), 'stroke' => array(), 'stroke-width' => array(), 'stroke-linecap' => array(), 'stroke-linejoin' => array(), 'aria-hidden' => array() ),
									'path'   => array( 'd' => array() ),
									'rect'   => array( 'x' => array(), 'y' => array(), 'width' => array(), 'height' => array(), 'rx' => array() ),
									'circle' => array( 'cx' => array(), 'cy' => array(), 'r' => array() ),
								)
							);
							?>
						</span>
						<?php echo esc_html( $item['label'] ); ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<div class="hero__art">
			<div class="hero__blob" aria-hidden="true"></div>
			<div class="hero__blob hero__blob--sage" aria-hidden="true"></div>

			<div class="hero__card">
				<div class="hero__card-inner vertical-carousel" data-vertical-carousel data-autoplay-delay="3200" aria-label="<?php esc_attr_e( 'Izdvojeni peškirići', 'cosypaw' ); ?>">
					<div class="vertical-carousel__track">
						<?php foreach ( $featured as $cosypaw_i => $f ) : ?>
							<div class="vertical-carousel__slide">
								<picture>
									<?php
									/*
									 * Desktop only, and deliberately so.
									 *
									 * .hero__art is display:none below 881px, but a
									 * browser still fetches an <img> inside a hidden
									 * subtree — and the first slide asks for
									 * fetchpriority="high". A phone was spending the
									 * best moment of its connection on ~40 KB of a
									 * picture it never paints, more than the entire
									 * falling-motif layer that replaced the card
									 * there. A media-gated <source> is the only way
									 * to skip that fetch without surrendering the
									 * priority on desktop, where this is the LCP
									 * element.
									 *
									 * srcset and sizes must stay character-identical
									 * to Assets::preload_lcp_image(), or the preload
									 * and the picture choose different candidates and
									 * the motif is downloaded twice. That includes
									 * HERO_SIZES' (max-width: 424px) branch, which is
									 * unreachable inside a source gated at 881px but
									 * load-bearing for the match.
									 */
									?>
									<source
										media="(min-width: 881px)"
										srcset="<?php echo esc_attr( \Theme\Assets::motif_srcset( $f ) ); ?>"
										sizes="<?php echo esc_attr( \Theme\Assets::HERO_SIZES ); ?>"
									>
									<?php
									// What a phone resolves to instead: a 1x1
									// transparent GIF, inline, so there is no request
									// to make. It is never painted either — the card
									// it would sit in is not rendered at this width.
									?>
									<img
										class="hero__slide"
										src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
										width="600"
										height="800"
										alt="<?php echo esc_attr( \Theme\Storefront::motif_alt( $f ) ); ?>"
										decoding="async"
										<?php
										// The first slide is the hero image; the rest sit
										// outside the card's visible area and can wait.
										echo 0 === $cosypaw_i ? 'fetchpriority="high"' : 'loading="lazy"';
										?>
									>
								</picture>
							</div>
						<?php endforeach; ?>
					</div>
					<?php
					// aria-hidden: the pill mirrors the active slide's own
					// aria-label, so exposing it would read every motif twice.
					?>
					<span class="hero__name-pill" data-carousel-label aria-hidden="true"><?php echo esc_html( $featured ? $featured[0]['name'] : '' ); ?></span>

					<?php
					// WCAG 2.2.2 — autoplay needs a pause control. Ships hidden;
					// VerticalCarousel.js unhides it once it takes over.
					?>
					<button
						type="button"
						class="carousel-toggle"
						data-carousel-toggle
						data-label-pause="<?php esc_attr_e( 'Pauziraj smenjivanje peškirića', 'cosypaw' ); ?>"
						data-label-play="<?php esc_attr_e( 'Pusti smenjivanje peškirića', 'cosypaw' ); ?>"
						aria-pressed="false"
						aria-label="<?php esc_attr_e( 'Pauziraj smenjivanje peškirića', 'cosypaw' ); ?>"
						hidden
					>
						<svg class="carousel-toggle__pause" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
						<svg class="carousel-toggle__play" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5.5v13l11-6.5z"/></svg>
					</button>

					<?php
					// Announces only user-driven slide changes; autoplay stays
					// silent so the page does not talk over the reader.
					?>
					<span class="screen-reader-text" data-carousel-status role="status" aria-live="polite"></span>
				</div>
			</div>

			<div class="hero__price-tag"><?php echo esc_html( sprintf( /* translators: %s: formatted price. */ __( 'od %s', 'cosypaw' ), \Theme\Catalog::format_price( $from_price ) ) ); ?></div>
		</div>
	</section>

	<!-- GALERIJA -->
	<section id="galerija" class="section">
		<div class="section__head">
			<span class="eyebrow"><?php esc_html_e( 'Cela družina', 'cosypaw' ); ?></span>
			<h2 class="section__title"><?php esc_html_e( 'Upoznaj sve peškiriće', 'cosypaw' ); ?></h2>
			<p class="section__lead"><?php echo esc_html( sprintf( /* translators: %s: formatted lowest unit price. */ __( 'Dečiji peškiri sa životinjicama, zalogajčićima i cvetićima, od %s po komadu — ili ih spoji u paket i uštedi.', 'cosypaw' ), \Theme\Catalog::format_price( $from_price ) ) ); ?></p>
		</div>

		<?php
		// data-collapsed is set here rather than by script so the grid never
		// paints its full height and then jumps. The CSS cuts at the tenth
		// card, and at the seventh below 880px. The <noscript> block after the
		// grid undoes the collapse where the toggle cannot run.
		?>
		<div class="motifs" id="motifs-grid" data-motif-grid data-collapsed>
			<?php
			foreach ( $products as $p ) :
				get_template_part(
					'template-parts/motif-card',
					null,
					array(
						'motif'  => $p,
						'ladder' => $cosypaw_ladder,
					)
				);
				?>
			<?php endforeach; ?>
		</div>

		<?php
		/* translators: %d: total number of motifs. */
		$cosypaw_more_label = sprintf( __( 'Prikaži sve peškiriće (%d)', 'cosypaw' ), count( $products ) );
		$cosypaw_less_label = __( 'Prikaži manje', 'cosypaw' );
		?>
		<button
			type="button"
			class="motifs-toggle"
			data-motifs-toggle
			aria-controls="motifs-grid"
			aria-expanded="false"
			data-label-more="<?php echo esc_attr( $cosypaw_more_label ); ?>"
			data-label-less="<?php echo esc_attr( $cosypaw_less_label ); ?>"
		>
			<span data-motifs-toggle-label><?php echo esc_html( $cosypaw_more_label ); ?></span>
			<svg class="motifs-toggle__chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
		</button>

		<?php
		// The grid used to end on the toggle, which left a visitor who liked
		// several motifs with no route to the package that makes them cheaper.
		// Only printed while the default package really does save something.
		$cosypaw_bundle_save = (int) ( $selected['old'] ?? 0 ) - (int) $selected['price'];
		if ( $cosypaw_bundle_save > 0 ) :
			?>
			<a class="motif-handoff" href="#napravi-paket">
				<span>
					<?php
					printf(
						/* translators: 1: towels in the package, 2: formatted saving, e.g. "990 RSD". */
						esc_html__( 'Spoji %1$d peškirića u paket — ušteda %2$s', 'cosypaw' ),
						(int) $selected['qty'],
						esc_html( \Theme\Catalog::format_price( $cosypaw_bundle_save ) )
					);
					?>
				</span>
				<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
			</a>
		<?php endif; ?>

		<?php
		// Without script the button cannot expand anything, so the collapse has
		// to lift and the control has to go. The builder button goes too: it
		// has nothing to drop a motif into until BundleBuilder boots, and the
		// "Kupi 1 kom" link beside it still works on its own href. Specificity
		// matches the rules in landing.css and this sits later in the document,
		// so it wins.
		?>
		<noscript>
			<style>
				.motifs[data-collapsed] .motif-card { display: block; }
				.motifs-toggle { display: none; }
				.motif-card__row .motif-add { display: none; }
			</style>
		</noscript>
	</section>

	<!-- PAKETI -->
	<?php get_template_part( 'template-parts/packages', null, $storefront ); ?>

	<!-- U TVOM DOMU (lifestyle) -->
	<section id="dom" class="section">
		<div class="section__head">
			<span class="eyebrow"><?php esc_html_e( 'U tvom domu', 'cosypaw' ); ?></span>
			<h2 class="section__title"><?php esc_html_e( 'Tvoj kutak, malo mekši', 'cosypaw' ); ?></h2>
			<p class="section__lead"><?php esc_html_e( 'Pored lavaboa, na kuki ili na polici — peškirići se uklope u svaki dom i unesu trunku topline.', 'cosypaw' ); ?></p>
		</div>

		<div class="lifestyle">
			<?php
			$lifestyle = array(
				array( 'file' => 'lifestyle1', 'cap' => __( 'Spremni za jutarnju rutinu', 'cosypaw' ) ),
				array( 'file' => 'lifestyle2', 'cap' => __( 'Na kuki, uvek pri ruci', 'cosypaw' ) ),
			);
			$assets_uri = get_template_directory_uri() . '/assets/';
			foreach ( $lifestyle as $shot ) :
				// These carried a `sizes` but no `srcset`, so every visitor got
				// the 1086w original for a slot at most 547 CSS px wide.
				$shot_uri = $assets_uri . $shot['file'];
				?>
				<figure class="lifestyle-card">
					<img
						class="lifestyle-card__img"
						src="<?php echo esc_url( $shot_uri . '-md.avif' ); ?>"
						srcset="<?php echo esc_attr( "{$shot_uri}-md.avif 600w, {$shot_uri}-lg.avif 900w, {$shot_uri}.avif 1086w" ); ?>"
						sizes="(max-width: 880px) calc(100vw - 44px), 547px"
						width="1086"
						height="1358"
						alt="<?php echo esc_attr( $shot['cap'] ); ?>"
						loading="lazy"
						decoding="async"
					>
					<figcaption class="lifestyle-card__cap"><?php echo esc_html( $shot['cap'] ); ?></figcaption>
				</figure>
			<?php endforeach; ?>
		</div>
	</section>

	<!-- ZAŠTO -->
	<section id="zasto" class="section">
		<div class="section__head">
			<span class="eyebrow"><?php esc_html_e( 'Zašto CosyPaw', 'cosypaw' ); ?></span>
			<h2 class="section__title"><?php esc_html_e( 'Mali zagrljaj pored lavaboa', 'cosypaw' ); ?></h2>
			<p class="section__lead"><?php esc_html_e( 'Svaki peškirić je mekan, upijajuć i ima alku za kačenje — uvek pri ruci, uvek sladak.', 'cosypaw' ); ?></p>
		</div>

		<div class="benefits">
			<?php
			/*
			 * Each card is illustrated by the motif whose own catalogue caption
			 * already carries the point it is making: the koala "spava dok se
			 * ti umivaš", the bear is "zagrljaj na kuki", the cherry is "mašna
			 * na vrhu dana". The pairing is editorial, not decorative — which
			 * is also why it is written here rather than derived from the order
			 * the catalogue happens to be in.
			 *
			 * These used to be four stock line icons — a cloud, a droplet, a
			 * hook, a gift — on the only section of the page that showed no
			 * product at all, in a shop that sells hand-sewn animals.
			 */
			$benefits = array(
				array(
					'motif' => 'koala',
					'title' => __( 'Deca ih biraju sama', 'cosypaw' ),
					'text'  => __( 'Ruke se obrišu bez pregovora kad na kuki visi drugar.', 'cosypaw' ),
				),
				array(
					'motif' => 'pingvin',
					'title' => __( 'Upija, ne samo ukrašava', 'cosypaw' ),
					'text'  => __( 'Plišana mikrofibra osuši ručice u trenu i ostane sveža do večeri.', 'cosypaw' ),
				),
				array(
					'motif' => 'meda',
					'title' => __( 'Šiven rukom, jedan po jedan', 'cosypaw' ),
					'text'  => __( 'Isečen, šiven i pregledan ručno. Nema dva potpuno ista.', 'cosypaw' ),
				),
				array(
					'motif' => 'tresnja',
					'title' => __( 'Poklon koji se pamti', 'cosypaw' ),
					'text'  => __( 'Sitnica koja izmami osmeh pre nego što je odmotana.', 'cosypaw' ),
				),
			);

			// Resolved against the in-stock list, so retiring a motif in
			// wp-admin cannot leave a card with a broken picture. The fallback
			// walks the catalogue rather than repeating one motif, which would
			// put the same towel on two cards side by side.
			$cosypaw_by_id = array_column( $products, null, 'id' );

			foreach ( $benefits as $cosypaw_b => $b ) :
				$motif = $cosypaw_by_id[ $b['motif'] ] ?? ( $products[ $cosypaw_b % count( $products ) ] ?? null );

				if ( ! $motif ) {
					continue;
				}
				?>
				<div class="benefit">
					<img
						class="benefit__photo"
						src="<?php echo esc_url( $motif['image_th'] ); ?>"
						width="192"
						height="192"
						alt="<?php echo esc_attr( \Theme\Storefront::motif_alt( $motif ) ); ?>"
						loading="lazy"
						decoding="async"
					>
					<h3 class="benefit__title"><?php echo esc_html( $b['title'] ); ?></h3>
					<p class="benefit__text"><?php echo esc_html( $b['text'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</section>

	<!-- UTISCI (social proof) -->
	<section id="utisci" class="section">
		<div class="section__head">
			<span class="eyebrow"><?php esc_html_e( 'Zadovoljne mušterije', 'cosypaw' ); ?></span>
			<h2 class="section__title"><?php esc_html_e( 'Mali peškirići, veliki osmesi', 'cosypaw' ); ?></h2>
		</div>

		<?php
		// Real reviews, pooled from every motif, so one review written on a
		// product page also does its work here. The 'meta' line names the towel
		// it was written about rather than a city.
		$testimonials = class_exists( '\Theme\Reviews' ) ? \Theme\Reviews::latest( 6 ) : array();

		// A section built for a row of cards looks half-finished with one card
		// in it, so the written-in copy holds the floor until the shop has
		// enough reviews of its own to fill it.
		if ( count( $testimonials ) < 3 ) {
			$testimonials = array(
				array(
					'quote' => __( 'Stigli su brže nego što sam očekivala i mekši su nego na slikama. Ćerka bira koji će da koristi svaki dan.', 'cosypaw' ),
					'name'  => __( 'Jovana M.', 'cosypaw' ),
					'meta'  => __( 'Novi Sad', 'cosypaw' ),
				),
				array(
					'quote' => __( 'Kupila sam 2+1 paket za poklon i bio je pravi hit. Pakovanje je preslatko, ne moraš ništa dodatno da uvijaš.', 'cosypaw' ),
					'name'  => __( 'Milica P.', 'cosypaw' ),
					'meta'  => __( 'Beograd', 'cosypaw' ),
				),
				array(
					'quote' => __( 'Alka za kačenje je sitnica koja mnogo znači — peškirić je uvek na svom mestu i ne završi na podu.', 'cosypaw' ),
					'name'  => __( 'Ana T.', 'cosypaw' ),
					'meta'  => __( 'Niš', 'cosypaw' ),
				),
			);
		}
		?>

		<div class="testimonials">
			<?php
			foreach ( $testimonials as $t ) :
				$rating    = (int) ( $t['rating'] ?? 5 );
				$permalink = (string) ( $t['permalink'] ?? '' );
				?>
				<figure class="testimonial">
					<?php /* translators: %d: star rating, 1-5. */ ?>
					<span class="testimonial__stars" role="img" aria-label="<?php echo esc_attr( sprintf( __( '%d od 5 zvezdica', 'cosypaw' ), $rating ) ); ?>">
						<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
							<svg class="<?php echo $i <= $rating ? 'testimonial__star' : 'testimonial__star testimonial__star--empty'; ?>" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l2.9 6.3 6.8.6-5.1 4.5 1.5 6.7L12 17l-6 3.6 1.5-6.7L2.4 9.4l6.8-.6z"/></svg>
						<?php endfor; ?>
					</span>
					<blockquote class="testimonial__quote"><?php echo esc_html( $t['quote'] ); ?></blockquote>
					<figcaption class="testimonial__author">
						<span class="testimonial__avatar" aria-hidden="true"><?php echo esc_html( mb_substr( $t['name'], 0, 1 ) ); ?></span>
						<span>
							<span class="testimonial__name"><?php echo esc_html( $t['name'] ); ?></span>
							<?php
							// The written-in copy has no review to link to; a real
							// one links to itself, in place on the product page.
							if ( '' !== $permalink ) :
								?>
								<a class="testimonial__meta" href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $t['meta'] ); ?></a>
							<?php else : ?>
								<span class="testimonial__meta"><?php echo esc_html( $t['meta'] ); ?></span>
							<?php endif; ?>
						</span>
					</figcaption>
				</figure>
			<?php endforeach; ?>

			<?php
			/*
			 * The section only ever pointed one way: it printed what other
			 * people wrote and left the reader with nowhere to write. Customers
			 * were asking where the form is, which is the question this answers
			 * at the moment it occurs to them — right under the last quote.
			 *
			 * Motifs only. BundlePricing keeps every order on motif line items
			 * (packages are cart-level pricing, not products anyone is shipped),
			 * so a motif is the thing a customer actually owns — and the thing
			 * WooCommerce can recognise them as the verified owner of. Package
			 * products can hold reviews, but no order has ever contained one.
			 */
			$reviewable = array_values(
				array_filter(
					$products,
					static fn( array $row ): bool => '' !== (string) ( $row['permalink'] ?? '' )
				)
			);

			// No WooCommerce, no product pages, no form to point at. The demo
			// catalog browses fine without this.
			if ( $reviewable ) :
				?>
				<div class="review-cta" id="ostavi-utisak">
					<span class="review-cta__mark" aria-hidden="true">
						<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
					</span>
					<h3 class="review-cta__title"><?php esc_html_e( 'Sad si ti na redu', 'cosypaw' ); ?></h3>
					<p class="review-cta__text"><?php esc_html_e( 'Stigao ti je peškirić? Napiši par reči — to je ono što sledećem kupcu pomogne da izabere.', 'cosypaw' ); ?></p>

					<?php
					/*
					 * A disclosure rather than a link, because WooCommerce has no
					 * shop-wide review form: every review is written on one
					 * product's page. The choice of towel is the first step of
					 * the flow whether we ask for it here or make them go and
					 * find it, so it is asked here, in one tap.
					 */
					?>
					<details class="review-cta__picker" data-review-picker>
						<summary class="review-cta__summary">
							<?php esc_html_e( 'Ostavi utisak', 'cosypaw' ); ?>
							<span class="review-cta__chevron" aria-hidden="true">
								<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
							</span>
						</summary>
						<p class="review-cta__hint"><?php esc_html_e( 'Izaberi peškirić koji imaš — forma je na njegovoj stranici.', 'cosypaw' ); ?></p>
						<ul class="review-cta__list">
							<?php foreach ( $reviewable as $cosypaw_r ) : ?>
								<li>
									<a class="review-cta__item" href="<?php echo esc_url( $cosypaw_r['permalink'] . '#reviews' ); ?>">
										<?php echo esc_html( $cosypaw_r['name'] ); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</details>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<!-- O PEŠKIRIMA (search copy) -->
	<?php
	/*
	 * The one block of running prose on the page. Everything above is written
	 * to sell in a glance, which leaves a search engine almost nothing to read
	 * about what the shop is; this says it in full sentences, with the phrases
	 * people search for, and sits low enough not to slow a buyer down.
	 *
	 * The two links are the SEO plan's internal links, one each: to the motif
	 * collection page and to the gift-set page. Each falls back to its section
	 * here while its page is missing, so a fresh install never links to a 404.
	 */
	$cosypaw_link        = static fn( string $href, string $text ): string => '<a href="' . esc_url( $href ) . '">' . esc_html( $text ) . '</a>';
	$cosypaw_kses        = array( 'a' => array( 'href' => array() ) );
	$cosypaw_motifs_href = \Theme\Pages::url( 'motivi' );
	$cosypaw_motifs_href = '' !== $cosypaw_motifs_href ? $cosypaw_motifs_href : '#galerija';
	$cosypaw_gifts_href  = \Theme\Pages::url( 'pokloni' );
	$cosypaw_gifts_href  = '' !== $cosypaw_gifts_href ? $cosypaw_gifts_href : '#napravi-paket';
	?>
	<section id="o-peskirima" class="section seo-copy">
		<div class="section__head">
			<span class="eyebrow"><?php esc_html_e( 'O našim peškirima', 'cosypaw' ); ?></span>
			<h2 class="section__title"><?php esc_html_e( 'Ručno rađeni peškiri za decu — i za šape', 'cosypaw' ); ?></h2>
		</div>

		<div class="seo-copy__body">
			<p><?php esc_html_e( 'Tražiš dečije peškire koji su mekani, upijajući i dovoljno slatki da dete samo poželi da obriše ruke? CosyPaw peškiri od mikrofibera šiju se ručno, jedan po jedan. Plišana mikrofibra je nežna prema dečijoj koži, upija u trenu, brzo se suši i ostaje meka i posle mnogo pranja.', 'cosypaw' ); ?></p>

			<p>
				<?php
				echo wp_kses(
					sprintf(
						/* translators: 1: link "N jedinstvenih motiva", to the motif gallery. */
						__( 'Svaki peškir ima alku za kačenje, pa visi na dečijoj visini — pored lavaboa, na kuki ili na vratima. Kad na kuki čeka drugar, pranje ruku postaje igra, a dete samo bira svoj peškir i samo ga koristi. Izaberi između %1$s: životinjice poput zeke, sove, pande i kapibare, zalogajčići i cvetići.', 'cosypaw' ),
						$cosypaw_link(
							$cosypaw_motifs_href,
							sprintf(
								/* translators: %d: how many motifs are on sale. */
								__( '%d jedinstvenih motiva', 'cosypaw' ),
								count( $products )
							)
						)
					),
					$cosypaw_kses
				);
				?>
			</p>

			<p>
				<?php
				echo wp_kses(
					sprintf(
						/* translators: 1: link "praktičan i lep poklon", to the gift-set page. */
						__( 'CosyPaw peškiri su i %1$s — za rođenje bebe, krštenje, prvi rođendan ili bebi šauer. Svaki paket stiže u CosyPaw kutiji, sa porukom dobrodošlice i mirisom lavande, a ručno rađene peškire šaljemo širom Srbije.', 'cosypaw' ),
						$cosypaw_link( $cosypaw_gifts_href, __( 'praktičan i lep poklon', 'cosypaw' ) )
					),
					$cosypaw_kses
				);
				?>
			</p>

			<p><?php esc_html_e( 'A kako ime CosyPaw kaže, nisu samo za decu: mnogi ih drže pored vrata kao peškir za pse, za brisanje šapa posle šetnje. Mikrofibra brzo upije vlagu i blato, a peškir se pere u mašini na 40°C.', 'cosypaw' ); ?></p>
		</div>

		<div class="seo-copy__cta">
			<a href="#napravi-paket" class="btn btn--primary"><?php esc_html_e( 'Izaberi paket', 'cosypaw' ); ?></a>
		</div>
	</section>

	<?php get_template_part( 'template-parts/faq' ); ?>
</main>

<?php
get_footer();
