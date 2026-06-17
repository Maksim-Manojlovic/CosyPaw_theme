<?php
/**
 * Post summary card (used in archive / search / blog loops).
 *
 * @package CosyPaw
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry-card' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="entry-card__media" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
			<?php the_post_thumbnail( 'large' ); ?>
		</a>
	<?php endif; ?>

	<div class="entry-card__body">
		<span class="entry-card__meta"><?php echo esc_html( get_the_date() ); ?></span>
		<h2 class="entry-card__title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h2>
		<p class="entry-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 28 ) ); ?></p>
		<a class="btn btn--ghost" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Pročitaj više', 'cosypaw' ); ?></a>
	</div>
</article>
