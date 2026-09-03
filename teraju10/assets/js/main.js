(function () {
	'use strict';

	/* Video asap Karhutla: hormati preferensi "reduce motion" pembaca —
	   video tetap tampil (masih menyampaikan pesan lewat gambar diam di
	   frame pertama), cuma tidak diputar/loop terus-menerus. */
	var smokeVideo = document.getElementById( 'karhutlaSmokeVideo' );
	if ( smokeVideo && window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
		smokeVideo.pause();
	}

	/*
	 * Submenu dropdown di menu utama (mis. "Berita" > Daerah/Nasional/
	 * Internasional). Menu tingkat atas memakai overflow-x:auto (geser
	 * horizontal di layar sempit), dan overflow-x:auto itu otomatis membuat
	 * overflow-y ikut "auto" juga — kalau dropdown-nya position:absolute,
	 * dia akan ikut terpotong. Makanya dropdown di sini memakai
	 * position:fixed (lolos dari clipping overflow leluhurnya) dengan
	 * posisi (top/left) dihitung di sini lewat getBoundingClientRect(),
	 * karena position:fixed tidak "nempel otomatis" di bawah induknya
	 * seperti position:absolute.
	 *
	 * Dibuka lewat hover & fokus keyboard di desktop, dan tap tombol panah
	 * di layar sentuh (karena :hover tidak ada di perangkat sentuh).
	 */
	var navParents = document.querySelectorAll( '.main-nav .menu-item-has-children' );
	if ( navParents.length ) {
		var closeAllSubmenus = function () {
			navParents.forEach( function ( item ) {
				item.classList.remove( 'is-open' );
				var t = item.querySelector( '.submenu-toggle' );
				if ( t ) {
					t.setAttribute( 'aria-expanded', 'false' );
				}
			} );
		};

		var openSubmenu = function ( item, link, submenu, toggle ) {
			closeAllSubmenus();

			submenu.style.visibility = 'hidden';
			item.classList.add( 'is-open' );

			var linkRect = link.getBoundingClientRect();
			var subWidth = submenu.offsetWidth;
			var left     = linkRect.left;
			var maxLeft  = window.innerWidth - subWidth - 8;
			if ( left > maxLeft ) {
				left = Math.max( 8, maxLeft );
			}

			submenu.style.top  = Math.round( linkRect.bottom ) + 'px';
			submenu.style.left = Math.round( left ) + 'px';
			submenu.style.visibility = '';

			if ( toggle ) {
				toggle.setAttribute( 'aria-expanded', 'true' );
			}
		};

		navParents.forEach( function ( item ) {
			var link = item.querySelector( ':scope > a' );
			var submenu = item.querySelector( ':scope > .sub-menu' );
			if ( ! link || ! submenu ) {
				return;
			}

			var toggle = document.createElement( 'button' );
			toggle.type = 'button';
			toggle.className = 'submenu-toggle';
			toggle.setAttribute( 'aria-expanded', 'false' );
			toggle.setAttribute( 'aria-label', 'Buka submenu ' + link.textContent.trim() );
			toggle.innerHTML = '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><path d="M6 9l6 6 6-6"></path></svg>';
			link.insertAdjacentElement( 'afterend', toggle );

			/* Mouse (desktop): hover induk membuka, keluar dari induk menutup —
			   submenu tetap DOM anak dari item, jadi hover di atasnya tidak
			   dianggap "keluar" walau posisinya fixed di layar. */
			item.addEventListener( 'mouseenter', function () {
				openSubmenu( item, link, submenu, toggle );
			} );
			item.addEventListener( 'mouseleave', closeAllSubmenus );

			/* Keyboard: fokus ke link/tombol di dalam item membuka submenu,
			   fokus keluar dari seluruh item (bukan cuma pindah antar anak
			   di dalamnya) menutupnya lagi. */
			item.addEventListener( 'focusin', function () {
				openSubmenu( item, link, submenu, toggle );
			} );
			item.addEventListener( 'focusout', function ( e ) {
				if ( ! item.contains( e.relatedTarget ) ) {
					closeAllSubmenus();
				}
			} );

			/* Sentuh: tap tombol panah untuk buka/tutup. */
			toggle.addEventListener( 'click', function ( e ) {
				e.stopPropagation();
				if ( item.classList.contains( 'is-open' ) ) {
					closeAllSubmenus();
				} else {
					openSubmenu( item, link, submenu, toggle );
				}
			} );
		} );

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

		window.addEventListener( 'resize', closeAllSubmenus );
		var navList = document.querySelector( '.main-nav > .wrap > ul' );
		if ( navList ) {
			navList.addEventListener( 'scroll', closeAllSubmenus, { passive: true } );
		}
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

	/* Lapor satu klik bagikan ke server (lihat inc/share-counter.php) — request
	   kecil terpisah lewat sendBeacon, sama seperti pola view-tracker.js, supaya
	   halaman artikel tetap ramah cache. Dipanggil untuk SEMUA jenis tombol
	   bagikan (termasuk "salin tautan", karena itu juga niat membagikan). */
	function trackShareClick() {
		var cfg = window.teraju10ShareTracker;
		if ( ! cfg || ! cfg.postId ) {
			return;
		}
		var body = new URLSearchParams();
		body.set( 'action', 'teraju10_track_share' );
		body.set( 'post_id', cfg.postId );
		body.set( 'nonce', cfg.nonce );

		if ( navigator.sendBeacon ) {
			navigator.sendBeacon( cfg.ajaxUrl, body );
		} else {
			fetch( cfg.ajaxUrl, { method: 'POST', body: body, credentials: 'omit', keepalive: true } )['catch']( function () {} );
		}
	}

	/* Tombol bagikan */
	var shareButtons = document.querySelectorAll( '[data-share]' );
	shareButtons.forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			var type  = btn.getAttribute( 'data-share' );
			var url   = window.location.href;
			var title = document.title;

			trackShareClick();

			if ( 'whatsapp' === type ) {
				window.open( 'https://wa.me/?text=' + encodeURIComponent( title + ' ' + url ), '_blank', 'noopener' );
				return;
			}

			if ( 'twitter' === type ) {
				window.open( 'https://twitter.com/intent/tweet?text=' + encodeURIComponent( title ) + '&url=' + encodeURIComponent( url ), '_blank', 'noopener' );
				return;
			}

			if ( 'copy' === type ) {
				var labelEl      = btn.querySelector( '.btn-label' );
				var restoreLabel = labelEl ? labelEl.textContent : btn.getAttribute( 'aria-label' );
				var finish       = function () {
					if ( labelEl ) {
						labelEl.textContent = 'Tersalin!';
						setTimeout( function () {
							labelEl.textContent = restoreLabel;
						}, 1800 );
					} else {
						btn.setAttribute( 'aria-label', 'Tautan tersalin' );
						setTimeout( function () {
							btn.setAttribute( 'aria-label', restoreLabel );
						}, 1800 );
					}
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

	/* Tombol bagikan di akhir artikel — muncul begitu pembaca sampai ujung */
	var endShare = document.getElementById( 'endShare' );
	if ( endShare && 'IntersectionObserver' in window ) {
		endShare.classList.add( 'end-share--armed' );
		var endShareObserver = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( entry ) {
				if ( entry.isIntersecting ) {
					endShare.classList.add( 'is-visible' );
					endShareObserver.unobserve( entry.target );
				}
			} );
		}, { threshold: 0.4 } );
		endShareObserver.observe( endShare );
	}
}() );
