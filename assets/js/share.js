( function () {
	'use strict';

	var rows = document.querySelectorAll( '.ih-share' );
	if ( ! rows.length ) {
		return;
	}

	rows.forEach( function ( row ) {
		var copy = row.querySelector( '.ih-share-copy' );
		var native = row.querySelector( '.ih-share-native' );

		if ( copy && navigator.clipboard && window.isSecureContext ) {
			copy.hidden = false;
			var button = copy.querySelector( 'button' );
			var name = button.querySelector( '.ih-share-name' );
			var original = name.textContent;
			var restore = function () {
				window.setTimeout( function () {
					copy.classList.remove( 'is-done', 'is-failed' );
					name.textContent = original;
				}, 1800 );
			};
			button.addEventListener( 'click', function () {
				navigator.clipboard.writeText( button.getAttribute( 'data-copy' ) ).then(
					function () {
						copy.classList.add( 'is-done' );
						name.textContent = button.getAttribute( 'data-done' );
						restore();
					},
					function () {
						// Permission refused or blocked by policy: say so, and offer the address for a manual copy.
						copy.classList.add( 'is-failed' );
						name.textContent = button.getAttribute( 'data-fail' );
						window.prompt( button.getAttribute( 'data-fail' ), button.getAttribute( 'data-copy' ) );
						restore();
					}
				);
			} );
		}

		if ( native && navigator.share ) {
			native.hidden = false;
			var share = native.querySelector( 'button' );
			share.addEventListener( 'click', function () {
				navigator.share( {
					title: share.getAttribute( 'data-share-title' ),
					url: share.getAttribute( 'data-share-url' )
				} ).catch( function () {} );
			} );
		}
	} );
}() );
