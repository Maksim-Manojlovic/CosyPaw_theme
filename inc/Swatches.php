<?php
/**
 * Swatches — the colour row of a product page: "Izaberi boju" and a dot per
 * colour, the same dots the product cards carry.
 *
 * Two kinds of product have colours here. The pillows and the Meda towel are
 * a product per colour, so on their page each dot is a link to its sibling
 * (links()). The penguin is one variable product, so its dots stand in for
 * WooCommerce's colour dropdown (radios()); a little script keeps the two in
 * step, and the dropdown is what the page shows without script.
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
	 * @param array<int,array{value:string,label:string,swatch:string}> $options  Colours, in order: the select's option `value`, its label, a CSS colour.
	 * @param string                                                    $selected Value that is selected now.
	 * @param string                                                    $select   Id of the <select> they drive.
	 * @return string HTML.
	 */
	public static function radios( array $options, string $selected, string $select ): string {
		$items = '';
		foreach ( $options as $option ) {
			$items .= sprintf(
				'<label class="colour-swatch" title="%1$s"><input type="radio" class="colour-swatch__input" name="%2$s" value="%3$s"%4$s>%5$s<span class="screen-reader-text">%1$s</span></label>',
				esc_attr( $option['label'] ),
				esc_attr( $select . '-swatch' ),
				esc_attr( $option['value'] ),
				$selected === $option['value'] ? ' checked' : '',
				self::dot( $option['swatch'] )
			);
		}

		return self::wrap( $items, ' data-colour-swatches="' . esc_attr( $select ) . '"' );
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
	 * Legend and list.
	 *
	 * @param string $items Swatches.
	 * @param string $attrs Extra attributes for the wrapper, with a leading space.
	 * @return string
	 */
	private static function wrap( string $items, string $attrs ): string {
		return sprintf(
			'<div class="colour-swatches"%1$s><span class="colour-swatches__legend">%2$s</span><span class="colour-swatches__list">%3$s</span></div>',
			$attrs,
			esc_html__( 'Izaberi boju', 'cosypaw' ),
			$items
		);
	}
}
