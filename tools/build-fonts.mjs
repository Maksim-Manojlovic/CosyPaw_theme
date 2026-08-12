/**
 * Vendor the two brand webfonts out of Google Fonts and into the theme.
 *
 * The hosted stylesheet was costing an extra render-blocking request plus a
 * second TLS handshake to fonts.gstatic.com before a single glyph could paint.
 * Both families are variable fonts, so Google serves ONE woff2 per subset that
 * covers the whole weight axis — vendoring is four files, not twenty.
 *
 * Subsets are deliberate:
 *   latin, latin-ext  — Serbian Latin (č ć š ž đ) and English
 *   cyrillic*         — Nunito only, for the Russian locale. Baloo 2 has no
 *                       Cyrillic cut at all, so RU headings fall back to the
 *                       system stack both before and after this change.
 * devanagari and vietnamese are dropped; nothing on the site renders them.
 *
 * The unicode-range on each @font-face is what keeps a Serbian visitor from
 * downloading the Cyrillic cut, so it is copied verbatim from Google's CSS.
 *
 * Usage: node tools/build-fonts.mjs
 */

import { mkdirSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';
import { openSync as openFont } from 'fontkit';

const OUT_FONTS = 'assets/fonts';
const OUT_CSS = 'assets/css/fonts.css';

// A modern UA is required or Google hands back the legacy TTF stylesheet.
const UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36';

const FAMILIES = [
	{
		family: 'Baloo 2',
		slug: 'baloo2',
		query: 'Baloo+2:wght@400..800',
		weights: '400 800',
		subsets: [ 'latin', 'latin-ext' ],
	},
	{
		family: 'Nunito',
		slug: 'nunito',
		query: 'Nunito:wght@400..800',
		weights: '400 800',
		subsets: [ 'latin', 'latin-ext', 'cyrillic', 'cyrillic-ext' ],
	},
];

// Arial is the metric reference for the fallback faces below. It is the one
// face present on effectively every desktop, and Android's Roboto is close
// enough to it (0.5% on ascent) that a single adjusted face covers both.
const FALLBACK_SRC = 'local("Arial"), local("Helvetica"), local("Roboto")';
const FALLBACK_REF = 'C:/Windows/Fonts/arial.ttf';

// Weighted toward what the site actually renders: Serbian Latin, mixed case.
const SAMPLE = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ čćšžđ .,!?';

/**
 * Vertical metrics plus mean advance width, all normalised to em units.
 *
 * @param {string} path Font file path.
 * @return {{ascent:number,descent:number,lineGap:number,avgWidth:number}}
 */
function metrics( path ) {
	const font = openFont( path );

	return {
		ascent: font.ascent / font.unitsPerEm,
		descent: Math.abs( font.descent ) / font.unitsPerEm,
		lineGap: font.lineGap / font.unitsPerEm,
		avgWidth: font.layout( SAMPLE ).advanceWidth / SAMPLE.length / font.unitsPerEm,
	};
}

/**
 * Split Google's stylesheet into { subset, url, unicodeRange } records.
 *
 * Google emits one @font-face per requested weight, each preceded by a
 * `/* subset *\/` comment. For a variable font every one of those blocks points
 * at the same woff2, so the map collapses to one entry per subset.
 */
function parse( css ) {
	const out = new Map();
	const re = /\/\*\s*([a-z-]+)\s*\*\/\s*@font-face\s*\{([^}]*)\}/g;
	let m;

	while ( ( m = re.exec( css ) ) !== null ) {
		const [ , subset, body ] = m;
		const url = body.match( /url\((https:[^)]+\.woff2)\)/ )?.[ 1 ];
		const range = body.match( /unicode-range:\s*([^;]+);/ )?.[ 1 ];
		if ( url && range && ! out.has( subset ) ) {
			out.set( subset, { subset, url, unicodeRange: range.trim() } );
		}
	}

	return out;
}

mkdirSync( OUT_FONTS, { recursive: true } );

const blocks = [];
let total = 0;

for ( const f of FAMILIES ) {
	const res = await fetch(
		`https://fonts.googleapis.com/css2?family=${ f.query }&display=swap`,
		{ headers: { 'User-Agent': UA } }
	);
	if ( ! res.ok ) {
		throw new Error( `Google Fonts returned ${ res.status } for ${ f.family }` );
	}

	const faces = parse( await res.text() );

	for ( const subset of f.subsets ) {
		const face = faces.get( subset );
		if ( ! face ) {
			throw new Error( `${ f.family } has no "${ subset }" subset` );
		}

		const name = `${ f.slug }-${ subset }.woff2`;
		const buf = Buffer.from( await ( await fetch( face.url ) ).arrayBuffer() );
		writeFileSync( join( OUT_FONTS, name ), buf );
		total += buf.length;

		console.log( `${ name.padEnd( 26 ) } ${ ( buf.length / 1024 ).toFixed( 1 ).padStart( 6 ) } KB` );

		blocks.push(
			`/* ${ f.family } — ${ subset } */\n` +
			`@font-face {\n` +
			`\tfont-family: "${ f.family }";\n` +
			`\tfont-style: normal;\n` +
			`\tfont-weight: ${ f.weights };\n` +
			`\tfont-display: swap;\n` +
			`\tsrc: url("../fonts/${ name }") format("woff2");\n` +
			`\tunicode-range: ${ face.unicodeRange };\n` +
			`}`
		);
	}

	// Metric-matched fallback. `font-display: swap` means text paints in the
	// fallback first and reflows when the real face lands, and these two
	// families are far taller than Arial per em — Nunito's line box is 136% of
	// the font size against Arial's 115%, Baloo 2's is 160%. Every paragraph
	// and heading therefore grew on swap, which is a layout shift on a page
	// made almost entirely of text. Overriding the fallback's metrics to match
	// makes the swap invisible: same line box before and after.
	const ref = metrics( FALLBACK_REF );
	const own = metrics( join( OUT_FONTS, `${ f.slug }-latin.woff2` ) );
	const sizeAdjust = own.avgWidth / ref.avgWidth;
	const pct = ( n ) => ( n * 100 ).toFixed( 2 ) + '%';

	blocks.push(
		`/* ${ f.family } — metric-matched fallback, prevents reflow on swap */\n` +
		`@font-face {\n` +
		`\tfont-family: "${ f.family } Fallback";\n` +
		`\tsrc: ${ FALLBACK_SRC };\n` +
		`\tsize-adjust: ${ pct( sizeAdjust ) };\n` +
		`\tascent-override: ${ pct( own.ascent / sizeAdjust ) };\n` +
		`\tdescent-override: ${ pct( own.descent / sizeAdjust ) };\n` +
		`\tline-gap-override: ${ pct( own.lineGap / sizeAdjust ) };\n` +
		`}`
	);

	console.log( `${ ( f.family + ' Fallback' ).padEnd( 26 ) }   size-adjust ${ pct( sizeAdjust ) }` );
}

writeFileSync(
	OUT_CSS,
	`/*\n * Generated by tools/build-fonts.mjs — do not edit by hand.\n *\n` +
	` * Self-hosted Baloo 2 + Nunito (SIL Open Font License 1.1), each followed\n` +
	` * by a metric-matched fallback face so the swap does not move the layout.\n` +
	` * Imported first from main.css so everything is declared before use; the\n` +
	` * "<family> Fallback" names have to stay in the font stacks in main.css.\n */\n\n` +
	blocks.join( '\n\n' ) + '\n'
);

console.log( `\n${ blocks.length } faces — ${ ( total / 1024 ).toFixed( 1 ) } KB total → ${ OUT_CSS }` );
