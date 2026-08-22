<?php
/**
 * ShopStrings — WooCommerce's Serbian, in the script and the words the shop uses.
 *
 * Two problems, one place.
 *
 * The first cost money. WooCommerce's sr_RS catalogue translates both
 * "Subtotal" and "Total" as "Укупно", so the cart totals table printed the same
 * word twice: "Укупно 2.970" on the subtotal row, the package saving under it,
 * then "Укупно 1.980". A customer reading the first big number sees the price
 * before the discount and concludes the discount did not apply — which is
 * exactly the report that led here. The subtotal row is renamed so the two rows
 * cannot be mistaken for each other.
 *
 * The second is script. WordPress ships Serbian as Cyrillic only, so every
 * WooCommerce string — the cart table, the checkout, the buttons — rendered in
 * a different alphabet from the rest of the site, which is Latin throughout.
 * Rather than transcribe the plugin by hand, the Cyrillic output is
 * transliterated: the mapping is exact and total, so it covers strings nobody
 * remembered to list, including ones a WooCommerce update adds later.
 *
 * Serbian only. An English or Russian visitor gets WooCommerce untouched.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

namespace Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ShopStrings.
 */
final class ShopStrings {

	/**
	 * Serbian Cyrillic to Latin, exactly.
	 *
	 * Digraphs are title-case (Lj, Nj, Dž) rather than upper (LJ, NJ, DŽ),
	 * which is right for a capitalised word and wrong inside an all-caps one.
	 * WooCommerce has no all-caps Serbian strings; CSS does the shouting where
	 * the design wants it.
	 *
	 * @var array<string,string>
	 */
	private const TO_LATIN = array(
		'А' => 'A',  'Б' => 'B',  'В' => 'V',  'Г' => 'G',  'Д' => 'D',
		'Ђ' => 'Đ',  'Е' => 'E',  'Ж' => 'Ž',  'З' => 'Z',  'И' => 'I',
		'Ј' => 'J',  'К' => 'K',  'Л' => 'L',  'Љ' => 'Lj', 'М' => 'M',
		'Н' => 'N',  'Њ' => 'Nj', 'О' => 'O',  'П' => 'P',  'Р' => 'R',
		'С' => 'S',  'Т' => 'T',  'Ћ' => 'Ć',  'У' => 'U',  'Ф' => 'F',
		'Х' => 'H',  'Ц' => 'C',  'Ч' => 'Č',  'Џ' => 'Dž', 'Ш' => 'Š',
		'а' => 'a',  'б' => 'b',  'в' => 'v',  'г' => 'g',  'д' => 'd',
		'ђ' => 'đ',  'е' => 'e',  'ж' => 'ž',  'з' => 'z',  'и' => 'i',
		'ј' => 'j',  'к' => 'k',  'л' => 'l',  'љ' => 'lj', 'м' => 'm',
		'н' => 'n',  'њ' => 'nj', 'о' => 'o',  'п' => 'p',  'р' => 'r',
		'с' => 's',  'т' => 't',  'ћ' => 'ć',  'у' => 'u',  'ф' => 'f',
		'х' => 'h',  'ц' => 'c',  'ч' => 'č',  'џ' => 'dž', 'ш' => 'š',
	);

	/**
	 * Wordings that transliteration alone cannot fix, keyed by English source.
	 *
	 * Only for strings where WooCommerce's Serbian is ambiguous or wrong for
	 * this shop — not a place to restyle the plugin's voice. Everything absent
	 * from this list is simply transliterated.
	 *
	 * @var array<string,string>
	 */
	private const OVERRIDES = array(
		// The whole reason this class exists: sr_RS renders this as "Укупно",
		// the same word it gives "Total", so the cart showed two rows called
		// "Ukupno" with different numbers and the first one was the price
		// before the package saving.
		'Subtotal'   => 'Međuzbir',
		'Cart totals' => 'Zbir korpe',
	);

	/**
	 * Whether this request is being served in Serbian. Null until resolved.
	 *
	 * @var bool|null
	 */
	private ?bool $serbian = null;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'gettext', array( $this, 'filter_gettext' ), 20, 3 );
		add_filter( 'gettext_with_context', array( $this, 'filter_gettext_with_context' ), 20, 4 );
		add_filter( 'ngettext', array( $this, 'filter_ngettext' ), 20, 5 );
		add_filter( 'ngettext_with_context', array( $this, 'filter_ngettext_with_context' ), 20, 6 );

		add_filter( 'woocommerce_currency_symbol', array( $this, 'filter_currency_symbol' ), 10, 2 );
	}

	/**
	 * Singular strings.
	 *
	 * @param string $translation Translated text.
	 * @param string $text        Original English text.
	 * @param string $domain      Text domain.
	 * @return string
	 */
	public function filter_gettext( $translation, $text, $domain ): string {
		return $this->resolve( (string) $translation, (string) $text, (string) $domain );
	}

	/**
	 * Singular strings carrying a context.
	 *
	 * @param string $translation Translated text.
	 * @param string $text        Original English text.
	 * @param string $context     Gettext context.
	 * @param string $domain      Text domain.
	 * @return string
	 */
	public function filter_gettext_with_context( $translation, $text, $context, $domain ): string {
		return $this->resolve( (string) $translation, (string) $text, (string) $domain );
	}

	/**
	 * Plural strings.
	 *
	 * @param string $translation Translated text.
	 * @param string $single      Singular source.
	 * @param string $plural      Plural source.
	 * @param int    $number      Count that selected the form.
	 * @param string $domain      Text domain.
	 * @return string
	 */
	public function filter_ngettext( $translation, $single, $plural, $number, $domain ): string {
		return $this->resolve( (string) $translation, (string) $single, (string) $domain );
	}

	/**
	 * Plural strings carrying a context.
	 *
	 * @param string $translation Translated text.
	 * @param string $single      Singular source.
	 * @param string $plural      Plural source.
	 * @param int    $number      Count that selected the form.
	 * @param string $context     Gettext context.
	 * @param string $domain      Text domain.
	 * @return string
	 */
	public function filter_ngettext_with_context( $translation, $single, $plural, $number, $context, $domain ): string {
		return $this->resolve( (string) $translation, (string) $single, (string) $domain );
	}

	/**
	 * Apply the override, or transliterate.
	 *
	 * @param string $translation What WooCommerce would have printed.
	 * @param string $source      The English msgid behind it.
	 * @param string $domain      Text domain.
	 * @return string
	 */
	private function resolve( string $translation, string $source, string $domain ): string {
		if ( 0 !== strncmp( $domain, 'woocommerce', 11 ) || ! $this->is_serbian() ) {
			return $translation;
		}

		if ( isset( self::OVERRIDES[ $source ] ) ) {
			return self::OVERRIDES[ $source ];
		}

		return self::to_latin( $translation );
	}

	/**
	 * Transliterate Serbian Cyrillic to Latin.
	 *
	 * Gated on a raw byte check first. This runs on every translated string on
	 * every page of the shop, and most of a checkout's strings are already
	 * Latin (prices, product names, the theme's own copy passing through), so
	 * the common case must not pay for a 60-entry strtr. Every Serbian Cyrillic
	 * letter is U+0400-U+045F, which in UTF-8 is a 0xD0 or 0xD1 lead byte and
	 * nothing else the shop prints starts a character that way.
	 *
	 * @param string $text Text that may contain Cyrillic.
	 * @return string
	 */
	public static function to_latin( string $text ): string {
		if ( ! preg_match( '/[\xD0\xD1]/', $text ) ) {
			return $text;
		}

		return strtr( $text, self::TO_LATIN );
	}

	/**
	 * Print prices as the rest of the site does.
	 *
	 * WooCommerce renders RSD as "рсд" — Cyrillic, lower case, and not what any
	 * other price on the site says. Catalog::format_price() has always written
	 * "RSD", and a shop that quotes one currency two ways in the same session
	 * looks careless at the moment it is asking to be trusted with money.
	 *
	 * @param string $symbol   Current symbol.
	 * @param string $currency Currency code.
	 * @return string
	 */
	public function filter_currency_symbol( $symbol, $currency ): string {
		return 'RSD' === $currency ? 'RSD' : (string) $symbol;
	}

	/**
	 * Whether the request is being served in Serbian.
	 *
	 * Resolved once: determine_locale() runs the `locale` filter, and this is
	 * consulted on every translated string in the request.
	 *
	 * @return bool
	 */
	private function is_serbian(): bool {
		if ( null === $this->serbian ) {
			$this->serbian = 0 === strncmp( determine_locale(), 'sr', 2 );
		}

		return $this->serbian;
	}
}
