/**
 * Analytics settings (Connectors, one tab per provider).
 *
 * Depends on theme aegisAdmin localized on aegis-admin-settings.
 */
( function ( $ ) {
	'use strict';

	function showNotice( message, type ) {
		$( '.aegis-notice' ).remove();
		const $notice = $(
			'<div class="aegis-notice aegis-notice-' + type + '">' + message + '</div>'
		);
		$( '.aegis-settings-header' ).after( $notice );
		setTimeout( function () {
			$notice.fadeOut( 300, function () {
				$( this ).remove();
			} );
		}, 3000 );
	}

	$( document ).on( 'click', '.aegis-save-analytics', function ( e ) {
		e.preventDefault();
		const $btn = $( this );
		const $wrap = $( '[data-aegis-analytics-settings]' );

		if ( typeof aegisAdmin === 'undefined' || ! $wrap.length ) {
			return;
		}

		const originalText = $btn.text();
		$btn.text( aegisAdmin.saving ).prop( 'disabled', true );

		const settings = {};

		$wrap.find( 'input[type="checkbox"]' ).each( function () {
			const name = $( this ).attr( 'name' );
			const match = name && name.match( /aegis_analytics\[(\w+)\]/ );

			if ( match ) {
				settings[ match[ 1 ] ] = $( this ).is( ':checked' ) ? '1' : '0';
			}
		} );

		$wrap.find( 'input[type="text"], input[type="url"], input[type="password"]' ).each( function () {
			const name = $( this ).attr( 'name' );
			const match = name && name.match( /aegis_analytics\[(\w+)\]/ );

			if ( match ) {
				settings[ match[ 1 ] ] = $( this ).val();
			}
		} );

		$.ajax( {
			url: aegisAdmin.ajaxUrl,
			type: 'POST',
			data: {
				action: 'aegis_save_analytics',
				nonce: aegisAdmin.nonce,
				settings: settings,
			},
			success( response ) {
				if ( response.success ) {
					showNotice(
						response.data.message || aegisAdmin.saved,
						'success'
					);
				} else {
					showNotice( response.data.message || aegisAdmin.error, 'error' );
				}
			},
			error() {
				showNotice( aegisAdmin.error, 'error' );
			},
			complete() {
				$btn.text( originalText ).prop( 'disabled', false );
			},
		} );
	} );
}( jQuery ) );
