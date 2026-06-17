/**
 * VerticalCarousel
 *
 * Accessible, performance-minded vertical carousel.
 *
 * Markup contract:
 *   <div class="vertical-carousel" data-vertical-carousel
 *        data-autoplay-delay="4000" data-transition-duration="600">
 *     <div class="vertical-carousel__track">
 *       <div class="vertical-carousel__slide">...</div>
 *       <div class="vertical-carousel__slide">...</div>
 *     </div>
 *   </div>
 *
 * Performance: IntersectionObserver pauses autoplay off-screen; requestAnimationFrame
 * applies transform: translateY() to avoid layout shifts. Honors prefers-reduced-motion.
 *
 * Accessibility: the root container is a single focus target (tabindex="0",
 * role="group", aria-live="polite"). ArrowUp/ArrowDown move prev/next while focused.
 *
 * Public API: next(), prev(), goTo(index), destroy().
 */
export class VerticalCarousel {
	/**
	 * @param {HTMLElement} element Root carousel element.
	 * @param {{autoplayDelay?: number, transitionDuration?: number}} [options]
	 */
	constructor(element, options = {}) {
		if (!(element instanceof HTMLElement)) {
			throw new TypeError('VerticalCarousel requires a root HTMLElement.');
		}

		this.root = element;
		this.track = element.querySelector('.vertical-carousel__track');
		this.slides = this.track
			? Array.from(this.track.querySelectorAll('.vertical-carousel__slide'))
			: [];

		// Options: explicit options object wins over data-* attributes wins over defaults.
		const data = element.dataset;
		this.autoplayDelay =
			options.autoplayDelay ?? this._toInt(data.autoplayDelay, 4000);
		this.transitionDuration =
			options.transitionDuration ?? this._toInt(data.transitionDuration, 600);

		this.index = 0;
		this.timerId = null;
		this.rafId = null;
		this.isVisible = false;

		this.reducedMotion =
			window.matchMedia &&
			window.matchMedia('(prefers-reduced-motion: reduce)').matches;

		// Bound handlers (kept as fields so destroy() can remove them).
		this._onKeydown = this._onKeydown.bind(this);
		this._onIntersect = this._onIntersect.bind(this);

		// Snapshot original inline state to restore on destroy().
		this._original = {
			tabindex: this.root.getAttribute('tabindex'),
			role: this.root.getAttribute('role'),
			ariaLive: this.root.getAttribute('aria-live'),
			trackTransform: this.track ? this.track.style.transform : '',
			trackTransition: this.track ? this.track.style.transition : '',
		};

		this._init();
	}

	/** @private */
	_init() {
		if (this.slides.length === 0) {
			return;
		}

		// A11y: one focus target for the whole component.
		this.root.setAttribute('tabindex', '0');
		this.root.setAttribute('role', 'group');
		this.root.setAttribute('aria-live', 'polite');
		this.root.addEventListener('keydown', this._onKeydown);

		this._applyTransition();
		this._render(false);

		// Pause autoplay when off-screen.
		if ('IntersectionObserver' in window) {
			this.observer = new IntersectionObserver(this._onIntersect, {
				threshold: 0.5,
			});
			this.observer.observe(this.root);
		} else {
			// No observer support: assume visible.
			this.isVisible = true;
			this._startAutoplay();
		}
	}

	/** @private */
	_toInt(value, fallback) {
		const n = parseInt(value, 10);
		return Number.isFinite(n) ? n : fallback;
	}

	/** @private */
	_applyTransition() {
		if (!this.track) return;
		this.track.style.transition = this.reducedMotion
			? 'none'
			: `transform ${this.transitionDuration}ms ease`;
	}

	/**
	 * Render the current slide via translateY.
	 * @private
	 * @param {boolean} animate
	 */
	_render(animate = true) {
		if (!this.track) return;

		if (this.rafId !== null) {
			cancelAnimationFrame(this.rafId);
		}

		const offset = this.index * 100;
		this.rafId = requestAnimationFrame(() => {
			if (!animate || this.reducedMotion) {
				const prev = this.track.style.transition;
				this.track.style.transition = 'none';
				this.track.style.transform = `translateY(-${offset}%)`;
				// Force reflow so the next transition re-applies cleanly.
				void this.track.offsetHeight;
				this.track.style.transition = prev;
			} else {
				this.track.style.transform = `translateY(-${offset}%)`;
			}
		});
	}

	/** @private */
	_onIntersect(entries) {
		for (const entry of entries) {
			this.isVisible = entry.isIntersecting;
			if (this.isVisible) {
				this._startAutoplay();
			} else {
				this._stopAutoplay();
			}
		}
	}

	/** @private */
	_startAutoplay() {
		this._stopAutoplay();
		if (this.reducedMotion || this.autoplayDelay <= 0 || this.slides.length < 2) {
			return;
		}
		this.timerId = window.setInterval(() => this.next(), this.autoplayDelay);
	}

	/** @private */
	_stopAutoplay() {
		if (this.timerId !== null) {
			clearInterval(this.timerId);
			this.timerId = null;
		}
	}

	/** @private */
	_onKeydown(event) {
		if (event.key === 'ArrowDown') {
			event.preventDefault();
			this.next();
		} else if (event.key === 'ArrowUp') {
			event.preventDefault();
			this.prev();
		}
	}

	/* ---------- Public API ---------- */

	/** Advance to the next slide (wraps). */
	next() {
		this.goTo((this.index + 1) % this.slides.length);
	}

	/** Go to the previous slide (wraps). */
	prev() {
		this.goTo((this.index - 1 + this.slides.length) % this.slides.length);
	}

	/**
	 * Jump to a specific slide.
	 * @param {number} index
	 */
	goTo(index) {
		if (this.slides.length === 0) return;
		const clamped = ((index % this.slides.length) + this.slides.length) % this.slides.length;
		this.index = clamped;
		this._render(true);

		// Notify listeners (e.g. the hero name pill) of the active slide.
		this.root.dispatchEvent(
			new CustomEvent('carousel:change', { detail: { index: this.index } })
		);

		// Reset autoplay timer after manual navigation.
		if (this.isVisible) {
			this._startAutoplay();
		}
	}

	/** Tear down: remove listeners/observers/timers and restore the DOM. */
	destroy() {
		this._stopAutoplay();

		if (this.rafId !== null) {
			cancelAnimationFrame(this.rafId);
			this.rafId = null;
		}

		if (this.observer) {
			this.observer.disconnect();
			this.observer = null;
		}

		this.root.removeEventListener('keydown', this._onKeydown);

		// Restore original attributes.
		this._restoreAttr('tabindex', this._original.tabindex);
		this._restoreAttr('role', this._original.role);
		this._restoreAttr('aria-live', this._original.ariaLive);

		if (this.track) {
			this.track.style.transform = this._original.trackTransform;
			this.track.style.transition = this._original.trackTransition;
		}
	}

	/** @private */
	_restoreAttr(name, value) {
		if (value === null) {
			this.root.removeAttribute(name);
		} else {
			this.root.setAttribute(name, value);
		}
	}
}

export default VerticalCarousel;
