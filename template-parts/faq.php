<?php
/**
 * The FAQ accordion.
 *
 * Rendered on the landing and under every single product: the four questions
 * it answers — fabric, washing, delivery, payment — are exactly the ones a
 * visitor has open while deciding, and the product page was sending them back
 * to the front page to find them.
 *
 * Only the landing carries the FAQPage structured data (see Theme\Seo). The
 * same markup on twenty product URLs would be the same FAQPage twenty times,
 * which is not what that node is for.
 *
 * @package CosyPaw
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cosypaw_faqs = \Theme\Seo::faqs();

if ( ! $cosypaw_faqs ) {
	return;
}
?>
<section id="faq" class="section">
	<div class="section__head">
		<span class="eyebrow"><?php esc_html_e( 'Česta pitanja', 'cosypaw' ); ?></span>
		<h2 class="section__title"><?php esc_html_e( 'Sve što te zanima', 'cosypaw' ); ?></h2>
		<p class="section__lead"><?php esc_html_e( 'Ako ne nađeš odgovor, piši nam — rado pomažemo.', 'cosypaw' ); ?></p>
	</div>

	<div class="faq">
		<?php
		// Shared with the FAQPage structured data in Theme\Seo so the two
		// cannot drift — Google penalises markup that does not match what
		// the page actually shows.
		foreach ( $cosypaw_faqs as $cosypaw_faq ) :
			?>
			<details class="faq-item">
				<summary class="faq-item__q">
					<?php echo esc_html( $cosypaw_faq['q'] ); ?>
					<span class="faq-item__icon" aria-hidden="true">
						<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
					</span>
				</summary>
				<p class="faq-item__a"><?php echo esc_html( $cosypaw_faq['a'] ); ?></p>
			</details>
		<?php endforeach; ?>
	</div>
</section>
