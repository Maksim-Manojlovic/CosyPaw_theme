/**
 * GalleryThumbs — makes the product gallery's thumbnail strip usable by touch.
 *
 * WooCommerce's slider is built on the whole gallery element, thumbnails
 * included, and treats any horizontal swipe on it as a swipe of the main
 * picture — so on a phone, where the strip is a scrolling row, a swipe along
 * it moved the main picture instead of the strip and the thumbnails could not
 * be reached. Touches that start on the strip are kept from the slider, so the
 * strip scrolls; tapping a thumbnail still selects it, as that listens on the
 * thumbnail itself.
 *
 * And a row that scrolls has to follow the slider: when another slide becomes
 * the active one, its thumbnail is scrolled into view.
 *
 * The slider builds the strip after the page has loaded, so this waits for it.
 */
export class GalleryThumbs {
	/**
	 * @param {HTMLElement} gallery The .woocommerce-product-gallery element.
	 */
	constructor(gallery) {
		this.gallery = gallery;
		this.strip = null;

		if (this.attach()) return;

		// Not built yet: look again when the slider adds to the gallery.
		this.watcher = new MutationObserver(() => {
			if (this.attach()) this.watcher.disconnect();
		});
		this.watcher.observe(gallery, { childList: true, subtree: true });
	}

	/**
	 * @return {boolean} Whether the strip exists and is wired up.
	 */
	attach() {
		const strip = this.gallery.querySelector('.flex-control-thumbs');
		if (!strip) return false;

		this.strip = strip;

		['touchstart', 'touchmove', 'touchend'].forEach((type) => {
			strip.addEventListener(type, (event) => event.stopPropagation(), { passive: true });
		});

		new MutationObserver(() => this.reveal()).observe(strip, {
			attributes: true,
			attributeFilter: ['class'],
			subtree: true,
		});

		this.reveal();
		return true;
	}

	/**
	 * Scroll the active thumbnail to the middle of a strip that scrolls.
	 */
	reveal() {
		const strip = this.strip;
		if (!strip || strip.scrollWidth <= strip.clientWidth) return;

		const active = strip.querySelector('img.flex-active');
		const item = active ? active.closest('li') : null;
		if (!item) return;

		const target = item.offsetLeft - (strip.clientWidth - item.offsetWidth) / 2;
		strip.scrollTo({ left: Math.max(0, target), behavior: 'smooth' });
	}
}
