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
 * role="group"). ArrowUp/ArrowDown move prev/next while focused.
 *
 * Autoplay carries a pause control ([data-carousel-toggle]) because WCAG 2.2.2
 * requires one for motion that starts on its own and runs past five seconds.
 * Autoplay also stops while the pointer is over the carousel or focus is inside
 * it, so a user reading a slide is not moved off it.
 *
 * The root is deliberately NOT a live region. It used to carry
 * aria-live="polite", and the hero name pill it contains is rewritten on every
 * slide change, so screen readers announced a motif name every few seconds for
 * as long as the hero stayed on screen. The pill is aria-hidden and the label is
 * announced only for user-driven navigation, via a separate polite region.
 *
 * Public API: next(), prev(), goTo(index), pause(), play(), destroy().
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
		// Autoplay is suppressed while any of these hold. Kept separate so
		// resuming one (pointer leaves) cannot override another (user paused).
		this.userPaused = false;
		this.hovered = false;
		this.focusWithin = false;

		this.toggle = element.querySelector('[data-carousel-toggle]');
		this.status = element.querySelector('[data-carousel-status]');

		this.reducedMotion =
			window.matchMedia &&
			window.matchMedia('(prefers-reduced-motion: reduce)').matches;

		// Bound handlers (kept as fields so destroy() can remove them).
		this._onKeydown = this._onKeydown.bind(this);
		this._onIntersect = this._onIntersect.bind(this);
		this._onToggle = this._onToggle.bind(this);
		this._onPointerEnter = this._onPointerEnter.bind(this);
		this._onPointerLeave = this._onPointerLeave.bind(this);
		this._onFocusIn = this._onFocusIn.bind(this);
		this._onFocusOut = this._onFocusOut.bind(this);

		// Snapshot original inline state to restore on destroy().
		this._original = {
			tabindex: this.root.getAttribute('tabindex'),
			role: this.root.getAttribute('role'),
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

		// A11y: one focus target for the whole component. No aria-live here —
		// see the note at the top of the file.
		this.root.setAttribute('tabindex', '0');
		this.root.setAttribute('role', 'group');
		this.root.addEventListener('keydown', this._onKeydown);

		// Autoplay yields to anyone reading a slide.
		this.root.addEventListener('pointerenter', this._onPointerEnter);
		this.root.addEventListener('pointerleave', this._onPointerLeave);
		this.root.addEventListener('focusin', this._onFocusIn);
		this.root.addEventListener('focusout', this._onFocusOut);

		if (this.toggle) {
			this.toggle.hidden = false;
			this.toggle.addEventListener('click', this._onToggle);
			this._syncToggle();
		}

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
		// Every suppressor is checked here, so no single resume path can
		// restart autoplay while another still holds it.
		if (this.userPaused || this.hovered || this.focusWithin || !this.isVisible) {
			return;
		}
		this.timerId = window.setInterval(() => this.next(), this.autoplayDelay);
	}

	/** @private */
	_syncToggle() {
		if (!this.toggle) return;
		this.toggle.setAttribute('aria-pressed', this.userPaused ? 'true' : 'false');
		const label = this.userPaused
			? this.toggle.dataset.labelPlay
			: this.toggle.dataset.labelPause;
		if (label) this.toggle.setAttribute('aria-label', label);
	}

	/** @private */
	_onToggle(e) {
		e.preventDefault();
		e.stopPropagation();
		if (this.userPaused) {
			this.play();
		} else {
			this.pause();
		}
	}

	/** @private */
	_onPointerEnter() {
		this.hovered = true;
		this._stopAutoplay();
	}

	/** @private */
	_onPointerLeave() {
		this.hovered = false;
		this._startAutoplay();
	}

	/** @private */
	_onFocusIn() {
		this.focusWithin = true;
		this._stopAutoplay();
	}

	/** @private */
	_onFocusOut(e) {
		if (this.root.contains(e.relatedTarget)) return;
		this.focusWithin = false;
		this._startAutoplay();
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
			this.next(true);
		} else if (event.key === 'ArrowUp') {
			event.preventDefault();
			this.prev(true);
		}
	}

	/* ---------- Public API ---------- */

	/**
	 * Advance to the next slide (wraps).
	 * @param {boolean} [announce=false] Announce the new slide to screen
	 *        readers. Only true for user-driven moves — announcing autoplay
	 *        would talk over the user every few seconds.
	 */
	next(announce = false) {
		this.goTo((this.index + 1) % this.slides.length, announce);
	}

	/**
	 * Go to the previous slide (wraps).
	 * @param {boolean} [announce=false]
	 */
	prev(announce = false) {
		this.goTo((this.index - 1 + this.slides.length) % this.slides.length, announce);
	}

	/** Stop autoplay until the user resumes it. */
	pause() {
		this.userPaused = true;
		this._stopAutoplay();
		this._syncToggle();
	}

	/** Resume autoplay (subject to hover/focus/visibility). */
	play() {
		this.userPaused = false;
		this._startAutoplay();
		this._syncToggle();
	}

	/**
	 * Jump to a specific slide.
	 * @param {number} index
	 * @param {boolean} [announce=false]
	 */
	goTo(index, announce = false) {
		if (this.slides.length === 0) return;
		const clamped = ((index % this.slides.length) + this.slides.length) % this.slides.length;
		this.index = clamped;
		this._render(true);

		// Notify listeners (e.g. the hero name pill) of the active slide.
		this.root.dispatchEvent(
			new CustomEvent('carousel:change', { detail: { index: this.index, announce } })
		);

		if (announce && this.status) {
			const slide = this.slides[this.index].querySelector('[aria-label]');
			this.status.textContent = slide ? slide.getAttribute('aria-label') : '';
		}

		// Reset the autoplay interval so a manual move gets a full dwell.
		this._startAutoplay();
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
		this.root.removeEventListener('pointerenter', this._onPointerEnter);
		this.root.removeEventListener('pointerleave', this._onPointerLeave);
		this.root.removeEventListener('focusin', this._onFocusIn);
		this.root.removeEventListener('focusout', this._onFocusOut);
		if (this.toggle) {
			this.toggle.removeEventListener('click', this._onToggle);
			this.toggle.hidden = true;
		}

		// Restore original attributes.
		this._restoreAttr('tabindex', this._original.tabindex);
		this._restoreAttr('role', this._original.role);

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
