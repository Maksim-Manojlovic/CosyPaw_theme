<?php
/**
 * Gift sets — "Poklon setovi peškira za bebu i decu".
 *
 * The SEO plan's page for people who search for a gift rather than a towel:
 * the occasions it is bought for, the package builder (every package already
 * ships boxed, with a note and lavender), and the plan's gift copy — rewritten
 * so it only promises what the shop does. There is no engraving or printed
 * message to order, so "personalised" here means what the builder already
 * offers: the buyer picks every motif.
 *
 * Rendered for the page \Theme\Pages creates under this slug; everything is
 * written here, in three languages, like the front page.
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
$selected   = $storefront['selected'];
$free_min   = (int) $storefront['free_min'];

/*
 * The occasions the plan names, each shown with a motif that suits it. The
 * pairing is editorial; a motif that is off sale falls back to the next one
 * in the catalogue, as the front page's benefit cards do.
 */
$cosypaw_occasions = array(
	array(
		'motif' => 'zeka',
		'title' => __( 'Za rođenje bebe', 'cosypaw' ),
		'text'  => __( 'Devojčica ili dečak — mekani drugar za dečiju sobu ili kupatilo, spreman da dočeka bebu.', 'cosypaw' ),
	),
	array(
		'motif' => 'sova',
		'title' => __( 'Za krštenje', 'cosypaw' ),
		'text'  => __( 'Nežan poklon set za bebu koji ostaje u upotrebi i dugo posle slavlja.', 'cosypaw' ),
	),
	array(
		'motif' => 'krofna',
		'title' => __( 'Za prvi rođendan', 'cosypaw' ),
		'text'  => __( 'Peškiri koje dete prepoznaje i uz koje uči da samo briše ruke.', 'cosypaw' ),
	),
	array(
		'motif' => 'meda',
		'title' => __( 'Za bebi šauer', 'cosypaw' ),
		'text'  => __( 'Poklon koji se otvara uz osmeh i koji roditelji zaista koriste svaki dan.', 'cosypaw' ),
	),
);
$cosypaw_by_id = array_column( $products, null, 'id' );

/*
 * Offer and delivery claims are derived, never typed: the same rule the hero
 * follows. Reprice the package or switch free delivery off in wp-admin and the
 * sentence stops making the promise instead of making a false one.
 */
$cosypaw_qty    = (int) ( $selected['qty'] ?? 0 );
$cosypaw_gratis = (int) ( $selected['gratis'] ?? 0 );
$cosypaw_pay    = $cosypaw_qty - $cosypaw_gratis;
$cosypaw_offer  = array();
if ( $cosypaw_qty > 1 && $cosypaw_gratis > 0 && $cosypaw_pay > 0 ) {
	$cosypaw_offer[] = sprintf(
		/* translators: 1: towels in the package, 2: towels paid for. */
		__( 'U paketu od %1$d peškira plaćaš %2$d — pokloni za decu ne moraju biti skupi da bi bili nezaboravni.', 'cosypaw' ),
		$cosypaw_qty,
		$cosypaw_pay
	);
}
if ( $free_min > 0 ) {
	$cosypaw_offer[] = sprintf(
		/* translators: %s: formatted free-delivery threshold, e.g. "2.000 RSD". */
		__( 'Za porudžbine preko %s dostava je besplatna, a plaćaš pouzećem.', 'cosypaw' ),
		\Theme\Catalog::format_price( $free_min )
	);
}

// The plan's internal links, one each: the motif collection and the home page.
$cosypaw_link        = static fn( string $href, string $text ): string => '<a href="' . esc_url( $href ) . '">' . esc_html( $text ) . '</a>';
$cosypaw_kses        = array( 'a' => array( 'href' => array() ) );
$cosypaw_motifs_href = \Theme\Pages::url( 'motivi' );
?>

<main id="primary" class="site-main collection" tabindex="-1">

	<section class="section collection__intro">
		<nav class="collection__crumbs" aria-label="<?php esc_attr_e( 'Putanja', 'cosypaw' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Početna', 'cosypaw' ); ?></a>
			<span aria-hidden="true">›</span>
			<span aria-current="page"><?php esc_html_e( 'Poklon setovi peškira za decu i bebe', 'cosypaw' ); ?></span>
		</nav>

		<div class="section__head">
			<span class="eyebrow"><?php esc_html_e( 'Pokloni', 'cosypaw' ); ?></span>
			<h1 class="section__title"><?php esc_html_e( 'Poklon setovi peškira za decu i bebe', 'cosypaw' ); ?></h1>
			<p class="section__lead"><?php esc_html_e( 'Ručno rađeni dečiji peškiri od mikrofibre, složeni u set po tvom izboru i spakovani u CosyPaw kutiju — poklon za bebu i decu koji se otvara uz osmeh i koristi svaki dan.', 'cosypaw' ); ?></p>
		</div>
	</section>

	<section class="section collection__group" id="prilike">
		<div class="section__head">
			<h2 class="section__title"><?php esc_html_e( 'Poklon za svaku priliku', 'cosypaw' ); ?></h2>
		</div>

		<div class="benefits">
			<?php
			foreach ( $cosypaw_occasions as $cosypaw_n => $cosypaw_o ) :
				$motif = $cosypaw_by_id[ $cosypaw_o['motif'] ] ?? ( $products[ $cosypaw_n % max( 1, count( $products ) ) ] ?? null );
				?>
				<div class="benefit">
					<?php if ( $motif ) : ?>
						<img
							class="benefit__photo"
							src="<?php echo esc_url( $motif['image_th'] ); ?>"
							width="192"
							height="192"
							alt="<?php echo esc_attr( \Theme\Storefront::motif_alt( $motif ) ); ?>"
							loading="lazy"
							decoding="async"
						>
					<?php endif; ?>
					<h3 class="benefit__title"><?php echo esc_html( $cosypaw_o['title'] ); ?></h3>
					<p class="benefit__text"><?php echo esc_html( $cosypaw_o['text'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</section>

	<section id="unikatni-pokloni" class="section seo-copy">
		<div class="section__head">
			<span class="eyebrow"><?php esc_html_e( 'Zašto CosyPaw poklon', 'cosypaw' ); ?></span>
			<h2 class="section__title"><?php esc_html_e( 'Unikatni pokloni za bebu i decu', 'cosypaw' ); ?></h2>
		</div>

		<div class="seo-copy__body">
			<p><?php esc_html_e( 'Pronaći unikatan poklon nije lako, ali poklon set peškira za bebu to rešava jednostavno: praktičan je, lep i pamti se. Svaki set stiže u CosyPaw kutiji, sa porukom dobrodošlice i mirisom lavande, spreman da se preda bez dodatnog pakovanja.', 'cosypaw' ); ?></p>

			<p><?php esc_html_e( 'Bilo da biraš poklon set za bebu za krštenje, poklon za rođenje bebe — devojčica ili dečak — ili poklone za prvi rođendan, roditelji će peškire koristiti svaki dan. Svaki put kad vide drugara na kuki, na dečijoj visini, setiće se tvog gesta. Kao poklon za baby shower naši setovi su posebno omiljeni, jer spajaju lepo i korisno.', 'cosypaw' ); ?></p>

			<p>
				<?php
				$cosypaw_motifs_text = __( 'dečijih peškira sa motivima životinja', 'cosypaw' );
				echo wp_kses(
					sprintf(
						/* translators: %s: link "dečijih peškira sa motivima životinja", to the motif collection. */
						__( 'Ako tražiš personalizovane poklone za decu, set sastavljaš sam: izaberi motive po ukusu deteta iz naše kolekcije %s, zalogajčića i cvetića.', 'cosypaw' ),
						'' !== $cosypaw_motifs_href ? $cosypaw_link( $cosypaw_motifs_href, $cosypaw_motifs_text ) : esc_html( $cosypaw_motifs_text )
					),
					$cosypaw_kses
				);
				?>
			</p>

			<?php if ( $cosypaw_offer ) : ?>
				<p><?php echo esc_html( implode( ' ', $cosypaw_offer ) ); ?></p>
			<?php endif; ?>

			<p>
				<?php
				echo wp_kses(
					sprintf(
						/* translators: %s: link "početnoj strani", to the home page. */
						__( 'Pokloni za bebu širom Srbije stižu za 2–4 radna dana. Više o tome kako nastaju CosyPaw peškiri pročitaj na %s.', 'cosypaw' ),
						$cosypaw_link( home_url( '/' ), __( 'početnoj strani', 'cosypaw' ) )
					),
					$cosypaw_kses
				);
				?>
			</p>
		</div>

		<div class="seo-copy__cta">
			<a href="#napravi-paket" class="btn btn--primary"><?php esc_html_e( 'Naruči poklon set', 'cosypaw' ); ?></a>
		</div>
	</section>

	<?php get_template_part( 'template-parts/packages', null, $storefront ); ?>
</main>

<?php
get_footer();
