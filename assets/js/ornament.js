( function () {
	'use strict';

	var root = document.documentElement;
	var targets = document.querySelectorAll( '.ih-entry, .ih-parts > *, .is-style-answer-item, .is-style-answer-capsule, .ih-plate' );
	if ( ! targets.length || ! ( 'IntersectionObserver' in window ) ) {
		return;
	}
	if ( window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
		return;
	}

	root.classList.add( 'ih-js' );

	var observer = new IntersectionObserver(
		function ( entries ) {
			entries.forEach( function ( entry ) {
				if ( entry.isIntersecting ) {
					entry.target.classList.add( 'is-in' );
					observer.unobserve( entry.target );
				}
			} );
		},
		{ rootMargin: '0px 0px -8% 0px', threshold: 0.05 }
	);

	targets.forEach( function ( el, i ) {
		el.classList.add( 'ih-reveal' );
		el.style.transitionDelay = Math.min( ( i % 6 ) * 60, 300 ) + 'ms';
		observer.observe( el );
	} );
}() );
