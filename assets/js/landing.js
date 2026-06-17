/**
 * CosyPaw landing entry (Vite).
 *
 * Loaded on the front page only. Pulls in landing styles and boots the
 * interactive components (hero carousel, cart drawer, package selector).
 */
import '../css/landing.css';
import { VerticalCarousel } from './components/VerticalCarousel.js';
import { CartDrawer } from './components/CartDrawer.js';
import { BundleBuilder } from './components/BundleBuilder.js';

const boot = () => {
	const l10n = window.CosyPawL10n || {};

	// Hero carousels — keep the name pill in sync with the active slide.
	document.querySelectorAll('[data-vertical-carousel]').forEach((el) => {
		new VerticalCarousel(el);
		const label = el.querySelector('[data-carousel-label]');
		if (label) {
			const slides = el.querySelectorAll('.vertical-carousel__slide [role="img"]');
			el.addEventListener('carousel:change', (e) => {
				const slide = slides[e.detail.index];
				if (slide) label.textContent = slide.getAttribute('aria-label') || '';
			});
		}
	});

	// Cart drawer (one per document).
	let cart = null;
	if (document.querySelector('[data-cart-drawer]')) {
		cart = new CartDrawer(document, l10n);
	}

	// Bundle builder ("Napravi svoj paket").
	const builder = document.querySelector('[data-bundle-builder]');
	if (builder) new BundleBuilder(builder, { cart, l10n });

	// WooCommerce AJAX add-to-cart feedback. WC fires `added_to_cart` (jQuery)
	// after a successful ajax add; we hide WC's default "View cart" link, so
	// supply our own confirmation: a toast + a brief button state change.
	const $ = window.jQuery;
	if ($) {
		$(document.body).on('added_to_cart', (e, fragments, cartHash, $button) => {
			if (cart) cart.toast(l10n.addedToCart || 'Dodato u korpu');

			const btn = $button && $button[0];
			if (btn && !btn.dataset.cpwBusy) {
				btn.dataset.cpwBusy = '1';
				const original = btn.innerHTML;
				btn.classList.add('is-added');
				btn.innerHTML =
					'<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>' +
					(l10n.addedShort || 'Dodato');
				setTimeout(() => {
					btn.innerHTML = original;
					btn.classList.remove('is-added');
					delete btn.dataset.cpwBusy;
				}, 1600);
			}
		});
	}
};

if (document.readyState !== 'loading') {
	boot();
} else {
	document.addEventListener('DOMContentLoaded', boot);
}
