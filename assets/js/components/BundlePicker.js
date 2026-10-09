/**
 * BundlePicker — keeps each row of "Složi svoj paket" in step with its pick.
 *
 * The picker is plain radio buttons inside WooCommerce's add-to-cart form
 * (template-parts/bundle-picker.php), so the pick posts without this. What
 * script adds is the row itself: the name and photo beside each part follow
 * the radio, and a towel shape tapped in the open panel folds the panel away
 * so the row shows what was chosen.
 */
export class BundlePicker {
	/**
	 * @param {HTMLElement} root The [data-bundle-picker] element.
	 */
	constructor(root) {
		this.root = root;
		root.addEventListener('change', (event) => this.onChange(event));
		root.addEventListener('click', (event) => this.onClick(event));
	}

	/**
	 * A radio changed: show its name and photo in its row.
	 *
	 * @param {Event} event
	 */
	onChange(event) {
		const input = event.target;
		if (!(input instanceof HTMLInputElement) || input.type !== 'radio') return;

		const slot = input.closest('[data-bundle-slot]');
		if (!slot) return;

		const name = slot.querySelector('[data-bundle-name]');
		if (name && input.dataset.name) name.textContent = input.dataset.name;

		const thumb = slot.querySelector('[data-bundle-thumb]');
		if (thumb && input.dataset.image) thumb.src = input.dataset.image;
	}

	/**
	 * A tile tapped or clicked closes its panel. Only a pointer click: arrow
	 * keys move through the radios with clicks of detail 0, and closing the
	 * panel under a keyboard user would hide what they are choosing from.
	 *
	 * @param {MouseEvent} event
	 */
	onClick(event) {
		if (event.detail < 1 || !(event.target instanceof Element)) return;

		const tile = event.target.closest('.bundle-tile');
		const panel = tile ? tile.closest('details') : null;
		if (!panel) return;

		// After the label has passed the click on to its radio.
		requestAnimationFrame(() => {
			panel.open = false;

			const summary = panel.querySelector('summary');
			if (!summary) return;
			summary.focus({ preventScroll: true });
			if (summary.getBoundingClientRect().top < 0) {
				summary.scrollIntoView({ block: 'nearest' });
			}
		});
	}
}
