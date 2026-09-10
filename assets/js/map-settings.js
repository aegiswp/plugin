/**
 * Google Maps API settings (Connectors → Google Maps).
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

	function secretFilled( value ) {
		return String( value || '' ).trim() !== '';
	}

	function syncGoogleMapsActionButtons() {
		const browserFilled = secretFilled(
			$( 'input[name="aegis_google_maps[browser_api_key]"]' ).val()
		);
		const serverFilled = secretFilled(
			$( 'input[name="aegis_google_maps[server_api_key]"]' ).val()
		);

		$( '.aegis-save-google-maps' ).prop( 'disabled', ! ( browserFilled || serverFilled ) );
		$( '.aegis-test-google-maps' ).prop( 'disabled', ! serverFilled );
	}

	$( document ).ready( syncGoogleMapsActionButtons );

	$( document ).on(
		'input change',
		'input[name="aegis_google_maps[browser_api_key]"], input[name="aegis_google_maps[server_api_key]"]',
		syncGoogleMapsActionButtons
	);

	$( document ).on( 'click', '.aegis-save-google-maps', function ( e ) {
		e.preventDefault();
		const $btn = $( this );

		if ( typeof aegisAdmin === 'undefined' || $btn.prop( 'disabled' ) ) {
			return;
		}

		const browserFilled = secretFilled(
			$( 'input[name="aegis_google_maps[browser_api_key]"]' ).val()
		);
		const serverFilled = secretFilled(
			$( 'input[name="aegis_google_maps[server_api_key]"]' ).val()
		);

		if ( ! browserFilled && ! serverFilled ) {
			syncGoogleMapsActionButtons();
			return;
		}

		const originalHtml = $btn.html();
		const $label = $btn.find( '.aegis-button-label' );
		$btn.prop( 'disabled', true );

		if ( $label.length ) {
			$label.text( aegisAdmin.saving );
		} else {
			$btn.text( aegisAdmin.saving );
		}

		const settings = {};

		$( '.aegis-google-maps-field' ).each( function () {
			const name = $( this ).attr( 'name' );
			const match = name.match( /aegis_google_maps\[(\w+)\]/ );

			if ( match ) {
				const value = $( this ).val();

				// Always include the field so the server can preserve masked secrets.
				settings[ match[ 1 ] ] = value || '';
			}
		} );

		$.ajax( {
			url: aegisAdmin.ajaxUrl,
			type: 'POST',
			data: {
				action: 'aegis_save_google_maps',
				nonce: aegisAdmin.nonce,
				settings: settings,
			},
			success( response ) {
				if ( response.success ) {
					showNotice( aegisAdmin.saved, 'success' );
				} else {
					showNotice( response.data.message || aegisAdmin.error, 'error' );
				}
			},
			error() {
				showNotice( aegisAdmin.error, 'error' );
			},
			complete() {
				$btn.html( originalHtml );
				syncGoogleMapsActionButtons();
			},
		} );
	} );

	$( document ).on( 'click', '.aegis-test-google-maps', function ( e ) {
		e.preventDefault();
		const $btn = $( this );

		if ( typeof aegisAdmin === 'undefined' || $btn.prop( 'disabled' ) ) {
			return;
		}

		if (
			! secretFilled(
				$( 'input[name="aegis_google_maps[server_api_key]"]' ).val()
			)
		) {
			syncGoogleMapsActionButtons();
			return;
		}

		const originalHtml = $btn.html();
		const $label = $btn.find( '.aegis-button-label' );
		$btn.prop( 'disabled', true );

		if ( $label.length ) {
			$label.text( 'Testing...' );
		} else {
			$btn.text( 'Testing...' );
		}

		$.ajax( {
			url: aegisAdmin.ajaxUrl,
			type: 'POST',
			data: {
				action: 'aegis_test_google_maps',
				nonce: aegisAdmin.nonce,
			},
			success( response ) {
				if ( response.success ) {
					showNotice( response.data.message, 'success' );
				} else {
					showNotice( response.data.message || aegisAdmin.error, 'error' );
				}
			},
			error() {
				showNotice( aegisAdmin.error, 'error' );
			},
			complete() {
				$btn.html( originalHtml );
				syncGoogleMapsActionButtons();
			},
		} );
	} );
}( jQuery ) );
