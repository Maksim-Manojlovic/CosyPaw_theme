<?php
/**
 * The "Napravi svoj paket" section: gift banner, package sizes and the bundle
 * builder.
 *
 * Shared by the front page and the motif collection page. The builder's
 * script (BundleBuilder, booted from landing.js) finds it by
 * [data-bundle-builder], and every [data-add-to-bundle] button on the page
 * drops its motif in here.
 *
 * @package CosyPaw
 *
 * @var array $args \Theme\Storefront::context() output.
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$products    = (array) ( $args['products'] ?? array() );
$packages    = (array) ( $args['packages'] ?? array() );
$default_pkg = (string) ( $args['default_pkg'] ?? '' );
$selected    = (array) ( $args['selected'] ?? array() );
$free_min    = (int) ( $args['free_min'] ?? 0 );

if ( ! $packages ) {
	return;
}
?>
<section id="paketi" class="packages">
	<div class="packages__inner">
		<div class="section__head">
			<span class="eyebrow"><?php esc_html_e( 'Napravi svoj paket', 'cosypaw' ); ?></span>
			<h2 class="section__title"><?php esc_html_e( 'Što više peškirića, veća ušteda', 'cosypaw' ); ?></h2>
			<p class="section__lead"><?php esc_html_e( 'Izaberi veličinu paketa, pa ubaci omiljene peškiriće. Cena po komadu pada sa svakim sledećim.', 'cosypaw' ); ?></p>
		</div>

		<?php get_template_part( 'template-parts/gift-banner' ); ?>

		<?php
		// The builder opens on data-default-package already selected, with
		// step 2 visible: an unselected grid read as decoration, and people
		// scrolled past it without noticing a size had to be picked.
		// Changing the size is still one click, and the tier is also what a
		// deep link from a product page (?motif=) lands on.
		?>
		<noscript>
			<?php // Nothing in step 2 works without JS, and it no longer starts hidden. ?>
			<style>.builder__reveal { display: none; }</style>
		</noscript>

		<div class="builder" id="napravi-paket" data-bundle-builder data-default-package="<?php echo esc_attr( $default_pkg ); ?>">

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
						aria-pressed="<?php echo $pkg['id'] === $default_pkg ? 'true' : 'false'; ?>"
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

			<?php
			/*
			 * The free-delivery bar, stated where the size is chosen.
			 *
			 * It was a pill on whichever package happened to clear the
			 * threshold and a line in the trust strip four screens up,
			 * which is not where the decision is made: a shopper sizing up
			 * the Duo could not see that one more towel would also carry
			 * the delivery. Said here it is an argument for the next size,
			 * not a label on one card.
			 *
			 * Threshold-derived like every other claim on the page, so it
			 * disappears rather than lies when free delivery is switched
			 * off in wp-admin.
			 */
			if ( $free_min > 0 ) :
				?>
				<p class="builder__ship">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 7h11v8H3zM14 10h4l3 3v2h-7z"/><circle cx="7" cy="18" r="1.6"/><circle cx="17.5" cy="18" r="1.6"/></svg>
					<span>
						<?php
						echo esc_html(
							sprintf(
								/* translators: %s: formatted free-delivery threshold, e.g. "2.000 RSD". */
								__( 'Porudžbine preko %s stižu uz besplatnu dostavu.', 'cosypaw' ),
								\Theme\Catalog::format_price( $free_min )
							)
						);
						?>
					</span>
				</p>
			<?php endif; ?>

			<div class="builder__reveal" data-builder-step2>
			<div class="builder__step builder__step--row">
				<div class="builder__step-head">
					<span class="builder__num">2</span>
					<span class="builder__step-title"><?php esc_html_e( 'Ubaci svoje peškiriće', 'cosypaw' ); ?></span>
				</div>
				<div class="builder__tools">
					<?php
					// Live region: the count is the only feedback that a motif
					// was added or removed, and it was changing silently.
					?>
					<span class="builder__count" role="status" aria-live="polite"><?php esc_html_e( 'Izabrano', 'cosypaw' ); ?> <b data-count>0</b> / <b data-qty-label><?php echo esc_html( (string) (int) $selected['qty'] ); ?></b></span>
					<button type="button" class="builder__tool" data-random>
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="4"/><circle cx="8.5" cy="8.5" r="1.3" fill="currentColor"/><circle cx="15.5" cy="15.5" r="1.3" fill="currentColor"/><circle cx="15.5" cy="8.5" r="1.3" fill="currentColor"/><circle cx="8.5" cy="15.5" r="1.3" fill="currentColor"/></svg>
						<?php esc_html_e( 'Iznenadi me', 'cosypaw' ); ?>
					</button>
					<button type="button" class="builder__tool builder__tool--ghost" data-clear hidden><?php esc_html_e( 'Očisti', 'cosypaw' ); ?></button>
				</div>
			</div>

			<div class="builder-card">
				<?php
				// The price and the button used to sit at the far end of the card,
				// a whole gallery below the towels they were pricing — two separate
				// screenfuls for one decision. They share a row with the slots now:
				// what is in the bundle, what it costs, and the way to buy it.
				?>
				<div class="builder-summary" data-builder-summary>
					<div class="builder-slots" data-slots></div>
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
			</div>

			<span class="pkg-note"><?php esc_html_e( 'Plaćanje pouzećem • Dostava 2–4 radna dana', 'cosypaw' ); ?></span>
			</div><!-- /.builder__reveal -->
		</div>
	</div>
</section>
