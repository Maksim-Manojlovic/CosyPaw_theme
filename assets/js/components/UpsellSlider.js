/**
 * UpsellSlider
 *
 * Arrows for the motif strip in the cart / checkout offer (Theme\Upsell).
 *
 * The strip scrolls on its own — it is an overflowing list with scroll-snap,
 * so a trackpad, a finger and the keyboard all work with no script at all.
 * This adds what a mouse alone cannot do: a pair of buttons that page it.
 * They ship hidden and stay hidden until there is really something off-screen,
 * so a strip of three towels in a wide column never grows controls that scroll
 * nothing.
 */
export class UpsellSlider {
	/**
	 * @param {HTMLElement} root Element carrying [data-upsell-slider].
	 */
	constructor(root) {
		this.track = root.querySelector('[data-upsell-track]');
		this.prev = root.querySelector('[data-upsell-prev]');
		this.next = root.querySelector('[data-upsell-next]');

		if (!this.track || !this.prev || !this.next) return;

		this._sync = this._sync.bind(this);

		this.prev.addEventListener('click', () => this._page(-1));
		this.next.addEventListener('click', () => this._page(1));
		this.track.addEventListener('scroll', this._sync, { passive: true });

		// The tiles carry lazy images, so the track's width settles after the
		// first paint; a ResizeObserver catches that as well as the column
		// reflowing under the totals at the breakpoint.
		if (window.ResizeObserver) {
			this._observer = new ResizeObserver(this._sync);
			this._observer.observe(this.track);
		} else {
			window.addEventListener('resize', this._sync);
		}

		this._sync();
	}

	/**
	 * Scroll by most of a screenful, so one tile stays in view as the anchor.
	 *
	 * @param {number} direction -1 back, 1 forward.
	 */
	_page(direction) {
		const calm = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

		this.track.scrollBy({
			left: direction * this.track.clientWidth * 0.8,
			behavior: calm ? 'auto' : 'smooth',
		});
	}

	/**
	 * Show the arrows only while they have somewhere to go.
	 */
	_sync() {
		const max = this.track.scrollWidth - this.track.clientWidth;
		// Sub-pixel widths leave a pixel of scroll behind on a zoomed page,
		// which would light up an arrow that then moves nothing visible.
		const overflows = max > 2;
		const at = this.track.scrollLeft;

		this.prev.hidden = !overflows;
		this.next.hidden = !overflows;
		this.prev.disabled = at <= 2;
		this.next.disabled = at >= max - 2;
	}
}
