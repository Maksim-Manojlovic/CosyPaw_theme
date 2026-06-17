<?php
/**
 * Minimal fallback template — required for a valid WordPress theme.
 *
 * Real templates (front-page, archive, single, WooCommerce hooks, ...) extend
 * from here. This file only guarantees the theme renders something for any query.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="primary" class="site-main glass-card">
	<?php
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<h2 class="entry-title"><?php the_title(); ?></h2>
				<div class="entry-content"><?php the_content(); ?></div>
			</article>
			<?php
		endwhile;

		the_posts_navigation();
	else :
		?>
		<p><?php esc_html_e( 'Nothing found.', 'cosypaw' ); ?></p>
		<?php
	endif;
	?>
</main>

<?php
get_footer();
