import { addFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';
import { InspectorControls, PanelColorSettings  } from '@wordpress/block-editor';
import { PanelBody, PanelRow, ToggleControl, __experimentalUnitControl, GradientPicker, TextControl } from '@wordpress/components';
import { useState } from '@wordpress/element';

    function addCustomFontSizeAttribute(settings, name) {
        var generalSettings = {
            hideBlock: {
                type: 'boolean',
                default: false,
            },
            hideBlockByDevice: {
                type: "object",
			    default: {"desktop": false, "mobile": false, "tablet": false}
            },
            mobileFontColor: {
                type: 'string',
                default: '#000',
            },
            tabletFontColor: {
                type: 'string',
                default: '#000',
            },
            tabletBGColor: {
                type: 'string',
            },
            mobileBGColor: {
                type: 'string',
            },
        }
        if ( name === 'core/paragraph' || name === 'core/heading' ) {
            generalSettings = {
                ...generalSettings,
                tabletFontSize: {
                    type: 'string',
                    default: 12,
                },
                mobileFontSize: {
                    type: 'string',
                    default: 12,
                }
            }
        }
        const newSettings = {
            ...settings,
            attributes: {
                ...settings.attributes,
                ...generalSettings
            }
        }
        return newSettings;
    }

    addFilter(
        'blocks.registerBlockType',
        'aegis-plugin/custom-font-size',
        addCustomFontSizeAttribute
    );
    const AegisSettings = ( obj ) => {
        const { attributes, setAttributes, name } = obj.properties;
        const { hideBlock, hideBlockByDevice } = attributes;
        const [ devices, setDevices ] = useState(hideBlockByDevice);
        setAttributes({hideBlockByDevice: devices});
        return <PanelBody title={__('General Settings')} initialOpen={false}>
            <PanelRow>
                <ToggleControl checked={ !! hideBlock }
                    label={ __('Hide Block', 'aegis-plugin')}
                    onChange={ () => setAttributes( {hideBlock: ! hideBlock,})} />
            </PanelRow>
            <h3>{__('Hide Blocks for Specific Devices', 'aegis-plugin')}</h3>
            <ToggleControl checked={ !!devices.mobile }
                label={ __('Mobile', 'aegis-plugin')}
                onChange={ (e) => setDevices( {"desktop": devices.desktop, "mobile": e, "tablet": devices.tablet} )} /><br />
            <ToggleControl checked={ !!devices.tablet }
                label={ __('Tablet', 'aegis-plugin')}
                onChange={ (e) => setDevices( {"desktop": devices.desktop, "mobile": devices.mobile, "tablet": e} )} /><br />
            <ToggleControl checked={ !!devices.desktop }
                label={ __('Desktop', 'aegis-plugin')}
                onChange={ (e) => setDevices( {"desktop": e, "mobile": devices.mobile, "tablet": devices.tablet} )} />
        </PanelBody>
    }
    const AegisFontSettings = ( obj ) => {
        const { attributes, setAttributes, name } = obj.properties;
        const { tabletFontSize, mobileFontSize } = attributes;
        if ( name === 'core/paragraph' || name === 'core/heading' ) {
            const units = [
                { value: 'px', label: 'px', default: 16 },
                { value: 'em', label: 'em', default: 1 },
                { value: 'rem', label: 'rem', default: 1 },
                { value: '%', label: '%', default: 100 },
            ];
            return <PanelBody title={__('Font Size Settings')} initialOpen={false}>
                <PanelRow>
                    <__experimentalUnitControl
                        label="Tablet Font Size" value={tabletFontSize || ''} units={units}
                        onChange={(value) => setAttributes({ tabletFontSize: value })} />
                </PanelRow>
                <PanelRow>
                    <__experimentalUnitControl
                    label="Mobile Font Size" value={mobileFontSize || ''} units={units}
                    onChange={(value) => setAttributes({ mobileFontSize: value })} />
                </PanelRow>
            </PanelBody>
        }
    }
    const AegisColors = ( obj ) => {
        const { attributes, setAttributes, name } = obj.properties;
        const { tabletFontColor, mobileFontColor, tabletBGColor, mobileBGColor } = attributes;
        return <PanelBody title={__('Color Settings')} initialOpen={false}>
            <PanelRow>
                <PanelColorSettings title={ __('Tablet Font Color', 'gutenblocks')}
                    colorSettings={[{
                    value: tabletFontColor,
                    onChange: (value) => setAttributes({tabletFontColor: value}),
                    label: __('Tablet layout color') }]} />
            </PanelRow>
            <PanelRow>
                <PanelColorSettings title={ __('Mobile Color', 'gutenblocks')}
                    colorSettings={[{
                    value: mobileFontColor,
                    onChange: (value) => setAttributes({mobileFontColor: value}),
                    label: __('Mobile layout color') }]} />
            </PanelRow>
            <p>{__('Tablet Background Color', 'aegis-plugin')}</p>
            <PanelRow>
                <GradientPicker
                    value={ tabletBGColor }
                    onChange={ (value) => setAttributes({tabletBGColor: value}) } />
            </PanelRow>
            <p>{__('Mobile Background Color', 'aegis-plugin')};</p>
            <PanelRow>
                <GradientPicker
                    value={ mobileBGColor }
                    onChange={ (value) => setAttributes({mobileBGColor: value}) } />
            </PanelRow>
        </PanelBody>
    }
    function addCustomFontSizeControl (BlockEdit) {
        return (props) => {
            return (
                <>
                    <BlockEdit {...props} />
                    <InspectorControls>
                        <AegisSettings properties={props} />
                        <AegisFontSettings properties={props} />
                        <AegisColors properties={props} />
                    </InspectorControls>
                </>
            );
        };
    }

    addFilter(
        'editor.BlockEdit',
        'aegis-plugin/custom-font-size',
        addCustomFontSizeControl
    );