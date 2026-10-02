<?php
/**
 * Gift bundles as cards: the collage, what is inside, the price against what
 * the same things cost bought separately, and an AJAX add-to-cart.
 *
 * Shared by the bundle page, the front page and the pages of the products a
 * bundle holds. The saving and the crossed-out price come from
 * \Theme\GiftBundles::offers(), which only lists a bundle while it really is
 * cheaper than its parts; the free-delivery chip is derived from the shipping
 * threshold, like every other delivery claim on the site.
 *
 * @package CosyPaw
 *
 * @var array $args {
 *     @type array $offers \Theme\GiftBundles::offers() rows.
 * }
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cosypaw_offers   = (array) ( $args['offers'] ?? array() );
$cosypaw_free_min = \Theme\Catalog::free_shipping_min();

if ( ! $cosypaw_offers ) {
	return;
}
?>
<div class="bundles">
	<?php foreach ( $cosypaw_offers as $cosypaw_b ) : ?>
		<div class="bundle-card">
			<?php
			// aria-hidden + tabindex="-1": the name below is the same link with
			// the accessible text; the photo would be a second, unlabelled stop.
			?>
			<a class="bundle-card__media" href="<?php echo esc_url( $cosypaw_b['permalink'] ); ?>" aria-hidden="true" tabindex="-1">
				<?php
				if ( $cosypaw_b['image_id'] > 0 ) {
					echo wp_get_attachment_image(
						(int) $cosypaw_b['image_id'],
						'woocommerce_single',
						false,
						array(
							'class'    => 'bundle-card__img',
							'loading'  => 'lazy',
							'decoding' => 'async',
							'sizes'    => '(max-width: 560px) calc(100vw - 44px), 360px',
						)
					);
				}
				?>
			</a>
			<div class="bundle-card__body">
				<div class="bundle-card__tags">
					<span class="bundle-card__badge">
						<?php
						echo esc_html(
							sprintf(
								/* translators: %s: formatted saving, e.g. "299 RSD". */
								__( 'Uštedi %s', 'cosypaw' ),
								\Theme\Catalog::format_price( (int) $cosypaw_b['saving'] )
							)
						);
						?>
					</span>
					<?php if ( $cosypaw_free_min > 0 && (int) $cosypaw_b['price'] >= $cosypaw_free_min ) : ?>
						<span class="bundle-card__chip"><?php esc_html_e( 'Besplatna dostava', 'cosypaw' ); ?></span>
					<?php endif; ?>
				</div>
				<a class="bundle-card__name" href="<?php echo esc_url( $cosypaw_b['permalink'] ); ?>"><?php echo esc_html( $cosypaw_b['name'] ); ?></a>
				<p class="bundle-card__includes">
					<span class="screen-reader-text"><?php esc_html_e( 'U paketu:', 'cosypaw' ); ?></span>
					<?php echo esc_html( $cosypaw_b['includes'] ); ?>
				</p>
				<span class="bundle-card__prices">
					<span class="bundle-card__price"><?php echo esc_html( \Theme\Catalog::format_price( (int) $cosypaw_b['price'] ) ); ?></span>
					<del class="bundle-card__old">
						<span class="screen-reader-text"><?php esc_html_e( 'Kupljeno posebno:', 'cosypaw' ); ?></span>
						<?php echo esc_html( \Theme\Catalog::format_price( (int) $cosypaw_b['separately'] ) ); ?>
					</del>
				</span>
				<a
					href="<?php echo esc_url( $cosypaw_b['add_to_cart_url'] ); ?>"
					class="btn btn--primary bundle-card__buy add_to_cart_button ajax_add_to_cart"
					data-product_id="<?php echo esc_attr( (string) (int) $cosypaw_b['id'] ); ?>"
					data-quantity="1"
					rel="nofollow"
					aria-label="<?php echo esc_attr( sprintf( /* translators: %s: product name. */ __( 'Dodaj %s u korpu', 'cosypaw' ), $cosypaw_b['name'] ) ); ?>"
				><?php esc_html_e( 'Dodaj paket u korpu', 'cosypaw' ); ?></a>
			</div>
		</div>
	<?php endforeach; ?>
</div>
