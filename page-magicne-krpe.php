<?php
/**
 * Magične krpe — "Magična krpa za savršeno čist dom".
 *
 * The SEO plan's cloth page: the two cloths up top where they can be bought,
 * then the plan's cleaning copy with its keywords (kuhinjske krpe, krpe za
 * staklo, mikrofiber krpa…). The plan's claims that could not be backed — a
 * crowd of users calling it the best, firms using it — are rephrased as what
 * the cloth does, not what others are said to say about it.
 *
 * Rendered for the page \Theme\Pages creates under this slug; the products
 * come from \Theme\Cloths. Until they exist the page still reads, without
 * the cards.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$cosypaw_cloths = class_exists( '\Theme\Cloths' ) ? \Theme\Cloths::products() : array();
$cosypaw_home   = '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'CosyPaw', 'cosypaw' ) . '</a>';
?>

<main id="primary" class="site-main collection" tabindex="-1">

	<section class="section collection__intro">
		<nav class="collection__crumbs" aria-label="<?php esc_attr_e( 'Putanja', 'cosypaw' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Početna', 'cosypaw' ); ?></a>
			<span aria-hidden="true">›</span>
			<span aria-current="page"><?php esc_html_e( 'Magične krpe', 'cosypaw' ); ?></span>
		</nav>

		<div class="section__head">
			<span class="eyebrow"><?php esc_html_e( 'Magične krpe', 'cosypaw' ); ?></span>
			<h1 class="section__title"><?php esc_html_e( 'Magična krpa za savršeno čist dom', 'cosypaw' ); ?></h1>
			<p class="section__lead">
				<?php
				echo esc_html(
					sprintf(
						/* translators: %s: formatted price of one cloth. */
						__( 'Mikrofiber krpa sa vezenim avokadom koja upija vodu, hvata prašinu i briše staklo bez tragova. Kuhinjska krpa i krpa za staklo u jednoj, za %s.', 'cosypaw' ),
						\Theme\Catalog::format_price( \Theme\Cloths::from_price( $cosypaw_cloths ) )
					)
				);
				?>
			</p>
		</div>
	</section>

	<?php if ( $cosypaw_cloths ) : ?>
		<section class="section collection__group cloths-section" id="krpe">
			<?php get_template_part( 'template-parts/cloth-cards', null, array( 'cloths' => $cosypaw_cloths ) ); ?>
		</section>
	<?php endif; ?>

	<section id="ciscenje-bez-tragova" class="section seo-copy">
		<div class="section__head">
			<span class="eyebrow"><?php esc_html_e( 'Čišćenje bez tragova', 'cosypaw' ); ?></span>
			<h2 class="section__title"><?php esc_html_e( 'Kuhinjske krpe i krpe za staklo u jednoj', 'cosypaw' ); ?></h2>
		</div>

		<div class="seo-copy__body">
			<p><?php esc_html_e( 'Želiš li da čišćenje bude brže, lakše i potpuno bez tragova? Magična krpa je upravo to. Ova mikrofiber krpa spaja mekoću, izdržljivost i praktičnost: kao kuhinjska krpa upija vodu sa radnih površina i sudova, a kao krpa za prašinu zadržava čestice umesto da ih razbacuje po vazduhu.', 'cosypaw' ); ?></p>

			<p><?php esc_html_e( 'Posebno je dobra na staklu. Magične krpe za stakla ostavljaju blistav sjaj bez mrlja, tragova i dlačica, bez agresivnih hemijskih sredstava. Nisu samo za prozore: ista krpa za staklo briše ogledala u kupatilu, staklene vitrine, tuš kabine, pa i ekran televizora.', 'cosypaw' ); ?></p>

			<p><?php esc_html_e( 'Ako tražiš najbolju krpu za staklo, onu kakvu očekuješ od profesionalnih krpa za stakla, a da ostane i u kuhinji, ovo je ta. Krpe su višekratne, peru se u mašini na 40°C i zamenjuju gomilu papirnih ubrusa.', 'cosypaw' ); ?></p>

			<p>
				<?php
				echo wp_kses(
					sprintf(
						/* translators: %s: link "CosyPaw", to the home page. */
						__( 'Magične krpice šaljemo kao i %s peškire — u CosyPaw paketu, uz plaćanje pouzećem i dostavu 2–4 dana širom Srbije.', 'cosypaw' ),
						$cosypaw_home
					),
					array( 'a' => array( 'href' => array() ) )
				);

				// Only while free delivery is switched on — derived, like every
				// other delivery claim on the site.
				$cosypaw_free_min = \Theme\Catalog::free_shipping_min();
				if ( $cosypaw_free_min > 0 ) {
					echo ' ' . esc_html(
						sprintf(
							/* translators: %s: formatted free-delivery threshold, e.g. "2.000 RSD". */
							__( 'Dodaj ih uz paket peškira i lakše stigni do besplatne dostave preko %s.', 'cosypaw' ),
							\Theme\Catalog::format_price( $cosypaw_free_min )
						)
					);
				}
				?>
			</p>
		</div>

		<?php if ( $cosypaw_cloths ) : ?>
			<div class="seo-copy__cta">
				<a href="#krpe" class="btn btn--primary"><?php esc_html_e( 'Poruči magične krpe', 'cosypaw' ); ?></a>
			</div>
		<?php endif; ?>
	</section>
</main>

<?php
get_footer();
