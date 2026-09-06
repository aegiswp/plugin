/**
 * Hooks admin list: enable/disable, delete, copy hook name.
 *
 * @package Aegis
 * @since   1.0.0
 */
( function ( $ ) {
	'use strict';

	var copyTimer = null;

	function config() {
		return window.aegisHooksList || {};
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

	function statusMarkup( enabled, postStatusLabel ) {
		var strings = config();

		if ( ! enabled ) {
			return (
				'<span class="aegis-status-disabled">' +
				( strings.disabledLabel || 'Disabled' ) +
				'</span>'
			);
		}

		return (
			'<span class="aegis-status-enabled">' +
			$( '<div>' ).text( postStatusLabel ).html() +
			'</span>'
		);
	}

	$( document ).on( 'click', '.aegis-hooks-copy', function () {
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
					$button.attr(
						'title',
						strings.copyHook || 'Copy hook name'
					);
					$icon
						.removeClass( 'dashicons-yes' )
						.addClass( 'dashicons-clipboard' );
				}, 2000 );
			} )
			.catch( function () {
				/* Clipboard may be blocked; leave the name visible to copy manually. */
			} );
	} );

	$( document ).on( 'change', '.aegis-hook-enabled-toggle', function () {
		var $toggle = $( this );
		var $row = $toggle.closest( 'tr' );
		var enabled = $toggle.is( ':checked' );
		var strings = config();

		if ( ! strings.nonce ) {
			return;
		}

		$toggle.prop( 'disabled', true );

		$.post( window.ajaxurl, {
			action: 'aegis_toggle_hook_instance',
			nonce: strings.nonce,
			post_id: $row.data( 'post-id' ),
			enabled: enabled ? 1 : 0,
		} )
			.done( function ( response ) {
				if ( ! response || ! response.success ) {
					$toggle.prop( 'checked', ! enabled );
					return;
				}

				$row.find( '.aegis-hooks-status' ).html(
					statusMarkup(
						enabled,
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

	$( document ).on( 'click', '.aegis-hook-delete', function () {
		var $button = $( this );
		var $row = $button.closest( 'tr' );
		var strings = config();

		if ( ! strings.nonce ) {
			return;
		}

		if (
			! window.confirm(
				strings.confirmDelete || 'Move this hook pattern to Trash?'
			)
		) {
			return;
		}

		$button.prop( 'disabled', true );

		$.post( window.ajaxurl, {
			action: 'aegis_delete_hook_instance',
			nonce: strings.nonce,
			post_id: $row.data( 'post-id' ),
		} )
			.done( function ( response ) {
				if ( ! response || ! response.success ) {
					$button.prop( 'disabled', false );
					return;
				}

				$row.remove();

				if ( $( '.aegis-hooks-list tbody tr' ).length === 0 ) {
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
}( jQuery ) );
