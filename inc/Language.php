<?php
/**
 * Language — lightweight front-end locale switcher (no multilingual plugin).
 *
 * Visitor language is chosen via ?lang=sr|en|ru, persisted in a cookie, and
 * applied through the `locale` filter so gettext (.mo in /languages) and
 * WooCommerce's own translations follow it. Admin language is left untouched.
 *
 * Instantiated very early (functions.php) so the `locale` filter is registered
 * before any text domain — theme, WooCommerce (init) — is loaded.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

namespace Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Language.
 */
final class Language {

	private const COOKIE       = 'cosypaw_lang';
	private const DEFAULT_LANG = 'sr';

	/**
	 * Supported lang code => WordPress locale.
	 *
	 * @var array<string,string>
	 */
	private const LOCALES = array(
		'sr' => 'sr_RS',
		'en' => 'en_US',
		'ru' => 'ru_RU',
	);

	/**
	 * Native language names (used as accessible labels).
	 *
	 * @var array<string,string>
	 */
	private const NAMES = array(
		'sr' => 'Srpski',
		'en' => 'English',
		'ru' => 'Русский',
	);

	/**
	 * Resolved current lang code.
	 *
	 * @var string
	 */
	private string $lang;

	/**
	 * Constructor — resolve the request language and hook the locale filter.
	 */
	public function __construct() {
		$this->lang = $this->detect();
		add_filter( 'locale', array( $this, 'filter_locale' ) );
	}

	/**
	 * Resolve language: ?lang= (also sets the cookie) > cookie > default.
	 *
	 * @return string
	 */
	private function detect(): string {
		$allowed = array_keys( self::LOCALES );

		if ( isset( $_GET['lang'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$lang = sanitize_key( wp_unslash( $_GET['lang'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( in_array( $lang, $allowed, true ) ) {
				if ( ! headers_sent() ) {
					setcookie(
						self::COOKIE,
						$lang,
						time() + YEAR_IN_SECONDS,
						defined( 'COOKIEPATH' ) ? COOKIEPATH : '/',
						defined( 'COOKIE_DOMAIN' ) && COOKIE_DOMAIN ? COOKIE_DOMAIN : ''
					);
				}
				$_COOKIE[ self::COOKIE ] = $lang;
				return $lang;
			}
		}

		if ( isset( $_COOKIE[ self::COOKIE ] ) ) {
			$lang = sanitize_key( wp_unslash( $_COOKIE[ self::COOKIE ] ) );
			if ( in_array( $lang, $allowed, true ) ) {
				return $lang;
			}
		}

		return self::DEFAULT_LANG;
	}

	/**
	 * Map the chosen language to a WP locale. Front-end only — admin keeps its
	 * own language.
	 *
	 * @param string $locale Incoming locale.
	 * @return string
	 */
	public function filter_locale( $locale ) {
		if ( is_admin() ) {
			return $locale;
		}

		return self::LOCALES[ $this->lang ] ?? $locale;
	}

	/**
	 * Current language code (sr|en|ru).
	 *
	 * @return string
	 */
	public function current(): string {
		return $this->lang;
	}

	/**
	 * Render the language switcher (flags) used in the nav.
	 *
	 * @return void
	 */
	public function switcher(): void {
		$svg_allowed = array(
			'svg'  => array( 'viewbox' => array(), 'class' => array(), 'aria-hidden' => array(), 'preserveaspectratio' => array() ),
			'rect' => array( 'x' => array(), 'y' => array(), 'width' => array(), 'height' => array(), 'fill' => array() ),
			'path' => array( 'd' => array(), 'stroke' => array(), 'stroke-width' => array(), 'fill' => array() ),
		);

		echo '<div class="lang-switch" role="group" aria-label="' . esc_attr__( 'Jezik', 'cosypaw' ) . '">';
		foreach ( array_keys( self::NAMES ) as $code ) {
			$url     = add_query_arg( 'lang', $code );
			$active  = $code === $this->lang;
			$classes = 'lang-switch__btn' . ( $active ? ' is-active' : '' );
			$name    = self::NAMES[ $code ];

			printf(
				'<a href="%1$s" class="%2$s" lang="%3$s" title="%4$s" aria-label="%4$s"%5$s>%6$s<span class="screen-reader-text">%4$s</span></a>',
				esc_url( $url ),
				esc_attr( $classes ),
				esc_attr( $code ),
				esc_attr( $name ),
				$active ? ' aria-current="true"' : '',
				wp_kses( $this->flag( $code ), $svg_allowed )
			);
		}
		echo '</div>';
	}

	/**
	 * Inline SVG flag for a language code (viewBox 0 0 60 30).
	 * Serbia is simplified (tricolor without the crest) for clarity at small size.
	 *
	 * @param string $code Language code.
	 * @return string SVG markup.
	 */
	private function flag( string $code ): string {
		switch ( $code ) {
			case 'sr':
				return '<svg class="lang-flag" viewBox="0 0 60 30" aria-hidden="true">'
					. '<rect width="60" height="30" fill="#ffffff"/>'
					. '<rect width="60" height="10" fill="#c6363c"/>'
					. '<rect y="10" width="60" height="10" fill="#0c4076"/>'
					. '</svg>';
			case 'ru':
				return '<svg class="lang-flag" viewBox="0 0 60 30" aria-hidden="true">'
					. '<rect width="60" height="30" fill="#ffffff"/>'
					. '<rect y="10" width="60" height="10" fill="#0039a6"/>'
					. '<rect y="20" width="60" height="10" fill="#d52b1e"/>'
					. '</svg>';
			case 'en':
			default:
				return '<svg class="lang-flag" viewBox="0 0 60 30" aria-hidden="true">'
					. '<rect width="60" height="30" fill="#012169"/>'
					. '<path d="M0,0 60,30 M60,0 0,30" stroke="#ffffff" stroke-width="6"/>'
					. '<path d="M0,0 60,30 M60,0 0,30" stroke="#c8102e" stroke-width="4"/>'
					. '<path d="M30,0 V30 M0,15 H60" stroke="#ffffff" stroke-width="10"/>'
					. '<path d="M30,0 V30 M0,15 H60" stroke="#c8102e" stroke-width="6"/>'
					. '</svg>';
		}
	}
}
