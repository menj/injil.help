( function () {
	'use strict';

	var root = document.documentElement;
	var body = document.body;
	var reduce = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	if ( reduce ) {
		return;
	}

	/* Reveal: cards and answers rise into view. CSS in ornament.css under .ih-js .ih-reveal. */
	var targets = document.querySelectorAll( '.ih-entry, .ih-parts > *, .is-style-answer-item, .is-style-answer-capsule, .ih-plate' );
	if ( targets.length && 'IntersectionObserver' in window && body.classList.contains( 'ih-orn' ) ) {
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
	}

	/*
	 * Parallax: each layered element gets --ih-p, its progress through the viewport
	 * (-1 when its centre is at the bottom edge, 0 at the middle, 1 at the top edge),
	 * and the root gets --ih-sy, the scroll position in pixels. ornament.css turns
	 * these into transforms at different rates per layer; the depth multiplier
	 * --ih-px comes from body.ih-px-strong / .ih-px-gentle.
	 */
	if ( ! body.classList.contains( 'ih-px-strong' ) && ! body.classList.contains( 'ih-px-gentle' ) ) {
		return;
	}
	var layers = Array.prototype.slice.call( document.querySelectorAll( '.ih-cover, .ih-section-head, .ih-plate, .ih-entries, .ih-answers-band, .ih-footer, .ih-dilemma' ) );
	if ( ! layers.length ) {
		return;
	}
	root.classList.add( 'ih-px' );
	var ticking = false;

	function update() {
		ticking = false;
		var vh = window.innerHeight || root.clientHeight;
		var half = vh / 2;
		root.style.setProperty( '--ih-sy', window.pageYOffset || root.scrollTop || 0 );
		layers.forEach( function ( el ) {
			var r = el.getBoundingClientRect();
			if ( r.bottom < -vh || r.top > vh * 2 ) {
				return;
			}
			var p = ( half - ( r.top + r.height / 2 ) ) / ( half + r.height / 2 );
			p = Math.max( -1, Math.min( 1, p ) );
			el.style.setProperty( '--ih-p', p.toFixed( 3 ) );
		} );
	}
	function request() {
		if ( ! ticking ) {
			ticking = true;
			window.requestAnimationFrame( update );
		}
	}
	window.addEventListener( 'scroll', request, { passive: true } );
	window.addEventListener( 'resize', request );
	update();
}() );
