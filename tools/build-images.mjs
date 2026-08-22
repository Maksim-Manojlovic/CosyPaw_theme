/**
 * Regenerate the theme's derived image sizes from their originals.
 *
 * Three families, each with its own rules:
 *
 *   motifs     assets/motifs/<id>.avif (1086x1448) is the source of truth and is
 *              never rewritten — ProductSeeder sideloads that exact file into
 *              the media library. Derives -lg (900w) and -md (600w).
 *
 *   lifestyle  assets/lifestyle<n>.avif, same two derived widths. These are
 *              shown at 547 CSS px at most, so the original is a candidate of
 *              last resort rather than the default.
 *
 *   logo       assets/logo-512.png is the master. The three AVIF cuts are the
 *              1x/2x/3x of a 76px badge; 4:4:4 chroma because it is line art
 *              with lettering, where 4:2:0 smears the edges for a few hundred
 *              bytes of savings.
 *
 *   squares    the cuts taken from `<id>-sm.avif` rather than the original:
 *              `-th` (192px, the benefit cards) and `-xs` (96px, the hero's
 *              falling sprites on a phone). Both want a square, and the square
 *              is the one a person chose — deriving them from the portrait
 *              original would reframe every one of them.
 *
 * `<id>-sm.avif` itself (the 360x360 bundle-builder thumbnail) is deliberately
 * never regenerated: it is that hand-picked 1:1 crop, not a plain downscale,
 * and it never reaches the critical path. The `squares` family only reads it.
 *
 * It is its own `--only` family because it is cheap — 20 small sources — while
 * `motifs` re-encodes twenty 1086x1448 originals at effort 9 and takes minutes.
 * Adding a square size should not cost a full motif rebuild.
 *
 * The photo quality numbers are tuned for terry-cloth texture viewed at ~350
 * CSS px. The -lg cut is always downscaled at least 2.4x on screen, so it
 * tolerates a lower quality than -md, which can be shown near 1:1.
 *
 * Usage: node tools/build-images.mjs [--only=motifs,squares,logo,lifestyle] [--dry]
 */

import { readdirSync, statSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';
import sharp from 'sharp';

const MOTIF_DIR = 'assets/motifs';
const ASSET_DIR = 'assets';

const PHOTO_VARIANTS = [
	{ suffix: '-lg', width: 900, quality: 36 },
	{ suffix: '-md', width: 600, quality: 42 },
];

// Cut from `-sm`, never from the original. `-th` is shown at 76 CSS px on the
// benefit cards, where it is looked *at*, so it carries the quality of a real
// thumbnail; `-xs` is looked *past* at 34-58 px behind the hero copy and only
// has to hold its shape.
const SQUARE_VARIANTS = [
	{ suffix: '-th', width: 192, quality: 50 },
	{ suffix: '-xs', width: 96, quality: 44 },
];

const args = process.argv.slice( 2 );
const dry = args.includes( '--dry' );
const only = ( args.find( ( a ) => a.startsWith( '--only=' ) ) || '' ).slice( 7 )
	.split( ',' )
	.filter( Boolean );

const wanted = ( family ) => ! only.length || only.includes( family );
const kb = ( n ) => ( n / 1024 ).toFixed( 1 ).padStart( 6 ) + ' KB';

let before = 0;
let after = 0;

/**
 * Encode one derived file and report how it compares to what it replaced.
 *
 * @param {string} src  Source image path.
 * @param {string} dest Destination path.
 * @param {object} opts `width`, `quality`, and optional `chroma`.
 */
async function derive( src, dest, { width, quality, chroma = '4:2:0' } ) {
	const prev = statSync( dest, { throwIfNoEntry: false } )?.size ?? 0;

	const buf = await sharp( src )
		.resize( { width } )
		.avif( { quality, effort: 9, chromaSubsampling: chroma } )
		.toBuffer();

	before += prev;
	after += buf.length;

	if ( ! dry ) {
		writeFileSync( dest, buf );
	}

	const delta = prev
		? ` (${ prev > buf.length ? '-' : '+' }${ Math.abs( Math.round( ( 1 - buf.length / prev ) * 100 ) ) }%)`
		: ' (new)';
	console.log( `${ dest.padEnd( 34 ) } ${ kb( buf.length ) }${ delta }` );
}

if ( wanted( 'motifs' ) ) {
	const ids = readdirSync( MOTIF_DIR )
		.filter( ( f ) => f.endsWith( '.avif' ) && ! /-(lg|md|sm|th|xs)\.avif$/.test( f ) )
		.map( ( f ) => f.replace( /\.avif$/, '' ) )
		.sort();

	for ( const id of ids ) {
		for ( const v of PHOTO_VARIANTS ) {
			await derive( join( MOTIF_DIR, `${ id }.avif` ), join( MOTIF_DIR, `${ id }${ v.suffix }.avif` ), v );
		}
	}
}

if ( wanted( 'squares' ) ) {
	const ids = readdirSync( MOTIF_DIR )
		.filter( ( f ) => f.endsWith( '-sm.avif' ) )
		.map( ( f ) => f.replace( /-sm\.avif$/, '' ) )
		.sort();

	// A motif with no hand-cropped square simply gets no square cuts. The page
	// falls back to another motif rather than reframing a portrait original,
	// which would read as a different picture.
	for ( const id of ids ) {
		for ( const v of SQUARE_VARIANTS ) {
			await derive( join( MOTIF_DIR, `${ id }-sm.avif` ), join( MOTIF_DIR, `${ id }${ v.suffix }.avif` ), v );
		}
	}
}

if ( wanted( 'lifestyle' ) ) {
	const shots = readdirSync( ASSET_DIR )
		.filter( ( f ) => /^lifestyle\d+\.avif$/.test( f ) )
		.map( ( f ) => f.replace( /\.avif$/, '' ) )
		.sort();

	for ( const id of shots ) {
		for ( const v of PHOTO_VARIANTS ) {
			await derive( join( ASSET_DIR, `${ id }.avif` ), join( ASSET_DIR, `${ id }${ v.suffix }.avif` ), v );
		}
	}
}

if ( wanted( 'logo' ) ) {
	for ( const width of [ 76, 152, 228 ] ) {
		await derive( join( ASSET_DIR, 'logo-512.png' ), join( ASSET_DIR, `logo-${ width }.avif` ), {
			width,
			quality: 50,
			chroma: '4:4:4',
		} );
	}
}

// `before` counts only variants that already existed, so the delta is honest on
// a re-run and simply reads as new bytes the first time a size is introduced.
console.log(
	`\nreplaced ${ kb( before ) } with ${ kb( after ) }` +
	( before ? ` (${ Math.round( ( after / before - 1 ) * 100 ) }%)` : '' ) +
	( dry ? '  [dry run, nothing written]' : '' )
);
