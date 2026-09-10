/**
 * BunnyCDN API settings (Connectors → BunnyCDN).
 *
 * Keeps Save / Test disabled until an Account API Key is present.
 * Depends on theme aegisAdmin localized on aegis-admin-settings.
 */
( function ( $ ) {
	'use strict';

	function secretFilled( value ) {
		return String( value || '' ).trim() !== '';
	}

	function syncBunnycdnActionButtons() {
		const $key = $( 'input[name="aegis_bunnycdn[api_key]"]' );
		const filled = $key.length > 0 && secretFilled( $key.val() );

		$( '.aegis-save-bunnycdn, .aegis-test-bunnycdn' ).each( function () {
			const $btn = $( this );

			// Leave in-flight busy buttons alone until ajaxComplete re-syncs.
			if ( $btn.data( 'aegisOriginalHtml' ) ) {
				return;
			}

			$btn.prop( 'disabled', ! filled );
		} );
	}

	$( document ).ready( syncBunnycdnActionButtons );

	$( document ).on(
		'input change',
		'input[name="aegis_bunnycdn[api_key]"]',
		syncBunnycdnActionButtons
	);

	$( document ).on( 'ajaxComplete', function ( _event, _xhr, settings ) {
		const data = settings && settings.data ? String( settings.data ) : '';

		if (
			data.indexOf( 'action=aegis_save_bunnycdn' ) !== -1 ||
			data.indexOf( 'action=aegis_test_bunnycdn' ) !== -1
		) {
			syncBunnycdnActionButtons();
		}
	} );
}( jQuery ) );
