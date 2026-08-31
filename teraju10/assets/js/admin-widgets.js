/* global jQuery, wp */
( function ( $ ) {
	'use strict';

	function bindAdWidgetButtons( context ) {
		$( context )
			.find( '.teraju10-ad-upload' )
			.off( 'click.teraju10' )
			.on( 'click.teraju10', function ( e ) {
				e.preventDefault();

				var button = $( this );
				var widget = button.closest( '.widget-content, .control-section' );
				var input  = widget.find( '.teraju10-ad-image-id' );
				var preview = widget.find( '.teraju10-ad-preview' );

				if ( ! window.wp || ! wp.media ) {
					return;
				}

				var frame = wp.media( {
					title: 'Pilih gambar promosi',
					button: { text: 'Gunakan gambar ini' },
					multiple: false,
				} );

				frame.on( 'select', function () {
					var attachment = frame.state().get( 'selection' ).first().toJSON();
					input.val( attachment.id ).trigger( 'change' );
					preview.attr( 'src', attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url ).show();
				} );

				frame.open();
			} );

		$( context )
			.find( '.teraju10-ad-remove' )
			.off( 'click.teraju10' )
			.on( 'click.teraju10', function ( e ) {
				e.preventDefault();
				var widget = $( this ).closest( '.widget-content, .control-section' );
				widget.find( '.teraju10-ad-image-id' ).val( '' ).trigger( 'change' );
				widget.find( '.teraju10-ad-preview' ).hide().attr( 'src', '' );
			} );
	}

	$( document ).on( 'widget-added widget-updated', function ( event, widget ) {
		bindAdWidgetButtons( widget );
	} );

	$( function () {
		bindAdWidgetButtons( document );

		if ( window.wp && wp.customize ) {
			wp.customize.bind( 'ready', function () {
				bindAdWidgetButtons( document );
			} );
		}
	} );
}( jQuery ) );
