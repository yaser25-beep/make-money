/**
 * Lapor satu "tayangan" artikel ke server lewat request kecil terpisah
 * (bukan lewat render halaman) — supaya halaman artikelnya sendiri tetap
 * bisa di-cache utuh oleh plugin cache apa pun. Satu artikel cuma dihitung
 * sekali per 30 menit per pembaca (dicek di localStorage), supaya reload
 * berkali-kali tidak menggelembungkan angka "populer minggu ini".
 */
(function () {
	'use strict';

	var cfg = window.teraju10ViewTracker;
	if ( ! cfg || ! cfg.postId ) {
		return;
	}

	var STORAGE_KEY   = 'teraju10-viewed-' + cfg.postId;
	var COOLDOWN_MS   = 30 * 60 * 1000;

	try {
		var last = window.localStorage.getItem( STORAGE_KEY );
		if ( last && ( Date.now() - parseInt( last, 10 ) ) < COOLDOWN_MS ) {
			return;
		}
	} catch ( e ) {
		/* localStorage tidak tersedia (mode privat, dsb) — lanjut kirim saja
		   tanpa de-dup, lebih baik dihitung dobel sesekali daripada tidak
		   pernah dihitung. */
	}

	var body = new URLSearchParams();
	body.set( 'action', 'teraju10_track_view' );
	body.set( 'post_id', cfg.postId );
	body.set( 'nonce', cfg.nonce );

	var sent = false;
	if ( navigator.sendBeacon ) {
		sent = navigator.sendBeacon( cfg.ajaxUrl, body );
	}
	if ( ! sent ) {
		fetch( cfg.ajaxUrl, { method: 'POST', body: body, credentials: 'omit', keepalive: true } )['catch']( function () {} );
	}

	try {
		window.localStorage.setItem( STORAGE_KEY, String( Date.now() ) );
	} catch ( e ) {
		/* Abaikan diam-diam kalau localStorage tidak bisa ditulis. */
	}
}() );
