/**
 * Block Interactions — front-end Interactivity API store.
 *
 * Authored as a native ES module: WordPress resolves the `@wordpress/interactivity`
 * import via its script-module import map, so no bundling step is required.
 *
 * The store owns only the *trigger* logic. The animation itself is pure CSS
 * (see assets/animations.css); this code simply flips `context.visible` to true
 * once the element scrolls into view, which CSS reacts to via the `is-visible`
 * class bound through `data-wp-class--is-visible`.
 */

import { store, getContext, getElement } from '@wordpress/interactivity';

store( 'blockInteractions', {
	callbacks: {
		/**
		 * Observes the element and reveals it the first time it enters the viewport.
		 */
		observe() {
			const context = getContext();
			const { ref } = getElement();

			if ( ! ref || context.visible ) {
				return;
			}

			const observer = new IntersectionObserver(
				( entries ) => {
					entries.forEach( ( entry ) => {
						if ( entry.isIntersecting ) {
							context.visible = true;
							observer.unobserve( entry.target );
						}
					} );
				},
				{ threshold: 0.15, rootMargin: '0px 0px -10% 0px' }
			);

			observer.observe( ref );
		},
	},
} );
