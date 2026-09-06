/**
 * Modal block editor.
 *
 * @package Aegis\Plugin
 */
( function ( wp ) {
	'use strict';

	const { registerBlockType } = wp.blocks;
	const { SVG, Path } = wp.primitives;
	const { __ } = wp.i18n;
	const { InspectorControls, InnerBlocks, MediaUpload, MediaUploadCheck, useBlockProps } = wp.blockEditor;
	const {
		PanelBody,
		SelectControl,
		TextControl,
		Button,
		RangeControl,
		ToggleControl,
		__experimentalUnitControl: ExperimentalUnitControl,
	} = wp.components;
	const { createElement: el } = wp.element;

	const UnitControl = ExperimentalUnitControl || TextControl;

	const icon = el(
		SVG,
		{ xmlns: 'http://www.w3.org/2000/svg', viewBox: '0 0 24 24' },
		el( Path, { d: 'M18 4H6c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 14H6V6h12v12zM8 8h8v2H8V8zm0 4h8v2H8v-2z' } )
	);

	function features() {
		return ( window.aegisModalEditor && window.aegisModalEditor.features ) || {};
	}

	function selectValue( options, current, fallback ) {
		if ( options.some( function ( option ) {
			return option.value === current;
		} ) ) {
			return current;
		}

		return options.length ? options[ 0 ].value : fallback;
	}

	function Edit( props ) {
		const { attributes, setAttributes } = props;
		const feat = features();

		const typeOptions = [ { label: __( 'Popup', 'aegis' ), value: 'popup' } ];
		if ( feat.offcanvas ) {
			typeOptions.push( { label: __( 'Off-Canvas', 'aegis' ), value: 'off-canvas' } );
			typeOptions.push( { label: __( 'Bottom Sheet', 'aegis' ), value: 'bottom-sheet' } );
		}
		if ( feat.fullscreen ) {
			typeOptions.push( { label: __( 'Fullscreen', 'aegis' ), value: 'fullscreen' } );
		}

		const triggerOptions = [];
		if ( feat.click ) {
			triggerOptions.push( { label: __( 'Button', 'aegis' ), value: 'button' } );
		}
		if ( feat.text ) {
			triggerOptions.push( { label: __( 'Text', 'aegis' ), value: 'text' } );
		}
		if ( feat.icon ) {
			triggerOptions.push( { label: __( 'Icon', 'aegis' ), value: 'icon' } );
		}
		if ( feat.image ) {
			triggerOptions.push( { label: __( 'Image', 'aegis' ), value: 'image' } );
		}
		if ( feat.scrollDepth ) {
			triggerOptions.push( { label: __( 'Scroll Position', 'aegis' ), value: 'scroll' } );
		}
		if ( feat.exitIntent ) {
			triggerOptions.push( { label: __( 'Exit Intent', 'aegis' ), value: 'exit-intent' } );
		}
		if ( feat.timeDelay ) {
			triggerOptions.push( { label: __( 'Timed Delay', 'aegis' ), value: 'timed' } );
		}

		const animationOptions = feat.animations
			? [
				{ label: __( 'Fade', 'aegis' ), value: 'fade' },
				{ label: __( 'Slide Up', 'aegis' ), value: 'slide-up' },
				{ label: __( 'Slide Down', 'aegis' ), value: 'slide-down' },
				{ label: __( 'Slide Left', 'aegis' ), value: 'slide-left' },
				{ label: __( 'Slide Right', 'aegis' ), value: 'slide-right' },
				{ label: __( 'Zoom', 'aegis' ), value: 'zoom' },
				{ label: __( 'None', 'aegis' ), value: 'none' },
			]
			: [ { label: __( 'None', 'aegis' ), value: 'none' } ];

		const modalTypeValue = selectValue( typeOptions, attributes.modalType, 'popup' );
		const triggerTypeValue = selectValue( triggerOptions, attributes.triggerType, 'button' );
		const animationValue = selectValue( animationOptions, attributes.animation, 'none' );
		const blockProps = useBlockProps( {
			className: 'aegis-modal-editor aegis-modal-type-' + modalTypeValue,
		} );

		const inspector = [
			el(
				PanelBody,
				{ title: __( 'Modal Settings', 'aegis' ), key: 'settings' },
				el( SelectControl, {
					label: __( 'Modal Type', 'aegis' ),
					value: modalTypeValue,
					options: typeOptions,
					onChange: function ( value ) {
						setAttributes( { modalType: value } );
					},
				} ),
				el( TextControl, {
					label: __( 'Modal Title', 'aegis' ),
					value: attributes.modalTitle,
					onChange: function ( value ) {
						setAttributes( { modalTitle: value } );
					},
					help: __( 'Used for accessibility. Can be visually hidden.', 'aegis' ),
				} ),
				el( TextControl, {
					label: __( 'Modal ID', 'aegis' ),
					value: attributes.modalId,
					onChange: function ( value ) {
						setAttributes( { modalId: value } );
					},
					help: __( 'Unique identifier. Auto-generated if empty.', 'aegis' ),
				} )
			),
		];

		if ( triggerOptions.length ) {
			inspector.push(
				el(
					PanelBody,
					{ title: __( 'Trigger Settings', 'aegis' ), initialOpen: false, key: 'trigger' },
					el( SelectControl, {
						label: __( 'Trigger Type', 'aegis' ),
						value: triggerTypeValue,
						options: triggerOptions,
						onChange: function ( value ) {
							setAttributes( { triggerType: value } );
						},
					} ),
					[ 'button', 'text', 'icon' ].indexOf( triggerTypeValue ) !== -1
						? el( TextControl, {
							label: __( 'Trigger Text', 'aegis' ),
							value: attributes.triggerText,
							onChange: function ( value ) {
								setAttributes( { triggerText: value } );
							},
						} )
						: null,
					triggerTypeValue === 'icon'
						? el( TextControl, {
							label: __( 'Trigger Icon HTML', 'aegis' ),
							value: attributes.triggerIcon,
							onChange: function ( value ) {
								setAttributes( { triggerIcon: value } );
							},
							help: __( 'Inline SVG or HTML for the icon trigger. Empty uses a default icon.', 'aegis' ),
						} )
						: null,
					triggerTypeValue === 'scroll'
						? el( RangeControl, {
							label: __( 'Scroll Percentage (%)', 'aegis' ),
							value: attributes.scrollTriggerPercent || 50,
							onChange: function ( value ) {
								setAttributes( { scrollTriggerPercent: value } );
							},
							min: 0,
							max: 100,
						} )
						: null,
					triggerTypeValue === 'scroll'
						? el( ToggleControl, {
							label: __( 'Trigger Only Once', 'aegis' ),
							checked: attributes.scrollTriggerOnce !== false,
							onChange: function ( value ) {
								setAttributes( { scrollTriggerOnce: value } );
							},
							help: __( 'Only trigger once per page load.', 'aegis' ),
						} )
						: null,
					triggerTypeValue === 'exit-intent'
						? el( RangeControl, {
							label: __( 'Sensitivity (px)', 'aegis' ),
							value: attributes.exitIntentSensitivity || 20,
							onChange: function ( value ) {
								setAttributes( { exitIntentSensitivity: value } );
							},
							min: 0,
							max: 100,
							help: __( 'Distance from the top of the viewport to trigger.', 'aegis' ),
						} )
						: null,
					triggerTypeValue === 'exit-intent'
						? el( RangeControl, {
							label: __( 'Delay (ms)', 'aegis' ),
							value: attributes.exitIntentDelay || 0,
							onChange: function ( value ) {
								setAttributes( { exitIntentDelay: value } );
							},
							min: 0,
							max: 5000,
							step: 100,
							help: __( 'Wait this long after exit intent before opening.', 'aegis' ),
						} )
						: null,
					triggerTypeValue === 'timed'
						? el( RangeControl, {
							label: __( 'Delay (seconds)', 'aegis' ),
							value: ( attributes.timedTriggerDelay || 5000 ) / 1000,
							onChange: function ( value ) {
								setAttributes( { timedTriggerDelay: value * 1000 } );
							},
							min: 1,
							max: 60,
						} )
						: null,
					triggerTypeValue === 'image'
						? el(
							MediaUploadCheck,
							null,
							el( MediaUpload, {
								onSelect: function ( media ) {
									setAttributes( {
										triggerImageUrl: media.url,
										triggerImageAlt: media.alt,
									} );
								},
								allowedTypes: [ 'image' ],
								render: function ( ref ) {
									return el(
										Button,
										{ onClick: ref.open, variant: 'secondary' },
										attributes.triggerImageUrl
											? __( 'Replace Image', 'aegis' )
											: __( 'Select Image', 'aegis' )
									);
								},
							} )
						)
						: null
				)
			);
		}

		inspector.push(
			el(
				PanelBody,
				{ title: __( 'Behavior', 'aegis' ), initialOpen: false, key: 'behavior' },
				el( SelectControl, {
					label: __( 'Animation', 'aegis' ),
					value: animationValue,
					options: animationOptions,
					onChange: function ( value ) {
						setAttributes( { animation: value } );
					},
				} ),
				feat.animations
					? el( RangeControl, {
						label: __( 'Animation Duration (ms)', 'aegis' ),
						value: attributes.animationDuration,
						onChange: function ( value ) {
							setAttributes( { animationDuration: value } );
						},
						min: 0,
						max: 1000,
						step: 50,
					} )
					: null,
				el( ToggleControl, {
					label: __( 'Close on Escape', 'aegis' ),
					checked: attributes.closeOnEsc,
					onChange: function ( value ) {
						setAttributes( { closeOnEsc: value } );
					},
				} ),
				el( ToggleControl, {
					label: __( 'Close on Overlay Click', 'aegis' ),
					checked: attributes.closeOnOverlay,
					onChange: function ( value ) {
						setAttributes( { closeOnOverlay: value } );
					},
				} ),
				el( ToggleControl, {
					label: __( 'Show Close Button', 'aegis' ),
					checked: attributes.showCloseButton,
					onChange: function ( value ) {
						setAttributes( { showCloseButton: value } );
					},
				} ),
				attributes.showCloseButton
					? el( SelectControl, {
						label: __( 'Close Button Position', 'aegis' ),
						value: attributes.closeButtonPosition,
						options: [
							{ label: __( 'On dialog', 'aegis' ), value: 'inside' },
							{ label: __( 'Top right of screen', 'aegis' ), value: 'outside' },
						],
						onChange: function ( value ) {
							setAttributes( { closeButtonPosition: value } );
						},
						help: __( 'On dialog pins the control to the panel. Top right of screen matches the Image lightbox.', 'aegis' ),
					} )
					: null
			),
			el(
				PanelBody,
				{ title: __( 'Accessibility', 'aegis' ), initialOpen: false, key: 'a11y' },
				el( ToggleControl, {
					label: __( 'Prevent Body Scroll', 'aegis' ),
					checked: attributes.preventBodyScroll,
					onChange: function ( value ) {
						setAttributes( { preventBodyScroll: value } );
					},
				} ),
				el( ToggleControl, {
					label: __( 'Focus Trap', 'aegis' ),
					checked: attributes.focusTrap,
					onChange: function ( value ) {
						setAttributes( { focusTrap: value } );
					},
					help: __( 'Keep focus within the modal when open.', 'aegis' ),
				} ),
				el( ToggleControl, {
					label: __( 'Return Focus', 'aegis' ),
					checked: attributes.returnFocus,
					onChange: function ( value ) {
						setAttributes( { returnFocus: value } );
					},
					help: __( 'Return focus to trigger when modal closes.', 'aegis' ),
				} )
			),
			el(
				PanelBody,
				{ title: __( 'Size & Styling', 'aegis' ), initialOpen: false, key: 'size' },
				el( UnitControl, {
					label: __( 'Width', 'aegis' ),
					value: attributes.width,
					onChange: function ( value ) {
						setAttributes( { width: value || '500px' } );
					},
				} ),
				el( UnitControl, {
					label: __( 'Max Width', 'aegis' ),
					value: attributes.maxWidth,
					onChange: function ( value ) {
						setAttributes( { maxWidth: value || '90vw' } );
					},
				} ),
				el( UnitControl, {
					label: __( 'Height', 'aegis' ),
					value: attributes.height,
					onChange: function ( value ) {
						setAttributes( { height: value || 'auto' } );
					},
				} ),
				el( UnitControl, {
					label: __( 'Max Height', 'aegis' ),
					value: attributes.maxHeight,
					onChange: function ( value ) {
						setAttributes( { maxHeight: value || '90vh' } );
					},
				} ),
				el( UnitControl, {
					label: __( 'Border Radius', 'aegis' ),
					value: attributes.borderRadius,
					onChange: function ( value ) {
						setAttributes( { borderRadius: value || '8px' } );
					},
				} ),
				el( UnitControl, {
					label: __( 'Padding', 'aegis' ),
					value: attributes.padding,
					onChange: function ( value ) {
						setAttributes( { padding: value || '24px' } );
					},
				} ),
				el( RangeControl, {
					label: __( 'Overlay Blur (px)', 'aegis' ),
					value: attributes.overlayBlur,
					onChange: function ( value ) {
						setAttributes( { overlayBlur: value } );
					},
					min: 0,
					max: 20,
				} )
			)
		);

		const triggerPreview = el(
			'div',
			{ className: 'aegis-modal-editor__trigger-preview' },
			el(
				'span',
				{ className: 'aegis-modal-trigger aegis-modal-trigger--' + triggerTypeValue },
				triggerTypeValue === 'image' && attributes.triggerImageUrl
					? el( 'img', { src: attributes.triggerImageUrl, alt: attributes.triggerImageAlt } )
					: el( 'span', null, attributes.triggerText || __( 'Open Modal', 'aegis' ) )
			)
		);

		return el(
			'div',
			blockProps,
			el( InspectorControls, null, inspector ),
			triggerPreview,
			el(
				'div',
				{ className: 'aegis-modal-editor__content-preview' },
				el(
					'div',
					{ className: 'aegis-modal-editor__content-label' },
					__( 'Modal Content', 'aegis' ),
					modalTypeValue !== 'popup'
						? el( 'span', { className: 'aegis-modal-editor__type-badge' }, modalTypeValue )
						: null
				),
				el( InnerBlocks )
			)
		);
	}

	const variations = [ {
		name: 'popup',
		title: __( 'Popup', 'aegis' ),
		description: __( 'A centered popup modal.', 'aegis' ),
		attributes: { modalType: 'popup' },
		isDefault: true,
	} ];

	const feat = features();

	if ( feat.offcanvas ) {
		variations.push(
			{
				name: 'off-canvas',
				title: __( 'Off-Canvas', 'aegis' ),
				description: __( 'A side panel that slides in.', 'aegis' ),
				attributes: { modalType: 'off-canvas' },
			},
			{
				name: 'bottom-sheet',
				title: __( 'Bottom Sheet', 'aegis' ),
				description: __( 'A panel that slides up from the bottom.', 'aegis' ),
				attributes: { modalType: 'bottom-sheet' },
			}
		);
	}

	if ( feat.fullscreen ) {
		variations.push( {
			name: 'fullscreen',
			title: __( 'Fullscreen', 'aegis' ),
			description: __( 'A fullscreen overlay.', 'aegis' ),
			attributes: { modalType: 'fullscreen' },
		} );
	}

	registerBlockType( 'aegis/modal', {
		icon: icon,
		edit: Edit,
		save: function () {
			return el( InnerBlocks.Content );
		},
		variations: variations,
		transforms: {
			from: [
				{
					type: 'block',
					blocks: [ 'core/group' ],
					transform: function ( attributes, innerBlocks ) {
						return wp.blocks.createBlock( 'aegis/modal', {}, innerBlocks );
					},
				},
			],
		},
	} );
} )( window.wp );
