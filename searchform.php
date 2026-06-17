<?php
/**
 * Custom search form.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cosypaw_search_id = 'search-' . wp_unique_id();
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $cosypaw_search_id ); ?>"><?php esc_html_e( 'Pretraga', 'cosypaw' ); ?></label>
	<input
		type="search"
		id="<?php echo esc_attr( $cosypaw_search_id ); ?>"
		class="search-field"
		placeholder="<?php esc_attr_e( 'Pretraži…', 'cosypaw' ); ?>"
		value="<?php echo get_search_query(); ?>"
		name="s"
	/>
	<button type="submit" class="search-submit"><?php esc_html_e( 'Traži', 'cosypaw' ); ?></button>
</form>
