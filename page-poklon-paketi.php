<?php
/**
 * Poklon paketi — "Poklon paketi za svaku priliku".
 *
 * Every gift bundle, grouped by occasion (baby and children, home and
 * couples, small gifts, holidays), each group under a heading people search
 * for, then copy for the broader gift searches (poklon za bebu, poklon za
 * useljenje, poklon za vaspitačicu, novogodišnji paketić…).
 *
 * Rendered for the page \Theme\Pages creates under this slug; the bundles come
 * from \Theme\GiftBundles. Until they exist the page still reads, without the
 * cards.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$cosypaw_offers = class_exists( '\Theme\GiftBundles' ) ? \Theme\GiftBundles::offers() : array();
$cosypaw_groups = $cosypaw_offers ? \Theme\GiftBundles::by_occasion( $cosypaw_offers ) : array();
$cosypaw_baby   = \Theme\Pages::url( 'pokloni' );

// One heading per occasion, worded for what people type in.
$cosypaw_heads = array(
	'deca'     => array(
		'title' => __( 'Pokloni za bebe i decu', 'cosypaw' ),
		'lead'  => __( 'Za rođenje, krštenje, prvi rođendan i polazak u vrtić.', 'cosypaw' ),
	),
	'dom'      => array(
		'title' => __( 'Pokloni za useljenje i mladence', 'cosypaw' ),
		'lead'  => __( 'Za novi dom, novo kupatilo i zajednički stan.', 'cosypaw' ),
	),
	'paznja'   => array(
		'title' => __( 'Mali pokloni i znak pažnje', 'cosypaw' ),
		'lead'  => __( 'Za vaspitačicu, učiteljicu, koleginicu ili domaćicu.', 'cosypaw' ),
	),
	'praznici' => array(
		'title' => __( 'Novogodišnji pokloni', 'cosypaw' ),
		'lead'  => __( 'Paketić za celu porodicu, za Novu godinu i Božić.', 'cosypaw' ),
	),
);
?>

<main id="primary" class="site-main collection" tabindex="-1">

	<section class="section collection__intro">
		<nav class="collection__crumbs" aria-label="<?php esc_attr_e( 'Putanja', 'cosypaw' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Početna', 'cosypaw' ); ?></a>
			<span aria-hidden="true">›</span>
			<span aria-current="page"><?php esc_html_e( 'Poklon paketi', 'cosypaw' ); ?></span>
		</nav>

		<div class="section__head">
			<span class="eyebrow"><?php esc_html_e( 'Poklon paketi', 'cosypaw' ); ?></span>
			<h1 class="section__title"><?php esc_html_e( 'Poklon paketi za svaku priliku', 'cosypaw' ); ?></h1>
			<p class="section__lead"><?php esc_html_e( 'Gotove kombinacije peškira, Cuddle Puff jastuka i magičnih krpica, složene za jednu priliku — i jeftinije nego kad ih kupuješ posebno.', 'cosypaw' ); ?></p>
		</div>

		<?php if ( count( $cosypaw_groups ) > 1 ) : ?>
			<nav class="collection__jump" aria-label="<?php esc_attr_e( 'Prilike', 'cosypaw' ); ?>">
				<?php foreach ( array_keys( $cosypaw_groups ) as $cosypaw_key ) : ?>
					<a href="#<?php echo esc_attr( 'paketi-' . $cosypaw_key ); ?>"><?php echo esc_html( $cosypaw_heads[ $cosypaw_key ]['title'] ); ?></a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>
	</section>

	<?php foreach ( $cosypaw_groups as $cosypaw_key => $cosypaw_group ) : ?>
		<section class="section collection__group bundles-section" id="<?php echo esc_attr( 'paketi-' . $cosypaw_key ); ?>">
			<div class="section__head">
				<h2 class="section__title"><?php echo esc_html( $cosypaw_heads[ $cosypaw_key ]['title'] ); ?></h2>
				<p class="section__lead"><?php echo esc_html( $cosypaw_heads[ $cosypaw_key ]['lead'] ); ?></p>
			</div>
			<?php get_template_part( 'template-parts/bundle-cards', null, array( 'offers' => $cosypaw_group['offers'] ) ); ?>
		</section>
	<?php endforeach; ?>

	<section id="pokloni-za-svaku-priliku" class="section seo-copy">
		<div class="section__head">
			<span class="eyebrow"><?php esc_html_e( 'Praktični pokloni', 'cosypaw' ); ?></span>
			<h2 class="section__title"><?php esc_html_e( 'Poklon koji se koristi svaki dan', 'cosypaw' ); ?></h2>
		</div>

		<div class="seo-copy__body">
			<p><?php esc_html_e( 'Najbolji pokloni su oni koji ne završe u ormaru. Zato su naši poklon paketi sastavljeni od stvari koje se koriste svaki dan: dečijih peškira u obliku životinja sa alkom za kačenje, dugih peškira sa vezenim medom, mekanih Cuddle Puff jastuka i magičnih krpica od mikrofibera.', 'cosypaw' ); ?></p>

			<p>
				<?php
				if ( '' !== $cosypaw_baby ) {
					echo wp_kses(
						sprintf(
							/* translators: %s: link "poklon setove za bebu i decu", to the baby gift page. */
							__( 'Tražiš poklon za bebu, poklon za krštenje ili rođendan? Paketi za bebu i decu spajaju peškire koje dete prepozna na prvi pogled i jastuk za dečiju sobu. Ako želiš da sam biraš svaki oblik, pogledaj i naše %s.', 'cosypaw' ),
							'<a href="' . esc_url( $cosypaw_baby ) . '">' . esc_html__( 'poklon setove za bebu i decu', 'cosypaw' ) . '</a>'
						),
						array( 'a' => array( 'href' => array() ) )
					);
				} else {
					esc_html_e( 'Tražiš poklon za bebu, poklon za krštenje ili rođendan? Paketi za bebu i decu spajaju peškire koje dete prepozna na prvi pogled i jastuk za dečiju sobu.', 'cosypaw' );
				}
				?>
			</p>

			<p><?php esc_html_e( 'Za poklon za useljenje ili poklon za mladence tu su paketi sa jastucima, dugim peškirima i krpicama — po nešto za dnevnu sobu, kupatilo i kuhinju. A kad ti treba mali znak pažnje za vaspitačicu, učiteljicu ili koleginicu, ili novogodišnji paketić za celu porodicu, i za to imamo gotov paket.', 'cosypaw' ); ?></p>

			<p>
				<?php
				esc_html_e( 'Svaki paket stiže u jednoj porudžbini, uz plaćanje pouzećem i dostavu 2–4 dana širom Srbije.', 'cosypaw' );

				// Only while free delivery is switched on — derived, like every
				// other delivery claim on the site.
				$cosypaw_free_min = \Theme\Catalog::free_shipping_min();
				if ( $cosypaw_free_min > 0 ) {
					echo ' ' . esc_html(
						sprintf(
							/* translators: %s: formatted free-delivery threshold, e.g. "2.000 RSD". */
							__( 'Paketi od %s naviše stižu uz besplatnu dostavu.', 'cosypaw' ),
							\Theme\Catalog::format_price( $cosypaw_free_min )
						)
					);
				}
				?>
			</p>
		</div>

		<?php if ( $cosypaw_groups ) : ?>
			<div class="seo-copy__cta">
				<a href="#<?php echo esc_attr( 'paketi-' . array_key_first( $cosypaw_groups ) ); ?>" class="btn btn--primary"><?php esc_html_e( 'Izaberi poklon paket', 'cosypaw' ); ?></a>
			</div>
		<?php endif; ?>
	</section>
</main>

<?php
get_footer();
