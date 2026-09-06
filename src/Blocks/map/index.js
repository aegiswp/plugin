/**
 * Map block editor.
 *
 * @package Aegis\Plugin
 */
( function ( wp ) {
	'use strict';

	const { registerBlockType } = wp.blocks;
	const { SVG, Path } = wp.primitives;
	const { __ } = wp.i18n;
	const { InspectorControls, useBlockProps } = wp.blockEditor;
	const {
		PanelBody,
		SelectControl,
		TextControl,
		Button,
		RangeControl,
		ToggleControl,
		Notice,
		__experimentalUnitControl: ExperimentalUnitControl,
	} = wp.components;
	const { useRef, useState, useEffect, useMemo, createElement: el, Fragment } = wp.element;

	const UnitControl = ExperimentalUnitControl || TextControl;

	const STYLE_PRESETS = {
		default: [],
		silver: [
			{ elementType: 'geometry', stylers: [ { color: '#f5f5f5' } ] },
			{ elementType: 'labels.icon', stylers: [ { visibility: 'off' } ] },
			{ elementType: 'labels.text.fill', stylers: [ { color: '#616161' } ] },
			{ elementType: 'labels.text.stroke', stylers: [ { color: '#f5f5f5' } ] },
			{ featureType: 'administrative.land_parcel', elementType: 'labels.text.fill', stylers: [ { color: '#bdbdbd' } ] },
			{ featureType: 'poi', elementType: 'geometry', stylers: [ { color: '#eeeeee' } ] },
			{ featureType: 'poi', elementType: 'labels.text.fill', stylers: [ { color: '#757575' } ] },
			{ featureType: 'road', elementType: 'geometry', stylers: [ { color: '#ffffff' } ] },
			{ featureType: 'road.arterial', elementType: 'labels.text.fill', stylers: [ { color: '#757575' } ] },
			{ featureType: 'road.highway', elementType: 'geometry', stylers: [ { color: '#dadada' } ] },
			{ featureType: 'water', elementType: 'geometry', stylers: [ { color: '#c9c9c9' } ] },
			{ featureType: 'water', elementType: 'labels.text.fill', stylers: [ { color: '#9e9e9e' } ] },
		],
		dark: [
			{ elementType: 'geometry', stylers: [ { color: '#212121' } ] },
			{ elementType: 'labels.icon', stylers: [ { visibility: 'off' } ] },
			{ elementType: 'labels.text.fill', stylers: [ { color: '#757575' } ] },
			{ elementType: 'labels.text.stroke', stylers: [ { color: '#212121' } ] },
			{ featureType: 'administrative', elementType: 'geometry', stylers: [ { color: '#757575' } ] },
			{ featureType: 'poi', elementType: 'geometry', stylers: [ { color: '#181818' } ] },
			{ featureType: 'road', elementType: 'geometry.fill', stylers: [ { color: '#2c2c2c' } ] },
			{ featureType: 'road', elementType: 'geometry.stroke', stylers: [ { color: '#212121' } ] },
			{ featureType: 'road.highway', elementType: 'geometry', stylers: [ { color: '#3c3c3c' } ] },
			{ featureType: 'water', elementType: 'geometry', stylers: [ { color: '#000000' } ] },
			{ featureType: 'water', elementType: 'labels.text.fill', stylers: [ { color: '#3d3d3d' } ] },
		],
		retro: [
			{ elementType: 'geometry', stylers: [ { color: '#ebe3cd' } ] },
			{ elementType: 'labels.text.fill', stylers: [ { color: '#523735' } ] },
			{ elementType: 'labels.text.stroke', stylers: [ { color: '#f5f1e6' } ] },
			{ featureType: 'administrative', elementType: 'geometry.stroke', stylers: [ { color: '#c9b2a6' } ] },
			{ featureType: 'poi', elementType: 'geometry', stylers: [ { color: '#dfd2ae' } ] },
			{ featureType: 'road', elementType: 'geometry', stylers: [ { color: '#f5f1e6' } ] },
			{ featureType: 'road.highway', elementType: 'geometry', stylers: [ { color: '#f8c967' } ] },
			{ featureType: 'road.highway', elementType: 'geometry.stroke', stylers: [ { color: '#e9bc62' } ] },
			{ featureType: 'water', elementType: 'geometry.fill', stylers: [ { color: '#b9d3c2' } ] },
		],
		night: [
			{ elementType: 'geometry', stylers: [ { color: '#242f3e' } ] },
			{ elementType: 'labels.text.fill', stylers: [ { color: '#746855' } ] },
			{ elementType: 'labels.text.stroke', stylers: [ { color: '#242f3e' } ] },
			{ featureType: 'administrative.locality', elementType: 'labels.text.fill', stylers: [ { color: '#d59563' } ] },
			{ featureType: 'poi', elementType: 'labels.text.fill', stylers: [ { color: '#d59563' } ] },
			{ featureType: 'road', elementType: 'geometry', stylers: [ { color: '#38414e' } ] },
			{ featureType: 'road', elementType: 'geometry.stroke', stylers: [ { color: '#212a37' } ] },
			{ featureType: 'road.highway', elementType: 'geometry', stylers: [ { color: '#746855' } ] },
			{ featureType: 'transit', elementType: 'geometry', stylers: [ { color: '#2f3948' } ] },
			{ featureType: 'water', elementType: 'geometry', stylers: [ { color: '#17263c' } ] },
			{ featureType: 'water', elementType: 'labels.text.fill', stylers: [ { color: '#515c6d' } ] },
		],
		aubergine: [
			{ elementType: 'geometry', stylers: [ { color: '#1d2c4d' } ] },
			{ elementType: 'labels.text.fill', stylers: [ { color: '#8ec3b9' } ] },
			{ elementType: 'labels.text.stroke', stylers: [ { color: '#1a3646' } ] },
			{ featureType: 'administrative.country', elementType: 'geometry.stroke', stylers: [ { color: '#4b6878' } ] },
			{ featureType: 'land_parcel', elementType: 'labels.text.fill', stylers: [ { color: '#64779e' } ] },
			{ featureType: 'poi', elementType: 'geometry', stylers: [ { color: '#283d6a' } ] },
			{ featureType: 'road', elementType: 'geometry', stylers: [ { color: '#304a7d' } ] },
			{ featureType: 'road', elementType: 'geometry.stroke', stylers: [ { color: '#255763' } ] },
			{ featureType: 'road.highway', elementType: 'geometry', stylers: [ { color: '#2c6675' } ] },
			{ featureType: 'transit', elementType: 'geometry', stylers: [ { color: '#182d57' } ] },
			{ featureType: 'water', elementType: 'geometry', stylers: [ { color: '#0e1626' } ] },
			{ featureType: 'water', elementType: 'labels.text.fill', stylers: [ { color: '#4e6d70' } ] },
		],
	};

	window.aegisMapStylePresets = STYLE_PRESETS;

	function features() {
		return ( window.aegisMapEditor && window.aegisMapEditor.features ) || {};
	}

	function editorConfig() {
		return window.aegisMapEditor || {};
	}

	function escapeHtml( value ) {
		return String( value || '' )
			.replace( /&/g, '&amp;' )
			.replace( /</g, '&lt;' )
			.replace( />/g, '&gt;' )
			.replace( /"/g, '&quot;' );
	}

	function previewMarkers( attributes, feat ) {
		if ( feat.markers && attributes.markers && attributes.markers.length ) {
			return attributes.markers;
		}

		return [ {
			lat: attributes.lat,
			lng: attributes.lng,
			title: attributes.address || '',
			description: '',
		} ];
	}

	function loadGoogleMaps( key, onReady ) {
		if ( typeof window.google !== 'undefined' && window.google.maps ) {
			onReady();
			return;
		}

		const existing = document.querySelector( 'script[data-aegis-map-editor-api]' );
		if ( existing ) {
			if ( typeof window.google !== 'undefined' && window.google.maps ) {
				onReady();
				return;
			}
			existing.addEventListener( 'load', onReady );
			return;
		}

		const script = document.createElement( 'script' );
		script.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent( key ) + '&libraries=places';
		script.async = true;
		script.defer = true;
		script.dataset.aegisMapEditorApi = '1';
		script.onload = onReady;
		document.head.appendChild( script );
	}

	function syncMarkers( map, markerRefs, items ) {
		markerRefs.current.forEach( function ( marker ) {
			marker.setMap( null );
		} );
		markerRefs.current = [];

		( items || [] ).forEach( function ( item ) {
			const marker = new window.google.maps.Marker( {
				position: { lat: item.lat, lng: item.lng },
				map: map,
				title: item.title || '',
			} );

			if ( item.title || item.description ) {
				const info = new window.google.maps.InfoWindow( {
					content:
						'<div class="aegis-map-info-window">' +
						( item.title ? '<strong>' + escapeHtml( item.title ) + '</strong>' : '' ) +
						( item.description ? '<p>' + escapeHtml( item.description ) + '</p>' : '' ) +
						'</div>',
				} );
				marker.addListener( 'click', function () {
					info.open( map, marker );
				} );
			}

			markerRefs.current.push( marker );
		} );
	}

	function publishEditorMap( canvas, map, markers ) {
		if ( ! canvas ) {
			return;
		}

		canvas._aegisMap = map || null;
		canvas.__gm_map = map || null;
		canvas._aegisMarkers = markers ? markers.slice() : [];

		const clientId = canvas.getAttribute( 'data-aegis-map-client' ) || '';
		window.aegisMapEditorCanvases = window.aegisMapEditorCanvases || {};
		if ( clientId ) {
			if ( map ) {
				window.aegisMapEditorCanvases[ clientId ] = canvas;
			} else {
				delete window.aegisMapEditorCanvases[ clientId ];
			}
		}

		window.dispatchEvent( new CustomEvent( 'aegis-map-updated', { detail: { clientId: clientId } } ) );
	}

	const icon = el(
		SVG,
		{ xmlns: 'http://www.w3.org/2000/svg', viewBox: '0 0 24 24' },
		el( Path, { d: 'M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z' } )
	);

	function Edit( props ) {
		const { attributes, setAttributes, clientId } = props;
		const feat = features();
		const config = editorConfig();
		const hasBrowserKey = !! config.hasBrowserKey;
		const useGoogle = attributes.provider === 'google' && hasBrowserKey;
		const osmFallback = !! feat.osmFallback;
		const showOsm = attributes.provider === 'openstreetmap' || ( attributes.provider === 'google' && ! hasBrowserKey && osmFallback );

		const blockProps = useBlockProps( { className: 'aegis-map-editor' } );
		const canvasRef = useRef( null );
		const mapRef = useRef( null );
		const markerRefs = useRef( [] );
		const [ loading, setLoading ] = useState( true );
		const [ addressInput, setAddressInput ] = useState( attributes.address || '' );
		const [ searching, setSearching ] = useState( false );
		const canGeocode = !! config.hasServerGeocode;

		useEffect( function () {
			if ( ! useGoogle || ! canvasRef.current || ! config.browserKey ) {
				mapRef.current = null;
				setLoading( false );
				return undefined;
			}

			let cancelled = false;
			setLoading( true );

			loadGoogleMaps( config.browserKey, function () {
				if ( cancelled || ! canvasRef.current ) {
					return;
				}

				const map = new window.google.maps.Map( canvasRef.current, {
					center: { lat: attributes.lat, lng: attributes.lng },
					zoom: attributes.zoom,
					mapTypeId: attributes.mapType,
					styles: feat.styles ? ( STYLE_PRESETS[ attributes.mapStyle ] || [] ) : [],
					zoomControl: feat.controls ? attributes.showZoomControl : true,
					mapTypeControl: feat.controls ? attributes.showMapType : false,
					streetViewControl: feat.controls ? attributes.showStreetView : false,
					fullscreenControl: feat.controls ? attributes.showFullscreen : false,
					scrollwheel: feat.controls ? attributes.scrollWheel : false,
					draggable: feat.controls ? attributes.draggable : true,
				} );

				mapRef.current = map;
				syncMarkers( map, markerRefs, previewMarkers( attributes, feat ) );
				publishEditorMap( canvasRef.current, map, markerRefs.current );
				setLoading( false );
			} );

			return function () {
				cancelled = true;
				if ( canvasRef.current ) {
					publishEditorMap( canvasRef.current, null, [] );
				}
				mapRef.current = null;
			};
		}, [ useGoogle, config.browserKey ] );

		useEffect( function () {
			if ( ! mapRef.current ) {
				return;
			}
			mapRef.current.setCenter( { lat: attributes.lat, lng: attributes.lng } );
			mapRef.current.setZoom( attributes.zoom );
		}, [ attributes.lat, attributes.lng, attributes.zoom ] );

		useEffect( function () {
			if ( ! mapRef.current ) {
				return;
			}
			mapRef.current.setMapTypeId( attributes.mapType );
			mapRef.current.setOptions( {
				styles: feat.styles ? ( STYLE_PRESETS[ attributes.mapStyle ] || [] ) : [],
				zoomControl: feat.controls ? attributes.showZoomControl : true,
				mapTypeControl: feat.controls ? attributes.showMapType : false,
				streetViewControl: feat.controls ? attributes.showStreetView : false,
				fullscreenControl: feat.controls ? attributes.showFullscreen : false,
				scrollwheel: feat.controls ? attributes.scrollWheel : false,
				draggable: feat.controls ? attributes.draggable : true,
				gestureHandling: attributes.scrollWheel ? 'auto' : 'cooperative',
			} );
			publishEditorMap( canvasRef.current, mapRef.current, markerRefs.current );
		}, [ attributes.mapType, attributes.mapStyle, attributes.showZoomControl, attributes.showMapType, attributes.showStreetView, attributes.showFullscreen, attributes.scrollWheel, attributes.draggable, feat.styles, feat.controls ] );

		useEffect( function () {
			if ( ! mapRef.current ) {
				return;
			}
			syncMarkers( mapRef.current, markerRefs, previewMarkers( attributes, feat ) );
			publishEditorMap( canvasRef.current, mapRef.current, markerRefs.current );
		}, [ attributes.markers, attributes.lat, attributes.lng, attributes.address, feat.markers ] );

		const osmSrc = useMemo( function () {
			const delta = 0.01 / Math.max( 1, attributes.zoom / 10 );
			return 'https://www.openstreetmap.org/export/embed.html?bbox=' +
				[ attributes.lng - delta, attributes.lat - delta, attributes.lng + delta, attributes.lat + delta ].join( '%2C' ) +
				'&layer=mapnik&marker=' + attributes.lat + '%2C' + attributes.lng;
		}, [ attributes.lat, attributes.lng, attributes.zoom ] );

		function updateMarker( index, patch ) {
			setAttributes( {
				markers: attributes.markers.map( function ( marker, i ) {
					return i === index ? Object.assign( {}, marker, patch ) : marker;
				} ),
			} );
		}

		function searchAddress() {
			const query = addressInput.trim();
			if ( ! query || ! config.restUrl || ! canGeocode ) {
				return;
			}

			setSearching( true );
			window.fetch( config.restUrl + '?address=' + encodeURIComponent( query ), {
				headers: { 'X-WP-Nonce': config.restNonce },
			} )
				.then( function ( response ) { return response.json().then( function ( body ) { return { ok: response.ok, body: body }; } ); } )
				.then( function ( result ) {
					if ( result.ok && result.body.lat && result.body.lng ) {
						const formatted = result.body.formatted_address || query;
						setAddressInput( formatted );
						setAttributes( {
							lat: result.body.lat,
							lng: result.body.lng,
							address: formatted,
						} );
					}
				} )
				.catch( function () {} )
				.finally( function () { setSearching( false ); } );
		}

		const inspector = [
			el(
				PanelBody,
				{ title: __( 'Map Settings', 'aegis' ), key: 'settings' },
				el( SelectControl, {
					label: __( 'Map Provider', 'aegis' ),
					value: attributes.provider,
					options: [
						{ label: __( 'Google Maps', 'aegis' ), value: 'google' },
						{ label: __( 'OpenStreetMap', 'aegis' ), value: 'openstreetmap' },
					],
					onChange: function ( value ) { setAttributes( { provider: value } ); },
					help: attributes.provider !== 'google' || hasBrowserKey
						? undefined
						: __( 'Configure your API key in Aegis → Connectors → Google Maps.', 'aegis' ),
				} ),
				el( TextControl, {
					label: __( 'Address', 'aegis' ),
					value: addressInput,
					onChange: setAddressInput,
					help: canGeocode
						? __( 'Enter an address and click Search to geocode.', 'aegis' )
						: __( 'Configure a server API key in Aegis → Connectors → Google Maps to search addresses.', 'aegis' ),
				} ),
				el( Button, {
					variant: 'secondary',
					onClick: searchAddress,
					isBusy: searching,
					disabled: searching || ! canGeocode || ! addressInput,
					style: { marginBottom: '16px' },
				}, searching ? __( 'Searching…', 'aegis' ) : __( 'Search Address', 'aegis' ) ),
				el( TextControl, {
					label: __( 'Latitude', 'aegis' ),
					value: String( attributes.lat ),
					onChange: function ( value ) {
						const parsed = parseFloat( value );
						if ( ! Number.isNaN( parsed ) ) {
							setAttributes( { lat: parsed } );
						}
					},
				} ),
				el( TextControl, {
					label: __( 'Longitude', 'aegis' ),
					value: String( attributes.lng ),
					onChange: function ( value ) {
						const parsed = parseFloat( value );
						if ( ! Number.isNaN( parsed ) ) {
							setAttributes( { lng: parsed } );
						}
					},
				} ),
				el( RangeControl, {
					label: __( 'Zoom', 'aegis' ),
					value: attributes.zoom,
					onChange: function ( value ) { setAttributes( { zoom: value } ); },
					min: 1,
					max: 21,
				} ),
				el( UnitControl, {
					label: __( 'Height', 'aegis' ),
					value: attributes.height,
					onChange: function ( value ) { setAttributes( { height: value || '400px' } ); },
				} )
			),
		];

		if ( feat.markers ) {
			inspector.push(
				el(
					PanelBody,
					{ title: __( 'Markers', 'aegis' ), initialOpen: false, key: 'markers' },
					( attributes.markers || [] ).map( function ( marker, index ) {
						return el(
							'div',
							{
								key: index,
								style: { padding: '12px', marginBottom: '12px', border: '1px solid #ddd', borderRadius: '4px', background: '#fafafa' },
							},
							el( TextControl, { label: __( 'Title', 'aegis' ), value: marker.title, onChange: function ( value ) { updateMarker( index, { title: value } ); } } ),
							el( TextControl, { label: __( 'Description', 'aegis' ), value: marker.description, onChange: function ( value ) { updateMarker( index, { description: value } ); } } ),
							el( TextControl, {
								label: __( 'Latitude', 'aegis' ),
								value: String( marker.lat ),
								onChange: function ( value ) {
									const parsed = parseFloat( value );
									if ( ! Number.isNaN( parsed ) ) {
										updateMarker( index, { lat: parsed } );
									}
								},
							} ),
							el( TextControl, {
								label: __( 'Longitude', 'aegis' ),
								value: String( marker.lng ),
								onChange: function ( value ) {
									const parsed = parseFloat( value );
									if ( ! Number.isNaN( parsed ) ) {
										updateMarker( index, { lng: parsed } );
									}
								},
							} ),
							el( Button, {
								variant: 'tertiary',
								isDestructive: true,
								onClick: function () {
									setAttributes( { markers: attributes.markers.filter( function ( _item, i ) { return i !== index; } ) } );
								},
								style: { marginTop: '4px' },
							}, __( 'Remove Marker', 'aegis' ) )
						);
					} ),
					el( Button, {
						variant: 'secondary',
						onClick: function () {
							setAttributes( {
								markers: ( attributes.markers || [] ).concat( [ { lat: attributes.lat, lng: attributes.lng, title: '', description: '' } ] ),
							} );
						},
						style: { width: '100%', justifyContent: 'center' },
					}, __( 'Add Marker at Center', 'aegis' ) )
				)
			);
		}

		if ( feat.styles || useGoogle ) {
			inspector.push(
				el(
					PanelBody,
					{ title: __( 'Map Style', 'aegis' ), initialOpen: false, key: 'style' },
					useGoogle
						? el(
							Fragment,
							null,
							el( SelectControl, {
								label: __( 'Map Type', 'aegis' ),
								value: attributes.mapType,
								options: [
									{ label: __( 'Roadmap', 'aegis' ), value: 'roadmap' },
									{ label: __( 'Satellite', 'aegis' ), value: 'satellite' },
									{ label: __( 'Hybrid', 'aegis' ), value: 'hybrid' },
									{ label: __( 'Terrain', 'aegis' ), value: 'terrain' },
								],
								onChange: function ( value ) { setAttributes( { mapType: value } ); },
							} ),
							feat.styles
								? el( SelectControl, {
									label: __( 'Style Preset', 'aegis' ),
									value: attributes.mapStyle,
									options: [
										{ label: __( 'Default', 'aegis' ), value: 'default' },
										{ label: __( 'Silver', 'aegis' ), value: 'silver' },
										{ label: __( 'Dark', 'aegis' ), value: 'dark' },
										{ label: __( 'Retro', 'aegis' ), value: 'retro' },
										{ label: __( 'Night', 'aegis' ), value: 'night' },
										{ label: __( 'Aubergine', 'aegis' ), value: 'aubergine' },
									],
									onChange: function ( value ) { setAttributes( { mapStyle: value } ); },
								} )
								: null
						)
						: el( 'p', { style: { color: '#757575', fontStyle: 'italic' } }, __( 'Style presets are available with Google Maps provider.', 'aegis' ) )
				)
			);
		}

		if ( feat.controls ) {
			inspector.push(
				el(
					PanelBody,
					{ title: __( 'Controls', 'aegis' ), initialOpen: false, key: 'controls' },
					el( ToggleControl, { label: __( 'Zoom Control', 'aegis' ), checked: attributes.showZoomControl, onChange: function ( value ) { setAttributes( { showZoomControl: value } ); } } ),
					useGoogle
						? el(
							Fragment,
							null,
							el( ToggleControl, { label: __( 'Map Type Control', 'aegis' ), checked: attributes.showMapType, onChange: function ( value ) { setAttributes( { showMapType: value } ); } } ),
							el( ToggleControl, { label: __( 'Street View Control', 'aegis' ), checked: attributes.showStreetView, onChange: function ( value ) { setAttributes( { showStreetView: value } ); } } ),
							el( ToggleControl, { label: __( 'Fullscreen Control', 'aegis' ), checked: attributes.showFullscreen, onChange: function ( value ) { setAttributes( { showFullscreen: value } ); } } )
						)
						: null,
					el( ToggleControl, { label: __( 'Scroll Wheel Zoom', 'aegis' ), checked: attributes.scrollWheel, onChange: function ( value ) { setAttributes( { scrollWheel: value } ); }, help: __( 'Allow zooming with the mouse scroll wheel.', 'aegis' ) } ),
					el( ToggleControl, { label: __( 'Draggable', 'aegis' ), checked: attributes.draggable, onChange: function ( value ) { setAttributes( { draggable: value } ); } } )
				)
			);
		}

		if ( feat.schema ) {
			inspector.push(
				el(
					PanelBody,
					{ title: __( 'Schema.org LocalBusiness', 'aegis' ), initialOpen: false, key: 'schema' },
					el( ToggleControl, { label: __( 'Enable Schema Markup', 'aegis' ), checked: attributes.schemaEnabled, onChange: function ( value ) { setAttributes( { schemaEnabled: value } ); }, help: __( 'Add LocalBusiness structured data for search engines.', 'aegis' ) } ),
					attributes.schemaEnabled
						? el(
							Fragment,
							null,
							el( TextControl, { label: __( 'Business Name', 'aegis' ), value: attributes.schemaBusinessName, onChange: function ( value ) { setAttributes( { schemaBusinessName: value } ); } } ),
							el( TextControl, { label: __( 'Phone', 'aegis' ), value: attributes.schemaPhone, onChange: function ( value ) { setAttributes( { schemaPhone: value } ); }, type: 'tel' } ),
							el( TextControl, { label: __( 'Address', 'aegis' ), value: attributes.schemaAddress, onChange: function ( value ) { setAttributes( { schemaAddress: value } ); } } )
						)
						: null
				)
			);
		}

		let previewNotice = null;
		if ( attributes.provider === 'google' && ! hasBrowserKey && osmFallback ) {
			previewNotice = el( Notice, { status: 'warning', isDismissible: false }, __( 'Google Maps API key not configured. Using OpenStreetMap fallback. Configure your key in Aegis → Connectors → Google Maps.', 'aegis' ) );
		} else if ( attributes.provider === 'google' && ! hasBrowserKey && ! osmFallback ) {
			previewNotice = el( Notice, { status: 'warning', isDismissible: false }, __( 'This map needs a Google Maps API key. Configure it in Aegis → Connectors → Google Maps.', 'aegis' ) );
		}

		let preview = null;
		if ( useGoogle ) {
			preview = el(
				'div',
				{ style: { position: 'relative', width: '100%', height: attributes.height } },
				loading
					? el( 'div', {
						className: 'aegis-map-editor__loading',
						style: { position: 'absolute', inset: 0, zIndex: 1, display: 'flex', alignItems: 'center', justifyContent: 'center', background: '#f0f0f0', borderRadius: '4px', color: '#757575' },
					}, __( 'Loading map…', 'aegis' ) )
					: null,
				el( 'div', {
					ref: canvasRef,
					className: 'aegis-map-editor__canvas',
					'data-aegis-map-client': clientId,
					style: { width: '100%', height: '100%', borderRadius: '4px', overflow: 'hidden' },
				} )
			);
		} else if ( showOsm ) {
			preview = el( 'iframe', {
				title: __( 'OpenStreetMap', 'aegis' ),
				src: osmSrc,
				style: { width: '100%', height: attributes.height, border: 'none', borderRadius: '4px' },
				loading: 'lazy',
			} );
		}

		return el(
			'div',
			blockProps,
			el( InspectorControls, null, inspector ),
			previewNotice,
			preview
		);
	}

	registerBlockType( 'aegis/map', {
		icon: icon,
		edit: Edit,
		save: function () {
			return null;
		},
	} );
} )( window.wp );
