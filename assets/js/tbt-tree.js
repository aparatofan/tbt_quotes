(function () {
	'use strict';

	var sequenceDuration = 3100;
	var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

	function setupTree(tree) {
		var leaves = tree.querySelectorAll('.cls-5, .cls-6, .cls-7');
		var graphic = tree.querySelector('[data-tbt-tree-graphic]');
		var cleanupTimer = 0;
		var lastReplay = 0;

		leaves.forEach(function (leaf, index) {
			leaf.style.setProperty('--tbt-leaf-index', index);
		});

		function clearAnimationClasses() {
			tree.classList.remove('is-initial', 'is-replaying');
		}

		function scheduleCleanup() {
			window.clearTimeout(cleanupTimer);
			cleanupTimer = window.setTimeout(clearAnimationClasses, sequenceDuration);
		}

		function replay() {
			var now = Date.now();

			if (reducedMotion.matches || now - lastReplay < 150) {
				return;
			}

			lastReplay = now;
			window.clearTimeout(cleanupTimer);
			clearAnimationClasses();

			// Reading offsetWidth commits the class removal before the replay class
			// is restored, which reliably restarts CSS animations on every hover.
			void tree.offsetWidth;
			tree.classList.add('is-replaying');
			scheduleCleanup();
		}

		if (graphic) {
			graphic.addEventListener('pointerenter', replay);
		}

		tree.addEventListener('focusin', replay);
		scheduleCleanup();
	}

	function init() {
		document.querySelectorAll('[data-tbt-tree-logo]').forEach(setupTree);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
}());
