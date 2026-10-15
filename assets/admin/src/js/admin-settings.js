/**
 * Aegis Admin Settings JavaScript
 *
 * Handles navigation and interactions for the admin settings page.
 *
 * @param {Object} $ jQuery instance.
 * @package
 * @since   1.0.0
 */

/* global jQuery, aegisAdmin */

( function ( $ ) {
	'use strict';

	$( document ).ready( function () {
		initTopBarResources();
		initSpaNavigation();
		initDismissGettingStarted();
		bindPageUi();
		initAjaxSave();
		initBulkActions();
		initPluginStackExtras();
		initSearch();
		initExportImport();
		initBlockToggleSuboptions();
	} );

	/**
	 * Close the top-bar Resources menu on outside click or Escape.
	 */
	function initTopBarResources() {
		document.addEventListener( 'click', function ( event ) {
			const resources = document.querySelector(
				'.aegis-top-bar-resources'
			);

			if (
				resources &&
				! resources.contains( event.target )
			) {
				resources.removeAttribute( 'open' );
			}
		} );

		document.addEventListener( 'keydown', function ( event ) {
			if ( event.key === 'Escape' ) {
				const resources = document.querySelector(
					'.aegis-top-bar-resources'
				);

				if ( resources ) {
					resources.removeAttribute( 'open' );
				}
			}
		} );
	}

	/**
	 * Dismiss the dashboard Getting Started section (persists per user).
	 */
	function initDismissGettingStarted() {
		$( document ).on(
			'click',
			'.aegis-dismiss-getting-started',
			function () {
				const $button = $( this );
				const $section = $button.closest(
					'.aegis-dashboard-getting-started-section'
				);
				const ajaxUrl =
					typeof aegisAdmin !== 'undefined' && aegisAdmin.ajaxUrl
						? aegisAdmin.ajaxUrl
						: window.ajaxurl;
				const body = new window.URLSearchParams();

				body.set( 'action', 'aegis_dismiss_getting_started' );
				body.set(
					'nonce',
					typeof aegisAdmin !== 'undefined' ? aegisAdmin.nonce : ''
				);

				$button.prop( 'disabled', true );

				window
					.fetch( ajaxUrl, {
						method: 'POST',
						credentials: 'same-origin',
						headers: {
							Accept: 'application/json',
							'Content-Type':
								'application/x-www-form-urlencoded; charset=UTF-8',
						},
						body: body.toString(),
					} )
					.then( function ( response ) {
						return response.json();
					} )
					.then( function ( json ) {
						if ( json && json.success ) {
							$section.slideUp( 160, function () {
								$section.remove();
							} );
							return;
						}

						$button.prop( 'disabled', false );
					} )
					.catch( function () {
						$button.prop( 'disabled', false );
					} );
			}
		);
	}

	/**
	 * Bind UI that is recreated on each in-admin page swap.
	 */
	function bindPageUi() {
		if ( redirectLegacyConnectorHashes() ) {
			return;
		}

		initNavigation();
		syncConnectorTestButtons();
	}

	/**
	 * True when a connector secret field has a value (including a masked stored key).
	 *
	 * @param {string} value Field value.
	 * @return {boolean} Whether Test Connection can run.
	 */
	function connectorSecretFilled( value ) {
		return String( value || '' ).trim() !== '';
	}

	/**
	 * Swap a connector action button into a busy state without dropping its icon.
	 *
	 * @param {jQuery}  $btn  Button.
	 * @param {boolean} busy  Whether the request is in flight.
	 * @param {string}  label Busy label.
	 */
	function setConnectorButtonBusy( $btn, busy, label ) {
		if ( busy ) {
			if ( ! $btn.data( 'aegisOriginalHtml' ) ) {
				$btn.data( 'aegisOriginalHtml', $btn.html() );
			}

			$btn.prop( 'disabled', true );
			const $label = $btn.find( '.aegis-button-label' );

			if ( $label.length ) {
				$label.text( label );
			} else {
				$btn.text( label );
			}

			return;
		}

		const html = $btn.data( 'aegisOriginalHtml' );

		if ( html ) {
			$btn.html( html );
			$btn.removeData( 'aegisOriginalHtml' );
		}

		$btn.prop( 'disabled', false );
	}

	/**
	 * Enable BunnyCDN Save / Test when the Account API Key field has a value.
	 * Google Maps button state is owned by assets/js/map-settings.js.
	 */
	function syncConnectorTestButtons() {
		const $bunnyKey = $( 'input[name="aegis_bunnycdn[api_key]"]' );
		const bunnyFilled =
			$bunnyKey.length > 0 && connectorSecretFilled( $bunnyKey.val() );
		const $bunnySave = $( '.aegis-save-bunnycdn' );
		const $bunnyTest = $( '.aegis-test-bunnycdn' );

		if ( $bunnySave.length ) {
			$bunnySave.prop( 'disabled', ! bunnyFilled );
		}

		if ( $bunnyTest.length ) {
			$bunnyTest.prop( 'disabled', ! bunnyFilled );
		}
	}

	$( document ).on(
		'input change',
		'input[name="aegis_bunnycdn[api_key]"]',
		syncConnectorTestButtons
	);

	/**
	 * Send leftover Integrations hashes and old Analytics anchors to Connectors tabs.
	 *
	 * @return {boolean} True when a redirect was started.
	 */
	function redirectLegacyConnectorHashes() {
		const params = new URLSearchParams( window.location.search );
		const page = params.get( 'page' );
		const hash = settingsTabHash();

		if ( page === 'aegis-integrations' ) {
			const connectorMap = {
				performance: 'bunnycdn',
				maps: 'maps',
				analytics: 'ga4',
			};

			if ( connectorMap[ hash ] ) {
				params.set( 'page', 'aegis-connectors' );
				window.location.replace(
					window.location.pathname +
						'?' +
						params.toString() +
						'#' +
						connectorMap[ hash ]
				);
				return true;
			}

			return false;
		}

		if ( page === 'aegis-settings' ) {
			const map = {
				'wp-fusion': 'wp-fusion',
				edd: 'easy-digital-downloads',
				acf: 'advanced-custom-fields',
				'meta-box': 'meta-box',
				woocommerce: 'woocommerce',
				learndash: 'learndash',
				lifterlms: 'lifter-lms',
				'lifter-lms': 'lifter-lms',
				sensei: 'sensei-lms',
				fluentforms: 'fluent-forms',
				fluentbooking: 'fluent-booking',
			};

			if ( map[ hash ] ) {
				params.set( 'page', 'aegis-integrations' );
				window.location.replace(
					window.location.pathname +
						'?' +
						params.toString() +
						'#' +
						map[ hash ]
				);
				return true;
			}

			return false;
		}

		if ( page === 'aegis-connectors' ) {
			const map = {
				analytics: 'ga4',
				'analytics-ga4': 'ga4',
				'analytics-gtm': 'gtm',
				'analytics-clarity': 'clarity',
				'analytics-plausible': 'plausible',
				'analytics-fathom': 'fathom',
				'analytics-matomo': 'matomo',
				'analytics-meta-pixel': 'meta-pixel',
				'analytics-privacy': 'privacy',
			};

			if ( map[ hash ] ) {
				window.location.replace(
					window.location.pathname +
						window.location.search +
						'#' +
						map[ hash ]
				);
				return true;
			}
		}

		if ( page === 'aegis-performance' && hash === 'blocks' ) {
			window.location.replace(
				window.location.pathname + window.location.search + '#core-blocks'
			);
			return true;
		}

		return false;
	}

	/**
	 * Current settings sidebar hash without leftover query fragments.
	 *
	 * @return {string} Tab id.
	 */
	function settingsTabHash() {
		return window.location.hash.replace( /^#/, '' ).split( '&' )[ 0 ];
	}

	/**
	 * Map plugin hashes onto Integrations category tabs.
	 *
	 * @param {string} hash Raw hash.
	 * @return {string} Category tab id.
	 */
	function normalizeIntegrationsHash( hash ) {
		const map = {
			woocommerce: 'ecommerce',
			'easy-digital-downloads': 'ecommerce',
			'affiliate-wp': 'ecommerce',
			learndash: 'lms',
			'lifter-lms': 'lms',
			lifterlms: 'lms',
			'sensei-lms': 'lms',
			sensei: 'lms',
			'fluent-forms': 'forms',
			fluentforms: 'forms',
			'fluent-booking': 'forms',
			fluentbooking: 'forms',
			'fluent-crm': 'crm',
			fluentcrm: 'crm',
			'gravity-forms': 'forms',
			'ninja-forms': 'forms',
			'co-authors-plus': 'content',
			bbpress: 'content',
			'rank-math': 'seo',
			'yoast-seo': 'seo',
			aioseo: 'seo',
			seopress: 'seo',
			'advanced-custom-fields': 'developer',
			acf: 'developer',
			'meta-box': 'developer',
			'code-block-pro': 'developer',
			'syntax-highlighting': 'developer',
			'wp-fusion': 'crm',
			edd: 'ecommerce',
		};

		const normalized = hash.replace( /_/g, '-' );

		if ( map[ normalized ] ) {
			return map[ normalized ];
		}

		return normalized;
	}

	/**
	 * In-admin navigation between Aegis screens (no full wp-admin reload).
	 */
	function initSpaNavigation() {
		if ( ! document.body.classList.contains( 'aegis-plugin-admin' ) ) {
			return;
		}

		history.replaceState( { aegisSpa: true }, '', window.location.href );

		document.addEventListener(
			'click',
			function ( event ) {
				if ( event.defaultPrevented || event.button !== 0 ) {
					return;
				}

				if (
					event.metaKey ||
					event.ctrlKey ||
					event.shiftKey ||
					event.altKey
				) {
					return;
				}

				const anchor = event.target.closest( 'a' );

				if ( ! anchor || ! anchor.href ) {
					return;
				}

				if (
					anchor.target &&
					anchor.target !== '_self' &&
					anchor.target !== ''
				) {
					return;
				}

				if ( anchor.hasAttribute( 'download' ) ) {
					return;
				}

				if ( ! isAegisSpaUrl( anchor.href ) ) {
					return;
				}

				const dest = new URL( anchor.href, window.location.href );
				const curr = new URL( window.location.href );

				if (
					dest.pathname === curr.pathname &&
					dest.search === curr.search &&
					dest.hash !== curr.hash
				) {
					return;
				}

				event.preventDefault();
				navigateAegisAdmin(
					dest.href,
					dest.pathname !== curr.pathname ||
						dest.search !== curr.search
				);
			}
		);

		document.addEventListener(
			'submit',
			function ( event ) {
				const form = event.target;

				if ( ! ( form instanceof window.HTMLFormElement ) ) {
					return;
				}

				if ( ( form.getAttribute( 'method' ) || 'get' ).toLowerCase() !== 'get' ) {
					return;
				}

				const dest = new window.URL(
					form.getAttribute( 'action' ) || window.location.href,
					window.location.href
				);

				new window.FormData( form ).forEach( function ( value, key ) {
					if ( typeof value === 'string' ) {
						dest.searchParams.set( key, value );
					}
				} );

				if ( ! isAegisSpaUrl( dest.href ) ) {
					return;
				}

				event.preventDefault();
				navigateAegisAdmin( dest.href, true );
			}
		);

		window.addEventListener( 'popstate', function () {
			if ( ! isAegisSpaUrl( window.location.href ) ) {
				window.location.reload();
				return;
			}

			navigateAegisAdmin( window.location.href, false );
		} );

		window.aegisNavigateAdmin = function ( href ) {
			if ( ! isAegisSpaUrl( href ) ) {
				window.location.href = href;
				return;
			}

			navigateAegisAdmin( href, true );
		};

		window.aegisReloadAdmin = reloadAegisAdminPage;
	}

	/**
	 * @param {string} href Absolute or relative URL.
	 * @return {boolean} Whether the URL is an Aegis admin screen we can swap in place.
	 */
	function isAegisSpaUrl( href ) {
		let url;

		try {
			url = new URL( href, window.location.href );
		} catch {
			return false;
		}

		if ( url.origin !== window.location.origin ) {
			return false;
		}

		const path = url.pathname.replace( /\/+$/, '' );

		if (
			! path.endsWith( 'admin.php' ) &&
			! path.endsWith( '/wp-admin' )
		) {
			return false;
		}

		const page = url.searchParams.get( 'page' ) || '';

		if ( ! page.startsWith( 'aegis-' ) ) {
			return false;
		}

		if ( url.searchParams.has( 'edit' ) ) {
			return false;
		}

		return true;
	}

	let spaController = null;

	/**
	 * Reload the current Aegis screen via AJAX (no full wp-admin load).
	 */
	function reloadAegisAdminPage() {
		navigateAegisAdmin( window.location.href, false );
	}

	/**
	 * @param {string}  href Destination admin URL.
	 * @param {boolean} push Whether to push a history entry.
	 */
	function navigateAegisAdmin( href, push ) {
		if ( spaController ) {
			spaController.abort();
		}

		spaController = new window.AbortController();
		const request = spaController;
		const shell = document.getElementById( 'wpcontent' );

		if ( shell ) {
			shell.classList.add( 'aegis-admin-navigating' );
			shell.setAttribute( 'aria-busy', 'true' );
		}

		const body = new window.URLSearchParams();
		body.set( 'action', 'aegis_load_admin_page' );
		body.set( 'nonce', typeof aegisAdmin !== 'undefined' ? aegisAdmin.nonce : '' );
		body.set( 'url', href );

		const ajaxUrl =
			typeof aegisAdmin !== 'undefined' && aegisAdmin.ajaxUrl
				? aegisAdmin.ajaxUrl
				: window.ajaxurl;

		window
			.fetch( ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				signal: request.signal,
				headers: {
					Accept: 'application/json',
					'Content-Type':
						'application/x-www-form-urlencoded; charset=UTF-8',
					'X-Requested-With': 'AegisAdmin',
				},
				body: body.toString(),
			} )
			.then( function ( response ) {
				if ( ! response.ok ) {
					throw new Error( 'navigate' );
				}

				return response.json();
			} )
			.then( function ( json ) {
				if ( ! json || ! json.success || ! json.data || ! json.data.content ) {
					throw new Error( 'navigate' );
				}

				applyAegisFragment( json.data, href, push );
				bindPageUi();
				window.scrollTo( 0, 0 );
			} )
			.catch( function ( error ) {
				if ( error && error.name === 'AbortError' ) {
					return;
				}

				window.location.href = href;
			} )
			.finally( function () {
				if ( spaController !== request ) {
					return;
				}

				if ( shell ) {
					shell.classList.remove( 'aegis-admin-navigating' );
					shell.removeAttribute( 'aria-busy' );
				}
			} );
	}

	/**
	 * @param {Object}  data Fragment payload.
	 * @param {string}  href Destination URL.
	 * @param {boolean} push Whether to push history.
	 */
	function applyAegisFragment( data, href, push ) {
		if ( data.topBar ) {
			const currBar = document.querySelector( '.aegis-top-bar' );
			const holder = document.createElement( 'div' );
			holder.innerHTML = data.topBar;
			const nextBar = holder.querySelector( '.aegis-top-bar' );

			if ( currBar && nextBar ) {
				currBar.replaceWith( nextBar );
			}
		}

		const currBody = document.querySelector( '#wpbody-content' );

		if ( currBody && data.content ) {
			currBody.innerHTML = data.content;
		}

		if ( data.title ) {
			document.title = data.title;
		}

		if ( push ) {
			history.pushState( { aegisSpa: true }, '', href );
		}

		const page =
			data.page ||
			new URL( href, window.location.href ).searchParams.get( 'page' ) ||
			'';
		updateAegisAdminMenu( page );
		document.dispatchEvent(
			new window.CustomEvent( 'aegis-admin-page-ready', {
				detail: { page, href },
			} )
		);
	}

	/**
	 * @param {string} page Query page slug, e.g. aegis-blocks.
	 */
	function updateAegisAdminMenu( page ) {
		const menu = document.querySelector(
			'#adminmenu li.toplevel_page_aegis-dashboard'
		);

		if ( ! menu ) {
			return;
		}

		menu.querySelectorAll( '.wp-submenu li' ).forEach( function ( item ) {
			const link = item.querySelector( 'a' );

			if ( ! link ) {
				return;
			}

			let itemPage = '';

			try {
				itemPage =
					new URL( link.href, window.location.href ).searchParams.get(
						'page'
					) || '';
			} catch {
				itemPage = '';
			}

			const current = itemPage === page;

			item.classList.toggle( 'current', current );
			link.classList.toggle( 'current', current );

			if ( current ) {
				link.setAttribute( 'aria-current', 'page' );
			} else {
				link.removeAttribute( 'aria-current' );
			}
		} );
	}

	/**
	 * Initialize block toggle suboptions visibility.
	 */
	function initBlockToggleSuboptions() {
		// Handle main block toggle changes
		$( document ).on(
			'change',
			'.aegis-blocks-form .aegis-toggle-main input[type="checkbox"]',
			function () {
				const $checkbox = $( this );
				const name = $checkbox.attr( 'name' );
				// Extract block key from name like "aegis_blocks[modal]"
				const match = name.match( /aegis_blocks\[(\w+)\]/ );

				if ( match ) {
					const blockKey = match[ 1 ];
					const $suboptions = $(
						'.aegis-toggle-suboptions[data-parent="' +
							blockKey +
							'"]'
					);

					if ( $checkbox.is( ':checked' ) ) {
						$suboptions.slideDown( 200 );
					} else {
						$suboptions.slideUp( 200 );
					}
				}
			}
		);
	}

	/**
	 * Initialize sidebar navigation with ARIA support and keyboard nav.
	 */
	function initNavigation() {
		const navItems = document.querySelectorAll( '.aegis-nav-item' );
		// Only top-level integration panels — nested blocks (e.g. Analytics providers) must not use .aegis-settings-section.
		const sections = document.querySelectorAll(
			'.aegis-settings-content > .aegis-settings-section'
		);

		if ( ! navItems.length || ! sections.length ) {
			return;
		}

		// Set ARIA attributes on nav container and items
		const navContainer = navItems[ 0 ] && navItems[ 0 ].parentElement;
		if ( navContainer ) {
			navContainer.setAttribute( 'role', 'tablist' );
			navContainer.setAttribute( 'aria-orientation', 'vertical' );
		}

		navItems.forEach( function ( item ) {
			const targetId = item.getAttribute( 'href' ).substring( 1 );
			item.setAttribute( 'role', 'tab' );
			item.setAttribute( 'aria-controls', targetId );
			item.setAttribute(
				'aria-selected',
				item.classList.contains( 'active' ) ? 'true' : 'false'
			);
			item.setAttribute(
				'tabindex',
				item.classList.contains( 'active' ) ? '0' : '-1'
			);
		} );

		sections.forEach( function ( section ) {
			section.setAttribute( 'role', 'tabpanel' );
			section.setAttribute( 'tabindex', '0' );
		} );

		function activateTab( item ) {
			const targetId = item.getAttribute( 'href' ).substring( 1 );
			const targetSection = document.getElementById( targetId );

			if ( ! targetSection ) {
				return;
			}

			navItems.forEach( function ( nav ) {
				nav.classList.remove( 'active' );
				nav.setAttribute( 'aria-selected', 'false' );
				nav.setAttribute( 'tabindex', '-1' );
			} );
			item.classList.add( 'active' );
			item.setAttribute( 'aria-selected', 'true' );
			item.setAttribute( 'tabindex', '0' );

			sections.forEach( function ( section ) {
				section.classList.remove( 'active' );
			} );
			targetSection.classList.add( 'active' );

			window.history.pushState( null, null, '#' + targetId );
		}

		navItems.forEach( function ( item ) {
			item.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				activateTab( this );
			} );

			item.addEventListener( 'keydown', function ( e ) {
				const index = Array.prototype.indexOf.call( navItems, this );
				let newIndex = -1;

				if ( e.key === 'ArrowDown' || e.key === 'ArrowRight' ) {
					e.preventDefault();
					newIndex = ( index + 1 ) % navItems.length;
				} else if ( e.key === 'ArrowUp' || e.key === 'ArrowLeft' ) {
					e.preventDefault();
					newIndex =
						( index - 1 + navItems.length ) % navItems.length;
				} else if ( e.key === 'Home' ) {
					e.preventDefault();
					newIndex = 0;
				} else if ( e.key === 'End' ) {
					e.preventDefault();
					newIndex = navItems.length - 1;
				}

				if ( newIndex >= 0 ) {
					navItems[ newIndex ].focus();
					activateTab( navItems[ newIndex ] );
				}
			} );
		} );

		// Handle initial hash
		const params = new URLSearchParams( window.location.search );
		let hash = settingsTabHash();

		if ( params.get( 'page' ) === 'aegis-integrations' && hash ) {
			hash = normalizeIntegrationsHash( hash );
		}

		if ( hash ) {
			const targetNav = document.querySelector(
				'.aegis-nav-item[href="#' + hash + '"]'
			);
			if ( targetNav ) {
				activateTab( targetNav );
			}
		}

		// Page tabs keyboard navigation
		initPageTabsKeyboard();
	}

	/**
	 * Initialize keyboard navigation for page-level tabs.
	 */
	function initPageTabsKeyboard() {
		const pageTabs = document.querySelectorAll( '.aegis-page-tab' );
		if ( ! pageTabs.length ) {
			return;
		}

		pageTabs.forEach( function ( tab, index ) {
			tab.setAttribute(
				'tabindex',
				tab.classList.contains( 'active' ) ? '0' : '-1'
			);

			tab.addEventListener( 'keydown', function ( e ) {
				let newIndex = -1;

				if ( e.key === 'ArrowRight' ) {
					e.preventDefault();
					newIndex = ( index + 1 ) % pageTabs.length;
				} else if ( e.key === 'ArrowLeft' ) {
					e.preventDefault();
					newIndex =
						( index - 1 + pageTabs.length ) % pageTabs.length;
				} else if ( e.key === 'Home' ) {
					e.preventDefault();
					newIndex = 0;
				} else if ( e.key === 'End' ) {
					e.preventDefault();
					newIndex = pageTabs.length - 1;
				} else if ( e.key === 'Enter' || e.key === ' ' ) {
					e.preventDefault();
					this.click();
					return;
				}

				if ( newIndex >= 0 ) {
					pageTabs[ newIndex ].focus();
				}
			} );
		} );
	}

	/**
	 * Initialize AJAX save functionality.
	 */
	function initAjaxSave() {
		// Handle Conditional Logic form
		$( document ).on(
			'submit',
			'.aegis-settings-form:not(.aegis-integrations-form):not(.aegis-blocks-form):not(.aegis-general-settings-form):not(.aegis-performance-form)',
			function ( e ) {
			e.preventDefault();

			if ( typeof aegisAdmin === 'undefined' ) {
				return;
			}

			const $form = $( this );
			const $submitBtn = $form.find( 'input[type="submit"]' );
			const originalText = $submitBtn.val();
			$submitBtn.val( aegisAdmin.saving ).prop( 'disabled', true );

			const settings = collectConditionalSettings( $form );

			$.ajax( {
				url: aegisAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'aegis_save_settings',
					nonce: aegisAdmin.nonce,
					settings,
				},
				success( response ) {
					if ( response.success ) {
						showNotice( aegisAdmin.saved, 'success' );
					} else {
						showNotice(
							response.data.message || aegisAdmin.error,
							'error'
						);
					}
				},
				error() {
					showNotice( aegisAdmin.error, 'error' );
				},
				complete() {
					$submitBtn.val( originalText ).prop( 'disabled', false );
				},
			} );
		} );

		// Handle Blocks form
		$( document ).on( 'submit', '.aegis-blocks-form', function ( e ) {
			e.preventDefault();

			if ( typeof aegisAdmin === 'undefined' ) {
				return;
			}

			const $form = $( this );
			const $submitBtn = $form.find( 'input[type="submit"]' );
			const originalText = $submitBtn.val();
			$submitBtn.val( aegisAdmin.saving ).prop( 'disabled', true );

			// Collect blocks settings
			const settings = {};
			$form.find( 'input[type="checkbox"]' ).each( function () {
				const name = $( this ).attr( 'name' );
				const match = name.match( /aegis_blocks\[(\w+)\]/ );

				if ( match ) {
					settings[ match[ 1 ] ] = $( this ).is( ':checked' )
						? '1'
						: '0';
				}
			} );

			$.ajax( {
				url: aegisAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'aegis_save_blocks',
					nonce: aegisAdmin.nonce,
					settings,
				},
				success( response ) {
					if ( response.success ) {
						showNotice( aegisAdmin.saved, 'success' );
					} else {
						showNotice(
							response.data.message || aegisAdmin.error,
							'error'
						);
					}
				},
				error() {
					showNotice( aegisAdmin.error, 'error' );
				},
				complete() {
					$submitBtn.val( originalText ).prop( 'disabled', false );
				},
			} );
		} );

		/**
		 * Collect analytics field values from Connectors provider tabs.
		 *
		 * @param {jQuery} $form Integrations form.
		 * @return {Object} Analytics settings keyed by field name.
		 */
		function collectAnalyticsSettings( $form ) {
			const settings = {};
			const $wrap = $form.find( '[data-aegis-analytics-settings]' );

			if ( ! $wrap.length ) {
				return settings;
			}

			$wrap.find( 'input[type="checkbox"]' ).each( function () {
				if ( $( this ).is( ':disabled' ) ) {
					return;
				}

				const name = $( this ).attr( 'name' );
				const match = name && name.match( /aegis_analytics\[(\w+)\]/ );

				if ( match ) {
					settings[ match[ 1 ] ] = $( this ).is( ':checked' )
						? '1'
						: '0';
				}
			} );

			$wrap
				.find(
					'input[type="text"], input[type="url"], input[type="password"]'
				)
				.each( function () {
					if ( $( this ).is( ':disabled' ) ) {
						return;
					}

					const name = $( this ).attr( 'name' );
					const match =
						name && name.match( /aegis_analytics\[(\w+)\]/ );

					if ( match ) {
						settings[ match[ 1 ] ] = $( this ).val();
					}
				} );

			return settings;
		}

		/**
		 * Collect nested aegis_conditional_logic[group][key] checkboxes.
		 *
		 * @param {jQuery} $form Settings form.
		 * @return {Object} Nested group/key map.
		 */
		function collectConditionalSettings( $form ) {
			const settings = {};

			$form.find( 'input[type="checkbox"]' ).each( function () {
				if ( $( this ).is( ':disabled' ) ) {
					return;
				}

				const name = $( this ).attr( 'name' );
				const match =
					name &&
					name.match( /aegis_conditional_logic\[(\w+)\]\[(\w+)\]/ );

				if ( match ) {
					const group = match[ 1 ];
					const key = match[ 2 ];

					if ( ! settings[ group ] ) {
						settings[ group ] = {};
					}

					settings[ group ][ key ] = $( this ).is( ':checked' )
						? '1'
						: '0';
				}
			} );

			return settings;
		}

		// Handle Integrations form (integration toggles + analytics in one save).
		$( document ).on( 'submit', '.aegis-integrations-form', function ( e ) {
			e.preventDefault();

			if ( typeof aegisAdmin === 'undefined' ) {
				return;
			}

			const $form = $( this );
			const $submitBtn = $form.find( 'input[type="submit"]' ).first();
			const originalText = $submitBtn.val();
			$submitBtn.val( aegisAdmin.saving ).prop( 'disabled', true );

			const integrationSettings = {};
			const patternSettings = {};
			$form.find( 'input[type="checkbox"]' ).each( function () {
				if ( $( this ).is( ':disabled' ) ) {
					return;
				}

				const name = $( this ).attr( 'name' );
				const integrationMatch =
					name && name.match( /aegis_integrations\[(\w+)\]/ );

				if ( integrationMatch ) {
					integrationSettings[ integrationMatch[ 1 ] ] = $( this ).is(
						':checked'
					)
						? '1'
						: '0';
					return;
				}

				const patternMatch =
					name && name.match( /aegis_pattern_control\[(\w+)\]/ );

				if ( patternMatch ) {
					patternSettings[ patternMatch[ 1 ] ] = $( this ).is(
						':checked'
					)
						? '1'
						: '0';
				}
			} );

			const analyticsSettings = collectAnalyticsSettings( $form );
			const conditionalSettings = collectConditionalSettings( $form );

			const requests = [
				$.post( aegisAdmin.ajaxUrl, {
					action: 'aegis_save_integrations',
					nonce: aegisAdmin.nonce,
					settings: integrationSettings,
					pattern_control: patternSettings,
				} ),
			];

			if ( Object.keys( analyticsSettings ).length ) {
				requests.push(
					$.post( aegisAdmin.ajaxUrl, {
						action: 'aegis_save_analytics',
						nonce: aegisAdmin.nonce,
						settings: analyticsSettings,
					} )
				);
			}

			if ( Object.keys( conditionalSettings ).length ) {
				requests.push(
					$.post( aegisAdmin.ajaxUrl, {
						action: 'aegis_save_settings',
						nonce: aegisAdmin.nonce,
						settings: conditionalSettings,
					} )
				);
			}

			$.when
				.apply( $, requests )
				.done( function () {
					const results =
						requests.length === 1
							? [ arguments[ 0 ] ]
							: Array.from( arguments ).map( ( a ) => a[ 0 ] );
					const failed = results.find( ( r ) => ! r || ! r.success );

					if ( failed ) {
						showNotice(
							( failed.data && failed.data.message ) ||
								aegisAdmin.error,
							'error'
						);
					} else {
						const warningResult = results.find(
							( r ) =>
								r &&
								r.data &&
								Array.isArray( r.data.warnings ) &&
								r.data.warnings.length
						);

						if ( warningResult ) {
							showNotice(
								warningResult.data.message +
									' ' +
									warningResult.data.warnings.join( ' ' ),
								'warning'
							);
						} else {
							showNotice( aegisAdmin.saved, 'success' );
						}
					}
				} )
				.fail( function () {
					showNotice( aegisAdmin.error, 'error' );
				} )
				.always( function () {
					$submitBtn.val( originalText ).prop( 'disabled', false );
				} );
		} );

		// Handle General Settings form
		$( document ).on( 'submit', '.aegis-general-settings-form', function ( e ) {
			e.preventDefault();

			if ( typeof aegisAdmin === 'undefined' ) {
				return;
			}

			const $form = $( this );
			const $submitBtn = $form.find( 'input[type="submit"]' );
			const originalText = $submitBtn.val();
			$submitBtn.val( aegisAdmin.saving ).prop( 'disabled', true );

			// Collect general settings
			const settings = {};
			$form.find( 'input[type="checkbox"]' ).each( function () {
				const name = $( this ).attr( 'name' );
				const match = name.match( /aegis_settings\[(\w+)\]/ );

				if ( match ) {
					settings[ match[ 1 ] ] = $( this ).is( ':checked' )
						? '1'
						: '0';
				}
			} );

			$.ajax( {
				url: aegisAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'aegis_save_general_settings',
					nonce: aegisAdmin.nonce,
					settings,
					removeData: $form
						.find(
							'input[name="aegis_remove_data_on_uninstall"]'
						)
						.is( ':checked' )
						? '1'
						: '0',
				},
				success( response ) {
					if ( response.success ) {
						showNotice( aegisAdmin.saved, 'success' );
					} else {
						showNotice(
							response.data.message || aegisAdmin.error,
							'error'
						);
					}
				},
				error() {
					showNotice( aegisAdmin.error, 'error' );
				},
				complete() {
					$submitBtn.val( originalText ).prop( 'disabled', false );
				},
			} );
		} );

		$( document ).on( 'submit', '.aegis-performance-form', function ( e ) {
			e.preventDefault();

			if ( typeof aegisAdmin === 'undefined' ) {
				return;
			}

			const $form = $( this );
			const $submitBtn = $form.find( 'input[type="submit"]' );
			const originalText = $submitBtn.val();
			$submitBtn.val( aegisAdmin.saving ).prop( 'disabled', true );

			const settings = {};
			$form.find( 'input[type="checkbox"]' ).each( function () {
				const name = $( this ).attr( 'name' );
				const match = name.match( /aegis_blocks\[(\w+)\]/ );

				if ( match ) {
					settings[ match[ 1 ] ] = $( this ).is( ':checked' )
						? '1'
						: '0';
				}
			} );

			$.ajax( {
				url: aegisAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'aegis_save_performance',
					nonce: aegisAdmin.nonce,
					settings,
				},
				success( response ) {
					if ( response.success ) {
						showNotice( aegisAdmin.saved, 'success' );
					} else {
						showNotice(
							response.data.message || aegisAdmin.error,
							'error'
						);
					}
				},
				error() {
					showNotice( aegisAdmin.error, 'error' );
				},
				complete() {
					$submitBtn.val( originalText ).prop( 'disabled', false );
				},
			} );
		} );

		// Handle Code Snippets settings form
		$( document ).on( 'submit', '.aegis-snippets-settings-form', function ( e ) {
			e.preventDefault();

			if ( typeof aegisAdmin === 'undefined' ) {
				return;
			}

			const $form = $( this );
			const $submitBtn = $form.find( 'input[type="submit"]' );
			const originalText = $submitBtn.val();
			$submitBtn.val( aegisAdmin.saving ).prop( 'disabled', true );

			const enabledLocations = [];
			$form
				.find( 'input[name="enabled_locations[]"]:checked' )
				.each( function () {
					enabledLocations.push( $( this ).val() );
				} );

			const settings = {
				php_enabled: $form.find( '[name="php_enabled"]' ).is( ':checked' )
					? '1'
					: '0',
				preview_hooks: $form
					.find( '[name="preview_hooks"]' )
					.is( ':checked' )
					? '1'
					: '0',
				preview_snippets: $form
					.find( '[name="preview_snippets"]' )
					.is( ':checked' )
					? '1'
					: '0',
				preview_admin_bar: $form
					.find( '[name="preview_admin_bar"]' )
					.is( ':checked' )
					? '1'
					: '0',
				enabled_locations: enabledLocations,
			};

			$.ajax( {
				url: aegisAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'aegis_save_snippets_settings',
					nonce: aegisAdmin.nonce,
					settings,
				},
				success( response ) {
					if ( response.success ) {
						showNotice(
							response.data.message || aegisAdmin.saved,
							'success'
						);
					} else {
						showNotice(
							response.data.message || aegisAdmin.error,
							'error'
						);
					}
				},
				error() {
					showNotice( aegisAdmin.error, 'error' );
				},
				complete() {
					$submitBtn.val( originalText ).prop( 'disabled', false );
				},
			} );
		} );

		// Handle BunnyCDN API settings save
		$( document ).on( 'click', '.aegis-save-bunnycdn', function ( e ) {
			e.preventDefault();

			if ( typeof aegisAdmin === 'undefined' ) {
				return;
			}

			const $btn = $( this );

			if ( $btn.prop( 'disabled' ) ) {
				return;
			}

			if (
				! connectorSecretFilled(
					$( 'input[name="aegis_bunnycdn[api_key]"]' ).val()
				)
			) {
				syncConnectorTestButtons();
				return;
			}

			setConnectorButtonBusy( $btn, true, aegisAdmin.saving );

			// Collect BunnyCDN settings
			const settings = {};
			const maskChar = '\u2022';

			$( '.aegis-bunnycdn-field' ).each( function () {
				const name = $( this ).attr( 'name' );
				const match = name.match( /aegis_bunnycdn\[(\w+)\]/ );

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
					action: 'aegis_save_bunnycdn',
					nonce: aegisAdmin.nonce,
					settings,
				},
				success( response ) {
					if ( response.success ) {
						showNotice( aegisAdmin.saved, 'success' );
					} else {
						showNotice(
							response.data.message || aegisAdmin.error,
							'error'
						);
					}
				},
				error() {
					showNotice( aegisAdmin.error, 'error' );
				},
				complete() {
					setConnectorButtonBusy( $btn, false );
					syncConnectorTestButtons();
				},
			} );
		} );

		// Handle BunnyCDN test connection
		$( document ).on( 'click', '.aegis-test-bunnycdn', function ( e ) {
			e.preventDefault();

			if ( typeof aegisAdmin === 'undefined' ) {
				return;
			}

			const $btn = $( this );

			if ( $btn.prop( 'disabled' ) ) {
				return;
			}

			if (
				! connectorSecretFilled(
					$( 'input[name="aegis_bunnycdn[api_key]"]' ).val()
				)
			) {
				syncConnectorTestButtons();
				return;
			}

			setConnectorButtonBusy( $btn, true, 'Testing...' );

			$.ajax( {
				url: aegisAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'aegis_test_bunnycdn',
					nonce: aegisAdmin.nonce,
				},
				success( response ) {
					if ( response.success ) {
						showNotice(
							response.data.message || aegisAdmin.saved,
							'success'
						);
					} else {
						showNotice(
							response.data.message || aegisAdmin.error,
							'error'
						);
					}
				},
				error() {
					showNotice( aegisAdmin.error, 'error' );
				},
				complete() {
					setConnectorButtonBusy( $btn, false );
					syncConnectorTestButtons();
				},
			} );
		} );
	}

	/**
	 * Main integration checkbox name for a stacked plugin.
	 *
	 * @param {string} key Registry integration key.
	 * @return {string} Input name.
	 */
	function integrationToggleName( key ) {
		return 'aegis_integrations[' + key + ']';
	}

	/**
	 * Lock extras when the parent integration is off, the plugin is inactive, or Pro is required.
	 *
	 * @param {jQuery} $stack Plugin stack wrapper.
	 */
	function syncPluginStackExtras( $stack ) {
		const key = String( $stack.data( 'integration-key' ) || '' );
		const $main = key
			? $stack.find(
					'input[type="checkbox"][name="' +
						integrationToggleName( key ) +
						'"]'
			  )
			: $();
		const pluginDisabled = ! $main.length || $main.is( ':disabled' );
		const parentOn =
			$main.length && $main.is( ':checked' ) && ! pluginDisabled;

		$stack.find( 'input[type="checkbox"]' ).each( function () {
			const $cb = $( this );
			const name = $cb.attr( 'name' ) || '';

			if ( key && name === integrationToggleName( key ) ) {
				return;
			}

			const $card = $cb.closest( '.aegis-toggle-card' );
			const locked =
				! parentOn ||
				pluginDisabled ||
				$card.hasClass( 'aegis-pro-feature' );

			$cb.prop( 'disabled', locked );

			if ( locked ) {
				$cb.prop( 'checked', false );
			}

			$card.toggleClass( 'aegis-toggle-disabled', locked );
		} );
	}

	/**
	 * Sync extras for every stacked plugin on Integrations.
	 */
	function syncAllPluginStackExtras() {
		$( '.aegis-plugin-stack' ).each( function () {
			syncPluginStackExtras( $( this ) );
		} );
	}

	/**
	 * Check or uncheck enabled toggles in a settings section.
	 *
	 * @param {jQuery} $section      Active settings section.
	 * @param {boolean} proInstalled Whether Aegis Pro is installed.
	 * @param {boolean} checked      Target checked state.
	 */
	function setEnabledSectionToggles( $section, proInstalled, checked ) {
		const selector = proInstalled
			? '.aegis-toggle input[type="checkbox"]:not(:disabled)'
			: '.aegis-toggle-card:not(.aegis-pro-feature) .aegis-toggle input[type="checkbox"]:not(:disabled)';

		$section.find( selector ).each( function () {
			const $cb = $( this );

			if (
				checked &&
				$cb.closest( '.aegis-toggle-skip-bulk' ).length
			) {
				return;
			}

			$cb.prop( 'checked', checked );
		} );
	}

	/**
	 * Sync extras after the parent integration toggle changes.
	 */
	function initPluginStackExtras() {
		syncAllPluginStackExtras();

		$( document ).on(
			'change',
			'.aegis-plugin-stack input[type="checkbox"][name^="aegis_integrations["]',
			function () {
				const $stack = $( this ).closest( '.aegis-plugin-stack' );
				const key = String( $stack.data( 'integration-key' ) || '' );

				if (
					key &&
					$( this ).attr( 'name' ) === integrationToggleName( key )
				) {
					syncPluginStackExtras( $stack );
				}
			}
		);
	}

	/**
	 * Initialize bulk enable/disable actions.
	 * If Pro is installed, toggles all features. Otherwise, only toggles free features.
	 */
	function initBulkActions() {
		$( document ).on( 'click', '.aegis-bulk-enable', function ( e ) {
			e.preventDefault();
			const $section = $( this ).closest( '.aegis-settings-section' );
			const proInstalled =
				$( this ).data( 'pro-installed' ) === true ||
				$( this ).data( 'pro-installed' ) === 'true';

			setEnabledSectionToggles( $section, proInstalled, true );
			$section.find( '.aegis-plugin-stack' ).each( function () {
				syncPluginStackExtras( $( this ) );
			} );
			setEnabledSectionToggles( $section, proInstalled, true );
		} );

		$( document ).on( 'click', '.aegis-bulk-disable', function ( e ) {
			e.preventDefault();
			const $section = $( this ).closest( '.aegis-settings-section' );
			const proInstalled =
				$( this ).data( 'pro-installed' ) === true ||
				$( this ).data( 'pro-installed' ) === 'true';

			setEnabledSectionToggles( $section, proInstalled, false );
			$section.find( '.aegis-plugin-stack' ).each( function () {
				syncPluginStackExtras( $( this ) );
			} );
		} );
	}

	/**
	 * Initialize search/filter functionality.
	 */
	function initSearch() {
		$( document ).on( 'input', '.aegis-search-input', function () {
			const query = $( this ).val().toLowerCase().trim();
			const $cards = $( '.aegis-toggle-card' );
			const $hookRows = $( '.aegis-hooks-row' );
			const $hookListRows = $( '.aegis-hooks-list tbody tr' );
			const $snippetItems = $( '.aegis-snippets-item' );
			const $modalRows = $( '.aegis-modals-list tbody tr' );

			if ( ! query ) {
				$cards.show();
				$hookRows.show();
				$hookListRows.show();
				$snippetItems.show();
				$modalRows.show();
				$( '.aegis-hooks-group' ).show();
				$( '.aegis-settings-content > .aegis-settings-section' ).show();
				$( '.aegis-nav-item' ).removeClass( 'has-results no-results' );
				return;
			}

			$cards.each( function () {
				const $card = $( this );
				const title = $card.find( 'h3' ).text().toLowerCase();
				const desc = $card.find( 'p' ).text().toLowerCase();

				if ( title.includes( query ) || desc.includes( query ) ) {
					$card.show();
				} else {
					$card.hide();
				}
			} );

			$hookRows.each( function () {
				const $row = $( this );
				$row.toggle( $row.text().toLowerCase().includes( query ) );
			} );

			$( '.aegis-hooks-group' ).each( function () {
				const visibleRows = $( this ).find(
					'.aegis-hooks-row:visible'
				).length;
				$( this ).toggle( visibleRows > 0 );
			} );

			$snippetItems.each( function () {
				const $item = $( this );
				const text = $item.text().toLowerCase();
				$item.toggle( text.includes( query ) );
			} );

			$modalRows.each( function () {
				const $row = $( this );
				$row.toggle( $row.text().toLowerCase().includes( query ) );
			} );

			$hookListRows.each( function () {
				const $row = $( this );
				$row.toggle( $row.text().toLowerCase().includes( query ) );
			} );

			// Show/hide sections based on visible cards
			$( '.aegis-settings-content > .aegis-settings-section' ).each(
				function () {
					const $section = $( this );
					const visibleCards = $section.find(
						'.aegis-toggle-card:visible'
					).length;
					const sectionId = $section.attr( 'id' );
					const $navItem = $(
						'.aegis-nav-item[href="#' + sectionId + '"]'
					);

					if ( visibleCards > 0 ) {
						$section.show();
						$navItem
							.addClass( 'has-results' )
							.removeClass( 'no-results' );
					} else {
						$navItem
							.addClass( 'no-results' )
							.removeClass( 'has-results' );
					}
				}
			);
		} );
	}

	/**
	 * Initialize export/import functionality.
	 */
	function initExportImport() {
		// Export settings
		$( document ).on( 'click', '.aegis-export-btn', function ( e ) {
			e.preventDefault();

			if ( typeof aegisAdmin === 'undefined' ) {
				return;
			}

			const $btn = $( this );
			const originalText = $btn.text();
			$btn.text( aegisAdmin.exporting || 'Exporting...' ).prop(
				'disabled',
				true
			);

			$.ajax( {
				url: aegisAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'aegis_export_settings',
					nonce: aegisAdmin.nonce,
				},
				success( response ) {
					if ( response.success ) {
						// Create and download file
						const blob = new Blob(
							[ JSON.stringify( response.data, null, 2 ) ],
							{ type: 'application/json' }
						);
						const url = URL.createObjectURL( blob );
						const a = document.createElement( 'a' );
						a.href = url;
						a.download =
							'aegis-settings-' +
							new Date().toISOString().slice( 0, 10 ) +
							'.json';
						document.body.appendChild( a );
						a.click();
						document.body.removeChild( a );
						URL.revokeObjectURL( url );
						showNotice(
							aegisAdmin.exported ||
								'Settings exported successfully.',
							'success'
						);
					} else {
						showNotice(
							response.data.message || aegisAdmin.error,
							'error'
						);
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

		// Import settings - trigger file input
		$( document ).on( 'click', '.aegis-import-btn', function ( e ) {
			e.preventDefault();
			$( '#aegis-import-file' ).trigger( 'click' );
		} );

		// Handle file selection
		$( document ).on( 'change', '#aegis-import-file', function ( e ) {
			const file = e.target.files[ 0 ];
			if ( ! file ) {
				return;
			}

			if ( file.type !== 'application/json' ) {
				showNotice(
					aegisAdmin.invalidFile ||
						'Please select a valid JSON file.',
					'error'
				);
				return;
			}

			const reader = new window.FileReader();
			reader.onload = function ( event ) {
				try {
					const settings = JSON.parse( event.target.result );
					importSettings( settings );
				} catch {
					showNotice(
						aegisAdmin.invalidFile || 'Invalid JSON file.',
						'error'
					);
				}
			};
			reader.readAsText( file );

			// Reset file input
			$( this ).val( '' );
		} );
	}

	/**
	 * Import settings via AJAX.
	 * @param {Object} settings Settings object parsed from the import file.
	 */
	function importSettings( settings ) {
		if ( typeof aegisAdmin === 'undefined' ) {
			return;
		}

		$.ajax( {
			url: aegisAdmin.ajaxUrl,
			type: 'POST',
			data: {
				action: 'aegis_import_settings',
				nonce: aegisAdmin.nonce,
				settings: JSON.stringify( settings ),
			},
			success( response ) {
				if ( response.success ) {
					showNotice(
						aegisAdmin.imported ||
							'Settings imported successfully. Reloading...',
						'success'
					);
					setTimeout( function () {
						reloadAegisAdminPage();
					}, 1500 );
				} else {
					showNotice(
						response.data.message || aegisAdmin.error,
						'error'
					);
				}
			},
			error() {
				showNotice( aegisAdmin.error, 'error' );
			},
		} );
	}

	/**
	 * Reset settings to defaults.
	 */
	$( document ).on( 'click', '.aegis-reset-settings', function ( e ) {
		e.preventDefault();
		const group = $( this ).data( 'group' );
		if ( ! group ) {
			return;
		}

		if (
			// eslint-disable-next-line no-alert
			! window.confirm(
				aegisAdmin.resetConfirm ||
					'Are you sure you want to reset these settings to defaults? This cannot be undone.'
			)
		) {
			return;
		}

		$.ajax( {
			url: aegisAdmin.ajaxUrl,
			type: 'POST',
			data: {
				action: 'aegis_reset_settings',
				nonce: aegisAdmin.nonce,
				group,
			},
			success( response ) {
				if ( response.success ) {
					showNotice(
						response.data.message || 'Settings reset to defaults.',
						'success'
					);
					setTimeout( function () {
						reloadAegisAdminPage();
					}, 1500 );
				} else {
					showNotice(
						response.data.message || aegisAdmin.error,
						'error'
					);
				}
			},
			error() {
				showNotice( aegisAdmin.error, 'error' );
			},
		} );
	} );

	$( document ).on( 'click', '.aegis-purge-data', function ( e ) {
		e.preventDefault();

		const typed = window.prompt(
			aegisAdmin.purgePrompt ||
				'This permanently deletes all Aegis data. Type DELETE to confirm.'
		);

		if ( typed !== 'DELETE' ) {
			return;
		}

		$.ajax( {
			url: aegisAdmin.ajaxUrl,
			type: 'POST',
			data: {
				action: 'aegis_purge_data',
				nonce: aegisAdmin.nonce,
				confirm: 'DELETE',
			},
			success( response ) {
				if ( response.success ) {
					showNotice(
						response.data.message ||
							'All Aegis data has been deleted.',
						'success'
					);
					setTimeout( function () {
						reloadAegisAdminPage();
					}, 1500 );
				} else {
					showNotice(
						response.data.message || aegisAdmin.error,
						'error'
					);
				}
			},
			error() {
				showNotice( aegisAdmin.error, 'error' );
			},
		} );
	} );

	/**
	 * Show a notice message.
	 * @param {string} message Notice text to display.
	 * @param {string} type    Notice type (success or error).
	 */
	function showNotice( message, type ) {
		// Remove existing notices
		$( '.aegis-notice' ).remove();

		const $notice = $(
			'<div class="aegis-notice aegis-notice-' +
				type +
				'">' +
				message +
				'</div>'
		);
		const $anchor = $(
			'.aegis-admin-page-header, .aegis-settings-header'
		).first();

		if ( $anchor.length ) {
			$anchor.after( $notice );
		} else {
			$( '.aegis-settings-wrap' ).prepend( $notice );
		}

		// Auto-hide after 3 seconds
		setTimeout( function () {
			$notice.fadeOut( 300, function () {
				$( this ).remove();
			} );
		}, 3000 );
	}

	if ( window.aegisVisibilityPresetsAdmin ) {
		const presetConfig = window.aegisVisibilityPresetsAdmin;

		$( document ).on( 'click', '#aegis-save-preset', function () {
			const name = $( '#aegis_preset_name' ).val();
			const visibility = $( '#aegis_preset_visibility' ).val();

			if ( ! name || ! visibility ) {
				showNotice( 'Name and visibility JSON are required.', 'error' );
				return;
			}

			$.post( presetConfig.ajaxUrl, {
				action: 'aegis_save_visibility_preset',
				nonce: presetConfig.nonce,
				name,
				visibility,
			} ).done( function ( response ) {
				if ( response.success ) {
					showNotice( 'Preset saved.', 'success' );
					reloadAegisAdminPage();
				} else {
					showNotice( 'Could not save preset.', 'error' );
				}
			} );
		} );

		$( document ).on( 'click', '.aegis-delete-preset', function () {
			const id = $( this ).data( 'id' );

			$.post( presetConfig.ajaxUrl, {
				action: 'aegis_delete_visibility_preset',
				nonce: presetConfig.nonce,
				id,
			} ).done( function ( response ) {
				if ( response.success ) {
					showNotice( 'Preset deleted.', 'success' );
					reloadAegisAdminPage();
				}
			} );
		} );
	}
} )( jQuery );
