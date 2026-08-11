<?php
/**
 * Site footer — footer columns, cart drawer, toast, closing document.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<footer class="site-footer">
	<div class="footer-inner">
		<div class="footer-grid">
			<div class="footer-brand">
				<div class="nav__brand">
					<span class="brand-mark">
						<svg width="22" height="22" viewBox="0 0 24 24" fill="#fff" aria-hidden="true"><circle cx="7" cy="9" r="2.1"/><circle cx="12" cy="6.6" r="2.1"/><circle cx="17" cy="9" r="2.1"/><path d="M12 11.5c-3 0-5.2 2.3-5.2 4.6 0 1.7 1.5 2.4 3 2.4 1 0 1.6-.4 2.2-.4s1.2.4 2.2.4c1.5 0 3-.7 3-2.4 0-2.3-2.2-4.6-5.2-4.6z"/></svg>
					</span>
					<span class="brand-name" style="font-size:24px;"><?php bloginfo( 'name' ); ?></span>
				</div>
				<p class="footer-brand__text"><?php esc_html_e( 'Mekani svet peškiriča. Ručno šiveni ljubimci koji čine svako kupatilo toplijim.', 'cosypaw' ); ?></p>
			</div>

			<div class="footer-col">
				<div class="footer-col__title"><?php esc_html_e( 'Brzi linkovi', 'cosypaw' ); ?></div>
				<div class="footer-links">
					<?php
					// The targets are homepage sections, so off the front page a
					// bare "#paketi" resolves against the current URL and goes
					// nowhere. Same home_url() prefix the header nav uses.
					$cosypaw_footer_links = array(
						'#paketi'   => __( 'Paketi', 'cosypaw' ),
						'#zasto'    => __( 'Zašto CosyPaw', 'cosypaw' ),
						'#galerija' => __( 'Svi motivi', 'cosypaw' ),
					);
					$cosypaw_home_base = is_front_page() ? '' : home_url( '/' );
					foreach ( $cosypaw_footer_links as $cosypaw_anchor => $cosypaw_label ) {
						printf(
							'<a href="%1$s">%2$s</a>',
							esc_url( $cosypaw_home_base . $cosypaw_anchor ),
							esc_html( $cosypaw_label )
						);
					}
					?>
				</div>
			</div>

			<div class="footer-col">
				<div class="footer-col__title"><?php esc_html_e( 'Poručivanje', 'cosypaw' ); ?></div>
				<a href="https://instagram.com" target="_blank" rel="noopener noreferrer" class="footer-ig">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="3.6"/><circle cx="17.4" cy="6.6" r="1.1" fill="currentColor" stroke="none"/></svg>
					@cosypaw
				</a>
				<p class="footer-note"><?php echo wp_kses( __( 'Poruči preko DM-a ili korpe.<br>Plaćanje pouzećem.', 'cosypaw' ), array( 'br' => array() ) ); ?></p>
			</div>
		</div>

		<div class="footer-bottom">
			<span><?php printf( /* translators: %d: current year. */ esc_html__( '© %d CosyPaw — Mekani svet peškiriča', 'cosypaw' ), (int) gmdate( 'Y' ) ); ?></span>
			<span><?php esc_html_e( 'Ručni rad • Made with love', 'cosypaw' ); ?></span>
		</div>
	</div>
</footer>

<!-- Toast -->
<div class="toast" data-toast role="status" aria-live="polite" hidden>
	<span class="toast__check">
		<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#3c6e60" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
	</span>
	<span data-toast-msg></span>
</div>

<!-- Cart drawer -->
<div class="cart-drawer" data-cart-drawer hidden>
	<?php
	// A plain div, not a button: the panel traps focus, and a focusable overlay
	// would put a second "close" stop in the tab cycle. Pointer users get the
	// click-to-dismiss, keyboard users get Escape and the close button.
	?>
	<div class="cart-drawer__overlay" data-cart-close aria-hidden="true"></div>
	<aside class="cart-drawer__panel" data-cart-panel tabindex="-1" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Tvoja korpa', 'cosypaw' ); ?>">
		<div class="cart-drawer__head">
			<span class="cart-drawer__title"><?php esc_html_e( 'Tvoja korpa', 'cosypaw' ); ?></span>
			<button type="button" class="cart-drawer__close" data-cart-close aria-label="<?php esc_attr_e( 'Zatvori', 'cosypaw' ); ?>">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
			</button>
		</div>

		<div class="cart-drawer__body" data-cart-body>
			<div class="cart-empty" data-cart-empty>
				<div class="cart-empty__emoji">🧺</div>
				<p style="font-weight:700;font-size:16px;margin:0;"><?php esc_html_e( 'Korpa je još prazna', 'cosypaw' ); ?></p>
				<p style="font-size:14px;margin:6px 0 0;"><?php esc_html_e( 'Izaberi paket ili omiljeni motiv.', 'cosypaw' ); ?></p>
			</div>
			<div data-cart-items></div>
		</div>

		<div class="cart-foot" data-cart-foot hidden>
			<div class="cart-total-row">
				<span class="cart-total-label"><?php esc_html_e( 'Ukupno', 'cosypaw' ); ?></span>
				<span class="cart-total" data-cart-total>0 RSD</span>
			</div>
			<button type="button" class="cart-checkout" data-cart-checkout><?php esc_html_e( 'Nastavi ka plaćanju', 'cosypaw' ); ?></button>
		</div>
	</aside>
</div>

<?php wp_footer(); ?>
</body>
</html>
