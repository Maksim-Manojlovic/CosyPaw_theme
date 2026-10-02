<?php
/**
 * Cuddle Puff jastuci — "Cuddle Puff jastuci sa okicama".
 *
 * The pillows' own page: one card per shape with its colours as swatches, up
 * top where they can be bought, then copy for what people search for (jastuk
 * za stolicu, ukrasni jastuk, plišani jastuk, jastuk za dečiju sobu…). It says
 * only what the photographs show — no size or fibre content until the shop has
 * them to state.
 *
 * Rendered for the page \Theme\Pages creates under this slug; the products
 * come from \Theme\Pillows. Until they exist the page still reads, without the
 * cards.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$cosypaw_pillows = class_exists( '\Theme\Pillows' ) ? \Theme\Pillows::products() : array();
$cosypaw_long    = \Theme\Pages::url( 'dugi' );
?>

<main id="primary" class="site-main collection" tabindex="-1">

	<section class="section collection__intro">
		<nav class="collection__crumbs" aria-label="<?php esc_attr_e( 'Putanja', 'cosypaw' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Početna', 'cosypaw' ); ?></a>
			<span aria-hidden="true">›</span>
			<span aria-current="page"><?php esc_html_e( 'Cuddle Puff jastuci', 'cosypaw' ); ?></span>
		</nav>

		<div class="section__head">
			<span class="eyebrow"><?php esc_html_e( 'Cuddle Puff jastuci', 'cosypaw' ); ?></span>
			<h1 class="section__title"><?php esc_html_e( 'Mekani Cuddle Puff jastuci sa okicama', 'cosypaw' ); ?></h1>
			<p class="section__lead">
				<?php
				echo esc_html(
					sprintf(
						/* translators: %s: formatted price of one pillow. */
						__( 'Punašni plišani jastuci sa vezenim okicama, kljunićem i listićem, u obliku kružića ili kockasti. Za stolicu, fotelju, sofu ili dečiju sobu, za %s.', 'cosypaw' ),
						\Theme\Catalog::format_price( \Theme\Pillows::from_price( $cosypaw_pillows ) )
					)
				);
				?>
			</p>
		</div>
	</section>

	<?php if ( $cosypaw_pillows ) : ?>
		<section class="section collection__group cloths-section" id="jastuci">
			<?php
			get_template_part(
				'template-parts/swatch-cards',
				null,
				array(
					'groups' => \Theme\Pillows::groups( $cosypaw_pillows ),
					'id'     => 'jastuk',
				)
			);
			?>
		</section>
	<?php endif; ?>

	<?php
	get_template_part(
		'template-parts/bundle-section',
		null,
		array(
			'keys'  => array( 'kutak-za-decju-sobu', 'novi-dom', 'za-dvoje' ),
			'id'    => 'poklon-paketi',
			'title' => __( 'Cuddle Puff jastuci u poklon paketu', 'cosypaw' ),
			'lead'  => __( 'Uz peškire i krpice — jeftinije nego posebno.', 'cosypaw' ),
		)
	);
	?>

	<section id="jastuci-za-dom" class="section seo-copy">
		<div class="section__head">
			<span class="eyebrow"><?php esc_html_e( 'Jastuci za dom', 'cosypaw' ); ?></span>
			<h2 class="section__title"><?php esc_html_e( 'Jastuk za stolicu, ukrasni jastuk i plišani drug u jednom', 'cosypaw' ); ?></h2>
		</div>

		<div class="seo-copy__body">
			<p><?php esc_html_e( 'Cuddle Puff jastuk je onaj koji svi požele da zagrle. Rebrasta plišana tkanina mekana je na dodir, a punašni, prošiveni oblik čini ga udobnim kao jastuk za stolicu ili fotelju — u kuhinji, za radnim stolom ili u dnevnoj sobi.', 'cosypaw' ); ?></p>

			<p><?php esc_html_e( 'Biraš između dva oblika. Kružić ima talasaste ivice kao cvet, a Kockasti je četvrtast, sa istim talasastim obrubom. Oba imaju vezene okice, žuti kljunić, zeleni plišani listić i omčicu za kačenje, i dolaze u nežnim bojama — od roze i žute do sive i braon.', 'cosypaw' ); ?></p>

			<p><?php esc_html_e( 'Kao ukrasni jastuk na sofi ili krevetu unose boju i osmeh, a u dečijoj sobi postaju plišani drug za maženje, čitanje i igru. Lepi su i kao poklon — za useljenje, rođendan ili bez povoda.', 'cosypaw' ); ?></p>

			<p>
				<?php
				if ( '' !== $cosypaw_long ) {
					echo wp_kses(
						sprintf(
							/* translators: %s: link "dugim peškirima sa vezenim medom", to the long towel page. */
							__( 'Cuddle Puff jastuke šaljemo kao i ostale CosyPaw proizvode — uz plaćanje pouzećem i dostavu 2–4 dana širom Srbije. Uz jastuk lepo idu i naši mekani proizvodi za kupatilo, poput %s.', 'cosypaw' ),
							'<a href="' . esc_url( $cosypaw_long ) . '">' . esc_html__( 'dugih peškira sa vezenim medom', 'cosypaw' ) . '</a>'
						),
						array( 'a' => array( 'href' => array() ) )
					);
				} else {
					esc_html_e( 'Cuddle Puff jastuke šaljemo kao i ostale CosyPaw proizvode — uz plaćanje pouzećem i dostavu 2–4 dana širom Srbije.', 'cosypaw' );
				}

				// Only while free delivery is switched on — derived, like every
				// other delivery claim on the site.
				$cosypaw_free_min = \Theme\Catalog::free_shipping_min();
				if ( $cosypaw_free_min > 0 ) {
					echo ' ' . esc_html(
						sprintf(
							/* translators: %s: formatted free-delivery threshold, e.g. "2.000 RSD". */
							__( 'Dodaj jastuk uz paket peškira i lakše stigni do besplatne dostave preko %s.', 'cosypaw' ),
							\Theme\Catalog::format_price( $cosypaw_free_min )
						)
					);
				}
				?>
			</p>
		</div>

		<?php if ( $cosypaw_pillows ) : ?>
			<div class="seo-copy__cta">
				<a href="#jastuci" class="btn btn--primary"><?php esc_html_e( 'Poruči Cuddle Puff jastuk', 'cosypaw' ); ?></a>
			</div>
		<?php endif; ?>
	</section>
</main>

<?php
get_footer();
