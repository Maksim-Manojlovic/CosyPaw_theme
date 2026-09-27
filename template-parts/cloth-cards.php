<?php
/**
 * The magične krpice as cards: photo, name, price and an AJAX add-to-cart.
 *
 * Shared by the front page section and the /magicne-krpe/ page. A cloth has
 * one way to buy it — it is not a towel, so there is no package to drop it in.
 *
 * @package CosyPaw
 *
 * @var array $args {
 *     @type array $cloths \Theme\Cloths::products() rows.
 * }
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cosypaw_cloths = (array) ( $args['cloths'] ?? array() );

if ( ! $cosypaw_cloths ) {
	return;
}
?>
<div class="cloths">
	<?php foreach ( $cosypaw_cloths as $cosypaw_c ) : ?>
		<div class="cloth-card">
			<?php
			// aria-hidden + tabindex="-1": the name below is the same link with
			// the accessible text; the photo would be a second, unlabelled stop.
			?>
			<a class="cloth-card__media" href="<?php echo esc_url( $cosypaw_c['permalink'] ); ?>" aria-hidden="true" tabindex="-1">
				<?php
				if ( $cosypaw_c['image_id'] > 0 ) {
					// The attachment carries its own descriptive alt, set when the
					// product was created; WordPress supplies the srcset.
					echo wp_get_attachment_image(
						(int) $cosypaw_c['image_id'],
						'woocommerce_single',
						false,
						array(
							'class'    => 'cloth-card__img',
							'loading'  => 'lazy',
							'decoding' => 'async',
							'sizes'    => '(max-width: 560px) calc(100vw - 44px), 360px',
						)
					);
				}
				?>
			</a>
			<div class="cloth-card__body">
				<a class="cloth-card__name" href="<?php echo esc_url( $cosypaw_c['permalink'] ); ?>"><?php echo esc_html( $cosypaw_c['name'] ); ?></a>
				<span class="cloth-card__price"><?php echo esc_html( \Theme\Catalog::format_price( (int) $cosypaw_c['price'] ) ); ?></span>
				<a
					href="<?php echo esc_url( $cosypaw_c['add_to_cart_url'] ); ?>"
					class="btn btn--primary cloth-card__buy add_to_cart_button ajax_add_to_cart"
					data-product_id="<?php echo esc_attr( (string) (int) $cosypaw_c['id'] ); ?>"
					data-quantity="1"
					rel="nofollow"
					aria-label="<?php echo esc_attr( sprintf( /* translators: %s: product name. */ __( 'Dodaj %s u korpu', 'cosypaw' ), $cosypaw_c['name'] ) ); ?>"
				><?php esc_html_e( 'Dodaj u korpu', 'cosypaw' ); ?></a>
			</div>
		</div>
	<?php endforeach; ?>
</div>
