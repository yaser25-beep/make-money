(function () {
	'use strict';

	/* Progress bar baca */
	var progressBar = document.getElementById( 'progressBar' );
	if ( progressBar ) {
		window.addEventListener( 'scroll', function () {
			var doc      = document.documentElement;
			var scrolled = doc.scrollHeight > doc.clientHeight
				? ( doc.scrollTop / ( doc.scrollHeight - doc.clientHeight ) ) * 100
				: 0;
			progressBar.style.width = scrolled + '%';
		}, { passive: true } );
	}

	/* Tombol simpan (bookmark visual) */
	var saveBtn = document.getElementById( 'saveBtn' );
	if ( saveBtn ) {
		saveBtn.addEventListener( 'click', function () {
			saveBtn.classList.toggle( 'is-saved' );
			var icon = saveBtn.querySelector( 'svg' );
			if ( icon ) {
				icon.setAttribute( 'fill', saveBtn.classList.contains( 'is-saved' ) ? 'currentColor' : 'none' );
			}
		} );
	}

	/* Tombol bagikan */
	var shareButtons = document.querySelectorAll( '[data-share]' );
	shareButtons.forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			var type  = btn.getAttribute( 'data-share' );
			var url   = window.location.href;
			var title = document.title;

			if ( 'whatsapp' === type ) {
				window.open( 'https://wa.me/?text=' + encodeURIComponent( title + ' ' + url ), '_blank', 'noopener' );
				return;
			}

			if ( 'copy' === type ) {
				var restoreLabel = btn.getAttribute( 'aria-label' );
				var finish       = function () {
					btn.setAttribute( 'aria-label', 'Tautan tersalin' );
					setTimeout( function () {
						btn.setAttribute( 'aria-label', restoreLabel );
					}, 1800 );
				};

				if ( navigator.clipboard && navigator.clipboard.writeText ) {
					navigator.clipboard.writeText( url ).then( finish )['catch']( function () {
						window.prompt( 'Salin tautan ini:', url );
					} );
				} else {
					window.prompt( 'Salin tautan ini:', url );
				}
			}
		} );
	} );
}() );
