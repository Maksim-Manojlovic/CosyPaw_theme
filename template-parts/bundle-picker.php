<?php
/**
 * "Složi svoj paket": a row per part of a gift bundle, inside the product
 * page's add-to-cart form.
 *
 * Each part is a radio group posted as cosypaw_bundle[slot index]. Colours
 * are swatches, shown inline; a towel can be any of some twenty shapes, so its
 * photo tiles fold away under the part's row and open on "Promeni". A part
 * with nothing to swap for is stated and posted as it is. The suggestion is
 * picked to start with, so pressing the button straight away buys the bundle
 * as photographed.
 *
 * Without script everything still posts; BundlePicker.js only keeps the name
 * and photo in each row in step with the pick.
 *
 * @package CosyPaw
 *
 * @var array $args {
 *     @type array $slots  \Theme\GiftBundles::slots() of the bundle on screen.
 *     @type array $picked Slot index => key posted with a failed add, to keep.
 * }
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cosypaw_slots  = (array) ( $args['slots'] ?? array() );
$cosypaw_picked = (array) ( $args['picked'] ?? array() );
$cosypaw_field  = \Theme\GiftBundlePicker::FIELD;

if ( ! $cosypaw_slots ) {
	return;
}
?>
<div class="bundle-picker" data-bundle-picker>
	<p class="bundle-picker__title"><?php esc_html_e( 'Složi svoj paket', 'cosypaw' ); ?></p>
	<p class="bundle-picker__hint"><?php esc_html_e( 'Ovo je naš predlog. Promeni boju ili oblik bilo kog dela — cena paketa ostaje ista.', 'cosypaw' ); ?></p>

	<ul class="bundle-picker__slots" role="list">
		<?php foreach ( $cosypaw_slots as $cosypaw_i => $cosypaw_slot ) : ?>
			<?php
			$cosypaw_choices = (array) $cosypaw_slot['choices'];
			$cosypaw_name    = $cosypaw_field . '[' . (int) $cosypaw_i . ']';

			// What a failed add posted, else the suggestion, else — the
			// suggestion off sale — the first thing that can take its place.
			$cosypaw_sel = (string) ( $cosypaw_picked[ $cosypaw_i ] ?? '' );
			if ( ! isset( $cosypaw_choices[ $cosypaw_sel ] ) ) {
				$cosypaw_sel = isset( $cosypaw_choices[ $cosypaw_slot['default'] ] ) ? (string) $cosypaw_slot['default'] : (string) array_key_first( $cosypaw_choices );
			}
			$cosypaw_current = $cosypaw_choices[ $cosypaw_sel ];

			$cosypaw_mode = 'tiles';
			if ( count( $cosypaw_choices ) < 2 ) {
				$cosypaw_mode = 'fixed';
			} elseif ( ! in_array( '', array_column( $cosypaw_choices, 'swatch' ), true ) ) {
				$cosypaw_mode = 'swatches';
			}

			ob_start();
			?>
			<span class="bundle-slot__head"<?php echo 'swatches' === $cosypaw_mode ? ' aria-hidden="true"' : ''; ?>>
				<?php if ( '' !== $cosypaw_current['image'] ) : ?>
					<img class="bundle-slot__thumb" src="<?php echo esc_url( $cosypaw_current['image'] ); ?>" alt="" width="48" height="48" decoding="async" data-bundle-thumb>
				<?php endif; ?>
				<span class="bundle-slot__text">
					<span class="bundle-slot__label"><?php echo esc_html( $cosypaw_slot['label'] ); ?></span>
					<span class="bundle-slot__name" data-bundle-name><?php echo esc_html( $cosypaw_current['name'] ); ?></span>
				</span>
			</span>
			<?php
			$cosypaw_head = (string) ob_get_clean();
			?>
			<li class="bundle-slot bundle-slot--<?php echo esc_attr( $cosypaw_mode ); ?>" data-bundle-slot>
				<?php if ( 'fixed' === $cosypaw_mode ) : ?>
					<?php echo $cosypaw_head; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?>
					<input type="hidden" name="<?php echo esc_attr( $cosypaw_name ); ?>" value="<?php echo esc_attr( $cosypaw_sel ); ?>">

				<?php elseif ( 'swatches' === $cosypaw_mode ) : ?>
					<fieldset class="bundle-slot__fieldset">
						<legend class="screen-reader-text">
							<?php
							/* translators: %s: the part of the bundle, e.g. "Jastuk Kružić". */
							echo esc_html( sprintf( __( '%s: izaberi boju', 'cosypaw' ), $cosypaw_slot['label'] ) );
							?>
						</legend>
						<?php echo $cosypaw_head; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?>
						<span class="bundle-slot__swatches">
							<?php foreach ( $cosypaw_choices as $cosypaw_c ) : ?>
								<label class="bundle-swatch" title="<?php echo esc_attr( $cosypaw_c['name'] ); ?>">
									<input
										type="radio"
										class="bundle-swatch__input"
										name="<?php echo esc_attr( $cosypaw_name ); ?>"
										value="<?php echo esc_attr( $cosypaw_c['key'] ); ?>"
										data-name="<?php echo esc_attr( $cosypaw_c['name'] ); ?>"
										data-image="<?php echo esc_url( $cosypaw_c['image'] ); ?>"
										<?php checked( $cosypaw_sel, $cosypaw_c['key'] ); ?>
									>
									<span class="bundle-swatch__dot" style="--swatch: <?php echo esc_attr( $cosypaw_c['swatch'] ); ?>" aria-hidden="true"></span>
									<span class="screen-reader-text"><?php echo esc_html( $cosypaw_c['name'] ); ?></span>
								</label>
							<?php endforeach; ?>
						</span>
					</fieldset>

				<?php else : ?>
					<details class="bundle-slot__more" name="cosypaw-bundle-tiles">
						<summary class="bundle-slot__summary">
							<?php echo $cosypaw_head; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?>
							<span class="bundle-slot__change"><?php esc_html_e( 'Promeni', 'cosypaw' ); ?></span>
						</summary>
						<fieldset class="bundle-slot__tiles">
							<legend class="screen-reader-text">
								<?php
								/* translators: %s: the part of the bundle, e.g. "Peškir". */
								echo esc_html( sprintf( __( '%s: izaberi oblik', 'cosypaw' ), $cosypaw_slot['label'] ) );
								?>
							</legend>
							<?php foreach ( $cosypaw_choices as $cosypaw_c ) : ?>
								<label class="bundle-tile">
									<input
										type="radio"
										class="bundle-tile__input"
										name="<?php echo esc_attr( $cosypaw_name ); ?>"
										value="<?php echo esc_attr( $cosypaw_c['key'] ); ?>"
										data-name="<?php echo esc_attr( $cosypaw_c['name'] ); ?>"
										data-image="<?php echo esc_url( $cosypaw_c['image'] ); ?>"
										<?php checked( $cosypaw_sel, $cosypaw_c['key'] ); ?>
									>
									<?php if ( '' !== $cosypaw_c['image'] ) : ?>
										<img class="bundle-tile__img" src="<?php echo esc_url( $cosypaw_c['image'] ); ?>" alt="" width="96" height="96" loading="lazy" decoding="async">
									<?php endif; ?>
									<span class="bundle-tile__name"><?php echo esc_html( $cosypaw_c['name'] ); ?></span>
								</label>
							<?php endforeach; ?>
						</fieldset>
					</details>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
