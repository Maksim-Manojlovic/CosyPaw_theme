<?php
/**
 * The gift banner: the unboxing clip beside the "every package is a gift" copy.
 *
 * Rendered on the landing (inside the packages section) and under every single
 * product, so it lives here rather than inline in either. Its styles are in
 * assets/css/blocks.css, which landing.css and woocommerce.css both import.
 *
 * @package CosyPaw
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The clip itself.
 *
 * Filterable because the file lives in uploads rather than in the theme, so a
 * copy of this theme anywhere else has its own — or none, in which case the
 * banner falls back to the copy alone rather than printing an empty frame.
 *
 * @param string $url Video URL.
 */
$cosypaw_video = (string) apply_filters(
	'cosypaw_pkg_banner_video',
	'https://cosypaw.rs/wp-content/uploads/2026/08/Unboxing-gorana-2x.mp4'
);
?>
<div class="pkg-banner">
	<?php if ( '' !== $cosypaw_video ) : ?>
		<?php
		// preload="metadata": enough for the browser to paint the clip's own
		// first frame, which is what the poster is now — the penguin still that
		// used to sit here was a photograph of a different thing entirely. The
		// 1.1 MB body is only fetched once the clip actually plays.
		//
		// The clip is silent, so `muted` costs nothing and buys the right to
		// start on its own; InViewVideo does that only in view and only where
		// motion is welcome.
		//
		// No native controls — a permanent grey bar across the photograph is
		// most of what this banner is. The toggle below is the pause WCAG 2.2.2
		// wants for a loop this long, and it ships hidden like the announcement
		// bar's: without script nothing ever moves, so there is nothing to pause.
		?>
		<div class="pkg-banner__media">
			<video
				class="pkg-banner__img pkg-banner__video"
				data-inview-video
				src="<?php echo esc_url( $cosypaw_video ); ?>"
				width="1086"
				height="1448"
				preload="metadata"
				muted
				loop
				playsinline
				aria-label="<?php esc_attr_e( 'Otvaranje CosyPaw paketa', 'cosypaw' ); ?>"
			></video>
			<button
				type="button"
				class="pkg-banner__toggle"
				data-video-toggle
				aria-pressed="false"
				aria-label="<?php esc_attr_e( 'Pauziraj video', 'cosypaw' ); ?>"
				hidden
			>
				<svg class="pkg-banner__pause-icon" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
				<svg class="pkg-banner__play-icon" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5.5v13l11-6.5z"/></svg>
			</button>
		</div>
	<?php endif; ?>
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
