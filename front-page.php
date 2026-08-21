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

$catalog       = new \Theme\Catalog();

/**
 * Motifs whose product has been retired (trashed or unpublished) keep their row
 * in the Catalog so past orders can still resolve their name, but they must not
 * be offered for sale. Rows carry no 'available' key at all when WooCommerce is
 * inactive or the motif is unmapped — the demo catalog stays fully browsable.
 */
$in_stock = static fn( array $row ): bool => (bool) ( $row['available'] ?? true );

$products      = array_values( array_filter( $catalog->products(), $in_stock ) );
$featured      = array_values( array_filter( $catalog->featured(), $in_stock ) );

// Motifs can be priced individually in wp-admin, so the headline price is the
// cheapest one actually on sale rather than a single catalog-wide figure.
$from_price = $products
	? (int) min( array_column( $products, 'price' ) )
	: \Theme\Catalog::UNIT_PRICE;
$packages      = $catalog->packages();
$default_pkg   = $catalog->default_package();

// The hero lead used to carry four facts — material, hanging loop, catalogue
// size and an instruction — two of which are repeated verbatim in the benefits
// section. It is one line now; the offer does the selling, from the ribbon and
// the button.
$tagline = sprintf(
	/* translators: %d: how many motifs are on sale. */
	__( '%d motiva od mekane mikrofibre, sa alkom za kačenje.', 'cosypaw' ),
	count( $products )
);

// Resolve the initially-selected package for the CTA label.
$selected = $packages[0];
foreach ( $packages as $pkg ) {
	if ( $pkg['id'] === $default_pkg ) {
		$selected = $pkg;
		break;
	}
}

/*
 * Every offer claim in the hero is derived from the package the bundle builder
 * opens on, never authored. Reprice the Trio in wp-admin and the "2+1 GRATIS"
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
			<h1 class="hero__title">
				<?php
				printf(
					/* translators: %s: highlighted phrase "tvoje kupatilo". */
					esc_html__( 'Ručno šiveni peškirići koji grle %s', 'cosypaw' ),
					'<em>' . esc_html__( 'tvoje kupatilo', 'cosypaw' ) . '</em>'
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
				<a href="#paketi" class="btn btn--primary hero__buy">
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

			if ( ! empty( $selected['free_ship'] ) ) {
				$trust_items[] = array(
					'label' => sprintf(
						/* translators: %s: package name, e.g. "Trio paket". */
						__( 'Besplatna dostava na %s', 'cosypaw' ),
						$selected['name']
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
								<img
									class="hero__slide"
									src="<?php echo esc_url( $f['image_md'] ); ?>"
									srcset="<?php echo esc_attr( \Theme\Assets::motif_srcset( $f ) ); ?>"
									sizes="<?php echo esc_attr( \Theme\Assets::HERO_SIZES ); ?>"
									width="600"
									height="800"
									alt="<?php echo esc_attr( $f['name'] ); ?>"
									decoding="async"
									<?php
									// The first slide is the hero image; the rest sit
									// outside the card's visible area and can wait.
									echo 0 === $cosypaw_i ? 'fetchpriority="high"' : 'loading="lazy"';
									?>
								>
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
			<p class="section__lead"><?php echo esc_html( sprintf( /* translators: %s: formatted lowest unit price. */ __( 'Ukrasni peškirići za kupatilo, od %s po komadu — ili ih spoji u paket i uštedi.', 'cosypaw' ), \Theme\Catalog::format_price( $from_price ) ) ); ?></p>
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
				/* translators: %s: motif name. */
				$item_label = sprintf( __( '%s • 1 kom', 'cosypaw' ), $p['name'] );
				?>
				<div class="motif-card">
					<?php
					// alt="" — the motif name is printed as text directly below.
					// The srcset omits image_sm on purpose: it is a 1:1 crop while
					// the others are 3:4, and srcset candidates have to be the same
					// picture at different sizes or the crop shifts with the
					// viewport.
					?>
					<img
						class="motif-card__img"
						src="<?php echo esc_url( $p['image_md'] ); ?>"
						srcset="<?php echo esc_attr( \Theme\Assets::motif_srcset( $p ) ); ?>"
						sizes="<?php echo esc_attr( \Theme\Assets::GRID_SIZES ); ?>"
						width="600"
						height="800"
						alt=""
						loading="lazy"
						decoding="async"
					>
					<div class="motif-card__row">
						<div>
							<div class="motif-name"><?php echo esc_html( $p['name'] ); ?></div>
							<div class="motif-price"><?php echo esc_html( \Theme\Catalog::format_price( (int) $p['price'] ) ); ?></div>
						</div>
						<?php if ( ! empty( $p['product_id'] ) ) : ?>
							<a
								href="<?php echo esc_url( $p['add_to_cart_url'] ); ?>"
								class="motif-add add_to_cart_button ajax_add_to_cart"
								data-product_id="<?php echo esc_attr( (string) (int) $p['product_id'] ); ?>"
								data-quantity="1"
								rel="nofollow"
							>
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
								<?php esc_html_e( 'Kupi', 'cosypaw' ); ?>
							</a>
						<?php else : ?>
							<button
								type="button"
								class="motif-add"
								data-cart-add
								data-name="<?php echo esc_attr( $item_label ); ?>"
								data-price="<?php echo esc_attr( (string) (int) $p['price'] ); ?>"
							>
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
								<?php esc_html_e( 'Kupi', 'cosypaw' ); ?>
							</button>
						<?php endif; ?>
					</div>
				</div>
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
		// Without script the button cannot expand anything, so the collapse has
		// to lift and the control has to go. Specificity matches the rule in
		// landing.css and this sits later in the document, so it wins.
		?>
		<noscript>
			<style>
				.motifs[data-collapsed] .motif-card { display: block; }
				.motifs-toggle { display: none; }
			</style>
		</noscript>
	</section>

	<!-- PAKETI -->
	<section id="paketi" class="packages">
		<div class="packages__inner">
			<div class="section__head">
				<span class="eyebrow"><?php esc_html_e( 'Napravi svoj paket', 'cosypaw' ); ?></span>
				<h2 class="section__title"><?php esc_html_e( 'Što više peškirića, veća ušteda', 'cosypaw' ); ?></h2>
				<p class="section__lead"><?php esc_html_e( 'Izaberi veličinu paketa, pa ubaci omiljene peškiriće. Cena po komadu pada sa svakim sledećim.', 'cosypaw' ); ?></p>
			</div>

			<?php
			// Unlike the hero and the grid, this one really is shown wide
			// (547 CSS px), so the 1086w original stays a candidate.
			$pkg_banner = get_template_directory_uri() . '/assets/motifs/pingvin';
			?>
			<div class="pkg-banner">
				<img
					class="pkg-banner__img"
					src="<?php echo esc_url( $pkg_banner . '-md.avif' ); ?>"
					srcset="<?php echo esc_attr( "{$pkg_banner}-md.avif 600w, {$pkg_banner}-lg.avif 900w, {$pkg_banner}.avif 1086w" ); ?>"
					sizes="(max-width: 880px) calc(100vw - 44px), 547px"
					width="1086"
					height="1448"
					alt="<?php esc_attr_e( 'Svaki paket je mali poklon', 'cosypaw' ); ?>"
					loading="lazy"
					decoding="async"
				>
				<div class="pkg-banner__body">
					<span class="eyebrow"><?php esc_html_e( 'Stiže spremno za poklon', 'cosypaw' ); ?></span>
					<h3 class="pkg-banner__title"><?php esc_html_e( 'Svaki paket je mali poklon', 'cosypaw' ); ?></h3>
					<p class="pkg-banner__text">
						<?php
						echo wp_kses(
							__( 'Pažljivo upakovano u CosyPaw kutiju, sa porukom dobrodošlice i mirisom lavande. Trio paket stiže uz <strong>besplatnu dostavu</strong> — idealno za rođendan, bebi šauer ili samo da nekog razmaziš.', 'cosypaw' ),
							array( 'strong' => array() )
						);
						?>
					</p>
					<span class="pkg-freeship">
						<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 7h11v8H3zM14 10h4l3 3v2h-7z"/><circle cx="7" cy="18" r="1.6"/><circle cx="17.5" cy="18" r="1.6"/></svg>
						<?php esc_html_e( 'Besplatna dostava na Trio paket', 'cosypaw' ); ?>
					</span>
				</div>
			</div>

			<div class="builder" data-bundle-builder>

				<div class="builder__step">
					<span class="builder__num">1</span>
					<span class="builder__step-title"><?php esc_html_e( 'Izaberi veličinu paketa', 'cosypaw' ); ?></span>
				</div>

				<div class="pkg-grid" data-tiers>
					<?php foreach ( $packages as $pkg ) : ?>
						<button
							type="button"
							class="pkg-card"
							data-package="<?php echo esc_attr( $pkg['id'] ); ?>"
							data-qty="<?php echo esc_attr( (string) (int) $pkg['qty'] ); ?>"
							data-name="<?php echo esc_attr( $pkg['name'] ); ?>"
							data-price="<?php echo esc_attr( (string) $pkg['price'] ); ?>"
							data-price-fmt="<?php echo esc_attr( \Theme\Catalog::format_price( $pkg['price'] ) ); ?>"
							data-per-fmt="<?php echo esc_attr( \Theme\Catalog::format_price( $pkg['per'] ) . ' / ' . __( 'kom', 'cosypaw' ) ); ?>"
							<?php if ( ! empty( $pkg['old'] ) ) : ?>
							data-old-fmt="<?php echo esc_attr( \Theme\Catalog::format_price( (int) $pkg['old'] ) ); ?>"
							<?php endif; ?>
							<?php if ( ! empty( $pkg['product_id'] ) ) : ?>
							data-product-id="<?php echo esc_attr( (string) (int) $pkg['product_id'] ); ?>"
							<?php endif; ?>
							aria-pressed="false"
						>
							<span class="pkg-card__ring" aria-hidden="true"></span>
							<?php if ( ! empty( $pkg['badge'] ) ) : ?>
								<span class="pkg-card__badge"><?php echo esc_html( $pkg['badge'] ); ?></span>
							<?php endif; ?>
							<span class="pkg-name"><?php echo esc_html( $pkg['name'] ); ?></span>
							<span class="pkg-desc"><?php echo esc_html( $pkg['desc'] ); ?></span>
							<div class="pkg-price-row">
								<span class="pkg-price"><?php echo esc_html( \Theme\Catalog::format_price( $pkg['price'] ) ); ?></span>
								<?php if ( ! empty( $pkg['old'] ) ) : ?>
									<span class="pkg-old"><?php echo esc_html( \Theme\Catalog::format_price( (int) $pkg['old'] ) ); ?></span>
								<?php endif; ?>
							</div>
							<?php
							// Derived in Catalog::gratis_count() from the live
							// prices, so this only prints while the bundle
							// really does hand a towel over for nothing.
							if ( ! empty( $pkg['gratis'] ) ) :
								?>
								<span class="pkg-gratis">
									<?php
									printf(
										/* translators: 1: towels paid for, 2: towels given free. */
										esc_html__( '%1$d+%2$d GRATIS', 'cosypaw' ),
										(int) $pkg['qty'] - (int) $pkg['gratis'],
										(int) $pkg['gratis']
									);
									?>
								</span>
							<?php endif; ?>
							<span class="pkg-per"><?php echo esc_html( \Theme\Catalog::format_price( $pkg['per'] ) . ' / ' . __( 'kom', 'cosypaw' ) ); ?></span>
							<?php if ( ! empty( $pkg['free_ship'] ) ) : ?>
								<span class="pkg-freeship">
									<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 7h11v8H3zM14 10h4l3 3v2h-7z"/><circle cx="7" cy="18" r="1.6"/><circle cx="17.5" cy="18" r="1.6"/></svg>
									<?php esc_html_e( 'Besplatna dostava', 'cosypaw' ); ?>
								</span>
							<?php endif; ?>
						</button>
					<?php endforeach; ?>
				</div>

				<div class="builder__reveal" data-builder-step2 hidden>
				<div class="builder__step builder__step--row">
					<div class="builder__step-head">
						<span class="builder__num">2</span>
						<?php // tabindex=-1: focus lands here when the step is revealed. ?>
						<span class="builder__step-title" data-builder-step2-heading tabindex="-1"><?php esc_html_e( 'Ubaci svoje peškiriće', 'cosypaw' ); ?></span>
					</div>
					<div class="builder__tools">
						<?php
						// Live region: the count is the only feedback that a motif
						// was added or removed, and it was changing silently.
						?>
						<span class="builder__count" role="status" aria-live="polite"><?php esc_html_e( 'Izabrano', 'cosypaw' ); ?> <b data-count>0</b> / <b data-qty-label>3</b></span>
						<button type="button" class="builder__tool" data-random>
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="4"/><circle cx="8.5" cy="8.5" r="1.3" fill="currentColor"/><circle cx="15.5" cy="15.5" r="1.3" fill="currentColor"/><circle cx="15.5" cy="8.5" r="1.3" fill="currentColor"/><circle cx="8.5" cy="15.5" r="1.3" fill="currentColor"/></svg>
							<?php esc_html_e( 'Iznenadi me', 'cosypaw' ); ?>
						</button>
						<button type="button" class="builder__tool builder__tool--ghost" data-clear hidden><?php esc_html_e( 'Očisti', 'cosypaw' ); ?></button>
					</div>
				</div>

				<div class="builder-card">
					<div class="builder-slots" data-slots></div>

					<div class="builder-gallery" data-gallery>
						<?php foreach ( $products as $p ) : ?>
							<button
								type="button"
								class="motif-pick"
								data-motif-id="<?php echo esc_attr( $p['id'] ); ?>"
								data-name="<?php echo esc_attr( $p['name'] ); ?>"
								data-image="<?php echo esc_url( $p['image_sm'] ); ?>"
							>
								<?php
								// alt="" — the tile's name is already the button's
								// accessible text, right below the image.
								?>
								<img
									class="motif-pick__img"
									src="<?php echo esc_url( $p['image_sm'] ); ?>"
									width="360"
									height="360"
									alt=""
									loading="lazy"
									decoding="async"
								>
								<span class="motif-pick__row">
									<span class="motif-pick__name"><?php echo esc_html( $p['name'] ); ?></span>
									<span class="motif-pick__used" data-used hidden>0</span>
								</span>
								<span class="motif-pick__add" aria-hidden="true">
									<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
								</span>
							</button>
						<?php endforeach; ?>
					</div>

					<div class="builder-cta">
						<div class="builder-cta__info">
							<div class="builder-cta__price">
								<span class="pkg-price" data-sel-price><?php echo esc_html( \Theme\Catalog::format_price( $selected['price'] ) ); ?></span>
								<span class="pkg-old" data-sel-old<?php echo empty( $selected['old'] ) ? ' hidden' : ''; ?>><?php echo esc_html( empty( $selected['old'] ) ? '' : \Theme\Catalog::format_price( (int) $selected['old'] ) ); ?></span>
							</div>
							<span class="builder-cta__meta"><span data-sel-name><?php echo esc_html( $selected['name'] ); ?></span> • <span data-sel-per><?php echo esc_html( \Theme\Catalog::format_price( $selected['per'] ) . ' / ' . __( 'kom', 'cosypaw' ) ); ?></span></span>
						</div>
						<button type="button" class="pkg-cta builder-cta__btn" data-add-bundle>
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 7h13l-1.2 8.4a2 2 0 0 1-2 1.7H9.2a2 2 0 0 1-2-1.7L6 4H3"/><circle cx="9.5" cy="20" r="1.2"/><circle cx="16.5" cy="20" r="1.2"/></svg>
							<span data-cta-label><?php esc_html_e( 'Dodaj u korpu', 'cosypaw' ); ?></span>
						</button>
					</div>
				</div>

				<span class="pkg-note"><?php esc_html_e( 'Plaćanje pouzećem • Dostava 2–4 radna dana', 'cosypaw' ); ?></span>
				</div><!-- /.builder__reveal -->
			</div>
		</div>
	</section>

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
			<h2 class="section__title"><?php esc_html_e( 'Mali zagrljaj pored sudopere', 'cosypaw' ); ?></h2>
			<p class="section__lead"><?php esc_html_e( 'Svaki peškirić je mekan, upijajuć i ima alku za kačenje — uvek pri ruci, uvek sladak.', 'cosypaw' ); ?></p>
		</div>

		<div class="benefits">
			<?php
			$benefits = array(
				array(
					'tone'  => 'sand',
					'icon'  => '<path d="M7 18a4 4 0 0 1 0-8 5 5 0 0 1 9.6-1.6A4 4 0 0 1 17 18z"/>',
					'title' => __( 'Mekano kao oblak', 'cosypaw' ),
					'text'  => __( 'Plišana mikrofibra prijatna i nežnoj dečjoj koži.', 'cosypaw' ),
				),
				array(
					'tone'  => 'sage',
					'icon'  => '<path d="M12 3c4 5 6 8 6 11a6 6 0 1 1-12 0c0-3 2-6 6-11z"/>',
					'title' => __( 'Upija u trenu', 'cosypaw' ),
					'text'  => __( 'Brzo suši ručice i ostaje suv i svež tokom dana.', 'cosypaw' ),
				),
				array(
					'tone'  => 'sand',
					'icon'  => '<path d="M12 4v6"/><circle cx="12" cy="15" r="5"/>',
					'title' => __( 'Alka za kačenje', 'cosypaw' ),
					'text'  => __( 'Okačiš ga na kuku ili ručku — uvek na svom mestu.', 'cosypaw' ),
				),
				array(
					'tone'  => 'sage',
					'icon'  => '<path d="M20 12v8H4v-8"/><path d="M2 7h20v5H2z"/><path d="M12 22V7"/><path d="M12 7S10.5 3 8 3a2.5 2.5 0 0 0 0 5zM12 7s1.5-4 4-4a2.5 2.5 0 0 1 0 5z"/>',
					'title' => __( 'Savršen poklon', 'cosypaw' ),
					'text'  => __( 'Slatka sitnica koja uvek izmami osmeh i „awww”.', 'cosypaw' ),
				),
			);
			foreach ( $benefits as $b ) :
				?>
				<div class="benefit">
					<span class="benefit__icon benefit__icon--<?php echo esc_attr( $b['tone'] ); ?>">
						<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?php echo wp_kses( $b['icon'], array( 'path' => array( 'd' => array() ), 'circle' => array( 'cx' => array(), 'cy' => array(), 'r' => array() ) ) ); ?></svg>
					</span>
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

		<div class="testimonials">
			<?php
			$testimonials = array(
				array(
					'quote' => __( 'Stigli su brže nego što sam očekivala i mekši su nego na slikama. Ćerka bira koji će da koristi svaki dan.', 'cosypaw' ),
					'name'  => __( 'Jovana M.', 'cosypaw' ),
					'meta'  => __( 'Novi Sad', 'cosypaw' ),
				),
				array(
					'quote' => __( 'Kupila sam Trio paket za poklon i bio je pravi hit. Pakovanje je preslatko, ne moraš ništa dodatno da uvijaš.', 'cosypaw' ),
					'name'  => __( 'Milica P.', 'cosypaw' ),
					'meta'  => __( 'Beograd', 'cosypaw' ),
				),
				array(
					'quote' => __( 'Alka za kačenje je sitnica koja mnogo znači — peškirić je uvek na svom mestu i ne završi na podu.', 'cosypaw' ),
					'name'  => __( 'Ana T.', 'cosypaw' ),
					'meta'  => __( 'Niš', 'cosypaw' ),
				),
			);
			foreach ( $testimonials as $t ) :
				?>
				<figure class="testimonial">
					<span class="testimonial__stars" role="img" aria-label="<?php esc_attr_e( '5 od 5 zvezdica', 'cosypaw' ); ?>">
						<?php for ( $i = 0; $i < 5; $i++ ) : ?>
							<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l2.9 6.3 6.8.6-5.1 4.5 1.5 6.7L12 17l-6 3.6 1.5-6.7L2.4 9.4l6.8-.6z"/></svg>
						<?php endfor; ?>
					</span>
					<blockquote class="testimonial__quote"><?php echo esc_html( $t['quote'] ); ?></blockquote>
					<figcaption class="testimonial__author">
						<span class="testimonial__avatar" aria-hidden="true"><?php echo esc_html( mb_substr( $t['name'], 0, 1 ) ); ?></span>
						<span>
							<span class="testimonial__name"><?php echo esc_html( $t['name'] ); ?></span>
							<span class="testimonial__meta"><?php echo esc_html( $t['meta'] ); ?></span>
						</span>
					</figcaption>
				</figure>
			<?php endforeach; ?>
		</div>
	</section>

	<!-- FAQ -->
	<section id="faq" class="section">
		<div class="section__head">
			<span class="eyebrow"><?php esc_html_e( 'Česta pitanja', 'cosypaw' ); ?></span>
			<h2 class="section__title"><?php esc_html_e( 'Sve što te zanima', 'cosypaw' ); ?></h2>
			<p class="section__lead"><?php esc_html_e( 'Ako ne nađeš odgovor, piši nam — rado pomažemo.', 'cosypaw' ); ?></p>
		</div>

		<div class="faq">
			<?php
			// Shared with the FAQPage structured data in Theme\Seo so the two
			// cannot drift — Google penalises markup that does not match what
			// the page actually shows.
			foreach ( \Theme\Seo::faqs() as $faq ) :
				?>
				<details class="faq-item">
					<summary class="faq-item__q">
						<?php echo esc_html( $faq['q'] ); ?>
						<span class="faq-item__icon" aria-hidden="true">
							<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
						</span>
					</summary>
					<p class="faq-item__a"><?php echo esc_html( $faq['a'] ); ?></p>
				</details>
			<?php endforeach; ?>
		</div>
	</section>
</main>

<?php
get_footer();
