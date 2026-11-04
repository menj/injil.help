/**
 * injil.help: Theme Options screen.
 * Tabs (WAI-ARIA tablist: arrow keys, Home and End, no reload), live slider values,
 * colour pickers, the confirm prompts and the batched page writer of the Content tab.
 */
( function ( $ ) {
	'use strict';

	var root = document.querySelector( '.ih-options' );
	var tabs = root ? Array.prototype.slice.call( root.querySelectorAll( '[role="tab"]' ) ) : [];
	var panels = root ? Array.prototype.slice.call( root.querySelectorAll( '[role="tabpanel"]' ) ) : [];
	if ( ! tabs.length ) {
		return;
	}
	root.classList.add( 'ih-js' );

	var referer = root.querySelector( 'input[name="_wp_http_referer"]' );

	function idOf( tab ) {
		return tab.getAttribute( 'href' ).slice( 1 );
	}

	function show( id, focus ) {
		var found = null;
		tabs.forEach( function ( t ) {
			var on = idOf( t ) === id;
			t.setAttribute( 'aria-selected', on ? 'true' : 'false' );
			t.tabIndex = on ? 0 : -1;
			if ( on ) {
				found = t;
			}
		} );
		if ( ! found ) {
			return false;
		}
		panels.forEach( function ( p ) {
			p.hidden = p.id !== 'panel-' + id;
		} );
		// The fragment is not sent with the save request, so carry it in the referer WordPress redirects back to.
		if ( referer ) {
			referer.value = referer.value.replace( /#.*$/, '' ) + '#' + id;
		}
		if ( focus ) {
			found.focus();
		}
		found.scrollIntoView( { block: 'nearest', inline: 'nearest' } );
		loadLazy( document.getElementById( 'panel-' + id ) );
		return true;
	}

	/*
	 * Heavy panel parts (the starter page list) are left out of the markup
	 * unless the screen opens on their ?tab=. Fetch them the first time the
	 * tab is shown; the placeholder link opens that ?tab= if this fails.
	 */
	function loadLazy( panel ) {
		var box = panel ? panel.querySelector( '[data-ih-lazy]' ) : null;
		var conf = window.injilHelpPopulate;
		if ( ! box || box.getAttribute( 'data-ih-loading' ) || ! conf || ! conf.pagesNonce || ! window.fetch ) {
			return;
		}
		box.setAttribute( 'data-ih-loading', '1' );
		var body = new URLSearchParams();
		body.append( 'action', box.getAttribute( 'data-ih-lazy' ) );
		body.append( 'nonce', conf.pagesNonce );
		window.fetch( conf.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body } )
			.then( function ( r ) {
				return r.json();
			} )
			.then( function ( res ) {
				if ( ! res || ! res.success ) {
					throw new Error( 'failed' );
				}
				box.innerHTML = res.data.html;
				box.removeAttribute( 'data-ih-lazy' );
				box.removeAttribute( 'aria-busy' );
			} )
			.catch( function () {
				box.removeAttribute( 'data-ih-loading' );
				box.removeAttribute( 'aria-busy' );
			} );
	}

	function select( tab, focus ) {
		history.replaceState( null, '', '#' + idOf( tab ) );
		show( idOf( tab ), focus );
	}

	tabs.forEach( function ( t, i ) {
		t.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			select( t, false );
		} );
		t.addEventListener( 'keydown', function ( e ) {
			var next = null;
			switch ( e.key ) {
				case 'ArrowRight':
				case 'ArrowDown':
					next = tabs[ ( i + 1 ) % tabs.length ];
					break;
				case 'ArrowLeft':
				case 'ArrowUp':
					next = tabs[ ( i - 1 + tabs.length ) % tabs.length ];
					break;
				case 'Home':
					next = tabs[ 0 ];
					break;
				case 'End':
					next = tabs[ tabs.length - 1 ];
					break;
				case ' ':
				case 'Enter':
					e.preventDefault();
					select( t, true );
					return;
			}
			if ( next ) {
				e.preventDefault();
				select( next, true );
			}
		} );
	} );

	var hash = window.location.hash.replace( '#', '' );
	if ( ! hash && window.URLSearchParams ) {
		hash = new URLSearchParams( window.location.search ).get( 'tab' ) || '';
	}
	if ( ! hash || ! show( hash, false ) ) {
		show( idOf( tabs[ 0 ] ), false );
	}

	// Range sliders: the number beside each slider follows it.
	root.querySelectorAll( '.ih-range__input' ).forEach( function ( input ) {
		var out = document.getElementById( input.id + '-value' );
		if ( out ) {
			input.addEventListener( 'input', function () {
				out.value = input.value;
			} );
		}
	} );

	// Delegated, so the buttons fetched with the starter page list work too.
	$( root ).on( 'click', '[data-confirm]', function ( e ) {
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
	$( root ).on( 'click', '[data-ih-populate]', function ( e ) {
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
}( jQuery ) );
