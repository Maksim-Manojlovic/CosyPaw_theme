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
import { CartDrawer } from './components/CartDrawer.js';

const boot = () => {
	const l10n = window.CosyPawL10n || {};

	const header = document.querySelector('[data-site-nav]');
	if (header) new SiteNav(header);

	if (document.querySelector('[data-cart-drawer]')) {
		window.CosyPawCart = new CartDrawer(document, l10n);
	}
};

if (document.readyState !== 'loading') {
	boot();
} else {
	document.addEventListener('DOMContentLoaded', boot);
}
