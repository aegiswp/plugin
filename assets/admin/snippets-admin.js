/**
 * Snippets admin page interactions.
 *
 * @package Aegis
 * @since   1.0.0
 */
( function ( $ ) {
	'use strict';

	$( document ).on( 'submit', '.aegis-snippet-settings-form, .aegis-snippets-settings-form', function () {
		$( this ).find( 'button[type="submit"], input[type="submit"]' ).prop( 'disabled', true );
	} );

	$( document ).on( 'click', '.aegis-safe-mode-url-regenerate', function ( e ) {
		var i18n = ( window.aegisSnippetsList && window.aegisSnippetsList.i18n ) || {};
		if ( ! window.confirm( i18n.confirmRegenerate || 'Regenerate the Safe Mode URL? The old URL will stop working.' ) ) {
			e.preventDefault();
		}
	} );

	$( document ).on( 'click', '.aegis-safe-mode-url-copy', function () {
		var text = $.trim( $( '#aegis-safe-mode-url' ).text() );
		var i18n = ( window.aegisSnippetsList && window.aegisSnippetsList.i18n ) || {};
		var $button = $( this );

		if ( ! text ) {
			return;
		}

		function markCopied() {
			$button.text( i18n.copied || 'Copied' );
			window.setTimeout( function () {
				$button.text( i18n.copyUrl || 'Copy' );
			}, 1500 );
		}

		if ( navigator.clipboard && navigator.clipboard.writeText ) {
			navigator.clipboard.writeText( text ).then( markCopied );
			return;
		}

		var input = document.createElement( 'textarea' );
		input.value = text;
		document.body.appendChild( input );
		input.select();
		document.execCommand( 'copy' );
		document.body.removeChild( input );
		markCopied();
	} );

	$( document ).on(
		'click',
		'.aegis-snippet-locations .aegis-hooks-group-header',
		function () {
			$( this ).closest( '.aegis-hooks-group' ).toggleClass( 'is-collapsed' );
		}
	);

	$( document ).on( 'click', '.aegis-snippet-locations .aegis-bulk-enable', function ( e ) {
		e.preventDefault();
		$( this ).closest( '.aegis-snippet-locations' ).find( '.aegis-hooks-row--toggle .aegis-toggle input[type="checkbox"]' ).prop( 'checked', true );
	} );

	$( document ).on( 'click', '.aegis-snippet-locations .aegis-bulk-disable', function ( e ) {
		e.preventDefault();
		$( this ).closest( '.aegis-snippet-locations' ).find( '.aegis-hooks-row--toggle .aegis-toggle input[type="checkbox"]' ).prop( 'checked', false );
	} );

	$( document ).on( 'change', '.aegis-snippet-status-toggle', function () {
		var $toggle = $( this );
		var $row = $toggle.closest( '[data-snippet-id]' );
		var id = $row.data( 'snippet-id' );
		var enabled = $toggle.is( ':checked' );
		var config = window.aegisSnippetsList || {};
		var i18n = config.i18n || {};

		if ( ! id || ! config.nonce ) {
			return;
		}

		$( '.aegis-snippet-item[data-snippet-id="' + id + '"]' )
			.attr( 'data-snippet-enabled', enabled ? '1' : '0' )
			.find( '.aegis-snippet-status-toggle' )
			.prop( 'checked', enabled );

		$( '.aegis-snippet-item[data-snippet-id="' + id + '"]' )
			.find( '.aegis-snippets-status' )
			.html(
				enabled
					? '<span class="aegis-status-enabled">' + ( i18n.publishedLabel || 'Published' ) + '</span>'
					: '<span class="aegis-status-disabled">' + ( i18n.disabledLabel || 'Disabled' ) + '</span>'
			);

		$.post( window.ajaxurl, {
			action: 'aegis_toggle_snippet',
			nonce: config.nonce,
			id: id,
			enabled: enabled ? 1 : 0,
		} ).fail( function () {
			$( '.aegis-snippet-item[data-snippet-id="' + id + '"]' )
				.attr( 'data-snippet-enabled', enabled ? '0' : '1' )
				.find( '.aegis-snippet-status-toggle' )
				.prop( 'checked', ! enabled )
				.end()
				.find( '.aegis-snippets-status' )
				.html(
					! enabled
						? '<span class="aegis-status-enabled">' + ( i18n.publishedLabel || 'Published' ) + '</span>'
						: '<span class="aegis-status-disabled">' + ( i18n.disabledLabel || 'Disabled' ) + '</span>'
				);
		} ).always( filterSnippetRows );
	} );

	function storageKey( name ) {
		return 'aegisSnippets' + name;
	}

	function filterSnippetRows() {
		var type = $( '.aegis-snippet-type-filter.is-active' ).data( 'type' ) || '';
		var tag = $( '#aegis-snippet-tag-filter' ).val() || '';
		var search = $.trim( $( '#aegis-snippet-search' ).val() || '' ).toLowerCase();
		var hideInactive = $( '#aegis-snippet-hide-inactive' ).is( ':checked' );
		var hidden = 0;

		$( '.aegis-snippet-item' ).each( function () {
			var $row = $( this );
			var matchType = ! type || $row.data( 'snippet-type' ) === type;
			var tags = String( $row.attr( 'data-snippet-tags' ) || '' ).split( ',' );
			var matchTag = ! tag || tags.indexOf( tag ) !== -1;
			var haystack = String( $row.attr( 'data-search' ) || '' );
			var matchSearch = ! search || haystack.indexOf( search ) !== -1;
			var enabled = String( $row.attr( 'data-snippet-enabled' ) ) === '1';
			var matchActive = ! hideInactive || enabled;
			var show = matchType && matchTag && matchSearch && matchActive;
			$row.toggle( show );
			if ( hideInactive && ! enabled && matchType && matchTag && matchSearch && $row.closest( '.aegis-snippets-table-view' ).length ) {
				hidden += 1;
			}
		} );

		$( '.aegis-snippets-folder' ).each( function () {
			var $folder = $( this );
			var visible = $folder.find( '.aegis-snippet-item:visible' ).length;
			$folder.toggle( visible > 0 );
			$folder.find( '.aegis-snippets-folder-count' ).text( visible );
		} );

		var $count = $( '.aegis-snippets-hidden-count' );
		if ( hidden > 0 ) {
			var tpl = ( window.aegisSnippetsList && window.aegisSnippetsList.i18n && window.aegisSnippetsList.i18n.hiddenInactive ) || '%d inactive snippets hidden.';
			$count.text( tpl.replace( '%d', hidden ) ).prop( 'hidden', false );
		} else {
			$count.prop( 'hidden', true );
		}
	}

	function sortSnippetRows() {
		var sort = $( '#aegis-snippet-sort' ).val() || 'name-asc';
		var parts = sort.split( '-' );
		var key = parts[ 0 ];
		var dir = parts[ 1 ] === 'desc' ? -1 : 1;
		var attr = 'sort' + key.charAt( 0 ).toUpperCase() + key.slice( 1 );

		$( '.aegis-snippets-table tbody' ).each( function () {
			var $body = $( this );
			var rows = $body.children( 'tr' ).get();
			rows.sort( function ( a, b ) {
				var av = $( a ).data( attr );
				var bv = $( b ).data( attr );
				if ( key === 'name' ) {
					return String( av || '' ).localeCompare( String( bv || '' ) ) * dir;
				}
				return ( Number( av ) - Number( bv ) ) * dir;
			} );
			$body.append( rows );
		} );
	}

	function setView( view ) {
		var $list = $( '.aegis-snippets-list' );
		$list.attr( 'data-view', view );
		$( '.aegis-snippet-view' ).removeClass( 'is-active' ).attr( 'aria-pressed', 'false' );
		$( '.aegis-snippet-view[data-view="' + view + '"]' ).addClass( 'is-active' ).attr( 'aria-pressed', 'true' );
		$( '.aegis-snippets-table-view' ).prop( 'hidden', view !== 'table' );
		$( '.aegis-snippets-grouped-view' ).prop( 'hidden', view !== 'grouped' );
		try {
			window.localStorage.setItem( storageKey( 'View' ), view );
		} catch ( e ) {}
	}

	$( document ).on( 'click', '.aegis-snippet-type-filter', function () {
		$( '.aegis-snippet-type-filter' ).removeClass( 'is-active current' ).attr( 'aria-pressed', 'false' );
		$( this ).addClass( 'is-active current' ).attr( 'aria-pressed', 'true' );
		filterSnippetRows();
	} );

	$( document ).on( 'change input', '#aegis-snippet-tag-filter, #aegis-snippet-search, #aegis-snippet-hide-inactive', function () {
		if ( this.id === 'aegis-snippet-hide-inactive' ) {
			try {
				window.localStorage.setItem( storageKey( 'HideInactive' ), $( this ).is( ':checked' ) ? '1' : '0' );
			} catch ( e ) {}
		}
		filterSnippetRows();
	} );

	$( document ).on( 'change', '#aegis-snippet-sort', function () {
		sortSnippetRows();
	} );

	$( document ).on( 'click', '.aegis-snippet-view', function () {
		setView( $( this ).data( 'view' ) );
	} );

	$( document ).on( 'click', '.aegis-snippets-folder-header', function () {
		$( this ).closest( '.aegis-snippets-folder' ).toggleClass( 'is-collapsed' );
	} );

	$( document ).on( 'click', '.aegis-snippet-delete', function ( e ) {
		var message = ( window.aegisSnippetsList && window.aegisSnippetsList.i18n && window.aegisSnippetsList.i18n.confirmDelete ) || 'Delete this snippet?';
		if ( ! window.confirm( message ) ) {
			e.preventDefault();
		}
	} );

	$( document ).on( 'click', '#aegis-snippet-import', function () {
		$( '#aegis-snippet-import-file' ).trigger( 'click' );
	} );

	$( document ).on( 'change', '#aegis-snippet-import-file', function () {
		var file = this.files && this.files[ 0 ];
		var config = window.aegisSnippetsList || {};
		if ( ! file || ! config.importNonce ) {
			return;
		}

		var reader = new FileReader();
		reader.onload = function () {
			$.post( window.ajaxurl, {
				action: 'aegis_import_snippets',
				nonce: config.importNonce,
				payload: reader.result,
			} ).done( function () {
				var dest =
					window.location.pathname +
					'?page=aegis-snippets&imported=1';

				if ( typeof window.aegisNavigateAdmin === 'function' ) {
					window.aegisNavigateAdmin( dest );
				} else {
					window.location.href = dest;
				}
			} ).fail( function () {
				window.alert( ( config.i18n && config.i18n.importFailed ) || 'Could not import snippets.' );
			} );
		};
		reader.readAsText( file );
		this.value = '';
	} );

	function currentType() {
		var $checked = $( 'input[name="snippet_type"]:checked' );
		return $checked.length ? $checked.val() : 'content';
	}

	function rebuildLocationCards( type, selected ) {
		var wrap = document.getElementById( 'aegis-snippet-location-cards' );
		var cards = ( window.aegisSnippetEditor && window.aegisSnippetEditor.locationCards && window.aegisSnippetEditor.locationCards[ type ] ) || [];

		if ( ! wrap ) {
			return;
		}

		wrap.innerHTML = '';

		cards.forEach( function ( card ) {
			var button = document.createElement( 'button' );
			button.type = 'button';
			button.className = 'aegis-snippet-location-card' + ( card.wide ? ' is-wide' : '' ) + ( card.value === selected ? ' is-selected' : '' );
			button.setAttribute( 'data-location', card.value );
			button.setAttribute( 'aria-pressed', card.value === selected ? 'true' : 'false' );

			var title = document.createElement( 'strong' );
			title.textContent = card.label;
			var help = document.createElement( 'span' );
			help.textContent = card.help;

			button.appendChild( title );
			button.appendChild( help );
			wrap.appendChild( button );
		} );
	}

	function rebuildLocations( type, preferred ) {
		var select = document.getElementById( 'snippet_location' );
		var groups = ( window.aegisSnippetEditor && window.aegisSnippetEditor.locations && window.aegisSnippetEditor.locations[ type ] ) || [];

		if ( ! select ) {
			return;
		}

		select.innerHTML = '';

		groups.forEach( function ( group ) {
			var options = group.options || {};
			var keys = Object.keys( options );
			if ( ! keys.length ) {
				return;
			}

			var parent = select;
			if ( group.label ) {
				parent = document.createElement( 'optgroup' );
				parent.label = group.label;
				select.appendChild( parent );
			}

			keys.forEach( function ( value ) {
				var option = document.createElement( 'option' );
				option.value = value;
				option.textContent = options[ value ];
				parent.appendChild( option );
			} );
		} );

		if ( preferred && Array.prototype.some.call( select.options, function ( option ) { return option.value === preferred; } ) ) {
			select.value = preferred;
		} else if ( select.options.length ) {
			select.selectedIndex = 0;
		}

		rebuildLocationCards( type, select.value );
		toggleShortcodeRow();
	}

	function syncSelectedCard() {
		var selected = $( '#snippet_location' ).val();
		$( '.aegis-snippet-location-card' ).each( function () {
			var isSelected = String( $( this ).data( 'location' ) ) === String( selected );
			$( this ).toggleClass( 'is-selected', isSelected );
			$( this ).attr( 'aria-pressed', isSelected ? 'true' : 'false' );
		} );
	}

	function toggleShortcodeRow() {
		var location = $( '#snippet_location' ).val();
		$( '.aegis-snippet-shortcode-row' ).prop( 'hidden', location !== 'shortcode' );
		syncSelectedCard();
		refreshShortcodeMarkup();
	}

	function slugFromTitle( title ) {
		return String( title || '' )
			.toLowerCase()
			.replace( /['"]/g, '' )
			.replace( /[^a-z0-9]+/g, '-' )
			.replace( /^-+|-+$/g, '' ) || 'snippet';
	}

	function refreshShortcodeMarkup() {
		var $code = $( '#aegis-snippet-shortcode-markup' );
		var tag = ( window.aegisSnippetsList && window.aegisSnippetsList.shortcodeTag ) || 'aegis_snippet';

		if ( ! $code.length || $code.data( 'locked' ) ) {
			return;
		}

		$code.text( '[' + tag + ' id="' + slugFromTitle( $( '#snippet_title' ).val() ) + '"]' );
	}

	function copyShortcode() {
		var text = $.trim( $( '#aegis-snippet-shortcode-markup' ).text() );
		var i18n = ( window.aegisSnippetsList && window.aegisSnippetsList.i18n ) || {};
		var $button = $( '.aegis-snippet-shortcode-copy-button' );

		if ( ! text ) {
			return;
		}

		function markCopied() {
			$button.attr( 'aria-label', i18n.copied || 'Copied' );
			window.setTimeout( function () {
				$button.attr( 'aria-label', i18n.copy || 'Copy shortcode' );
			}, 1500 );
		}

		if ( navigator.clipboard && navigator.clipboard.writeText ) {
			navigator.clipboard.writeText( text ).then( markCopied );
			return;
		}

		var input = document.createElement( 'textarea' );
		input.value = text;
		document.body.appendChild( input );
		input.select();
		document.execCommand( 'copy' );
		document.body.removeChild( input );
		markCopied();
	}

	function syncTypeTabs() {
		$( '.aegis-snippet-type-tabs .nav-tab' ).removeClass( 'nav-tab-active' );
		$( 'input[name="snippet_type"]:checked' ).closest( '.nav-tab' ).addClass( 'nav-tab-active' );
	}

	$( document ).on( 'change', 'input[name="snippet_type"]', function () {
		syncTypeTabs();
		rebuildLocations( currentType() );
	} );

	$( document ).on( 'change', '#snippet_location', toggleShortcodeRow );

	$( document ).on( 'click', '.aegis-snippet-location-card', function () {
		var location = $( this ).data( 'location' );
		if ( location ) {
			$( '#snippet_location' ).val( location ).trigger( 'change' );
		}
	} );

	$( document ).on( 'input', '#snippet_title', refreshShortcodeMarkup );

	$( document ).on( 'click', '.aegis-snippet-shortcode-copy-button', copyShortcode );

	$( function () {
		if ( document.querySelector( '.aegis-snippet-editor' ) ) {
			syncTypeTabs();
			toggleShortcodeRow();
		}

		if ( document.querySelector( '.aegis-snippets-list' ) ) {
			try {
				if ( window.localStorage.getItem( storageKey( 'HideInactive' ) ) === '1' ) {
					$( '#aegis-snippet-hide-inactive' ).prop( 'checked', true );
				}
				var savedView = window.localStorage.getItem( storageKey( 'View' ) );
				if ( savedView === 'grouped' || savedView === 'table' ) {
					setView( savedView );
				}
			} catch ( e ) {}
			sortSnippetRows();
			filterSnippetRows();
		}
	} );
}( jQuery ) );
