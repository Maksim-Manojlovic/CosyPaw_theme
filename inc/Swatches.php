<?php
/**
 * Swatches — the colour row of a product page: "Izaberi boju" and a dot per
 * colour, the same dots the product cards carry.
 *
 * Two kinds of product have colours here. The pillows and the Meda towel are
 * a product per colour, so on their page each dot is a link to its sibling
 * (links()). The penguin is one variable product, so its dots stand in for
 * WooCommerce's colour dropdown (radios()); a little script keeps the two in
 * step, and the dropdown is what the page shows without script. The Dugonja
 * is a variable product too, but its options are friends, not colours: each
 * radio shows the friend's head instead of a dot, under its own legend.
 *
 * Markup only: no WordPress or WooCommerce calls beyond escaping, so it can be
 * tested on its own.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

namespace Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Swatches.
 */
final class Swatches {

	/**
	 * A row of links, one per colour of a design, the current one marked.
	 *
	 * @param array<int,array<string,mixed>> $variants ProductLine::groups() variants: `key`, `color`, `swatch`, `permalink`.
	 * @param string                         $current  ITEMS key of the product on screen.
	 * @return string HTML, '' when there is nothing to choose between.
	 */
	public static function links( array $variants, string $current ): string {
		if ( count( $variants ) < 2 ) {
			return '';
		}

		$items = '';
		foreach ( $variants as $variant ) {
			$is_current = (string) ( $variant['key'] ?? '' ) === $current;
			$colour     = (string) ( $variant['color'] ?? '' );

			$items .= sprintf(
				'<a class="colour-swatch" href="%1$s" title="%2$s" aria-label="%2$s"%3$s>%4$s</a>',
				esc_url( (string) ( $variant['permalink'] ?? '' ) ),
				esc_attr( $colour ),
				$is_current ? ' aria-current="true"' : '',
				self::dot( (string) ( $variant['swatch'] ?? '' ) )
			);
		}

		return self::wrap( $items, '' );
	}

	/**
	 * A row of radio buttons that drive a <select>.
	 *
	 * @param array<int,array{value:string,label:string,swatch:string,image?:string}> $options  Options, in order: the select's option `value`, its label, a CSS colour and, optionally, the URL of a picture (a head) shown instead of the dot.
	 * @param string                                                                   $selected Value that is selected now.
	 * @param string                                                                   $select   Id of the <select> they drive.
	 * @param string                                                                   $legend   Translated legend; "Izaberi boju" when empty.
	 * @return string HTML.
	 */
	public static function radios( array $options, string $selected, string $select, string $legend = '' ): string {
		$items = '';
		foreach ( $options as $option ) {
			$image  = (string) ( $option['image'] ?? '' );
			$items .= sprintf(
				'<label class="colour-swatch%6$s" title="%1$s"><input type="radio" class="colour-swatch__input" name="%2$s" value="%3$s"%4$s>%5$s<span class="screen-reader-text">%1$s</span></label>',
				esc_attr( $option['label'] ),
				esc_attr( $select . '-swatch' ),
				esc_attr( $option['value'] ),
				$selected === $option['value'] ? ' checked' : '',
				'' !== $image ? self::head( $image ) : self::dot( $option['swatch'] ),
				'' !== $image ? ' colour-swatch--head' : ''
			);
		}

		return self::wrap( $items, ' data-colour-swatches="' . esc_attr( $select ) . '"', $legend );
	}

	/**
	 * The coloured dot.
	 *
	 * @param string $css CSS colour.
	 * @return string
	 */
	private static function dot( string $css ): string {
		return sprintf( '<span class="colour-swatch__dot" style="--swatch: %s" aria-hidden="true"></span>', esc_attr( $css ) );
	}

	/**
	 * A picture in place of the dot: the friend's head, round like a dot. The
	 * label beside it names the option, so the picture itself is decorative.
	 *
	 * @param string $url Picture URL.
	 * @return string
	 */
	private static function head( string $url ): string {
		return sprintf( '<img class="colour-swatch__dot colour-swatch__dot--head" src="%s" alt="" width="48" height="48" loading="lazy" decoding="async">', esc_url( $url ) );
	}

	/**
	 * Legend and list.
	 *
	 * @param string $items  Swatches.
	 * @param string $attrs  Extra attributes for the wrapper, with a leading space.
	 * @param string $legend Translated legend; "Izaberi boju" when empty.
	 * @return string
	 */
	private static function wrap( string $items, string $attrs, string $legend = '' ): string {
		return sprintf(
			'<div class="colour-swatches"%1$s><span class="colour-swatches__legend">%2$s</span><span class="colour-swatches__list">%3$s</span></div>',
			$attrs,
			esc_html( '' !== $legend ? $legend : __( 'Izaberi boju', 'cosypaw' ) ),
			$items
		);
	}
}
