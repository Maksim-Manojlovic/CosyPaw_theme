<?php
/**
 * Single post / page article body.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry' ); ?>>
	<header class="entry__header">
		<?php if ( 'post' === get_post_type() ) : ?>
			<span class="entry__meta"><?php echo esc_html( get_the_date() ); ?></span>
		<?php endif; ?>
		<h1 class="entry__title"><?php the_title(); ?></h1>
	</header>

	<?php if ( has_post_thumbnail() ) : ?>
		<div class="entry__thumb"><?php the_post_thumbnail( 'large' ); ?></div>
	<?php endif; ?>

	<div class="entry__content">
		<?php
		the_content();

		wp_link_pages(
			array(
				'before' => '<div class="page-links">' . esc_html__( 'Strane:', 'cosypaw' ) . ' ',
				'after'  => '</div>',
			)
		);
		?>
	</div>
</article>
