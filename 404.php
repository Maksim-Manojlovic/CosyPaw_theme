<?php
/**
 * 404 — page not found.
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
	<div class="notfound">
		<div class="notfound__emoji" aria-hidden="true">🧺</div>
		<h1 class="notfound__title"><?php esc_html_e( 'Ova stranica se sakrila', 'cosypaw' ); ?></h1>
		<p class="notfound__text"><?php esc_html_e( 'Stranicu nismo našli — možda je premeštena. Probaj pretragu ili se vrati na početnu.', 'cosypaw' ); ?></p>

		<?php get_search_form(); ?>

		<p style="margin-top:24px;">
			<a class="btn btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Nazad na početnu', 'cosypaw' ); ?></a>
		</p>
	</div>
</main>

<?php
get_footer();
