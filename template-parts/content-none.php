<?php
/**
 * "Nothing found" message for empty loops / failed searches.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="entry">
	<h2 class="entry__title"><?php esc_html_e( 'Ništa nije pronađeno', 'cosypaw' ); ?></h2>
	<div class="entry__content">
		<?php if ( is_search() ) : ?>
			<p><?php esc_html_e( 'Nažalost, ništa ne odgovara pretrazi. Probaj druge ključne reči.', 'cosypaw' ); ?></p>
			<?php get_search_form(); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'Ovde još nema sadržaja.', 'cosypaw' ); ?></p>
		<?php endif; ?>
	</div>
</div>
