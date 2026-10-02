<?php
/**
 * Dugi peškiri — "Dugi peškiri sa vezenim medom".
 *
 * The long towels' own page: the four colours up top where they can be
 * bought, then copy for what people search for (peškir za lice, peškir za
 * ruke, peškiri za kupatilo…). It says only what the photographs show — no
 * size or fibre content until the shop has them to state.
 *
 * Rendered for the page \Theme\Pages creates under this slug; the products
 * come from \Theme\LongTowels. Until they exist the page still reads, without
 * the cards.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$cosypaw_long       = class_exists( '\Theme\LongTowels' ) ? \Theme\LongTowels::products() : array();
$cosypaw_collection = \Theme\Pages::url( 'motivi' );
?>

<main id="primary" class="site-main collection" tabindex="-1">

	<section class="section collection__intro">
		<nav class="collection__crumbs" aria-label="<?php esc_attr_e( 'Putanja', 'cosypaw' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Početna', 'cosypaw' ); ?></a>
			<span aria-hidden="true">›</span>
			<span aria-current="page"><?php esc_html_e( 'Dugi peškiri', 'cosypaw' ); ?></span>
		</nav>

		<div class="section__head">
			<span class="eyebrow"><?php esc_html_e( 'Dugi peškiri', 'cosypaw' ); ?></span>
			<h1 class="section__title"><?php esc_html_e( 'Dugi peškiri sa vezenim medom', 'cosypaw' ); ?></h1>
			<p class="section__lead">
				<?php
				echo esc_html(
					sprintf(
						/* translators: %s: formatted price of one long towel. */
						__( 'Mekani, upijajući peškiri za lice, ruke i kosu, sa vezenim medom i šapicom na borduri. Nežne boje za svako kupatilo, za %s po komadu.', 'cosypaw' ),
						\Theme\Catalog::format_price( \Theme\LongTowels::from_price( $cosypaw_long ) )
					)
				);
				?>
			</p>
		</div>
	</section>

	<?php if ( $cosypaw_long ) : ?>
		<section class="section collection__group cloths-section" id="peskiri">
			<?php
			get_template_part(
				'template-parts/cloth-cards',
				null,
				array(
					'cloths' => $cosypaw_long,
					'layout' => 'four',
				)
			);
			?>
		</section>
	<?php endif; ?>

	<section id="peskiri-za-kupatilo" class="section seo-copy">
		<div class="section__head">
			<span class="eyebrow"><?php esc_html_e( 'Peškiri za kupatilo', 'cosypaw' ); ?></span>
			<h2 class="section__title"><?php esc_html_e( 'Mekani peškiri za lice, ruke i kosu', 'cosypaw' ); ?></h2>
		</div>

		<div class="seo-copy__body">
			<p><?php esc_html_e( 'Dugi peškir je onaj koji uzmeš svakog jutra: za lice posle umivanja, za ruke pored lavaboa i za kosu posle tuširanja. Duži je od peškirića za ruke, pa jednim potezom obrišeš i lice i kosu, a mekana, upijajuća tkanina brzo pokupi vodu.', 'cosypaw' ); ?></p>

			<p><?php esc_html_e( 'Na tkanoj borduri izvezen je mali meda sa šapicom i natpis GOODLUCK — sitan detalj koji kupatilo čini toplijim. Krem, roze, plava i bež su nežne boje koje se lako uklope uz pločice, mermer ili drvo, a lepo stoje i kada ih kombinuješ.', 'cosypaw' ); ?></p>

			<p>
				<?php
				if ( '' !== $cosypaw_collection ) {
					echo wp_kses(
						sprintf(
							/* translators: %s: link "dečije peškire sa motivima životinja", to the motif collection. */
							__( 'Ako tražiš peškir za lice ili peškir za ruke koji lepo izgleda i na držaču i na merdevinama za peškire, ovo je taj. A za najmlađe imamo i %s — ručno rađene peškiriće sa alkom za kačenje.', 'cosypaw' ),
							'<a href="' . esc_url( $cosypaw_collection ) . '">' . esc_html__( 'dečije peškire sa motivima životinja', 'cosypaw' ) . '</a>'
						),
						array( 'a' => array( 'href' => array() ) )
					);
				} else {
					esc_html_e( 'Ako tražiš peškir za lice ili peškir za ruke koji lepo izgleda i na držaču i na merdevinama za peškire, ovo je taj.', 'cosypaw' );
				}
				?>
			</p>

			<p>
				<?php
				esc_html_e( 'Dugi peškiri stižu kao i ostali CosyPaw proizvodi — uz plaćanje pouzećem i dostavu 2–4 dana širom Srbije.', 'cosypaw' );

				// Only while free delivery is switched on — derived, like every
				// other delivery claim on the site.
				$cosypaw_free_min = \Theme\Catalog::free_shipping_min();
				if ( $cosypaw_free_min > 0 ) {
					echo ' ' . esc_html(
						sprintf(
							/* translators: %s: formatted free-delivery threshold, e.g. "2.000 RSD". */
							__( 'Dodaj ih uz paket peškirića i lakše stigni do besplatne dostave preko %s.', 'cosypaw' ),
							\Theme\Catalog::format_price( $cosypaw_free_min )
						)
					);
				}
				?>
			</p>
		</div>

		<?php if ( $cosypaw_long ) : ?>
			<div class="seo-copy__cta">
				<a href="#peskiri" class="btn btn--primary"><?php esc_html_e( 'Poruči dugi peškir', 'cosypaw' ); ?></a>
			</div>
		<?php endif; ?>
	</section>
</main>

<?php
get_footer();
