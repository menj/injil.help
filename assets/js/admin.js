( function ( $ ) {
	'use strict';

	var tabs = document.querySelectorAll( '.ih-tabs a' );
	var panels = document.querySelectorAll( '.ih-panel' );
	if ( ! tabs.length ) {
		return;
	}
	document.querySelector( '.ih-options' ).classList.add( 'ih-js' );

	var referer = document.querySelector( 'input[name="_wp_http_referer"]' );

	function show( id ) {
		var found = false;
		tabs.forEach( function ( t ) {
			var on = t.getAttribute( 'href' ) === '#' + id;
			t.setAttribute( 'aria-selected', on ? 'true' : 'false' );
			found = found || on;
		} );
		panels.forEach( function ( p ) {
			p.hidden = p.id !== 'panel-' + id;
		} );
		// The fragment is not sent with the save request, so carry it in the referer WordPress redirects back to.
		if ( found && referer ) {
			referer.value = referer.value.replace( /#.*$/, '' ) + '#' + id;
		}
		return found;
	}

	function fromHash() {
		var id = window.location.hash.replace( '#', '' );
		if ( ! id || ! show( id ) ) {
			show( 'colours' );
		}
	}

	tabs.forEach( function ( t ) {
		t.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			history.replaceState( null, '', t.getAttribute( 'href' ) );
			show( t.getAttribute( 'href' ).slice( 1 ) );
		} );
	} );

	$( '.ih-color' ).wpColorPicker();
	fromHash();
}( jQuery ) );
