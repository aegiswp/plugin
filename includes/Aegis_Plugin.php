<?php
namespace Aegis\Plugin;
defined( 'ABSPATH' ) || exit;
if ( !class_exists('Aegis_Plugin') ) {
    class Aegis_Plugin
    {
        function __construct()
        {
            add_action( 'enqueue_block_editor_assets', array($this, 'aegis_enqueue_block_editor_assets') );
            add_action( 'wp_enqueue_scripts', array( $this, 'aegis_enqueue_frontend' ) );
            add_filter( 'render_block', array($this,    'wporg_block_wrapper'), 10, 2 );
        }
        public function wporg_block_wrapper( $block_content, $block ) 
        {
            $attributes = !empty($block['attrs']) ? $block['attrs'] : array();
            if ( !empty($attributes['hideBlock']) && 'yes' == $attributes['hideBlock'] ) {
                $block_content = '';
            } else {
                $blockAttributes = array(
                    'mobileFontColor' => !empty($attributes['mobileFontColor']) ? $attributes['mobileFontColor'] : '',
                    'tabletFontColor' => !empty($attributes['tabletFontColor']) ? $attributes['tabletFontColor'] : '',
                    'tabletBGColor' => !empty($attributes['tabletBGColor']) ? $attributes['tabletBGColor'] : '',
                    'mobileBGColor' => !empty($attributes['mobileBGColor']) ? $attributes['mobileBGColor'] : '',
                    'tabletFontSize' => !empty($attributes['tabletFontSize']) ? $attributes['tabletFontSize'] : '',
                    'mobileFontSize' => !empty($attributes['mobileFontSize']) ? $attributes['mobileFontSize'] : '',
                );
                $block_classes = 'aegis-section';
                if ( !empty($attributes['hideBlockByDevice']) ) {
                    $screen = $attributes['hideBlockByDevice'];
                    $desktop = !empty($screen['desktop']) ? 'hide_for_desktop' : '';
                    $tablet = !empty($screen['tablet']) ? 'hide_for_tablet' : '';
                    $mobile = !empty($screen['mobile']) ? 'hide_for_mobile' : '';
                    $block_classes = sprintf('aegis-section %s %s %s', $desktop, $tablet, $mobile);
                }
                ob_start();
                printf("<div class='%s' data-attributes='%s'>", $block_classes, wp_json_encode($blockAttributes));
                echo $block_content;
                echo '</div>';
                $block_content = ob_get_clean();
            }
            return $block_content;
        }
        public function aegis_enqueue_frontend() 
        {
            $asset_file = include AEGIS_DIR . 'build/index.asset.php';
            wp_enqueue_style('aegis-fronend-styles', AEGIS_URL . 'assets/video-block/video.css', '', $asset_file['version']);
            wp_enqueue_style('aegis-typography-styles', AEGIS_URL . 'assets/typography.css', '', $asset_file['version']);
            // wp_enqueue_script( 'aegis-video-script', AEGIS_URL . 'build/video.js', array(), '1.0.0' );
            wp_enqueue_script('aegis-typography-scripts', AEGIS_URL . 'assets/typography.js', $asset_file['dependencies'], $asset_file['version']);
        }
        function aegis_enqueue_block_editor_assets() 
        {
            $asset_file = include AEGIS_DIR . 'build/index.asset.php';
            // wp_enqueue_style( 'aegis-editor-styles', AEGIS_URL . 'build/index.css', array(), $asset_file['version'] );
            wp_enqueue_script('aegis-editor-scripts', AEGIS_URL . 'build/index.js', $asset_file['dependencies'], $asset_file['version']);
            wp_set_script_translations('aegis-editor-scripts', 'aegis-plugin');
        }
    }
}