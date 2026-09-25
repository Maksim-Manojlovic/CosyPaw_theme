<?php
/**
 * Motif collection — "Dečiji peškiri sa motivima životinja".
 *
 * The SEO plan's category page: every towel on one page, grouped the way the
 * shop files them, with the hygiene-through-play copy the plan asks for and the
 * package builder underneath so a visitor who arrives from search can buy
 * without going anywhere else.
 *
 * WordPress picks this file for the page whose slug it is named after. That
 * page is created by \Theme\Pages and has no body of its own: everything here
 * is written in the theme, and so translated with everything else.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$storefront = \Theme\Storefront::context( new \Theme\Catalog() );
$products   = $storefront['products'];

/*
 * Heading and lead per subcategory, in the order the page shows them —
 * animals first, since that is what the page is named for. Written here rather
 * than read from the term, because a term name is a Serbian database value and
 * this page is served in three languages. "Peškirići u torbi" gets no lead:
 * what is in the bag is the product page's to describe.
 */
$cosypaw_group_copy = array(
	'zivotinjice'       => array(
		'title' => __( 'Peškiri sa životinjicama', 'cosypaw' ),
		'lead'  => __( 'Meki drugari koji čekaju na kuki — dete samo bira svog i raduje mu se posle svakog pranja ruku.', 'cosypaw' ),
	),
	'zalogajcici'       => array(
		'title' => __( 'Zalogajčići', 'cosypaw' ),
		'lead'  => __( 'Voće i poslastice od mikrofibre — vesela sitnica za kupatilo ili kuhinju.', 'cosypaw' ),
	),
	'cvetici-i-listici' => array(
		'title' => __( 'Cvetići i listići', 'cosypaw' ),
		'lead'  => __( 'Nežni cvetni motivi za mirniji, prirodni kutak.', 'cosypaw' ),
	),
	'peskirici-u-torbi' => array(
		'title' => __( 'Peškirići u torbi', 'cosypaw' ),
		'lead'  => '',
	),
);

$groups = \Theme\Storefront::by_subcategory( $products, array_keys( $cosypaw_group_copy ) );

// Until the shop files its towels into subcategories every motif lands in the
// unfiled group, and a lone "Još peškira" heading under the page's own H1
// would be a heading with nothing before it to be "more" than.
$cosypaw_only_unfiled = array( '' ) === array_keys( $groups );

$cosypaw_home_link = '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'ručno rađeni peškiri', 'cosypaw' ) . '</a>';
?>

<main id="primary" class="site-main collection" tabindex="-1">

	<section class="section collection__intro">
		<nav class="collection__crumbs" aria-label="<?php esc_attr_e( 'Putanja', 'cosypaw' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Početna', 'cosypaw' ); ?></a>
			<span aria-hidden="true">›</span>
			<span aria-current="page"><?php esc_html_e( 'Dečiji peškiri sa motivima životinja', 'cosypaw' ); ?></span>
		</nav>

		<div class="section__head">
			<span class="eyebrow"><?php esc_html_e( 'Kolekcija', 'cosypaw' ); ?></span>
			<h1 class="section__title"><?php esc_html_e( 'Dečiji peškiri sa motivima životinja', 'cosypaw' ); ?></h1>
			<p class="section__lead">
				<?php
				echo esc_html(
					sprintf(
						/* translators: 1: how many motifs are on sale, 2: formatted lowest unit price. */
						__( '%1$d ručno rađenih peškira od mikrofibre, sa alkom za kačenje, od %2$s po komadu. Izaberi drugara za svoje dete — kupi jedan ili ih spoji u paket i uštedi.', 'cosypaw' ),
						count( $products ),
						\Theme\Catalog::format_price( (int) $storefront['from_price'] )
					)
				);
				?>
			</p>
		</div>
	</section>

	<?php foreach ( $groups as $cosypaw_slug => $cosypaw_rows ) : ?>
		<?php
		$cosypaw_copy = $cosypaw_group_copy[ $cosypaw_slug ] ?? array(
			'title' => $cosypaw_only_unfiled ? '' : __( 'Još peškira', 'cosypaw' ),
			'lead'  => '',
		);
		?>
		<section class="section collection__group"<?php echo '' !== $cosypaw_slug ? ' id="' . esc_attr( $cosypaw_slug ) . '"' : ''; ?>>
			<?php if ( '' !== $cosypaw_copy['title'] ) : ?>
				<div class="section__head">
					<h2 class="section__title"><?php echo esc_html( $cosypaw_copy['title'] ); ?></h2>
					<?php if ( '' !== $cosypaw_copy['lead'] ) : ?>
						<p class="section__lead"><?php echo esc_html( $cosypaw_copy['lead'] ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="motifs">
				<?php
				foreach ( $cosypaw_rows as $cosypaw_row ) {
					get_template_part(
						'template-parts/motif-card',
						null,
						array(
							'motif'  => $cosypaw_row,
							'ladder' => $storefront['ladder'],
						)
					);
				}
				?>
			</div>
		</section>
	<?php endforeach; ?>

	<?php
	// The plan's hygiene-through-play copy, rewritten around the motifs the
	// shop actually sells. It ends on the builder below, not on another page.
	?>
	<section id="higijena-kroz-igru" class="section seo-copy">
		<div class="section__head">
			<span class="eyebrow"><?php esc_html_e( 'Zašto motivi', 'cosypaw' ); ?></span>
			<h2 class="section__title"><?php esc_html_e( 'Dečija higijena kroz igru', 'cosypaw' ); ?></h2>
		</div>

		<div class="seo-copy__body">
			<p><?php esc_html_e( 'Dečiji peškiri sa motivima životinja su mnogo više od običnog tekstila za kupatilo. Kad dete vidi omiljenog drugara kako ga čeka na kuki, baš na njegovoj visini, pranje ruku prestaje da bude obaveza i postaje igra.', 'cosypaw' ); ?></p>

			<p><?php esc_html_e( 'Zato je peškir za ruke zeka jedan od najomiljenijih: mekan je, prijateljski i deca ga odmah prepoznaju. Sovica podseća na mirne večernje rutine, a panda i kapibarica unose malo smeha u svako jutro.', 'cosypaw' ); ?></p>

			<p><?php esc_html_e( 'Svi peškiri su od plišane mikrofibre, nežne prema koži, koja se brzo suši i ostaje meka i posle mnogo pranja. Alka za kačenje drži peškir uvek nadohvat ruke, pa dete samo briše ruke i gradi higijenske navike koje ostaju. Svaki je šiven ručno, pa nema dva potpuno ista — baš kao ni dece.', 'cosypaw' ); ?></p>

			<p>
				<?php
				echo wp_kses(
					sprintf(
						/* translators: %s: link "ručno rađeni peškiri", to the home page. */
						__( 'Kako nastaju naši %s i šta sve stiže u paketu, pročitaj na početnoj strani.', 'cosypaw' ),
						$cosypaw_home_link
					),
					array( 'a' => array( 'href' => array() ) )
				);
				?>
			</p>
		</div>

		<div class="seo-copy__cta">
			<a href="#napravi-paket" class="btn btn--primary"><?php esc_html_e( 'Napravi svoj paket', 'cosypaw' ); ?></a>
		</div>
	</section>

	<?php get_template_part( 'template-parts/packages', null, $storefront ); ?>
</main>

<?php
get_footer();
