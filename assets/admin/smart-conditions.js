/**
 * Smart Conditional Logic UI — shared across Snippets, Hook Patterns, and posts.
 *
 * @package Aegis\Admin
 */
( function () {
	'use strict';

	var configs = document.querySelectorAll( '[data-aegis-smart-conditions]' );

	if ( ! configs.length && ! document.getElementById( 'aegis-conditions-ui' ) && ! document.getElementById( 'aegis-smart-conditions-ui' ) ) {
		return;
	}

	function el( tag, attrs, children ) {
		var node = document.createElement( tag );
		if ( attrs ) {
			Object.keys( attrs ).forEach( function ( key ) {
				if ( key === 'className' ) {
					node.className = attrs[ key ];
				} else if ( key.indexOf( 'on' ) === 0 ) {
					node.addEventListener( key.slice( 2 ).toLowerCase(), attrs[ key ] );
				} else {
					node.setAttribute( key, attrs[ key ] );
				}
			} );
		}
		if ( children ) {
			if ( ! Array.isArray( children ) ) {
				children = [ children ];
			}
			children.forEach( function ( child ) {
				if ( typeof child === 'string' ) {
					node.appendChild( document.createTextNode( child ) );
				} else if ( child ) {
					node.appendChild( child );
				}
			} );
		}
		return node;
	}

	var fieldOptions = [
		{ value: 'post_type', label: 'Page / Post Type' },
		{ value: 'user_status', label: 'User / Logged-in' },
		{ value: 'user_role', label: 'User Role' },
		{ value: 'page_url', label: 'Page / URL' },
		{ value: 'query_string', label: 'Query Parameter' },
	];

	if ( window.aegisConditionsConfig && ( window.aegisConditionsConfig.hasPro || ( window.aegisConditionsConfig.settings && window.aegisConditionsConfig.settings.wp_fusion && window.aegisConditionsConfig.settings.wp_fusion.tags ) ) ) {
		fieldOptions.push( { value: 'wp_fusion_tag', label: 'WP Fusion Tag' } );
	}

	var operatorOptions = [
		{ value: 'is', label: 'Equal' },
		{ value: 'isNot', label: 'Is not' },
		{ value: 'contains', label: 'Includes' },
		{ value: 'notContains', label: "Doesn't contain" },
		{ value: 'startsWith', label: 'Starts with' },
	];

	function defaultSmartLogic() {
		return {
			enabled: false,
			action: 'show',
			groups: [
				{
					relation: 'all',
					rules: [ { field: 'post_type', operator: 'contains', value: '' } ],
				},
			],
		};
	}

	function initMount( mount, textarea, config ) {
		var conditions = config.conditions || {};
		if ( ! conditions.smartLogic ) {
			conditions.smartLogic = defaultSmartLogic();
		}

		function sync() {
			if ( textarea ) {
				textarea.value = JSON.stringify( conditions );
			}
		}

		mount.className = 'aegis-smart-conditions';

		var body = el( 'div', { className: 'aegis-smart-conditions-body' } );
		var isEmbedded = mount.id === 'aegis-smart-conditions-ui';

		if ( ! isEmbedded ) {
			var headerBtn = el( 'button', {
				type: 'button',
				className: 'aegis-smart-conditions-header',
				onClick: function () {
					body.classList.toggle( 'hidden' );
				},
			}, 'Advanced Conditional Logic' );
			mount.appendChild( headerBtn );
		}

		mount.appendChild( body );

		var enableRow = el( 'div', { className: 'aegis-smart-conditions-enable' } );
		enableRow.appendChild( el( 'span', null, 'Enable Conditional Logic' ) );
		var enableWrap = el( 'label', { className: 'aegis-toggle' } );
		var enableToggle = el( 'input', { type: 'checkbox' } );
		enableToggle.checked = !! conditions.smartLogic.enabled;
		enableWrap.appendChild( enableToggle );
		enableWrap.appendChild( el( 'span', { className: 'aegis-toggle-slider' } ) );
		enableRow.appendChild( enableWrap );
		body.appendChild( enableRow );

		var builder = el( 'div', { className: 'aegis-smart-logic-builder' } );
		builder.hidden = ! conditions.smartLogic.enabled;
		body.appendChild( builder );

		enableToggle.addEventListener( 'change', function () {
			conditions.smartLogic.enabled = enableToggle.checked;
			builder.hidden = ! enableToggle.checked;
			sync();
		} );

		var actionRow = el( 'div', { className: 'aegis-smart-logic-action' } );
		var actionSelect = el( 'select' );
		[ { value: 'show', label: 'Show' }, { value: 'hide', label: 'Hide' } ].forEach( function ( opt ) {
			var o = el( 'option', { value: opt.value }, opt.label );
			if ( opt.value === conditions.smartLogic.action ) {
				o.selected = true;
			}
			actionSelect.appendChild( o );
		} );
		actionSelect.addEventListener( 'change', function () {
			conditions.smartLogic.action = actionSelect.value;
			sync();
		} );
		actionRow.appendChild( actionSelect );
		actionRow.appendChild( document.createTextNode( ' this snippet if' ) );
		builder.appendChild( actionRow );

		var groupsWrap = el( 'div', { className: 'aegis-smart-groups' } );
		builder.appendChild( groupsWrap );

		function valueInputFor( rule ) {
			var field = rule.field || 'post_type';
			var input;

			if ( field === 'user_status' ) {
				input = el( 'select' );
				[
					{ value: 'true', label: 'True' },
					{ value: 'false', label: 'False' },
				].forEach( function ( opt ) {
					var o = el( 'option', { value: opt.value }, opt.label );
					if ( String( rule.value ) === opt.value || ( opt.value === 'true' && rule.value === 'logged-in' ) || ( opt.value === 'false' && rule.value === 'logged-out' ) ) {
						o.selected = true;
					}
					input.appendChild( o );
				} );
			} else {
				input = el( 'input', {
					type: 'text',
					value: rule.value || '',
					placeholder: field === 'post_type' ? 'post, page, product' : 'Value',
				} );
			}

			input.addEventListener( 'change', function () {
				rule.value = input.value;
				sync();
			} );

			return input;
		}

		function renderGroups() {
			groupsWrap.innerHTML = '';
			conditions.smartLogic.groups.forEach( function ( group, groupIdx ) {
				if ( groupIdx > 0 ) {
					groupsWrap.appendChild( el( 'div', { className: 'aegis-smart-group-or' }, 'OR' ) );
				}

				var groupEl = el( 'div', { className: 'aegis-smart-group' } );

				( group.rules || [] ).forEach( function ( rule, ruleIdx ) {
					var row = el( 'div', { className: 'aegis-smart-rule' } );

					var fieldSel = el( 'select' );
					fieldOptions.forEach( function ( f ) {
						var o = el( 'option', { value: f.value }, f.label );
						if ( f.value === rule.field ) {
							o.selected = true;
						}
						fieldSel.appendChild( o );
					} );
					fieldSel.addEventListener( 'change', function () {
						rule.field = fieldSel.value;
						rule.value = '';
						renderGroups();
						sync();
					} );
					row.appendChild( fieldSel );

					var opSel = el( 'select' );
					operatorOptions.forEach( function ( o ) {
						var opt = el( 'option', { value: o.value }, o.label );
						if ( o.value === rule.operator ) {
							opt.selected = true;
						}
						opSel.appendChild( opt );
					} );
					opSel.addEventListener( 'change', function () {
						rule.operator = opSel.value;
						sync();
					} );
					row.appendChild( opSel );

					row.appendChild( valueInputFor( rule ) );

					var removeBtn = el( 'button', {
						type: 'button',
						className: 'aegis-smart-rule-remove',
						title: 'Remove',
						onClick: function () {
							group.rules.splice( ruleIdx, 1 );
							renderGroups();
							sync();
						},
					}, '\u00D7' );
					row.appendChild( removeBtn );
					groupEl.appendChild( row );
				} );

				var actions = el( 'div', { className: 'aegis-smart-group-actions' } );
				var addRule = el( 'button', {
					type: 'button',
					className: 'button aegis-smart-add-rule',
					onClick: function () {
						group.rules.push( { field: 'post_type', operator: 'contains', value: '' } );
						renderGroups();
						sync();
					},
				}, '+ And' );
				actions.appendChild( addRule );
				groupEl.appendChild( actions );
				groupsWrap.appendChild( groupEl );
			} );
		}

		renderGroups();

		var addGroup = el( 'button', {
			type: 'button',
			className: 'button button-primary aegis-smart-add-group',
			onClick: function () {
				conditions.smartLogic.groups.push( {
					relation: 'all',
					rules: [ { field: 'post_type', operator: 'contains', value: '' } ],
				} );
				renderGroups();
				sync();
			},
		}, '+ Add new group' );
		builder.appendChild( addGroup );

		sync();
	}

	function initTagChips( container, hiddenInput, initialTags ) {
		var tags = Array.isArray( initialTags ) ? initialTags.slice() : [];

		container.className = 'aegis-tag-chips';
		var input = el( 'input', { type: 'text', className: 'aegis-tag-chips-input', placeholder: 'Add tag...' } );

		function render() {
			container.innerHTML = '';
			tags.forEach( function ( tag, idx ) {
				var chip = el( 'span', { className: 'aegis-tag-chip' } );
				var remove = el( 'button', {
					type: 'button',
					className: 'aegis-tag-chip-remove',
					onClick: function () {
						tags.splice( idx, 1 );
						render();
					},
				}, '\u00D7' );
				chip.appendChild( remove );
				chip.appendChild( document.createTextNode( tag ) );
				container.appendChild( chip );
			} );
			container.appendChild( input );
			if ( hiddenInput ) {
				hiddenInput.value = tags.join( ',' );
			}
		}

		input.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Enter' || e.key === ',' ) {
				e.preventDefault();
				var val = input.value.trim().replace( /,$/, '' );
				if ( val && tags.indexOf( val ) === -1 ) {
					tags.push( val );
					input.value = '';
					render();
				}
			}
		} );

		render();
	}

	window.AegisSmartConditions = {
		initMount: initMount,
		initTagChips: initTagChips,
	};

	var legacyMount = document.getElementById( 'aegis-conditions-ui' );
	var legacyTextarea = document.getElementById( 'aegis_conditions' );
	if ( legacyMount && legacyTextarea && window.aegisConditionsConfig ) {
		initMount( legacyMount, legacyTextarea, window.aegisConditionsConfig );
	}

	var snippetMount = document.getElementById( 'aegis-smart-conditions-ui' );
	var snippetTextarea = document.getElementById( 'snippet_conditions' );
	if ( snippetMount && snippetTextarea && window.aegisSnippetConditionsConfig ) {
		initMount( snippetMount, snippetTextarea, window.aegisSnippetConditionsConfig );
	}

	var tagContainer = document.getElementById( 'aegis-snippet-tags' );
	var tagHidden = document.getElementById( 'snippet_tags' );
	if ( tagContainer && tagHidden && window.aegisSnippetConditionsConfig ) {
		initTagChips( tagContainer, tagHidden, window.aegisSnippetConditionsConfig.tags || [] );
	}

	var hookTagContainer = document.getElementById( 'aegis-hook-pattern-tags' );
	var hookTagHidden = document.getElementById( 'aegis_tags' );
	if ( hookTagContainer && hookTagHidden && window.aegisSnippetConditionsConfig ) {
		initTagChips( hookTagContainer, hookTagHidden, window.aegisSnippetConditionsConfig.tags || [] );
	}

	var hookMount = document.getElementById( 'aegis-smart-conditions-ui' );
	var hookTextarea = document.getElementById( 'aegis_conditions' );
	if ( hookMount && hookTextarea && window.aegisSnippetConditionsConfig ) {
		initMount( hookMount, hookTextarea, window.aegisSnippetConditionsConfig );
	}
}() );
