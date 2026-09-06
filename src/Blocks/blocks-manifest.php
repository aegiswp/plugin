<?php
// This file is generated. Do not modify it manually.
return array(
	'map' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aegis/map',
		'version' => '1.0.0',
		'title' => 'Map',
		'category' => 'design',
		'description' => 'An interactive map block with Google Maps and OpenStreetMap support, markers, style presets, and map controls.',
		'icon' => 'location-alt',
		'textdomain' => 'aegis',
		'keywords' => array(
			'map',
			'google maps',
			'openstreetmap',
			'location',
			'marker'
		),
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'className' => true,
			'anchor' => true
		),
		'attributes' => array(
			'address' => array(
				'type' => 'string',
				'default' => ''
			),
			'lat' => array(
				'type' => 'number',
				'default' => 4.6495
			),
			'lng' => array(
				'type' => 'number',
				'default' => -74.0627
			),
			'zoom' => array(
				'type' => 'number',
				'default' => 15
			),
			'height' => array(
				'type' => 'string',
				'default' => '400px'
			),
			'mapType' => array(
				'type' => 'string',
				'default' => 'roadmap',
				'enum' => array(
					'roadmap',
					'satellite',
					'hybrid',
					'terrain'
				)
			),
			'mapStyle' => array(
				'type' => 'string',
				'default' => 'default',
				'enum' => array(
					'default',
					'silver',
					'dark',
					'retro',
					'night',
					'aubergine'
				)
			),
			'markers' => array(
				'type' => 'array',
				'default' => array(
					
				),
				'items' => array(
					'type' => 'object'
				)
			),
			'showZoomControl' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showMapType' => array(
				'type' => 'boolean',
				'default' => false
			),
			'showStreetView' => array(
				'type' => 'boolean',
				'default' => false
			),
			'showFullscreen' => array(
				'type' => 'boolean',
				'default' => false
			),
			'scrollWheel' => array(
				'type' => 'boolean',
				'default' => false
			),
			'draggable' => array(
				'type' => 'boolean',
				'default' => true
			),
			'provider' => array(
				'type' => 'string',
				'default' => 'google',
				'enum' => array(
					'google',
					'openstreetmap'
				)
			),
			'schemaEnabled' => array(
				'type' => 'boolean',
				'default' => false
			),
			'schemaBusinessName' => array(
				'type' => 'string',
				'default' => ''
			),
			'schemaPhone' => array(
				'type' => 'string',
				'default' => ''
			),
			'schemaAddress' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'editorScript' => 'file:index.js',
		'style' => 'file:style.css',
		'viewScript' => 'file:view.js',
		'render' => 'file:render.php'
	),
	'modal' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aegis/modal',
		'version' => '1.0.0',
		'title' => 'Modal',
		'category' => 'design',
		'description' => 'A fully accessible modal/popup block with multiple trigger types and animations.',
		'icon' => 'editor-expand',
		'textdomain' => 'aegis',
		'keywords' => array(
			'modal',
			'popup',
			'dialog',
			'lightbox',
			'overlay'
		),
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'className' => true,
			'anchor' => true
		),
		'attributes' => array(
			'modalId' => array(
				'type' => 'string',
				'default' => ''
			),
			'modalType' => array(
				'type' => 'string',
				'default' => 'popup',
				'enum' => array(
					'popup',
					'off-canvas',
					'bottom-sheet',
					'fullscreen'
				)
			),
			'triggerType' => array(
				'type' => 'string',
				'default' => 'button',
				'enum' => array(
					'button',
					'text',
					'icon',
					'image',
					'scroll',
					'exit-intent',
					'timed'
				)
			),
			'triggerText' => array(
				'type' => 'string',
				'default' => 'Open Modal'
			),
			'triggerIcon' => array(
				'type' => 'string',
				'default' => ''
			),
			'triggerImageUrl' => array(
				'type' => 'string',
				'default' => ''
			),
			'triggerImageAlt' => array(
				'type' => 'string',
				'default' => ''
			),
			'modalTitle' => array(
				'type' => 'string',
				'default' => ''
			),
			'animation' => array(
				'type' => 'string',
				'default' => 'fade',
				'enum' => array(
					'fade',
					'slide-up',
					'slide-down',
					'slide-left',
					'slide-right',
					'zoom',
					'none'
				)
			),
			'animationDuration' => array(
				'type' => 'number',
				'default' => 200
			),
			'closeOnEsc' => array(
				'type' => 'boolean',
				'default' => true
			),
			'closeOnOverlay' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showCloseButton' => array(
				'type' => 'boolean',
				'default' => true
			),
			'closeButtonPosition' => array(
				'type' => 'string',
				'default' => 'outside',
				'enum' => array(
					'inside',
					'outside'
				)
			),
			'preventBodyScroll' => array(
				'type' => 'boolean',
				'default' => true
			),
			'focusTrap' => array(
				'type' => 'boolean',
				'default' => true
			),
			'returnFocus' => array(
				'type' => 'boolean',
				'default' => true
			),
			'width' => array(
				'type' => 'string',
				'default' => '500px'
			),
			'maxWidth' => array(
				'type' => 'string',
				'default' => '90vw'
			),
			'height' => array(
				'type' => 'string',
				'default' => 'auto'
			),
			'maxHeight' => array(
				'type' => 'string',
				'default' => '90vh'
			),
			'overlayColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'overlayBlur' => array(
				'type' => 'number',
				'default' => 0
			),
			'backgroundColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadius' => array(
				'type' => 'string',
				'default' => '8px'
			),
			'padding' => array(
				'type' => 'string',
				'default' => '24px'
			),
			'scrollTriggerPercent' => array(
				'type' => 'number',
				'default' => 50
			),
			'scrollTriggerOnce' => array(
				'type' => 'boolean',
				'default' => true
			),
			'exitIntentSensitivity' => array(
				'type' => 'number',
				'default' => 20
			),
			'exitIntentDelay' => array(
				'type' => 'number',
				'default' => 0
			),
			'timedTriggerDelay' => array(
				'type' => 'number',
				'default' => 5000
			),
			'autoCloseDelay' => array(
				'type' => 'number',
				'default' => 0
			),
			'showOnce' => array(
				'type' => 'boolean',
				'default' => false
			),
			'showOnceExpiry' => array(
				'type' => 'number',
				'default' => 7
			),
			'deviceVisibility' => array(
				'type' => 'array',
				'default' => array(
					'desktop',
					'tablet',
					'mobile'
				)
			),
			'isEnabled' => array(
				'type' => 'boolean',
				'default' => true
			)
		),
		'editorScript' => 'file:index.js',
		'style' => 'file:style.css',
		'viewScript' => 'file:view.js',
		'render' => 'file:render.php'
	)
);
