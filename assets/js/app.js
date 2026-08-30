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
	if (InViewVideo.wanted()) {
		document.querySelectorAll('[data-inview-video]').forEach((video) => {
			new InViewVideo(video, l10n);
		});
	}

	// The cart / checkout package offer: its motif strip, and the re-render
	// that keeps it in step with WooCommerce's AJAX cart. Those two views load
	// no entry of their own beyond this one, so it boots from here.
	document.querySelectorAll('[data-upsell-panel]').forEach((slot) => {
		new CartUpsell(slot);
	});

	if (document.querySelector('[data-cart-drawer]')) {
		window.CosyPawCart = new CartDrawer(document, l10n);
	}
};

if (document.readyState !== 'loading') {
	boot();
} else {
	document.addEventListener('DOMContentLoaded', boot);
}
