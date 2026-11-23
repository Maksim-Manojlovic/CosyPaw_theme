/**
 * CosyPaw global entry (Vite).
 *
 * Loaded on every view. Carries the global stylesheet plus the chrome that
 * renders site-wide: the collapsing header nav and the cart drawer.
 *
 * The cart drawer and toast markup live in footer.php on every page, so their
 * behaviour belongs here — booting them from the landing entry left the header
 * cart button inert everywhere except the front page. The instance is published
 * on `window.CosyPawCart` so the landing entry can reuse it without importing
 * (and therefore re-bundling) the component.
 */
import '../css/main.css';
import { SiteNav } from './components/SiteNav.js';
import { Marquee } from './components/Marquee.js';
import { CartDrawer } from './components/CartDrawer.js';
import { InViewVideo } from './components/InViewVideo.js';
import { CartUpsell } from './components/CartUpsell.js';
import { CartQuantity } from './components/CartQuantity.js';
import { BundlePicker } from './components/BundlePicker.js';
import { MotifColours } from './components/MotifColours.js';
import { ColourSwatches } from './components/ColourSwatches.js';
import { GalleryThumbs } from './components/GalleryThumbs.js';
import { GiftPopup } from './components/GiftPopup.js';
import { MiniCart } from './components/MiniCart.js';

/**
 * Publish the sticky header's height as --header-h, which main.css turns into
 * scroll-padding-top. Without a number there, every anchor jump parks its
 * target underneath the header.
 */
const syncHeaderOffset = (header) => {
	if (!header) return;
	// The admin bar is fixed above the header and takes it with it.
	let offset = header.offsetHeight;
	if (document.body.classList.contains('admin-bar')) {
		offset += window.matchMedia('(max-width: 782px)').matches ? 46 : 32;
	}
	document.documentElement.style.setProperty('--header-h', Math.round(offset) + 'px');
};

const boot = () => {
	const l10n = window.CosyPawL10n || {};

	const header = document.querySelector('[data-site-nav]');
	if (header) new SiteNav(header);

	syncHeaderOffset(header);
	window.addEventListener('resize', () => syncHeaderOffset(header));

	const marquee = document.querySelector('[data-marquee]');
	if (marquee) new Marquee(marquee, l10n);

	// The gift banner's unboxing clip. It is on the landing and under every
	// product now, so it boots from here rather than from the landing entry.
	// A product's own clip, in its gallery, boots here too — and does so even
	// where nothing may start by itself, because it is the only way to play
	// a clip the visitor has just asked to see.
	const autoplay = InViewVideo.wanted();
	document.querySelectorAll('[data-inview-video]').forEach((video) => {
		if (autoplay || video.hasAttribute('data-video-manual')) {
			new InViewVideo(video, l10n);
		}
	});

	// The cart / checkout package offer: its motif strip, and the re-render
	// that keeps it in step with WooCommerce's AJAX cart. Those two views load
	// no entry of their own beyond this one, so it boots from here.
	document.querySelectorAll('[data-upsell-panel]').forEach((slot) => {
		new CartUpsell(slot);
	});

	// The cart's quantity fields, which post themselves now that the update
	// button is hidden.
	const cartForm = document.querySelector('.woocommerce-cart-form');
	if (cartForm) new CartQuantity(cartForm);

	// "Složi svoj paket" on a gift bundle's page. Product pages load no entry
	// of their own beyond this one, so it boots from here.
	document.querySelectorAll('[data-bundle-picker]').forEach((picker) => {
		new BundlePicker(picker);
	});

	// The colour dots: on a motif card (the penguin), and on a variable
	// product's page in place of WooCommerce's dropdown.
	document.querySelectorAll('[data-motif-colours]').forEach((card) => {
		new MotifColours(card);
	});
	document.querySelectorAll('[data-colour-swatches]').forEach((swatches) => {
		new ColourSwatches(swatches);
	});

	// A product gallery's thumbnail strip, which has to scroll by touch.
	document.querySelectorAll('.woocommerce-product-gallery').forEach((gallery) => {
		new GalleryThumbs(gallery);
	});

	// Crni petak: the gift picker, printed in the footer while it runs.
	const giftPopup = document.querySelector('[data-gift-popup]');
	if (giftPopup) new GiftPopup(giftPopup);

	// The cart as a panel from the right. Published so the landing entry can
	// tell it is there and leave the confirmation to it.
	const miniCart = document.querySelector('[data-mini-cart]');
	if (miniCart) window.CosyPawMiniCart = new MiniCart(miniCart);

	if (document.querySelector('[data-cart-drawer]')) {
		window.CosyPawCart = new CartDrawer(document, l10n);
	}
};

if (document.readyState !== 'loading') {
	boot();
} else {
	document.addEventListener('DOMContentLoaded', boot);
}
