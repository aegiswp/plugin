/**
 * Hook Patterns — Conditional Logic UI
 *
 * Builds the conditions meta box UI from JSON data and syncs
 * changes back to the hidden textarea for form submission.
 *
 * @package Aegis\Admin
 * @since   1.0.0
 */
( function ( wp ) {
	'use strict';

	var __ = wp.i18n.__;
	var sprintf = wp.i18n.sprintf;

	var config = window.aegisConditionsConfig || window.aegisSnippetConditionsConfig || {};
	var conditions = window.aegisActiveConditions || config.conditions || {};
	var roles = config.roles || [];
	var timezones = config.timezones || [];
	var hasPro = config.hasPro || false;
	var settings = config.settings || {};
	var postType = config.postType || '';

	/**
	 * Check whether a specific condition feature is enabled in admin settings.
	 *
	 * @param {string} group   Settings group key (e.g. 'visibility').
	 * @param {string} feature Feature key within the group (e.g. 'lockdown').
	 * @return {boolean}
	 */
	function isEnabled( group, feature ) {
		return !! ( settings[ group ] && settings[ group ][ feature ] );
	}

	function isListedTimezone( value ) {
		var zone = String( value || '' );
		if ( zone === '' ) {
			return true;
		}
		return timezones.some( function ( o ) {
			return String( o.value ) === zone;
		} );
	}

	function timezoneSelectOptions( current ) {
		var opts = [ { value: '', label: __( 'Site timezone', 'aegis' ) } ].concat( timezones );
		var value = String( current || '' );
		if ( value !== '' && ! isListedTimezone( value ) ) {
			opts.splice( 1, 0, {
				value: value,
				label: sprintf(
					/* translators: %s: saved timezone string that is not IANA */
					__( '%s (invalid, using site timezone)', 'aegis' ),
					value
				),
			} );
		}
		return opts;
	}

	function timezoneHelp( current ) {
		var base = __( 'Applies to Date & Time, daily range, and weekdays. Leave empty to use the site timezone.', 'aegis' );
		if ( String( current || '' ) !== '' && ! isListedTimezone( current ) ) {
			return base + ' ' + __( 'The saved value is not an IANA timezone, so the site timezone is used.', 'aegis' );
		}
		return base;
	}

	function normalizeCapability( value ) {
		return String( value || '' )
			.trim()
			.toLowerCase()
			.replace( /\s+/g, '_' )
			.replace( /[^a-z0-9_\-]/g, '' );
	}

	var container = document.getElementById( 'aegis-conditions-ui' );
	var textarea = document.getElementById( 'aegis_conditions' );

	if ( ! container || ! textarea ) {
		return;
	}

	// -------------------------------------------------------------------------
	// Helpers
	// -------------------------------------------------------------------------

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

	function sync() {
		textarea.value = JSON.stringify( conditions );
	}

	function get( key, fallback ) {
		return conditions[ key ] !== undefined ? conditions[ key ] : fallback;
	}

	function set( key, value ) {
		if ( value === '' || value === null || value === undefined ||
			( Array.isArray( value ) && value.length === 0 ) ) {
			delete conditions[ key ];
		} else {
			conditions[ key ] = value;
		}
		sync();
	}

	// -------------------------------------------------------------------------
	// Section builder
	// -------------------------------------------------------------------------

	function buildSection( id, title, contentFn, opts ) {
		opts = opts || {};
		var isOpen = false;

		// Check if section has any data set.
		if ( opts.keys ) {
			opts.keys.forEach( function ( k ) {
				if ( conditions[ k ] !== undefined && conditions[ k ] !== '' ) {
					isOpen = true;
				}
			} );
		}

		var body = el( 'div', { className: 'aegis-cond-section-body' + ( isOpen ? '' : ' hidden' ) } );

		// SVG chevron matching WP's native panel arrow.
		var chevron = document.createElementNS( 'http://www.w3.org/2000/svg', 'svg' );
		chevron.setAttribute( 'xmlns', 'http://www.w3.org/2000/svg' );
		chevron.setAttribute( 'viewBox', '0 0 24 24' );
		chevron.setAttribute( 'width', '24' );
		chevron.setAttribute( 'height', '24' );
		chevron.setAttribute( 'aria-hidden', 'true' );
		chevron.setAttribute( 'focusable', 'false' );
		chevron.setAttribute( 'class', 'aegis-cond-chevron' + ( isOpen ? ' is-open' : '' ) );
		var path = document.createElementNS( 'http://www.w3.org/2000/svg', 'path' );
		path.setAttribute( 'd', 'M17.5 11.6L12 16l-5.5-4.4.9-1.1L12 14l4.5-3.5 1 1.1z' );
		chevron.appendChild( path );

		var header = el( 'button', {
			type: 'button',
			className: 'aegis-cond-section-header',
			onClick: function () {
				var hidden = body.classList.toggle( 'hidden' );
				chevron.classList.toggle( 'is-open', ! hidden );
			},
		}, [
			el( 'span', { className: 'aegis-cond-section-title' }, title ),
			chevron,
		] );

		var section = el( 'div', { className: 'aegis-cond-section', id: 'aegis-cond-' + id }, [
			header,
			body,
		] );

		contentFn( body );
		return section;
	}

	function addSelect( parent, label, key, options, defaultVal ) {
		var current = get( key, defaultVal || '' );
		var select = el( 'select', { className: 'widefat' } );
		options.forEach( function ( opt ) {
			var o = el( 'option', { value: opt.value }, opt.label );
			if ( opt.value === current ) {
				o.selected = true;
			}
			select.appendChild( o );
		} );
		select.addEventListener( 'change', function () {
			set( key, select.value );
		} );
		parent.appendChild(
			el( 'div', { className: 'aegis-cond-field' }, [
				el( 'label', null, label ),
				select,
			] )
		);
		return select;
	}

	function addInput( parent, label, key, type, defaultVal ) {
		var current = get( key, defaultVal || '' );
		var input = el( 'input', { type: type || 'text', className: 'widefat', value: current } );
		input.addEventListener( 'change', function () {
			set( key, input.value );
		} );
		parent.appendChild(
			el( 'div', { className: 'aegis-cond-field' }, [
				el( 'label', null, label ),
				input,
			] )
		);
		return input;
	}

	function addCheckbox( parent, label, key ) {
		var current = get( key, false );
		var input = el( 'input', { type: 'checkbox' } );
		input.checked = !! current;
		input.addEventListener( 'change', function () {
			set( key, input.checked ? true : false );
		} );
		parent.appendChild(
			el( 'div', { className: 'aegis-cond-field aegis-cond-field--checkbox' }, [
				el( 'label', null, [ input, ' ' + label ] ),
			] )
		);
		return input;
	}

	// -------------------------------------------------------------------------
	// WP-style Date & Time picker (matches Publish popover)
	// -------------------------------------------------------------------------

	var monthLabels = [
		'January', 'February', 'March', 'April', 'May', 'June',
		'July', 'August', 'September', 'October', 'November', 'December',
	];

	function parseDateTimeValue( val ) {
		if ( ! val ) {
			return { hours: '', minutes: '', ampm: 'AM', day: '', month: '', year: '' };
		}
		// Stored as ISO: YYYY-MM-DDTHH:MM
		var parts = val.split( 'T' );
		var dateParts = ( parts[ 0 ] || '' ).split( '-' );
		var timeParts = ( parts[ 1 ] || '' ).split( ':' );
		var h = parseInt( timeParts[ 0 ], 10 ) || 0;
		var ampm = h >= 12 ? 'PM' : 'AM';
		if ( h === 0 ) { h = 12; }
		else if ( h > 12 ) { h = h - 12; }
		return {
			hours: h ? String( h ) : '',
			minutes: timeParts[ 1 ] || '',
			ampm: ampm,
			year: dateParts[ 0 ] || '',
			month: dateParts[ 1 ] || '',
			day: dateParts[ 2 ] || '',
		};
	}

	function buildDateTimeValue( p ) {
		if ( ! p.year && ! p.month && ! p.day && ! p.hours && ! p.minutes ) {
			return '';
		}
		var h = parseInt( p.hours, 10 ) || 0;
		if ( p.ampm === 'PM' && h < 12 ) { h += 12; }
		if ( p.ampm === 'AM' && h === 12 ) { h = 0; }
		var hh = ( h < 10 ? '0' : '' ) + h;
		var mm = ( p.minutes || '00' );
		if ( mm.length < 2 ) { mm = '0' + mm; }
		var yy = p.year || new Date().getFullYear();
		var mo = p.month || '01';
		var dd = p.day || '01';
		if ( String( dd ).length < 2 ) { dd = '0' + dd; }
		if ( String( mo ).length < 2 ) { mo = '0' + mo; }
		return yy + '-' + mo + '-' + dd + 'T' + hh + ':' + mm;
	}

	function addDateTimePicker( parent, label, key ) {
		var p = parseDateTimeValue( get( key, '' ) );

		function save() {
			set( key, buildDateTimeValue( p ) );
		}

		// TIME row: [HH] : [MM]  [AM|PM]
		var hoursInput = el( 'input', {
			type: 'text', className: 'aegis-dt-input aegis-dt-hours',
			value: p.hours, placeholder: 'HH', maxlength: '2',
		} );
		hoursInput.addEventListener( 'change', function () {
			var v = parseInt( hoursInput.value, 10 );
			if ( isNaN( v ) || v < 1 ) { v = 12; }
			if ( v > 12 ) { v = 12; }
			hoursInput.value = v;
			p.hours = String( v );
			save();
		} );

		var minutesInput = el( 'input', {
			type: 'text', className: 'aegis-dt-input aegis-dt-minutes',
			value: p.minutes, placeholder: 'MM', maxlength: '2',
		} );
		minutesInput.addEventListener( 'change', function () {
			var v = parseInt( minutesInput.value, 10 );
			if ( isNaN( v ) || v < 0 ) { v = 0; }
			if ( v > 59 ) { v = 59; }
			minutesInput.value = ( v < 10 ? '0' : '' ) + v;
			p.minutes = minutesInput.value;
			save();
		} );

		var amBtn = el( 'button', {
			type: 'button',
			className: 'aegis-dt-ampm' + ( p.ampm === 'AM' ? ' is-active' : '' ),
		}, 'AM' );
		var pmBtn = el( 'button', {
			type: 'button',
			className: 'aegis-dt-ampm' + ( p.ampm === 'PM' ? ' is-active' : '' ),
		}, 'PM' );
		amBtn.addEventListener( 'click', function () {
			p.ampm = 'AM';
			amBtn.classList.add( 'is-active' );
			pmBtn.classList.remove( 'is-active' );
			save();
		} );
		pmBtn.addEventListener( 'click', function () {
			p.ampm = 'PM';
			pmBtn.classList.add( 'is-active' );
			amBtn.classList.remove( 'is-active' );
			save();
		} );

		var timeRow = el( 'div', { className: 'aegis-dt-time-row' }, [
			hoursInput,
			el( 'span', { className: 'aegis-dt-colon' }, ':' ),
			minutesInput,
			el( 'span', { className: 'aegis-dt-ampm-group' }, [ amBtn, pmBtn ] ),
		] );

		// DATE row: [DD] [Month ▾] [YYYY]
		var dayInput = el( 'input', {
			type: 'text', className: 'aegis-dt-input aegis-dt-day',
			value: p.day ? String( parseInt( p.day, 10 ) ) : '', placeholder: 'DD', maxlength: '2',
		} );
		dayInput.addEventListener( 'change', function () {
			var v = parseInt( dayInput.value, 10 );
			if ( isNaN( v ) || v < 1 ) { v = 1; }
			if ( v > 31 ) { v = 31; }
			dayInput.value = v;
			p.day = String( v );
			save();
		} );

		var monthSelect = el( 'select', { className: 'aegis-dt-month' } );
		monthLabels.forEach( function ( m, i ) {
			var val = ( i + 1 < 10 ? '0' : '' ) + ( i + 1 );
			var opt = el( 'option', { value: val }, m );
			if ( val === p.month ) { opt.selected = true; }
			monthSelect.appendChild( opt );
		} );
		monthSelect.addEventListener( 'change', function () {
			p.month = monthSelect.value;
			save();
		} );

		var yearInput = el( 'input', {
			type: 'text', className: 'aegis-dt-input aegis-dt-year',
			value: p.year, placeholder: 'YYYY', maxlength: '4',
		} );
		yearInput.addEventListener( 'change', function () {
			var v = parseInt( yearInput.value, 10 );
			if ( isNaN( v ) || v < 2000 ) { v = new Date().getFullYear(); }
			yearInput.value = v;
			p.year = String( v );
			save();
		} );

		var dateRow = el( 'div', { className: 'aegis-dt-date-row' }, [
			dayInput, monthSelect, yearInput,
		] );

		parent.appendChild(
			el( 'div', { className: 'aegis-cond-field aegis-dt-picker' }, [
				el( 'label', null, label ),
				el( 'div', { className: 'aegis-dt-label' }, __( 'Time', 'aegis' ) ),
				timeRow,
				el( 'div', { className: 'aegis-dt-label' }, __( 'Date', 'aegis' ) ),
				dateRow,
			] )
		);
	}

	// -------------------------------------------------------------------------
	// Rule list builder (for array-based conditions)
	// -------------------------------------------------------------------------

	var showHideOptions = [
		{ value: 'show', label: 'Show when matched' },
		{ value: 'hide', label: 'Hide when matched' },
	];

	var relationOptions = [
		{ value: 'all', label: 'All rules must match (AND)' },
		{ value: 'any', label: 'Any rule must match (OR)' },
	];

	function buildRuleList( parent, rulesKey, logicKey, relationKey, columns, newRule ) {
		var rules = get( rulesKey, [] );
		if ( ! Array.isArray( rules ) ) {
			rules = [];
		}

		// Logic + relation selects.
		addSelect( parent, 'Logic', logicKey, showHideOptions, 'show' );
		addSelect( parent, 'Relation', relationKey, relationOptions, 'all' );

		var listEl = el( 'div', { className: 'aegis-cond-rules' } );
		parent.appendChild( listEl );

		function renderRules() {
			listEl.innerHTML = '';
			rules.forEach( function ( rule, idx ) {
				var row = el( 'div', { className: 'aegis-cond-rule-row' } );
				columns.forEach( function ( col ) {
					if ( col.type === 'select' ) {
						var sel = el( 'select', { className: 'aegis-cond-rule-input' } );
						col.options.forEach( function ( opt ) {
							var o = el( 'option', { value: opt.value }, opt.label );
							if ( rule[ col.key ] === opt.value ) {
								o.selected = true;
							}
							sel.appendChild( o );
						} );
						sel.addEventListener( 'change', function () {
							rule[ col.key ] = sel.value;
							set( rulesKey, rules );
						} );
						row.appendChild( sel );
					} else {
						var inp = el( 'input', {
							type: 'text',
							className: 'aegis-cond-rule-input',
							placeholder: col.placeholder || col.label,
							value: rule[ col.key ] || '',
						} );
						inp.addEventListener( 'change', function () {
							var next = inp.value;
							if ( col.normalize === 'capability' ) {
								next = normalizeCapability( next );
								inp.value = next;
							}
							rule[ col.key ] = next;
							set( rulesKey, rules );
						} );
						row.appendChild( inp );
					}
				} );
				var removeBtn = el( 'button', {
					type: 'button',
					className: 'aegis-cond-rule-remove',
					title: 'Remove rule',
					onClick: function () {
						rules.splice( idx, 1 );
						set( rulesKey, rules );
						renderRules();
					},
				}, '\u00D7' );
				row.appendChild( removeBtn );
				listEl.appendChild( row );
			} );
		}

		renderRules();

		var addBtn = el( 'button', {
			type: 'button',
			className: 'button aegis-cond-add-rule',
			onClick: function () {
				var r = {};
				Object.keys( newRule ).forEach( function ( k ) {
					r[ k ] = newRule[ k ];
				} );
				rules.push( r );
				set( rulesKey, rules );
				renderRules();
			},
		}, '+ Add Rule' );
		parent.appendChild( addBtn );
	}

	// -------------------------------------------------------------------------
	// Operator options shared across rule types
	// -------------------------------------------------------------------------

	var basicOperators = [
		{ value: 'is', label: 'Is' },
		{ value: 'isNot', label: 'Is Not' },
		{ value: 'exists', label: 'Exists' },
		{ value: 'notExists', label: 'Does Not Exist' },
		{ value: 'contains', label: 'Contains' },
	];

	var extendedOperators = basicOperators.concat( [
		{ value: 'greaterThan', label: 'Greater Than' },
		{ value: 'lessThan', label: 'Less Than' },
		{ value: 'greaterThanOrEqual', label: '>=' },
		{ value: 'lessThanOrEqual', label: '<=' },
	] );

	var deviceOptions = [
		{ value: '', label: 'Select...' },
		{ value: 'ios', label: 'iOS' },
		{ value: 'android', label: 'Android' },
		{ value: 'windows', label: 'Windows' },
		{ value: 'macos', label: 'macOS' },
		{ value: 'linux', label: 'Linux' },
		{ value: 'chrome', label: 'Chrome' },
		{ value: 'firefox', label: 'Firefox' },
		{ value: 'safari', label: 'Safari' },
		{ value: 'edge', label: 'Edge' },
		{ value: 'mobile', label: 'Mobile' },
		{ value: 'tablet', label: 'Tablet' },
		{ value: 'desktop', label: 'Desktop' },
	];

	var isIsNotOperators = [
		{ value: 'is', label: __( 'Is', 'aegis' ) },
		{ value: 'isNot', label: __( 'Is Not', 'aegis' ) },
	];

	// -------------------------------------------------------------------------
	// Build all sections
	// -------------------------------------------------------------------------

	// Description.
	container.appendChild(
		el( 'p', { className: 'description' },
			'Leave sections empty for no restriction. Active sections are combined with AND logic.'
		)
	);

	// --- Lockdown ---
	if ( isEnabled( 'visibility', 'lockdown' ) ) {
	container.appendChild( buildSection( 'lockdown', 'Lockdown', function ( body ) {
		addCheckbox( body, 'Hide this pattern from all frontend visitors', 'lockdown' );
	}, { keys: [ 'lockdown' ] } ) );
	}

	// --- User Status ---
	if ( isEnabled( 'user', 'user_status' ) ) {
	container.appendChild( buildSection( 'user-status', __( 'User Status', 'aegis' ), function ( body ) {
		addSelect( body, __( 'Show to', 'aegis' ), 'userStatus', [
			{ value: '', label: __( 'All Users', 'aegis' ) },
			{ value: 'logged-in', label: __( 'Logged In Only', 'aegis' ) },
			{ value: 'logged-out', label: __( 'Logged Out Only', 'aegis' ) },
		] );
	}, { keys: [ 'userStatus' ] } ) );
	}

	// --- User Role ---
	if ( isEnabled( 'user', 'user_role' ) ) {
	container.appendChild( buildSection( 'user-role', __( 'User Role', 'aegis' ), function ( body ) {
		var roleOpts = [ { value: '', label: __( 'Any Role', 'aegis' ) } ].concat( roles );
		buildRuleList( body, 'userRoleRules', 'userRoleLogic', 'userRoleRelation', [
			{ key: 'role', type: 'select', label: __( 'Role', 'aegis' ), options: roleOpts },
			{ key: 'operator', type: 'select', label: __( 'Operator', 'aegis' ), options: isIsNotOperators },
		], { role: '', operator: 'is' } );
	}, { keys: [ 'userRoleRules' ] } ) );
	}

	// --- User Capability ---
	if ( isEnabled( 'user', 'user_capability' ) ) {
	container.appendChild( buildSection( 'user-capability', __( 'User Capability', 'aegis' ), function ( body ) {
		body.appendChild( el( 'p', { className: 'description' }, __( 'Use a primitive capability slug such as edit_posts. Object caps like edit_post need a post ID and will not match.', 'aegis' ) ) );
		buildRuleList( body, 'userCapabilityRules', 'userCapabilityLogic', 'userCapabilityRelation', [
			{ key: 'capability', type: 'text', label: __( 'Capability', 'aegis' ), placeholder: 'edit_posts', normalize: 'capability' },
			{ key: 'operator', type: 'select', label: __( 'Operator', 'aegis' ), options: isIsNotOperators },
		], { capability: '', operator: 'is' } );
	}, { keys: [ 'userCapabilityRules' ] } ) );
	}

	// --- Schedule ---
	if ( isEnabled( 'schedule', 'date_time' ) || isEnabled( 'schedule', 'time_range' ) || isEnabled( 'schedule', 'timezone' ) ) {
	container.appendChild( buildSection( 'schedule', __( 'Date & Time', 'aegis' ), function ( body ) {
		if ( isEnabled( 'schedule', 'date_time' ) ) {
			body.appendChild( el( 'p', { className: 'description' }, isEnabled( 'schedule', 'timezone' )
				? __( 'Interpreted in the schedule timezone, or the site timezone if Timezone is empty.', 'aegis' )
				: __( 'Interpreted in the site timezone.', 'aegis' ) ) );
			addDateTimePicker( body, __( 'Start', 'aegis' ), 'scheduleStart' );
			addDateTimePicker( body, __( 'End', 'aegis' ), 'scheduleEnd' );
		}
		if ( isEnabled( 'schedule', 'time_range' ) ) {
			body.appendChild( el( 'p', { className: 'description' }, __( 'If end is before start, the window wraps overnight.', 'aegis' ) ) );
			addInput( body, __( 'Daily Start Time', 'aegis' ), 'scheduleTimeStart', 'time' );
			addInput( body, __( 'Daily End Time', 'aegis' ), 'scheduleTimeEnd', 'time' );
		}
		if ( isEnabled( 'schedule', 'timezone' ) ) {
			addSelect( body, __( 'Timezone', 'aegis' ), 'scheduleTimezone', timezoneSelectOptions( get( 'scheduleTimezone', '' ) ), '' );
			body.appendChild( el( 'p', { className: 'description' }, timezoneHelp( get( 'scheduleTimezone', '' ) ) ) );
		}
	}, { keys: [ 'scheduleStart', 'scheduleEnd', 'scheduleTimeStart', 'scheduleTimeEnd', 'scheduleTimezone' ] } ) );
	}

	if ( isEnabled( 'schedule', 'days_of_week' ) ) {
	container.appendChild( buildSection( 'weekdays', __( 'Days of Week', 'aegis' ), function ( body ) {
		var weekdayOptions = [
			{ value: 0, label: __( 'Sunday', 'aegis' ) },
			{ value: 1, label: __( 'Monday', 'aegis' ) },
			{ value: 2, label: __( 'Tuesday', 'aegis' ) },
			{ value: 3, label: __( 'Wednesday', 'aegis' ) },
			{ value: 4, label: __( 'Thursday', 'aegis' ) },
			{ value: 5, label: __( 'Friday', 'aegis' ) },
			{ value: 6, label: __( 'Saturday', 'aegis' ) },
		];
		var selected = ( get( 'scheduleDays', [] ) || [] ).map( Number );
		weekdayOptions.forEach( function ( day ) {
			var wrap = el( 'div', { className: 'aegis-cond-field aegis-cond-field--checkbox' } );
			var input = el( 'input', { type: 'checkbox' } );
			input.checked = selected.indexOf( day.value ) !== -1;
			input.addEventListener( 'change', function () {
				var next = ( get( 'scheduleDays', [] ) || [] ).map( Number );
				var idx = next.indexOf( day.value );
				if ( input.checked && idx === -1 ) {
					next.push( day.value );
				} else if ( ! input.checked && idx !== -1 ) {
					next.splice( idx, 1 );
				}
				set( 'scheduleDays', next );
			} );
			wrap.appendChild( el( 'label', null, [ input, ' ' + day.label ] ) );
			body.appendChild( wrap );
		} );
	}, { keys: [ 'scheduleDays' ] } ) );
	}

	// --- Page Type ---
	if ( isEnabled( 'visibility', 'page_type' ) ) {
	container.appendChild( buildSection( 'location', 'Location', function ( body ) {
		addSelect( body, 'Page Type', 'location', [
			{ value: '', label: 'All Pages' },
			{ value: 'front-page', label: 'Front Page' },
			{ value: 'blog', label: 'Blog Page' },
			{ value: 'singular', label: 'All Singular' },
			{ value: 'archive', label: 'All Archives' },
			{ value: 'search', label: 'Search Results' },
			{ value: '404', label: '404 Page' },
		] );
		addSelect( body, 'Logic', 'locationLogic', showHideOptions, 'show' );
	}, { keys: [ 'location' ] } ) );
	}

	// --- Screen Size ---
	if ( isEnabled( 'visibility', 'screen_size' ) ) {
	container.appendChild( buildSection( 'screen-size', 'Screen Size', function ( body ) {
		addCheckbox( body, 'Hide on Mobile (< 480px)', 'hideOnMobile' );
		addCheckbox( body, 'Hide on Tablet (480–1023px)', 'hideOnTablet' );
		addCheckbox( body, 'Hide on Desktop (≥ 1024px)', 'hideOnDesktop' );
	}, { keys: [ 'hideOnMobile', 'hideOnTablet', 'hideOnDesktop' ] } ) );
	}

	if ( isEnabled( 'visibility', 'custom_breakpoints' ) ) {
	container.appendChild( buildSection( 'custom-breakpoints', 'Custom Breakpoints', function ( body ) {
		addInput( body, 'Hide below width (px)', 'hideBelowWidth', 'number' );
		addInput( body, 'Hide above width (px)', 'hideAboveWidth', 'number' );
	}, { keys: [ 'hideBelowWidth', 'hideAboveWidth' ] } ) );
	}

	// --- Browser & Device ---
	if ( isEnabled( 'visibility', 'browser_device' ) ) {
	container.appendChild( buildSection( 'browser-device', 'Browser & Device', function ( body ) {
		buildRuleList( body, 'deviceRules', 'deviceLogic', 'deviceRelation', [
			{ key: 'device', type: 'select', label: 'Device', options: deviceOptions },
			{ key: 'operator', type: 'select', label: 'Operator', options: isIsNotOperators },
		], { device: '', operator: 'is' } );
	}, { keys: [ 'deviceRules' ] } ) );
	}

	// --- URL Query String ---
	if ( isEnabled( 'visibility', 'query_string' ) ) {
	container.appendChild( buildSection( 'query-string', 'URL Query String', function ( body ) {
		buildRuleList( body, 'queryStringRules', 'queryStringLogic', 'queryStringRelation', [
			{ key: 'param', type: 'text', label: 'Parameter', placeholder: 'param' },
			{ key: 'operator', type: 'select', label: 'Operator', options: extendedOperators },
			{ key: 'value', type: 'text', label: 'Value', placeholder: 'value' },
		], { param: '', operator: 'is', value: '' } );
	}, { keys: [ 'queryStringRules' ] } ) );
	}

	// --- Specific Users ---
	if ( isEnabled( 'visibility', 'specific_users' ) ) {
	container.appendChild( buildSection( 'specific-users', 'Specific Users', function ( body ) {
		addInput( body, 'User IDs (comma-separated)', 'specificUserIds', 'text' );
		addSelect( body, 'Logic', 'specificUsersLogic', showHideOptions, 'show' );
	}, { keys: [ 'specificUserIds' ] } ) );
	}

	// --- Accessibility ---
	if ( isEnabled( 'accessibility', 'screen_reader_only' ) || isEnabled( 'accessibility', 'reduced_motion' ) || isEnabled( 'accessibility', 'color_scheme' ) || isEnabled( 'accessibility', 'high_contrast' ) || isEnabled( 'accessibility', 'forced_colors' ) ) {
	container.appendChild( buildSection( 'accessibility', 'Accessibility', function ( body ) {
		if ( isEnabled( 'accessibility', 'screen_reader_only' ) ) {
			addCheckbox( body, 'Screen reader only', 'screenReaderOnly' );
		}
		if ( isEnabled( 'accessibility', 'reduced_motion' ) ) {
			addCheckbox( body, 'Hide when reduced motion preferred', 'reducedMotion' );
		}
		if ( isEnabled( 'accessibility', 'color_scheme' ) ) {
			addSelect( body, 'Hide for color scheme', 'colorScheme', [
				{ value: '', label: 'None' },
				{ value: 'dark', label: 'Dark' },
				{ value: 'light', label: 'Light' },
			] );
			body.appendChild( el( 'p', { className: 'description' }, __( 'Hides the pattern when the Aegis dark/light toggle or the site default matches that scheme.', 'aegis' ) ) );
		}
		if ( isEnabled( 'accessibility', 'high_contrast' ) ) {
			addCheckbox( body, 'Hide when high contrast preferred', 'highContrast' );
		}
		if ( isEnabled( 'accessibility', 'forced_colors' ) ) {
			addCheckbox( body, 'Hide when forced colors active', 'forcedColors' );
		}
	}, { keys: [ 'screenReaderOnly', 'reducedMotion', 'colorScheme', 'highContrast', 'forcedColors' ] } ) );
	}

	// --- Cookie ---
	if ( isEnabled( 'pro_conditions', 'cookie' ) ) {
	container.appendChild( buildSection( 'cookie', 'Cookie', function ( body ) {
		buildRuleList( body, 'cookieRules', 'cookieLogic', 'cookieRelation', [
			{ key: 'name', type: 'text', label: 'Cookie Name', placeholder: 'cookie_name' },
			{ key: 'operator', type: 'select', label: 'Operator', options: basicOperators },
			{ key: 'value', type: 'text', label: 'Value', placeholder: 'value' },
		], { name: '', operator: 'is', value: '' } );
	}, { keys: [ 'cookieRules' ] } ) );
	}

	// --- Referral Source ---
	if ( isEnabled( 'pro_conditions', 'referral' ) ) {
	container.appendChild( buildSection( 'referral', 'Referral Source', function ( body ) {
		buildRuleList( body, 'referralRules', 'referralLogic', 'referralRelation', [
			{ key: 'domain', type: 'text', label: 'Domain', placeholder: 'example.com' },
			{ key: 'operator', type: 'select', label: 'Operator', options: [
				{ value: 'is', label: 'Is' },
				{ value: 'isNot', label: 'Is Not' },
				{ value: 'contains', label: 'Contains' },
			] },
		], { domain: '', operator: 'is' } );
	}, { keys: [ 'referralRules' ] } ) );
	}

	// --- ACF Field ---
	if ( isEnabled( 'pro_conditions', 'acf_field' ) ) {
	container.appendChild( buildSection( 'acf-field', __( 'ACF Field', 'aegis' ), function ( body ) {
		buildRuleList( body, 'acfRules', 'acfLogic', 'acfRelation', [
			{ key: 'field', type: 'text', label: __( 'Field Name', 'aegis' ), placeholder: 'field_name' },
			{ key: 'operator', type: 'select', label: __( 'Operator', 'aegis' ), options: extendedOperators },
			{ key: 'value', type: 'text', label: __( 'Value', 'aegis' ), placeholder: 'value' },
		], { field: '', operator: 'is', value: '' } );
	}, { keys: [ 'acfRules' ] } ) );
	}

	// --- MetaBox Field ---
	if ( isEnabled( 'pro_conditions', 'metabox_field' ) ) {
	container.appendChild( buildSection( 'metabox-field', __( 'Meta Box Field', 'aegis' ), function ( body ) {
		buildRuleList( body, 'metaboxRules', 'metaboxLogic', 'metaboxRelation', [
			{ key: 'field', type: 'text', label: __( 'Field ID', 'aegis' ), placeholder: 'field_id' },
			{ key: 'operator', type: 'select', label: __( 'Operator', 'aegis' ), options: extendedOperators },
			{ key: 'value', type: 'text', label: __( 'Value', 'aegis' ), placeholder: 'value' },
		], { field: '', operator: 'is', value: '' } );
	}, { keys: [ 'metaboxRules' ] } ) );
	}

	// --- Post Meta ---
	if ( isEnabled( 'pro_conditions', 'post_meta' ) ) {
	container.appendChild( buildSection( 'post-meta', 'Post Meta', function ( body ) {
		buildRuleList( body, 'postMetaRules', 'postMetaLogic', 'postMetaRelation', [
			{ key: 'key', type: 'text', label: 'Meta Key', placeholder: 'meta_key' },
			{ key: 'operator', type: 'select', label: 'Operator', options: extendedOperators },
			{ key: 'value', type: 'text', label: 'Value', placeholder: 'value' },
		], { key: '', operator: 'is', value: '' } );
	}, { keys: [ 'postMetaRules' ] } ) );
	}

	// --- User Meta ---
	if ( isEnabled( 'pro_conditions', 'user_meta' ) ) {
	container.appendChild( buildSection( 'user-meta', 'User Meta', function ( body ) {
		buildRuleList( body, 'userMetaRules', 'userMetaLogic', 'userMetaRelation', [
			{ key: 'key', type: 'text', label: 'Meta Key', placeholder: 'meta_key' },
			{ key: 'operator', type: 'select', label: 'Operator', options: extendedOperators },
			{ key: 'value', type: 'text', label: 'Value', placeholder: 'value' },
		], { key: '', operator: 'is', value: '' } );
	}, { keys: [ 'userMetaRules' ] } ) );
	}

	// --- Advanced Location ---
	if ( isEnabled( 'pro_conditions', 'advanced_location' ) ) {
	container.appendChild( buildSection( 'advanced-location', 'Advanced Location', function ( body ) {
		var typeOptions = [
			{ value: '', label: 'Select...' },
			{ value: 'postType', label: 'Post Type' },
			{ value: 'postIds', label: 'Specific Post IDs' },
			{ value: 'taxonomyTerm', label: 'Taxonomy Term' },
			{ value: 'urlPath', label: 'URL Path' },
			{ value: 'archiveType', label: 'Archive Type' },
		];

		var pathOperators = [
			{ value: 'is', label: 'Is' },
			{ value: 'isNot', label: 'Is Not' },
			{ value: 'contains', label: 'Contains' },
			{ value: 'startsWith', label: 'Starts With' },
		];

		buildRuleList( body, 'advancedLocationRules', 'advancedLocationLogic', 'advancedLocationRelation', [
			{ key: 'type', type: 'select', label: 'Type', options: typeOptions },
			{ key: 'operator', type: 'select', label: 'Operator', options: pathOperators },
			{ key: 'value', type: 'text', label: 'Value', placeholder: 'post, 1,5,42, taxonomy:term, /path/' },
		], { type: '', operator: 'is', value: '' } );
	}, { keys: [ 'advancedLocationRules' ] } ) );
	}

	// Initial sync.
	sync();
} )( window.wp );
