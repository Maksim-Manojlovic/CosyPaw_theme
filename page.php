<?php
/**
 * Single page template.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// WooCommerce pages (cart, checkout, account) render full-width without the
// blog "entry" card, so their tables/forms get the WC brand styling cleanly.
$cosypaw_is_wc_page = function_exists( 'is_cart' )
	&& ( is_cart() || is_checkout() || is_account_page() );
?>

<main id="primary" class="site-main" tabindex="-1">
	<?php if ( $cosypaw_is_wc_page ) : ?>
		<div class="shop-main">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<header class="page-hero">
					<h1 class="page-hero__title"><?php the_title(); ?></h1>
				</header>
				<div class="entry__content"><?php the_content(); ?></div>
				<?php
			endwhile;
			?>
		</div>
	<?php else : ?>
		<div class="content-wrap content-wrap--full">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/content', 'single' );
			endwhile;
			?>
		</div>
	<?php endif; ?>
</main>

<?php
get_footer();
