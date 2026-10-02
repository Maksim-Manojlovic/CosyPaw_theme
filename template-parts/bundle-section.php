<?php
/**
 * A section of chosen gift bundles, with a link on to all of them.
 *
 * Used where a bundle is the natural next step: the front page, and the pages
 * of the products the bundles hold. Prints nothing while none of the chosen
 * bundles is on offer (\Theme\GiftBundles::offers()).
 *
 * @package CosyPaw
 *
 * @var array $args {
 *     @type array<int,string> $keys  GiftBundles::ITEMS keys, in order.
 *     @type string            $id    Section id.
 *     @type string            $title Heading.
 *     @type string            $lead  Lead paragraph.
 * }
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cosypaw_offers = class_exists( '\Theme\GiftBundles' ) ? \Theme\GiftBundles::offers( (array) ( $args['keys'] ?? array() ) ) : array();
if ( ! $cosypaw_offers ) {
	return;
}

$cosypaw_all = \Theme\Pages::url( 'paketi' );
?>
<section id="<?php echo esc_attr( (string) ( $args['id'] ?? 'poklon-paketi' ) ); ?>" class="section bundles-section">
	<div class="section__head">
		<span class="eyebrow"><?php esc_html_e( 'Poklon paketi', 'cosypaw' ); ?></span>
		<h2 class="section__title"><?php echo esc_html( (string) ( $args['title'] ?? '' ) ); ?></h2>
		<?php if ( ! empty( $args['lead'] ) ) : ?>
			<p class="section__lead"><?php echo esc_html( (string) $args['lead'] ); ?></p>
		<?php endif; ?>
	</div>

	<?php get_template_part( 'template-parts/bundle-cards', null, array( 'offers' => $cosypaw_offers ) ); ?>

	<?php if ( '' !== $cosypaw_all && ! is_page( \Theme\Pages::REGISTRY['paketi']['slug'] ) ) : ?>
		<a class="motif-handoff" href="<?php echo esc_url( $cosypaw_all ); ?>">
			<span><?php esc_html_e( 'Svi poklon paketi', 'cosypaw' ); ?></span>
			<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
		</a>
	<?php endif; ?>
</section>
