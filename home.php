<?php
/**
 * Blog posts index (used when a static front page is set).
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
				<h1 class="page-hero__title"><?php echo esc_html( get_the_title( (int) get_option( 'page_for_posts' ) ) ?: __( 'Žurnal', 'cosypaw' ) ); ?></h1>
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
