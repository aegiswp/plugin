/**
 * Map block frontend: Google Maps facade → interactive map.
 *
 * @package Aegis\Plugin
 */
( function () {
	'use strict';

	var INIT_ATTR = 'data-aegis-map-init';

	var STYLE_PRESETS = {
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

	function parseConfig( wrapper ) {
		var markers = [];
		var raw = wrapper.getAttribute( 'data-markers' );

		if ( raw ) {
			try {
				markers = JSON.parse( raw );
			} catch ( err ) {
				markers = [];
			}
		}

		return {
			lat: parseFloat( wrapper.getAttribute( 'data-lat' ) || '0' ),
			lng: parseFloat( wrapper.getAttribute( 'data-lng' ) || '0' ),
			zoom: parseInt( wrapper.getAttribute( 'data-zoom' ) || '15', 10 ),
			mapType: wrapper.getAttribute( 'data-map-type' ) || 'roadmap',
			mapStyle: wrapper.getAttribute( 'data-map-style' ) || 'default',
			provider: wrapper.getAttribute( 'data-provider' ) || 'google',
			markers: markers,
			zoomControl: wrapper.getAttribute( 'data-zoom-control' ) !== 'false',
			mapTypeControl: wrapper.getAttribute( 'data-map-type-control' ) === 'true',
			streetView: wrapper.getAttribute( 'data-street-view' ) === 'true',
			fullscreen: wrapper.getAttribute( 'data-fullscreen' ) === 'true',
			scrollWheel: wrapper.getAttribute( 'data-scroll-wheel' ) === 'true',
			draggable: wrapper.getAttribute( 'data-draggable' ) !== 'false',
		};
	}

	function loadGoogleMaps( src, onReady ) {
		if ( typeof google !== 'undefined' && google.maps ) {
			onReady();
			return;
		}

		if ( document.querySelector( 'script[src="' + src + '"]' ) ) {
			var attempts = 0;
			var timer = setInterval( function () {
				attempts += 1;
				if ( typeof google !== 'undefined' && google.maps ) {
					clearInterval( timer );
					onReady();
				} else if ( attempts > 50 ) {
					clearInterval( timer );
				}
			}, 100 );
			return;
		}

		var script = document.createElement( 'script' );
		script.src = src;
		script.async = true;
		script.defer = true;
		script.onload = onReady;
		document.head.appendChild( script );
	}

	function escapeHtml( value ) {
		return String( value || '' )
			.replace( /&/g, '&amp;' )
			.replace( /</g, '&lt;' )
			.replace( />/g, '&gt;' )
			.replace( /"/g, '&quot;' );
	}

	function createMarkers( map, items ) {
		var instances = [];

		items.forEach( function ( item ) {
			var marker = new google.maps.Marker( {
				position: { lat: item.lat, lng: item.lng },
				map: map,
				title: item.title || '',
			} );

			if ( item.title || item.description ) {
				var info = new google.maps.InfoWindow( {
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

			instances.push( marker );
		} );

		return instances;
	}

	function activateMap( canvas, config ) {
		var options = {
			center: { lat: config.lat, lng: config.lng },
			zoom: config.zoom,
			mapTypeId: config.mapType,
			zoomControl: config.zoomControl,
			mapTypeControl: config.mapTypeControl,
			streetViewControl: config.streetView,
			fullscreenControl: config.fullscreen,
			scrollwheel: config.scrollWheel,
			draggable: config.draggable,
			gestureHandling: config.scrollWheel ? 'auto' : 'cooperative',
			styles: STYLE_PRESETS[ config.mapStyle ] || [],
		};

		var map = new google.maps.Map( canvas, options );
		var markers = createMarkers( map, config.markers );

		canvas._aegisMap = map;
		canvas.__gm_map = map;
		canvas._aegisMarkers = markers;

		return map;
	}

	function initWrapper( wrapper ) {
		if ( wrapper.hasAttribute( INIT_ATTR ) ) {
			return;
		}

		wrapper.setAttribute( INIT_ATTR, '' );

		var config = parseConfig( wrapper );

		if ( config.provider !== 'google' ) {
			return;
		}

		var facade = wrapper.querySelector( '.aegis-map__facade' );
		var canvas = wrapper.querySelector( '.aegis-map__canvas' );
		var activate = wrapper.querySelector( '.aegis-map__activate' );
		var apiSrc = wrapper.querySelector( '.aegis-map__api-src' );

		if ( ! facade || ! canvas || ! activate || ! apiSrc ) {
			return;
		}

		var src = apiSrc.getAttribute( 'data-src' );

		if ( ! src ) {
			return;
		}

		activate.addEventListener( 'click', function () {
			if ( wrapper.classList.contains( 'is-activated' ) ) {
				return;
			}

			activate.disabled = true;

			loadGoogleMaps( src, function () {
				facade.setAttribute( 'hidden', '' );
				canvas.removeAttribute( 'hidden' );
				activateMap( canvas, config );
				wrapper.classList.add( 'is-activated' );
			} );
		} );
	}

	function initAll() {
		document.querySelectorAll( '.aegis-map:not([' + INIT_ATTR + '])' ).forEach( initWrapper );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initAll );
	} else {
		initAll();
	}
} )();
