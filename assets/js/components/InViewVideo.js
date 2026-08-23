/**
 * InViewVideo
 *
 * Drives a silent looping clip that carries no `autoplay` attribute: playback
 * starts here, only while the clip is on screen and only where motion is
 * welcome. Everything else — a refused autoplay, no IntersectionObserver,
 * reduced motion — leaves the poster frame, which is the whole fallback.
 *
 * Pairs with a [data-video-toggle] button beside the video, which ships hidden
 * and is revealed here: without this script nothing ever moves, so there would
 * be nothing to pause. That button is the pause WCAG 2.2.2 asks of a loop this
 * long.
 *
 * Markup contract: see template-parts/gift-banner.php.
 */
export class InViewVideo {
	/**
	 * @param {HTMLVideoElement} video  The [data-inview-video] element.
	 * @param {object}           [l10n] { videoPause, videoPlay }.
	 */
	constructor(video, l10n = {}) {
		if (!(video instanceof HTMLElement)) {
			throw new TypeError('InViewVideo requires its video element.');
		}

		this.video = video;
		this.l10n = l10n;
		this.toggle = video.parentElement
			? video.parentElement.querySelector('[data-video-toggle]')
			: null;

		// A pause the visitor asked for has to stick, or scrolling away and back
		// would start the clip again over their objection. `ours` is the pause
		// that follows the clip off screen; theirs ends the arrangement.
		this.ours = false;
		this.auto = true;

		this._bind();
	}

	/**
	 * Whether this browser and this visitor will have the clip play at all.
	 * @returns {boolean}
	 */
	static wanted() {
		return (
			'IntersectionObserver' in window &&
			!window.matchMedia('(prefers-reduced-motion: reduce)').matches
		);
	}

	/** @private */
	_bind() {
		if (this.toggle) {
			this.toggle.hidden = false;
			this.toggle.addEventListener('click', () => {
				if (this.video.paused) {
					this.video.play().catch(() => {});
				} else {
					this.video.pause();
				}
			});
		}

		this.video.addEventListener('pause', () => {
			this._syncToggle();
			if (this.ours) {
				this.ours = false;
				return;
			}
			this.auto = false;
		});

		this.video.addEventListener('play', () => {
			this.auto = true;
			this._syncToggle();
		});

		this.observer = new IntersectionObserver(
			(entries) => {
				entries.forEach((entry) => {
					if (entry.isIntersecting) {
						if (this.auto) this.video.play().catch(() => {});
					} else if (!this.video.paused) {
						this.ours = true;
						this.video.pause();
					}
				});
			},
			{ threshold: 0.35 }
		);
		this.observer.observe(this.video);
	}

	/** @private */
	_syncToggle() {
		if (!this.toggle) return;

		const paused = this.video.paused;
		this.toggle.setAttribute('aria-pressed', paused ? 'true' : 'false');
		this.toggle.setAttribute(
			'aria-label',
			paused ? this.l10n.videoPlay || 'Pusti video' : this.l10n.videoPause || 'Pauziraj video'
		);
	}

	destroy() {
		if (this.observer) this.observer.disconnect();
	}
}

export default InViewVideo;
