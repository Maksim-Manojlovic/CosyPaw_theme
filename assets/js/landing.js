/**
 * CosyPaw landing entry (Vite).
 *
 * Loaded on the front page only. Pulls in landing styles and boots the
 * front-page-only components (hero carousel, package builder).
 *
 * The cart drawer is site-wide chrome and boots from app.js; this entry reads
 * the instance off `window.CosyPawCart` rather than importing the component, so
 * CartDrawer stays in a single bundle.
 */
import '../css/landing.css';
import { VerticalCarousel } from './components/VerticalCarousel.js';
import { BundleBuilder } from './components/BundleBuilder.js';

const boot = () => {
	const l10n = window.CosyPawL10n || {};

	// Hero carousels — keep the name pill in sync with the active slide.
	document.querySelectorAll('[data-vertical-carousel]').forEach((el) => {
		new VerticalCarousel(el);
		const label = el.querySelector('[data-carousel-label]');
		if (label) {
			// Slides are <img> since the responsive-image conversion, so the
			// motif name is the alt text, not a role="img" aria-label.
			const slides = el.querySelectorAll('.vertical-carousel__slide img');
			el.addEventListener('carousel:change', (e) => {
				const slide = slides[e.detail.index];
				if (slide) label.textContent = slide.getAttribute('alt') || '';
			});
		}
	});

	// Motif grid "show all" gate. The grid ships collapsed; the CSS decides
	// where the cut falls (nine cards, six on a phone) and this only flips the
	// attribute the cut hangs off.
	const motifGrid = document.querySelector('[data-motif-grid]');
	const motifToggle = document.querySelector('[data-motifs-toggle]');
	if (motifGrid && motifToggle) {
		const motifLabel =
			motifToggle.querySelector('[data-motifs-toggle-label]') || motifToggle;

		motifToggle.addEventListener('click', () => {
			const wasCollapsed = motifGrid.hasAttribute('data-collapsed');

			if (wasCollapsed) {
				motifGrid.removeAttribute('data-collapsed');
			} else {
				motifGrid.setAttribute('data-collapsed', '');
			}

			motifToggle.setAttribute('aria-expanded', String(wasCollapsed));
			motifLabel.textContent = wasCollapsed
				? motifToggle.dataset.labelLess
				: motifToggle.dataset.labelMore;

			// Collapsing removes fourteen cards from above the button, which
			// otherwise leaves the viewport parked in the packages section.
			if (!wasCollapsed) {
				motifToggle.scrollIntoView({ block: 'center' });
			}
		});
	}

	// The unboxing clip in the packages banner. It carries no audio and no
	// autoplay attribute: playback starts here, only while the clip is on
	// screen and only where motion is welcome. Everything else — a refused
	// autoplay, no IntersectionObserver, reduced motion — leaves the poster,
	// which is the still the video replaced.
	document.querySelectorAll('[data-inview-video]').forEach((video) => {
		if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
		if (!('IntersectionObserver' in window)) return;

		// A pause the visitor asked for has to stick, or scrolling away and back
		// would start the clip again over their objection. Ours is the pause
		// that follows the clip off screen; theirs ends the arrangement.
		let ours = false;
		let auto = true;

		// Nothing moves without this script, so the pause control only appears
		// once there is something for it to pause.
		const toggle = video.parentElement
			? video.parentElement.querySelector('[data-video-toggle]')
			: null;
		if (toggle) {
			toggle.hidden = false;
			toggle.addEventListener('click', () => {
				if (video.paused) {
					video.play().catch(() => {});
				} else {
					video.pause();
				}
			});
		}

		const syncToggle = () => {
			if (!toggle) return;
			toggle.setAttribute('aria-pressed', video.paused ? 'true' : 'false');
			toggle.setAttribute(
				'aria-label',
				video.paused
					? l10n.videoPlay || 'Pusti video'
					: l10n.videoPause || 'Pauziraj video'
			);
		};

		video.addEventListener('pause', () => {
			syncToggle();
			if (ours) {
				ours = false;
				return;
			}
			auto = false;
		});
		video.addEventListener('play', () => {
			auto = true;
			syncToggle();
		});

		new IntersectionObserver(
			(entries) => {
				entries.forEach((entry) => {
					if (entry.isIntersecting) {
						if (auto) video.play().catch(() => {});
					} else if (!video.paused) {
						ours = true;
						video.pause();
					}
				});
			},
			{ threshold: 0.35 }
		).observe(video);
	});

	// Cart drawer — booted site-wide by app.js.
	const cart = window.CosyPawCart || null;

	// Bundle builder ("Napravi svoj paket").
	const builderEl = document.querySelector('[data-bundle-builder]');
	const builder = builderEl ? new BundleBuilder(builderEl, { cart, l10n }) : null;

	// The motif grid's main action drops the towel into a builder slot instead
	// of buying a single one — the grid is far above the builder, so the
	// component handles the scroll and the confirmation.
	if (builder) {
		document.querySelectorAll('[data-add-to-bundle]').forEach((btn) => {
			btn.addEventListener('click', () => {
				builder.addMotifFromGallery(btn.dataset.motifId);
			});
		});
	}

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
