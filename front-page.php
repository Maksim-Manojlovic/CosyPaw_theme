<?php
/**
 * Front page — the CosyPaw landing experience.
 *
 * Section order (hero, motif grid, packages, lifestyle, benefits, testimonials,
 * FAQ) is deliberate: the catalogue has to precede the bundle builder, because
 * step 2 of the builder asks the visitor to choose motifs and the gallery is
 * where they meet them. It used to run the other way round — "Upoznaj sve
 * motive" sat after the section that made you pick three. Everything from
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
$tagline       = __( 'Ukrasni peškirići-ljubimci od mekane mikrofibre, sa alkom za kačenje. Preko 20 motiva — izaberi svoje i razmazi kupatilo.', 'cosypaw' );

// Resolve the initially-selected package for the CTA label.
$selected = $packages[0];
foreach ( $packages as $pkg ) {
	if ( $pkg['id'] === $default_pkg ) {
		$selected = $pkg;
		break;
	}
}
?>

<main id="primary" class="site-main" tabindex="-1">

	<!-- HERO -->
	<section id="top" class="hero">
		<div class="hero__copy">
			<span class="badge">
				<svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 21s-7-4.6-9.3-9C1.2 9 2.6 5.5 6 5.5c2 0 3.2 1.2 4 2.4.8-1.2 2-2.4 4-2.4 3.4 0 4.8 3.5 3.3 6.5C19 16.4 12 21 12 21z"/></svg>
				<?php esc_html_e( 'Mekani svet peškirića', 'cosypaw' ); ?>
			</span>
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

			<div class="hero__cta">
				<a href="#paketi" class="btn btn--primary"><?php esc_html_e( 'Izaberi paket', 'cosypaw' ); ?></a>
				<a href="#galerija" class="btn btn--ghost"><?php esc_html_e( 'Pogledaj motive', 'cosypaw' ); ?></a>
			</div>

			<div class="trust">
				<?php
				$trust_items = array(
					__( 'Ručni rad', 'cosypaw' ),
					__( 'Mekano i upijajuće', 'cosypaw' ),
					__( 'Besplatna dostava na 3', 'cosypaw' ),
				);
				foreach ( $trust_items as $item ) :
					?>
					<div class="trust__item">
						<span class="trust__check"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--ink)" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg></span>
						<?php echo esc_html( $item ); ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="hero__art">
			<div class="hero__blob" aria-hidden="true"></div>
			<div class="hero__blob hero__blob--sage" aria-hidden="true"></div>

			<div class="hero__card">
				<div class="hero__card-inner vertical-carousel" data-vertical-carousel data-autoplay-delay="3200" aria-label="<?php esc_attr_e( 'Izdvojeni motivi', 'cosypaw' ); ?>">
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
						data-label-pause="<?php esc_attr_e( 'Pauziraj smenjivanje motiva', 'cosypaw' ); ?>"
						data-label-play="<?php esc_attr_e( 'Pusti smenjivanje motiva', 'cosypaw' ); ?>"
						aria-pressed="false"
						aria-label="<?php esc_attr_e( 'Pauziraj smenjivanje motiva', 'cosypaw' ); ?>"
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
			<h2 class="section__title"><?php esc_html_e( 'Upoznaj sve motive', 'cosypaw' ); ?></h2>
			<p class="section__lead"><?php echo esc_html( sprintf( /* translators: %s: formatted lowest unit price. */ __( 'Ukrasni peškirići za kupatilo, od %s po komadu — ili ih spoji u paket i uštedi.', 'cosypaw' ), \Theme\Catalog::format_price( $from_price ) ) ); ?></p>
		</div>

		<div class="motifs">
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
	</section>

	<!-- PAKETI -->
	<section id="paketi" class="packages">
		<div class="packages__inner">
			<div class="section__head">
				<span class="eyebrow"><?php esc_html_e( 'Napravi svoj paket', 'cosypaw' ); ?></span>
				<h2 class="section__title"><?php esc_html_e( 'Što više peškirića, veća ušteda', 'cosypaw' ); ?></h2>
				<p class="section__lead"><?php esc_html_e( 'Izaberi veličinu paketa, pa ubaci omiljene motive. Cena po komadu pada sa svakim sledećim.', 'cosypaw' ); ?></p>
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
						<span class="builder__step-title" data-builder-step2-heading tabindex="-1"><?php esc_html_e( 'Ubaci svoje motive', 'cosypaw' ); ?></span>
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
