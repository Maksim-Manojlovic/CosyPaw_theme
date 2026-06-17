<?php
/**
 * Archive template (categories, tags, date, author) + blog home fallback.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="primary" class="site-main">
	<div class="content-wrap">
		<div>
			<header class="page-hero">
				<span class="page-hero__eyebrow"><?php esc_html_e( 'CosyPaw žurnal', 'cosypaw' ); ?></span>
				<h1 class="page-hero__title"><?php the_archive_title(); ?></h1>
				<?php
				$cosypaw_desc = get_the_archive_description();
				if ( $cosypaw_desc ) :
					?>
					<div class="page-hero__lead"><?php echo wp_kses_post( $cosypaw_desc ); ?></div>
				<?php endif; ?>
			</header>

			<?php if ( have_posts() ) : ?>
				<div class="post-list">
					<?php
					while ( have_posts() ) :
						the_post();
						get_template_part( 'template-parts/content' );
					endwhile;
					?>
				</div>

				<?php
				the_posts_pagination(
					array(
						'prev_text' => esc_html__( 'Prethodna', 'cosypaw' ),
						'next_text' => esc_html__( 'Sledeća', 'cosypaw' ),
					)
				);
				?>
			<?php else : ?>
				<?php get_template_part( 'template-parts/content', 'none' ); ?>
			<?php endif; ?>
		</div>
		<?php get_sidebar(); ?>
	</div>
</main>

<?php
get_footer();
