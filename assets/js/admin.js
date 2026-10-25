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

	$( '[data-confirm]' ).on( 'click', function ( e ) {
		if ( ! window.confirm( $( this ).data( 'confirm' ) ) ) {
			e.preventDefault();
			e.stopImmediatePropagation();
		}
	} );

	/*
	 * The Content tab's buttons: write the pages in short batches over admin-ajax, show the
	 * progress, then load the screen with the result at the top. Without this script, or if a
	 * request fails, the link itself still runs the whole job in one request.
	 */
	var cfg = window.injilHelpPopulate;
	var status = document.getElementById( 'ih-populate-status' );
	function say( text, kind ) {
		if ( ! status ) {
			return;
		}
		status.hidden = false;
		status.className = 'notice inline notice-' + ( kind || 'info' );
		status.querySelector( 'p' ).textContent = text;
		status.scrollIntoView( { block: 'nearest' } );
	}
	function format( template, a, b ) {
		return template.replace( '%1$d', a ).replace( '%2$d', b );
	}
	$( '[data-ih-populate]' ).on( 'click', function ( e ) {
		if ( ! cfg || ! window.fetch ) {
			return; // the link runs the job in one request
		}
		e.preventDefault();
		var buttons = $( '[data-ih-populate]' ).attr( 'aria-disabled', 'true' ).css( 'pointer-events', 'none' );
		var overwrite = 'update' === $( this ).data( 'ih-populate' ) ? '1' : '';
		var tally = {};
		function batch( offset ) {
			var body = new URLSearchParams();
			body.append( 'action', 'injil_help_populate_batch' );
			body.append( 'nonce', cfg.nonce );
			body.append( 'offset', offset );
			if ( overwrite ) {
				body.append( 'overwrite', overwrite );
			}
			Object.keys( tally ).forEach( function ( k ) {
				body.append( 'tally[' + k + ']', tally[ k ] );
			} );
			return window.fetch( cfg.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body } )
				.then( function ( r ) {
					return r.json();
				} )
				.then( function ( res ) {
					if ( ! res || ! res.success ) {
						throw new Error( 'failed' );
					}
					var d = res.data;
					if ( d.busy ) {
						say( cfg.busy, 'warning' );
						return new Promise( function ( resolve ) {
							window.setTimeout( resolve, 5000 );
						} ).then( function () {
							return batch( offset );
						} );
					}
					tally = d.tally;
					if ( -1 === d.next ) {
						window.location.href = d.redirect;
						return null;
					}
					say( format( cfg.working, d.next, d.total ) );
					return batch( d.next );
				} );
		}
		say( format( cfg.working, 0, '…' ) );
		batch( 0 ).catch( function () {
			say( cfg.failed, 'error' );
			buttons.removeAttr( 'aria-disabled' ).css( 'pointer-events', '' );
		} );
	} );

	$( '.ih-color' ).wpColorPicker();
	fromHash();
}( jQuery ) );
