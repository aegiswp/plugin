/**
 * Shared Aegis code editor (CodeMirror via wp.codeEditor).
 *
 * @package Aegis\Admin
 */
( function ( wp ) {
	'use strict';

	if ( ! wp || ! wp.codeEditor ) {
		return;
	}

	const editors = {};

	const MIME_BY_TYPE = {
		css: 'text/css',
		js: 'application/javascript',
		content: 'text/html',
		html: 'text/html',
		php: 'application/x-httpd-php',
	};

	function starters() {
		return ( window.aegisSnippetEditor && window.aegisSnippetEditor.starters ) || {};
	}

	function normalizeStarter( text ) {
		return ( text || '' ).replace( /\r\n/g, '\n' ).trim();
	}

	function isStarterOrEmpty( text ) {
		const current = normalizeStarter( text );

		if ( current === '' ) {
			return true;
		}

		const all = starters();
		return Object.keys( all ).some( function ( key ) {
			return current === normalizeStarter( all[ key ] );
		} );
	}

	function applyStarter( cm, type ) {
		if ( ! cm ) {
			return;
		}

		const next = starters()[ type ];

		if ( typeof next !== 'string' ) {
			return;
		}

		if ( ! isStarterOrEmpty( cm.getValue() ) ) {
			return;
		}

		cm.setValue( next );

		if ( type === 'php' ) {
			cm.setCursor( { line: 2, ch: 0 } );
		}
	}

	function updateTypeBadge( type ) {
		const badge = document.getElementById( 'aegis-snippet-type-badge' );
		const badges = ( window.aegisSnippetEditor && window.aegisSnippetEditor.typeBadges ) || {};

		if ( badge ) {
			badge.textContent = badges[ type ] || type;
		}
	}

	let phpLintTimer = null;

	function getType( config ) {
		if ( config.typeInputName ) {
			const checked = document.querySelector( 'input[name="' + config.typeInputName + '"]:checked' );
			if ( checked ) {
				return checked.value;
			}
		}

		if ( config.typeSelectId ) {
			const select = document.getElementById( config.typeSelectId );
			if ( select ) {
				return select.value;
			}
		}

		return 'content';
	}

	function onTypeChange( config, handler ) {
		if ( config.typeInputName ) {
			document.querySelectorAll( 'input[name="' + config.typeInputName + '"]' ).forEach( function ( input ) {
				input.addEventListener( 'change', handler );
			} );
			return;
		}

		if ( config.typeSelectId ) {
			const select = document.getElementById( config.typeSelectId );
			if ( select ) {
				select.addEventListener( 'change', handler );
			}
		}
	}

	function phpLintAsync( text, updateLinting, options, cm ) {
		const editorConfig = window.aegisSnippetEditor || {};

		if ( typeof updateLinting !== 'function' || ! editorConfig.ajaxUrl || ! editorConfig.lintNonce ) {
			if ( typeof updateLinting === 'function' ) {
				updateLinting( [] );
			}
			return;
		}

		window.clearTimeout( phpLintTimer );
		phpLintTimer = window.setTimeout( function () {
			const body = new window.URLSearchParams();
			body.append( 'action', 'aegis_lint_php' );
			body.append( '_ajax_nonce', editorConfig.lintNonce );
			body.append( 'code', text );

			window.fetch( editorConfig.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
				body: body.toString(),
			} ).then( function ( response ) {
				return response.json();
			} ).then( function ( payload ) {
				const errors = ( payload && payload.success && payload.data && payload.data.errors ) ? payload.data.errors : [];
				const annotations = errors.map( function ( error ) {
					const line = Math.max( 0, ( parseInt( error.line, 10 ) || 1 ) - 1 );
					const CM = ( window.wp && window.wp.CodeMirror ) ? window.wp.CodeMirror : window.CodeMirror;
					return {
						from: CM.Pos( line, 0 ),
						to: CM.Pos( line, 200 ),
						message: error.message || 'Parse error',
						severity: error.severity || 'error',
					};
				} );
				updateLinting( annotations );
			} ).catch( function () {
				updateLinting( [] );
			} );
		}, 400 );
	}

	function lintOptionForType( type ) {
		if ( type === 'php' ) {
			return {
				getAnnotations: phpLintAsync,
				async: true,
			};
		}

		return true;
	}

	function applyMode( instance, type ) {
		if ( ! instance || ! instance.codemirror ) {
			return;
		}

		const cm = instance.codemirror;
		const mime = MIME_BY_TYPE[ type ] || 'text/html';

		cm.setOption( 'mode', mime );
		cm.setOption( 'lint', lintOptionForType( type ) );
		cm.setOption( 'gutters', [ 'CodeMirror-lint-markers', 'CodeMirror-linenumbers' ] );
		cm.setOption( 'matchBrackets', true );
		cm.setOption( 'autoCloseBrackets', true );
		cm.setOption( 'indentUnit', 4 );
		cm.setOption( 'tabSize', 4 );

		window.setTimeout( function () {
			cm.refresh();
			if ( typeof cm.performLint === 'function' ) {
				cm.performLint();
			}
		}, 50 );
	}

	function sniffType( text ) {
		const trimmed = ( text || '' ).trim();

		if ( trimmed.indexOf( '<?php' ) === 0 || trimmed.indexOf( '<?=' ) === 0 ) {
			return 'php';
		}

		if ( /<\/?[a-z][\s\S]*>/i.test( trimmed ) ) {
			return 'content';
		}

		if ( trimmed.indexOf( '{' ) !== -1 && trimmed.indexOf( ':' ) !== -1 && trimmed.indexOf( '<' ) === -1 ) {
			return 'css';
		}

		return '';
	}

	function showTypeHint( suggested ) {
		const hint = document.getElementById( 'aegis-snippet-type-hint' );
		const i18n = ( window.aegisSnippetEditor && window.aegisSnippetEditor.i18n ) || {};

		if ( ! hint || ! suggested ) {
			return;
		}

		const messages = {
			php: i18n.suggestPhp,
			css: i18n.suggestCss,
			content: i18n.suggestContent,
		};

		hint.innerHTML = '';
		hint.appendChild( document.createTextNode( messages[ suggested ] || '' ) );

		const switchBtn = document.createElement( 'button' );
		switchBtn.type = 'button';
		switchBtn.className = 'button button-small';
		switchBtn.textContent = i18n.switchType || 'Switch type';
		switchBtn.addEventListener( 'click', function () {
			const radio = document.querySelector( 'input[name="snippet_type"][value="' + suggested + '"]' );
			if ( radio ) {
				radio.checked = true;
				radio.dispatchEvent( new Event( 'change', { bubbles: true } ) );
			}
			hint.hidden = true;
		} );

		const dismiss = document.createElement( 'button' );
		dismiss.type = 'button';
		dismiss.className = 'button-link';
		dismiss.textContent = i18n.dismiss || 'Dismiss';
		dismiss.addEventListener( 'click', function () {
			hint.hidden = true;
		} );

		hint.appendChild( document.createTextNode( ' ' ) );
		hint.appendChild( switchBtn );
		hint.appendChild( document.createTextNode( ' ' ) );
		hint.appendChild( dismiss );
		hint.hidden = false;
	}

	function formatEditor( instance ) {
		if ( ! instance || ! instance.codemirror ) {
			return;
		}

		const cm = instance.codemirror;
		cm.operation( function () {
			for ( let i = 0; i < cm.lineCount(); i++ ) {
				cm.indentLine( i, 'smart' );
			}
		} );
	}

	function init( config ) {
		const textarea = document.getElementById( config.textareaId );

		if ( ! textarea ) {
			return null;
		}

		const wrapper = document.createElement( 'div' );
		wrapper.className = 'aegis-code-editor';
		textarea.parentNode.insertBefore( wrapper, textarea );
		wrapper.appendChild( textarea );

		const initialType = getType( config );
		const mime = MIME_BY_TYPE[ initialType ] || 'text/html';
		const editorSettings = wp.codeEditor.defaultSettings ? wp.codeEditor.defaultSettings : {};
		const settings = Object.assign( {}, editorSettings, {
			codemirror: Object.assign( {}, editorSettings.codemirror || {}, {
				mode: mime,
				lineNumbers: true,
				lineWrapping: true,
				indentUnit: 4,
				tabSize: 4,
				matchBrackets: true,
				autoCloseBrackets: true,
				lint: lintOptionForType( initialType ),
				gutters: [ 'CodeMirror-lint-markers', 'CodeMirror-linenumbers' ],
			} ),
		} );

		const instance = wp.codeEditor.initialize( textarea, settings );
		editors[ config.textareaId ] = instance;

		applyMode( instance, initialType );
		applyStarter( instance.codemirror, initialType );
		updateTypeBadge( initialType );

		onTypeChange( config, function () {
			const type = getType( config );
			applyMode( instance, type );
			applyStarter( instance.codemirror, type );
			updateTypeBadge( type );
		} );

		if ( instance.codemirror ) {
			instance.codemirror.on( 'paste', function ( cm ) {
				window.setTimeout( function () {
					const suggested = sniffType( cm.getValue() );
					const current = getType( config );
					if ( suggested && suggested !== current ) {
						showTypeHint( suggested );
					}
				}, 0 );
			} );
		}

		const formatBtn = document.getElementById( 'aegis-snippet-format' );
		if ( formatBtn ) {
			formatBtn.addEventListener( 'click', function () {
				formatEditor( instance );
			} );
		}

		const form = textarea.closest( 'form' );
		if ( form ) {
			form.addEventListener( 'submit', function () {
				if ( instance.codemirror ) {
					textarea.value = instance.codemirror.getValue();
				}
			} );
		}

		return instance;
	}

	window.AegisCodeEditor = {
		init: init,
		get: function ( id ) {
			return editors[ id ] || null;
		},
		applyMode: applyMode,
	};

	document.addEventListener( 'DOMContentLoaded', function () {
		const auto = window.aegisCodeEditorConfig;
		if ( auto && auto.textareaId ) {
			init( auto );
		}
	} );
}( window.wp ) );
