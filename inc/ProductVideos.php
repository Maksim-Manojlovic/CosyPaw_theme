<?php
/**
 * ProductVideos — a towel's promo clip, as the last slide of its own gallery.
 *
 * A few motifs have a short clip as well as a photograph. It goes where a
 * shopper already looks for more pictures of the thing: the product gallery,
 * after the photographs, with a thumbnail of its own in the strip under them.
 * WooCommerce's slider treats any `.woocommerce-product-gallery__image` as a
 * slide, so the clip needs no gallery code — a towel with one photograph
 * simply becomes a gallery of two.
 *
 * The clips ship with the theme, in assets/videos/, rather than through the
 * media library: a deploy carries files and never the database, and a clip is
 * tied to a motif id the same way the motif photographs are.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

namespace Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ProductVideos.
 */
final class ProductVideos {

	/**
	 * Where the clips live, relative to the theme root. The landing's "U tvom
	 * domu" (front-page.php) plays two clips out of the same folder: one of
	 * these, and one of its own that belongs to no product.
	 */
	public const DIR = 'assets/videos/';

	/**
	 * Class the gallery carries when its last slide is a clip.
	 */
	public const GALLERY_CLASS = 'cosypaw-gallery--video';

	/**
	 * Motif id => the part of the frame to keep in view (CSS object-position).
	 *
	 * Each id resolves to three files in assets/videos/: `<id>.mp4` (720x1280,
	 * H.264, no sound track — it plays muted, in a loop), `<id>.webp` (its first
	 * frame, the poster) and `<id>-th.webp` (a 200x200 cut of that frame, for
	 * the thumbnail strip). Made from the shop's originals with ffmpeg:
	 * `-an -vf scale=720:1280 -c:v libx264 -preset veryslow -crf 24
	 * -pix_fmt yuv420p -movflags +faststart`, at 24 frames a second, or 30
	 * where the original has 60. The owl is all fine plush under moving hands
	 * and went through at `-crf 26`, which is what keeps it near a megabyte.
	 *
	 * The clips are 9:16 and the slide takes the shape of the photograph beside
	 * it, so some of the frame's height is always cut. The position says which
	 * part survives: the doughnut sits low in its frame, with the icing pooling
	 * under it, and centred it would lose that; the owl's loop and the pony
	 * standing up behind the phone are both near the top of theirs.
	 */
	private const CLIPS = array(
		'bundeva' => '50% 50%',
		'konjic'  => '50% 40%',
		'krofna'  => '50% 66%',
		'sova'    => '50% 40%',
	);

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'woocommerce_single_product_image_gallery_classes', array( $this, 'gallery_classes' ) );

		// After WooCommerce's own thumbnails (20), so the clip is the last
		// slide: the lightbox numbers the photographs by their place in the
		// gallery, and a clip in among them would shift every one after it.
		add_action( 'woocommerce_product_thumbnails', array( $this, 'slide' ), 30 );
	}

	/**
	 * Motif ids that have a clip.
	 *
	 * @return array<int,string>
	 */
	public static function motifs(): array {
		return array_keys( self::CLIPS );
	}

	/**
	 * The clip of a product, or null when it has none.
	 *
	 * @param int $product_id Product ID.
	 * @return array{id:string,src:string,poster:string,thumb:string,focus:string}|null
	 */
	public static function clip_for( int $product_id ): ?array {
		$map = array_map( 'intval', (array) get_option( WooCommerce::PRODUCT_MAP_OPTION, array() ) );
		$id  = $product_id > 0 ? array_search( $product_id, $map, true ) : false;

		if ( ! is_string( $id ) || ! isset( self::CLIPS[ $id ] ) ) {
			return null;
		}

		$base = get_template_directory_uri() . '/' . self::DIR . $id;

		return array(
			'id'     => $id,
			'src'    => $base . '.mp4',
			'poster' => $base . '.webp',
			'thumb'  => $base . '-th.webp',
			'focus'  => self::CLIPS[ $id ],
		);
	}

	/**
	 * Mark the gallery whose last slide is a clip.
	 *
	 * The thumbnail strip is built by the slider script, one bare image per
	 * slide, so the play badge on the clip's thumbnail can only be hung on the
	 * gallery: "the last thumbnail of a gallery that has a clip".
	 *
	 * @param mixed $classes Gallery wrapper classes.
	 * @return array<int,string>
	 */
	public function gallery_classes( $classes ): array {
		$classes = array_values( array_map( 'strval', (array) $classes ) );

		if ( null !== $this->current_clip() ) {
			$classes[] = self::GALLERY_CLASS;
		}

		return $classes;
	}

	/**
	 * The clip's slide, inside the gallery of a product that has one.
	 *
	 * @return void
	 */
	public function slide(): void {
		$product = function_exists( 'wc_get_product' ) ? wc_get_product() : null;
		$clip    = $product instanceof \WC_Product ? self::clip_for( (int) $product->get_id() ) : null;

		if ( null === $clip ) {
			return;
		}

		get_template_part(
			'template-parts/product-video',
			null,
			$clip + array(
				'name'  => (string) $product->get_name(),
				'ratio' => $this->photo_ratio( $product ),
			)
		);
	}

	/**
	 * The clip of the product on screen, or null.
	 *
	 * @return array<string,string>|null
	 */
	private function current_clip(): ?array {
		$product = function_exists( 'wc_get_product' ) ? wc_get_product() : null;

		return $product instanceof \WC_Product ? self::clip_for( (int) $product->get_id() ) : null;
	}

	/**
	 * The shape of the product's photograph, as a CSS aspect-ratio.
	 *
	 * The slider resizes the gallery to the slide on show. A clip in its own
	 * 9:16 would make the gallery a third taller the moment it slid in and
	 * push the thumbnails under it down the page; in the photograph's shape
	 * nothing moves. The towels are not all cut alike — the theme's own are
	 * 3:4, one uploaded in wp-admin is whatever was uploaded — so the shape is
	 * read from the picture rather than assumed. '' leaves it to the
	 * stylesheet's 3:4.
	 *
	 * @param \WC_Product $product Product.
	 * @return string
	 */
	private function photo_ratio( \WC_Product $product ): string {
		$image_id = (int) $product->get_image_id();
		$src      = $image_id > 0 ? wp_get_attachment_image_src( $image_id, 'woocommerce_single' ) : false;

		$width  = is_array( $src ) ? (int) ( $src[1] ?? 0 ) : 0;
		$height = is_array( $src ) ? (int) ( $src[2] ?? 0 ) : 0;

		return $width > 0 && $height > 0 ? $width . ' / ' . $height : '';
	}
}
