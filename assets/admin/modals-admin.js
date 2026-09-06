/**
 * Modals admin list: enable/disable, delete, copy ID.
 *
 * @package Aegis
 * @since   1.0.0
 */
( function ( $ ) {
	'use strict';

	var copyTimer = null;

	function config() {
		return window.aegisModalsList || {};
	}

	function copyText( text ) {
		if ( navigator.clipboard && navigator.clipboard.writeText ) {
			return navigator.clipboard.writeText( text );
		}

		return new Promise( function ( resolve, reject ) {
			var input = document.createElement( 'textarea' );
			input.value = text;
			input.setAttribute( 'readonly', '' );
			input.style.position = 'absolute';
			input.style.left = '-9999px';
			document.body.appendChild( input );
			input.select();

			try {
				document.execCommand( 'copy' );
				resolve();
			} catch ( error ) {
				reject( error );
			} finally {
				document.body.removeChild( input );
			}
		} );
	}

	function statusMarkup( enabled, triggerEnabled, postStatusLabel ) {
		var strings = config();

		if ( ! enabled ) {
			return (
				'<span class="aegis-status-disabled">' +
				( strings.disabledLabel || 'Disabled' ) +
				'</span>'
			);
		}

		if ( ! triggerEnabled ) {
			return (
				'<span class="aegis-status-disabled">' +
				( strings.triggerOffLabel || 'Trigger off' ) +
				'</span>'
			);
		}

		return (
			'<span class="aegis-status-enabled">' +
			$( '<div>' ).text( postStatusLabel ).html() +
			'</span>'
		);
	}

	$( document ).on( 'click', '.aegis-modals-id', function () {
		var $button = $( this );
		var text = $button.attr( 'data-copy' ) || '';
		var strings = config();

		if ( ! text ) {
			return;
		}

		copyText( text )
			.then( function () {
				var $icon = $button.find( '.dashicons' );
				$button.addClass( 'is-copied' );
				$button.attr( 'title', strings.copied || 'Copied' );
				$icon
					.removeClass( 'dashicons-clipboard' )
					.addClass( 'dashicons-yes' );

				window.clearTimeout( copyTimer );
				copyTimer = window.setTimeout( function () {
					$button.removeClass( 'is-copied' );
					$button.attr( 'title', strings.copyId || 'Copy modal ID' );
					$icon
						.removeClass( 'dashicons-yes' )
						.addClass( 'dashicons-clipboard' );
				}, 2000 );
			} )
			.catch( function () {
				/* Clipboard may be blocked; leave the ID visible to copy manually. */
			} );
	} );

	$( document ).on( 'change', '.aegis-modal-enabled-toggle', function () {
		var $toggle = $( this );
		var $row = $toggle.closest( 'tr' );
		var enabled = $toggle.is( ':checked' );
		var strings = config();

		if ( ! strings.nonce ) {
			return;
		}

		$toggle.prop( 'disabled', true );

		$.post( window.ajaxurl, {
			action: 'aegis_toggle_modal',
			nonce: strings.nonce,
			post_id: $row.data( 'post-id' ),
			modal_id: $row.data( 'modal-id' ),
			block_index: $row.data( 'block-index' ),
			enabled: enabled ? 1 : 0,
		} )
			.done( function ( response ) {
				if ( ! response || ! response.success ) {
					$toggle.prop( 'checked', ! enabled );
					return;
				}

				$row.find( '.aegis-modals-status' ).html(
					statusMarkup(
						enabled,
						String( $row.data( 'trigger-enabled' ) ) === '1',
						String( $row.data( 'post-status-label' ) || '' )
					)
				);
			} )
			.fail( function () {
				$toggle.prop( 'checked', ! enabled );
			} )
			.always( function () {
				$toggle.prop( 'disabled', false );
			} );
	} );

	$( document ).on( 'click', '.aegis-modal-delete', function () {
		var $button = $( this );
		var $row = $button.closest( 'tr' );
		var strings = config();

		if ( ! strings.nonce ) {
			return;
		}

		if (
			! window.confirm(
				strings.confirmDelete ||
					'Delete this modal from the page?'
			)
		) {
			return;
		}

		$button.prop( 'disabled', true );

		$.post( window.ajaxurl, {
			action: 'aegis_delete_modal',
			nonce: strings.nonce,
			post_id: $row.data( 'post-id' ),
			modal_id: $row.data( 'modal-id' ),
			block_index: $row.data( 'block-index' ),
		} )
			.done( function ( response ) {
				if ( ! response || ! response.success ) {
					$button.prop( 'disabled', false );
					return;
				}

				$row.remove();

				if ( $( '.aegis-modals-list tbody tr' ).length === 0 ) {
					if ( typeof window.aegisReloadAdmin === 'function' ) {
						window.aegisReloadAdmin();
					} else {
						window.location.reload();
					}
				}
			} )
			.fail( function () {
				$button.prop( 'disabled', false );
			} );
	} );

	function chooser() {
		return document.getElementById( 'aegis-modals-chooser' );
	}

	function closeChooser() {
		var dialog = chooser();

		if ( dialog && dialog.open ) {
			dialog.close();
		}
	}

	$( document ).on( 'click', '.aegis-modals-add-new', function ( event ) {
		var dialog = chooser();

		if ( ! dialog || typeof dialog.showModal !== 'function' ) {
			return;
		}

		event.preventDefault();
		dialog.showModal();
	} );

	$( document ).on( 'click', '.aegis-modals-chooser-close', function ( event ) {
		event.preventDefault();
		closeChooser();
	} );

	$( document ).on( 'click', '#aegis-modals-chooser', function ( event ) {
		if ( event.target === this ) {
			closeChooser();
		}
	} );
}( jQuery ) );
