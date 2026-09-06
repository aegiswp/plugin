/**
 * Modal block frontend: open/close, focus trap, and gated triggers.
 *
 * @package Aegis\Plugin
 */
( function () {
	'use strict';

	var INIT_ATTR = 'data-aegis-modal-init';

	function focusable( dialog ) {
		return Array.from(
			dialog.querySelectorAll(
				'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])'
			)
		);
	}

	function canShow( config ) {
		if ( ! config.showOnce ) {
			return true;
		}

		var key = 'aegis-modal-' + config.modalId + '-shown';
		var stored = localStorage.getItem( key );

		if ( ! stored ) {
			return true;
		}

		try {
			var seen = new Date( stored );
			var expiry = new Date( seen );
			expiry.setDate( expiry.getDate() + config.showOnceExpiry );

			if ( new Date() >= expiry ) {
				localStorage.removeItem( key );
				return true;
			}

			return false;
		} catch ( err ) {
			localStorage.removeItem( key );
			return true;
		}
	}

	function markShown( config ) {
		if ( ! config.showOnce ) {
			return;
		}

		localStorage.setItem( 'aegis-modal-' + config.modalId + '-shown', new Date().toISOString() );
	}

	function hasProDeviceClasses( wrapper ) {
		return wrapper.classList.contains( 'aegis-modal--hide-desktop' )
			|| wrapper.classList.contains( 'aegis-modal--hide-tablet' )
			|| wrapper.classList.contains( 'aegis-modal--hide-mobile' );
	}

	function clearAutoClose( instance ) {
		if ( instance.autoCloseTimer ) {
			clearTimeout( instance.autoCloseTimer );
			instance.autoCloseTimer = null;
		}
	}

	function openModal( instance ) {
		var dialog = instance.dialog;
		var config = instance.config;

		if ( ! dialog.hasAttribute( 'hidden' ) ) {
			return;
		}

		if ( config.deviceVisibility.indexOf( currentDevice() ) === -1 ) {
			return;
		}

		if ( ! canShow( config ) ) {
			return;
		}

		instance.previousFocus = document.activeElement;
		dialog.removeAttribute( 'hidden' );
		dialog.setAttribute( 'aria-hidden', 'false' );

		if ( config.preventScroll ) {
			document.body.style.overflow = 'hidden';
		}

		requestAnimationFrame( function () {
			var items = focusable( dialog );
			if ( items.length > 0 ) {
				items[ 0 ].focus();
			} else {
				dialog.focus();
			}
		} );

		if ( instance.trigger ) {
			instance.trigger.setAttribute( 'aria-expanded', 'true' );
		}

		markShown( config );

		instance.wrapper.dispatchEvent(
			new CustomEvent( 'aegis-modal-opened', {
				bubbles: true,
				detail: { modalId: config.modalId },
			} )
		);

		clearAutoClose( instance );

		if ( config.autoCloseDelay > 0 ) {
			instance.autoCloseTimer = setTimeout( function () {
				instance.autoCloseTimer = null;
				closeModal( instance );
			}, config.autoCloseDelay );
		}
	}

	function closeModal( instance ) {
		var dialog = instance.dialog;
		var config = instance.config;

		clearAutoClose( instance );

		dialog.setAttribute( 'hidden', '' );
		dialog.setAttribute( 'aria-hidden', 'true' );

		if ( config.preventScroll ) {
			document.body.style.overflow = '';
		}

		if ( instance.trigger ) {
			instance.trigger.setAttribute( 'aria-expanded', 'false' );
		}

		if ( config.returnFocus && instance.previousFocus ) {
			instance.previousFocus.focus();
		}

		instance.wrapper.dispatchEvent(
			new CustomEvent( 'aegis-modal-closed', {
				bubbles: true,
				detail: { modalId: config.modalId },
			} )
		);
	}

	function onKeydown( event, instance ) {
		var dialog = instance.dialog;
		var config = instance.config;

		if ( event.key === 'Escape' && config.closeEsc ) {
			event.preventDefault();
			closeModal( instance );
			return;
		}

		if ( event.key !== 'Tab' || ! config.focusTrap ) {
			return;
		}

		var items = focusable( dialog );

		if ( items.length === 0 ) {
			return;
		}

		var first = items[ 0 ];
		var last = items[ items.length - 1 ];

		if ( event.shiftKey ) {
			if ( document.activeElement === first ) {
				event.preventDefault();
				last.focus();
			}
			return;
		}

		if ( document.activeElement === last ) {
			event.preventDefault();
			first.focus();
		}
	}

	function currentDevice() {
		var width = window.innerWidth;

		if ( width >= 1024 ) {
			return 'desktop';
		}

		if ( width >= 768 ) {
			return 'tablet';
		}

		return 'mobile';
	}

	function parseConfig( wrapper ) {
		return {
			modalId: wrapper.getAttribute( 'data-modal-id' ) || '',
			modalType: wrapper.getAttribute( 'data-modal-type' ) || 'popup',
			triggerType: wrapper.getAttribute( 'data-trigger-type' ) || 'button',
			animation: wrapper.getAttribute( 'data-animation' ) || 'fade',
			closeEsc: wrapper.getAttribute( 'data-close-esc' ) !== 'false',
			closeOverlay: wrapper.getAttribute( 'data-close-overlay' ) !== 'false',
			preventScroll: wrapper.getAttribute( 'data-prevent-scroll' ) !== 'false',
			focusTrap: wrapper.getAttribute( 'data-focus-trap' ) !== 'false',
			returnFocus: wrapper.getAttribute( 'data-return-focus' ) !== 'false',
			scrollTriggerPercent: parseInt( wrapper.getAttribute( 'data-scroll-trigger-percent' ) || '50', 10 ),
			scrollTriggerOnce: wrapper.getAttribute( 'data-scroll-trigger-once' ) !== 'false',
			exitIntentSensitivity: parseInt( wrapper.getAttribute( 'data-exit-intent-sensitivity' ) || '20', 10 ),
			exitIntentDelay: parseInt( wrapper.getAttribute( 'data-exit-intent-delay' ) || '0', 10 ),
			timedTriggerDelay: parseInt( wrapper.getAttribute( 'data-timed-trigger-delay' ) || '5000', 10 ),
			autoCloseDelay: parseInt( wrapper.getAttribute( 'data-auto-close-delay' ) || '0', 10 ),
			showOnce: wrapper.getAttribute( 'data-show-once' ) === 'true',
			showOnceExpiry: parseInt( wrapper.getAttribute( 'data-show-once-expiry' ) || '7', 10 ),
			deviceVisibility: ( wrapper.getAttribute( 'data-device-visibility' ) || 'desktop,tablet,mobile' )
				.split( ',' )
				.map( function ( device ) {
					return device.trim();
				} )
				.filter( Boolean ),
		};
	}

	function bindAutomaticTriggers( instance ) {
		var config = instance.config;

		if ( config.triggerType === 'scroll' ) {
			var scrolled = false;
			var onScroll = function () {
				if ( scrolled && config.scrollTriggerOnce ) {
					return;
				}

				var range = document.documentElement.scrollHeight - window.innerHeight;

				if ( range <= 0 ) {
					return;
				}

				var percent = ( window.scrollY / range ) * 100;

				if ( percent >= config.scrollTriggerPercent ) {
					scrolled = true;
					openModal( instance );
				}
			};

			window.addEventListener( 'scroll', onScroll, { passive: true } );
		}

		if ( config.triggerType === 'exit-intent' ) {
			var exited = false;
			document.addEventListener( 'mouseout', function ( event ) {
				if ( exited ) {
					return;
				}

				if ( event.clientY <= config.exitIntentSensitivity && event.relatedTarget === null ) {
					exited = true;
					if ( config.exitIntentDelay > 0 ) {
						setTimeout( function () {
							openModal( instance );
						}, config.exitIntentDelay );
					} else {
						openModal( instance );
					}
				}
			} );
		}

		if ( config.triggerType === 'timed' ) {
			setTimeout( function () {
				openModal( instance );
			}, config.timedTriggerDelay );
		}
	}

	function initWrapper( wrapper ) {
		if ( wrapper.hasAttribute( INIT_ATTR ) ) {
			return;
		}

		wrapper.setAttribute( INIT_ATTR, '' );

		var config = parseConfig( wrapper );
		var dialog = wrapper.querySelector( '.aegis-modal' );
		var trigger = wrapper.querySelector( '.aegis-modal-trigger' );

		if ( ! dialog ) {
			return;
		}

		if ( ! hasProDeviceClasses( wrapper ) && config.deviceVisibility.indexOf( currentDevice() ) === -1 ) {
			wrapper.style.display = 'none';
			return;
		}

		var instance = {
			wrapper: wrapper,
			dialog: dialog,
			trigger: trigger,
			closeButtons: wrapper.querySelectorAll( '[data-close-modal]' ),
			config: config,
			previousFocus: null,
			autoCloseTimer: null,
		};

		if ( trigger ) {
			trigger.addEventListener( 'click', function () {
				openModal( instance );
			} );
		}

		instance.closeButtons.forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				closeModal( instance );
			} );
		} );

		dialog.addEventListener( 'keydown', function ( event ) {
			onKeydown( event, instance );
		} );

		wrapper.addEventListener( 'aegis-modal-open', function () {
			openModal( instance );
		} );

		wrapper.addEventListener( 'aegis-modal-close', function () {
			closeModal( instance );
		} );

		bindAutomaticTriggers( instance );
	}

	function init() {
		document.querySelectorAll( '.aegis-modal-wrapper' ).forEach( initWrapper );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
