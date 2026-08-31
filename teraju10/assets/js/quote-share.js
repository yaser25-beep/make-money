/**
 * "Kutip & bagikan" — sorot teks di badan artikel untuk membagikannya
 * lengkap dengan tautan yang langsung menyorot kalimat itu di halaman
 * (Scroll-To-Text-Fragment, dukungan Chrome/Edge/Android sejak 2020;
 * di browser lain tautan tetap jalan, hanya tanpa efek sorot otomatis).
 * Dipakai media besar (NYT, Guardian, Medium) tapi jarang ada di tema WP
 * lokal. Hanya aktif di halaman artikel, tanpa dependensi apa pun.
 */
(function () {
	'use strict';

	var article = document.querySelector( '.article-body' );
	if ( ! article || ! window.getSelection ) {
		return;
	}

	var cfg      = window.teraju10QuoteShare || {};
	var labels   = cfg.labels || {};
	var toolbar  = null;
	var quote    = '';
	var hideTimer;

	function buildToolbar() {
		toolbar = document.createElement( 'div' );
		toolbar.className = 'quote-share-bar';
		toolbar.setAttribute( 'role', 'toolbar' );
		toolbar.hidden = true;

		toolbar.innerHTML =
			'<button type="button" data-action="whatsapp" aria-label="' + ( labels.whatsapp || 'Bagikan ke WhatsApp' ) + '" title="' + ( labels.whatsapp || 'Bagikan ke WhatsApp' ) + '">' +
				'<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 11.5a8.5 8.5 0 0 1-12.36 7.56L4 20l1.06-4.48A8.5 8.5 0 1 1 21 11.5z"></path></svg>' +
			'</button>' +
			'<button type="button" data-action="twitter" aria-label="' + ( labels.twitter || 'Bagikan ke X' ) + '" title="' + ( labels.twitter || 'Bagikan ke X' ) + '">X</button>' +
			'<button type="button" data-action="copy" aria-label="' + ( labels.copy || 'Salin kutipan & tautan' ) + '" title="' + ( labels.copy || 'Salin kutipan & tautan' ) + '">' +
				'<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M10 14a5 5 0 0 0 7 0l3-3a5 5 0 0 0-7-7l-1.5 1.5"></path><path d="M14 10a5 5 0 0 0-7 0l-3 3a5 5 0 0 0 7 7l1.5-1.5"></path></svg>' +
			'</button>';

		document.body.appendChild( toolbar );

		/* Cegah klik tombol menghapus seleksi teks sebelum action dibaca. */
		toolbar.addEventListener( 'mousedown', function ( e ) {
			e.preventDefault();
		} );

		toolbar.addEventListener( 'click', function ( e ) {
			var btn = e.target.closest ? e.target.closest( '[data-action]' ) : null;
			if ( btn ) {
				handleAction( btn.getAttribute( 'data-action' ), btn );
			}
		} );
	}

	function deepLink() {
		var base    = window.location.origin + window.location.pathname;
		var snippet = quote.length > 200 ? quote.slice( 0, 200 ) : quote;
		var frag    = encodeURIComponent( snippet ).replace( /-/g, '%2D' );
		return base + '#:~:text=' + frag;
	}

	function handleAction( action, btn ) {
		var url = deepLink();

		if ( 'whatsapp' === action ) {
			window.open( 'https://wa.me/?text=' + encodeURIComponent( '"' + quote + '"\n\n' + url ), '_blank', 'noopener' );
		} else if ( 'twitter' === action ) {
			window.open( 'https://twitter.com/intent/tweet?text=' + encodeURIComponent( '"' + quote + '"' ) + '&url=' + encodeURIComponent( url ), '_blank', 'noopener' );
		} else if ( 'copy' === action ) {
			var text     = '"' + quote + '" — ' + url;
			var original = btn.getAttribute( 'aria-label' );
			var finish   = function () {
				btn.setAttribute( 'aria-label', labels.copied || 'Tersalin!' );
				setTimeout( function () {
					btn.setAttribute( 'aria-label', original );
				}, 1600 );
			};

			if ( navigator.clipboard && navigator.clipboard.writeText ) {
				navigator.clipboard.writeText( text ).then( finish )['catch']( function () {
					window.prompt( 'Salin teks ini:', text );
				} );
			} else {
				window.prompt( 'Salin teks ini:', text );
			}
		}

		hideToolbar();
	}

	function hideToolbar() {
		if ( toolbar ) {
			toolbar.hidden = true;
		}
	}

	function showToolbarForSelection() {
		var selection = window.getSelection();
		var text      = selection && selection.rangeCount ? selection.toString().trim() : '';

		if ( ! text || text.length < 8 || text.length > 280 ) {
			hideToolbar();
			return;
		}

		var range = selection.getRangeAt( 0 );
		if ( ! article.contains( range.commonAncestorContainer ) ) {
			hideToolbar();
			return;
		}

		quote = text;

		if ( ! toolbar ) {
			buildToolbar();
		}

		var rect = range.getBoundingClientRect();
		toolbar.hidden = false;
		toolbar.style.top  = ( window.scrollY + rect.top - toolbar.offsetHeight - 10 ) + 'px';
		toolbar.style.left = ( window.scrollX + rect.left + ( rect.width / 2 ) - ( toolbar.offsetWidth / 2 ) ) + 'px';
	}

	document.addEventListener( 'selectionchange', function () {
		clearTimeout( hideTimer );
		hideTimer = setTimeout( showToolbarForSelection, 200 );
	} );

	document.addEventListener( 'mousedown', function ( e ) {
		if ( toolbar && ! toolbar.contains( e.target ) ) {
			hideToolbar();
		}
	} );

	document.addEventListener( 'keydown', function ( e ) {
		if ( 'Escape' === e.key ) {
			hideToolbar();
		}
	} );
}() );
