<?php
/**
 * Search results template.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="primary" class="site-main" tabindex="-1">
	<div class="content-wrap">
		<div>
			<header class="page-hero">
				<span class="page-hero__eyebrow"><?php esc_html_e( 'Pretraga', 'cosypaw' ); ?></span>
				<h1 class="page-hero__title">
					<?php
					printf(
						/* translators: %s: search query. */
						esc_html__( 'Rezultati za „%s”', 'cosypaw' ),
						'<span>' . esc_html( get_search_query() ) . '</span>'
					);
					?>
				</h1>
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
			else :
				get_template_part( 'template-parts/content', 'none' );
			endif;
			?>
		</div>
		<?php get_sidebar(); ?>
	</div>
</main>

<?php
get_footer();
