<?php
/**
 * Front page — the CosyPaw landing experience.
 *
 * Sections: hero (with vertical motif carousel), benefits, packages, motif grid.
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
$products      = $catalog->products();
$featured      = $catalog->featured();
$packages      = $catalog->packages();
$default_pkg   = $catalog->default_package();
$tagline       = __( 'Ručno šiveni peškirići-ljubimci koji čine kupatilo mekanim, urednim i — preslatkim.', 'cosypaw' );

// Resolve the initially-selected package for the CTA label.
$selected = $packages[0];
foreach ( $packages as $pkg ) {
	if ( $pkg['id'] === $default_pkg ) {
		$selected = $pkg;
		break;
	}
}
?>

<main id="primary" class="site-main">

	<!-- HERO -->
	<section id="top" class="hero">
		<div class="hero__copy">
			<span class="badge">
				<svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 21s-7-4.6-9.3-9C1.2 9 2.6 5.5 6 5.5c2 0 3.2 1.2 4 2.4.8-1.2 2-2.4 4-2.4 3.4 0 4.8 3.5 3.3 6.5C19 16.4 12 21 12 21z"/></svg>
				<?php esc_html_e( 'Mekani svet peškiriča', 'cosypaw' ); ?>
			</span>
			<h1 class="hero__title">
				<?php
				printf(
					/* translators: %s: highlighted phrase "tvoje kupatilo". */
					esc_html__( 'Slatki peškirići koji grle %s', 'cosypaw' ),
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
			<div class="hero__blob hero__blob--mint" aria-hidden="true"></div>

			<div class="hero__card">
				<div class="hero__card-inner vertical-carousel" data-vertical-carousel data-autoplay-delay="3200" aria-label="<?php esc_attr_e( 'Izdvojeni motivi', 'cosypaw' ); ?>">
					<div class="vertical-carousel__track">
						<?php foreach ( $featured as $f ) : ?>
							<div class="vertical-carousel__slide">
								<div class="hero__slide" role="img" aria-label="<?php echo esc_attr( $f['name'] ); ?>" style="background-image:url('<?php echo esc_url( $f['image'] ); ?>');"></div>
							</div>
						<?php endforeach; ?>
					</div>
					<span class="hero__name-pill" data-carousel-label><?php echo esc_html( $featured ? $featured[0]['name'] : '' ); ?></span>
				</div>
			</div>

			<div class="hero__price-tag"><?php echo esc_html( sprintf( /* translators: %s: formatted price. */ __( 'od %s', 'cosypaw' ), \Theme\Catalog::format_price( \Theme\Catalog::UNIT_PRICE ) ) ); ?></div>
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
					'tone'  => 'pink',
					'icon'  => '<path d="M7 18a4 4 0 0 1 0-8 5 5 0 0 1 9.6-1.6A4 4 0 0 1 17 18z"/>',
					'title' => __( 'Mekano kao oblak', 'cosypaw' ),
					'text'  => __( 'Plišana mikrofibra prijatna i nežnoj dečjoj koži.', 'cosypaw' ),
				),
				array(
					'tone'  => 'mint',
					'icon'  => '<path d="M12 3c4 5 6 8 6 11a6 6 0 1 1-12 0c0-3 2-6 6-11z"/>',
					'title' => __( 'Upija u trenu', 'cosypaw' ),
					'text'  => __( 'Brzo suši ručice i ostaje suv i svež tokom dana.', 'cosypaw' ),
				),
				array(
					'tone'  => 'pink',
					'icon'  => '<path d="M12 4v6"/><circle cx="12" cy="15" r="5"/>',
					'title' => __( 'Alka za kačenje', 'cosypaw' ),
					'text'  => __( 'Okačiš ga na kuku ili ručku — uvek na svom mestu.', 'cosypaw' ),
				),
				array(
					'tone'  => 'mint',
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

	<!-- U TVOM DOMU (lifestyle) -->
	<section id="dom" class="section">
		<div class="section__head">
			<span class="eyebrow"><?php esc_html_e( 'U tvom domu', 'cosypaw' ); ?></span>
			<h2 class="section__title"><?php esc_html_e( 'Tvoj kutak, malo mekši', 'cosypaw' ); ?></h2>
			<p class="section__lead"><?php esc_html_e( 'Pored lavabo, na kuki ili na polici — peškirići se uklope u svaki dom i unesu trunku topline.', 'cosypaw' ); ?></p>
		</div>

		<div class="lifestyle">
			<?php
			$lifestyle = array(
				array( 'file' => 'lifestyle1.jpg', 'cap' => __( 'Spremni za jutarnju rutinu', 'cosypaw' ) ),
				array( 'file' => 'lifestyle2.jpg', 'cap' => __( 'Cela družina na okupu', 'cosypaw' ) ),
			);
			$assets_uri = get_template_directory_uri() . '/assets/';
			foreach ( $lifestyle as $shot ) :
				?>
				<figure class="lifestyle-card">
					<div class="lifestyle-card__img" role="img" aria-label="<?php echo esc_attr( $shot['cap'] ); ?>" style="background-image:url('<?php echo esc_url( $assets_uri . $shot['file'] ); ?>');"></div>
					<figcaption class="lifestyle-card__cap"><?php echo esc_html( $shot['cap'] ); ?></figcaption>
				</figure>
			<?php endforeach; ?>
		</div>
	</section>

	<!-- PAKETI -->
	<section id="paketi" class="packages">
		<div class="packages__inner">
			<div class="section__head">
				<span class="eyebrow"><?php esc_html_e( 'Napravi svoj paket', 'cosypaw' ); ?></span>
				<h2 class="section__title"><?php esc_html_e( 'Što više grlića, veća ušteda', 'cosypaw' ); ?></h2>
				<p class="section__lead"><?php esc_html_e( 'Izaberi veličinu paketa, pa ubaci omiljene motive. Cena po komadu pada sa svakim sledećim.', 'cosypaw' ); ?></p>
			</div>

			<div class="pkg-banner">
				<div class="pkg-banner__img" role="img" aria-label="<?php esc_attr_e( 'Svaki paket je mali poklon', 'cosypaw' ); ?>" style="background-image:url('<?php echo esc_url( get_template_directory_uri() . '/assets/lifestyle3.jpg' ); ?>');"></div>
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
						<span class="builder__step-title"><?php esc_html_e( 'Ubaci svoje motive', 'cosypaw' ); ?></span>
					</div>
					<div class="builder__tools">
						<span class="builder__count"><?php esc_html_e( 'Izabrano', 'cosypaw' ); ?> <b data-count>0</b> / <b data-qty-label>3</b></span>
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
								data-image="<?php echo esc_url( $p['image'] ); ?>"
							>
								<span class="motif-pick__img" role="img" aria-label="<?php echo esc_attr( $p['name'] ); ?>" style="background-image:url('<?php echo esc_url( $p['image'] ); ?>');"></span>
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

	<!-- GALERIJA -->
	<section id="galerija" class="section">
		<div class="section__head">
			<span class="eyebrow"><?php esc_html_e( 'Cela družina', 'cosypaw' ); ?></span>
			<h2 class="section__title"><?php esc_html_e( 'Upoznaj sve motive', 'cosypaw' ); ?></h2>
			<p class="section__lead"><?php echo esc_html( sprintf( /* translators: %s: formatted unit price. */ __( 'Svaki po %s pojedinačno — ili ih spoji u paket i uštedi.', 'cosypaw' ), \Theme\Catalog::format_price( \Theme\Catalog::UNIT_PRICE ) ) ); ?></p>
		</div>

		<div class="motifs">
			<?php
			foreach ( $products as $p ) :
				/* translators: %s: motif name. */
				$item_label = sprintf( __( '%s • 1 kom', 'cosypaw' ), $p['name'] );
				?>
				<div class="motif-card">
					<div class="motif-card__img" role="img" aria-label="<?php echo esc_attr( $p['name'] ); ?>" style="background-image:url('<?php echo esc_url( $p['image'] ); ?>');"></div>
					<div class="motif-card__row">
						<div>
							<div class="motif-name"><?php echo esc_html( $p['name'] ); ?></div>
							<div class="motif-price"><?php echo esc_html( \Theme\Catalog::format_price( \Theme\Catalog::UNIT_PRICE ) ); ?></div>
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
								data-price="<?php echo esc_attr( (string) \Theme\Catalog::UNIT_PRICE ); ?>"
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

	<!-- UTISCI (social proof) -->
	<section id="utisci" class="section">
		<div class="section__head">
			<span class="eyebrow"><?php esc_html_e( 'Zadovoljne mušterije', 'cosypaw' ); ?></span>
			<h2 class="section__title"><?php esc_html_e( 'Mali peškirići, veliki osmesi', 'cosypaw' ); ?></h2>
			<p class="section__lead"><?php esc_html_e( 'Hiljade ručica već se suše uz CosyPaw — evo šta kažu njihovi vlasnici.', 'cosypaw' ); ?></p>
		</div>

		<div class="proof-stats">
			<?php
			$stats = array(
				array( 'num' => __( '4.9/5', 'cosypaw' ), 'label' => __( 'Prosečna ocena', 'cosypaw' ) ),
				array( 'num' => __( '12.000+', 'cosypaw' ), 'label' => __( 'Prodatih peškiriča', 'cosypaw' ) ),
				array( 'num' => __( '98%', 'cosypaw' ), 'label' => __( 'Preporučuje prijateljima', 'cosypaw' ) ),
			);
			foreach ( $stats as $s ) :
				?>
				<div class="proof-stat">
					<div class="proof-stat__num"><?php echo esc_html( $s['num'] ); ?></div>
					<div class="proof-stat__label"><?php echo esc_html( $s['label'] ); ?></div>
				</div>
			<?php endforeach; ?>
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
			$faqs = array(
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
			foreach ( $faqs as $faq ) :
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
