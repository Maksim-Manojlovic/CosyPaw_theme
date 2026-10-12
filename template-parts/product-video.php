<?php
/**
 * A product's promo clip, as a slide of its WooCommerce gallery.
 *
 * Printed inside the gallery wrapper, after the photographs. The outer class
 * is WooCommerce's own slide class, which is all its slider asks of a slide;
 * `data-thumb` is the picture the slider puts in the thumbnail strip. There is
 * no link and no <img> in here, so the zoom and the lightbox — both of which
 * look for those — pass the clip by.
 *
 * The clip is driven by InViewVideo, exactly as the gift banner's is: silent,
 * looping, started only while its slide is the one on show. `preload="none"`
 * because that slide starts out off screen — until someone asks for it, the
 * page fetches the poster and nothing else. `data-video-manual` keeps the
 * play button where motion is not welcome: unlike the banner's, this clip is
 * one the visitor went looking for.
 *
 * The toggle borrows the gift banner's classes (assets/css/blocks.css) rather
 * than restating the same button under another name.
 *
 * @package CosyPaw
 *
 * @var array $args {
 *     @type string $src    The clip.
 *     @type string $poster Its first frame.
 *     @type string $thumb  A square cut of that frame, for the thumbnail strip.
 *     @type string $focus  CSS object-position: the part of the frame to keep.
 *     @type string $ratio  CSS aspect-ratio of the product photograph, or ''.
 *     @type string $name   Product name.
 * }
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cosypaw_src = (string) ( $args['src'] ?? '' );

if ( '' === $cosypaw_src ) {
	return;
}

$cosypaw_ratio = (string) ( $args['ratio'] ?? '' );
$cosypaw_focus = (string) ( $args['focus'] ?? '' );
$cosypaw_style = ( '' !== $cosypaw_ratio ? '--clip-ratio: ' . $cosypaw_ratio . '; ' : '' )
	. ( '' !== $cosypaw_focus ? '--clip-focus: ' . $cosypaw_focus . ';' : '' );

/* translators: %s: product name, e.g. "Krofnica". */
$cosypaw_label = sprintf( __( 'Video: %s', 'cosypaw' ), (string) ( $args['name'] ?? '' ) );
?>
<div
	class="woocommerce-product-gallery__image cosypaw-gallery-video"
	data-thumb="<?php echo esc_url( (string) ( $args['thumb'] ?? '' ) ); ?>"
	data-thumb-alt="<?php echo esc_attr( $cosypaw_label ); ?>"
	<?php if ( '' !== trim( $cosypaw_style ) ) : ?>
	style="<?php echo esc_attr( trim( $cosypaw_style ) ); ?>"
	<?php endif; ?>
>
	<video
		class="cosypaw-gallery-video__clip"
		data-inview-video
		data-video-manual
		src="<?php echo esc_url( $cosypaw_src ); ?>"
		poster="<?php echo esc_url( (string) ( $args['poster'] ?? '' ) ); ?>"
		width="720"
		height="1280"
		preload="none"
		muted
		loop
		playsinline
		aria-label="<?php echo esc_attr( $cosypaw_label ); ?>"
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
