<?php
/**
 * Plugin Name: Aegis Plugin
 * Description: A plugin that extends the Aegis Block Theme functionalities.
 * Version: 1.0.0
 * Author: Atmostfear Entertainment
 * License: GPL-2.0-or-later
 * Text Domain: aegis-plugin
 */
namespace Aegis\Plugin;
defined('ABSPATH') || exit;
if ( !defined('AEGIS_DIR') ) {
	define('AEGIS_DIR', plugin_dir_path(__FILE__));
}
if ( !defined('AEGIS_URL') ) {
	define('AEGIS_URL', plugin_dir_url(__FILE__));
}
// Include Composer autoloader
if ( file_exists(__DIR__ . '/vendor/autoload.php') && 'aegis' == wp_get_theme()->get_stylesheet()) {
    include_once __DIR__ . '/vendor/autoload.php';
    // Initialize the plugin
    $aegis_plugin = new Aegis_Plugin();
} else {
    add_action( 'admin_notices', function () {
        echo '<div class="notice notice-error"><p>';
        echo __( 'Aegis Plugin error: Composer autoload file not found or Aegis theme is not active. Please run "composer install" to install dependencies.', 'aegis-plugin');
        echo '</p></div>';
        return;
    });
}