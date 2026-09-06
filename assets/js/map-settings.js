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

	$( document ).on( 'click', '.aegis-save-google-maps', function ( e ) {
		e.preventDefault();
		const $btn = $( this );

		if ( typeof aegisAdmin === 'undefined' ) {
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
		const maskChar = '\u2022';

		$( '.aegis-google-maps-field' ).each( function () {
			const name = $( this ).attr( 'name' );
			const match = name.match( /aegis_google_maps\[(\w+)\]/ );

			if ( match ) {
				const value = $( this ).val();

				if (
					$( this ).attr( 'type' ) === 'password' &&
					value &&
					value.indexOf( maskChar ) !== -1
				) {
					return;
				}

				settings[ match[ 1 ] ] = value;
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
				$btn.html( originalHtml ).prop( 'disabled', false );
			},
		} );
	} );

	$( document ).on( 'click', '.aegis-test-google-maps', function ( e ) {
		e.preventDefault();
		const $btn = $( this );

		if ( typeof aegisAdmin === 'undefined' || $btn.prop( 'disabled' ) ) {
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
				const hasKey =
					String(
						$( 'input[name="aegis_google_maps[server_api_key]"]' ).val() ||
							''
					).trim() !== '';
				$btn.prop( 'disabled', ! hasKey );
			},
		} );
	} );

	$( document ).on(
		'input change',
		'input[name="aegis_google_maps[server_api_key]"]',
		function () {
			const hasKey =
				String( $( this ).val() || '' ).trim() !== '';
			$( '.aegis-test-google-maps' ).prop( 'disabled', ! hasKey );
		}
	);
}( jQuery ) );
