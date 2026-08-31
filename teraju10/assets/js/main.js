(function () {
	'use strict';

	/*
	 * Submenu dropdown di menu utama (mis. "Berita" > Daerah/Nasional/
	 * Internasional). Buka otomatis lewat :hover/:focus-within di CSS untuk
	 * mouse & keyboard; skrip ini hanya menambah tombol panah kecil supaya
	 * bisa dibuka/ditutup dengan TAP di layar sentuh, karena :hover tidak
	 * ada di perangkat sentuh.
	 */
	var navParents = document.querySelectorAll( '.main-nav .menu-item-has-children' );
	if ( navParents.length ) {
		navParents.forEach( function ( item ) {
			var link = item.querySelector( ':scope > a' );
			if ( ! link ) {
				return;
			}

			var toggle = document.createElement( 'button' );
			toggle.type = 'button';
			toggle.className = 'submenu-toggle';
			toggle.setAttribute( 'aria-expanded', 'false' );
			toggle.setAttribute( 'aria-label', 'Buka submenu ' + link.textContent.trim() );
			toggle.innerHTML = '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><path d="M6 9l6 6 6-6"></path></svg>';
			link.insertAdjacentElement( 'afterend', toggle );

			toggle.addEventListener( 'click', function ( e ) {
				e.stopPropagation();
				var willOpen = ! item.classList.contains( 'is-open' );
				closeAllSubmenus();
				if ( willOpen ) {
					item.classList.add( 'is-open' );
					toggle.setAttribute( 'aria-expanded', 'true' );
				}
			} );
		} );

		var closeAllSubmenus = function () {
			navParents.forEach( function ( item ) {
				item.classList.remove( 'is-open' );
				var t = item.querySelector( '.submenu-toggle' );
				if ( t ) {
					t.setAttribute( 'aria-expanded', 'false' );
				}
			} );
		};

		document.addEventListener( 'click', function ( e ) {
			if ( ! e.target.closest( '.menu-item-has-children' ) ) {
				closeAllSubmenus();
			}
		} );

		document.addEventListener( 'keydown', function ( e ) {
			if ( 'Escape' === e.key ) {
				closeAllSubmenus();
			}
		} );
	}

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
