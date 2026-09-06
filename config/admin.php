<?php
/**
 * Admin settings bootstrap (repository, AJAX, general settings page).
 *
 * @package Aegis\Plugin
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Aegis\Plugin\General\AdminPage;
use Aegis\Plugin\Settings\Controller;
use Aegis\Plugin\Settings\Migration;

$settings_controller = new Controller();

add_action(
	'wp_ajax_aegis_save_general_settings',
	array( $settings_controller, 'ajax_save_general_settings' )
);
add_action(
	'wp_ajax_aegis_export_settings',
	array( $settings_controller, 'ajax_export_settings' )
);
add_action(
	'wp_ajax_aegis_import_settings',
	array( $settings_controller, 'ajax_import_settings' )
);
add_action(
	'wp_ajax_aegis_reset_settings',
	array( $settings_controller, 'ajax_reset_settings' )
);
add_action(
	'wp_ajax_aegis_purge_data',
	array( $settings_controller, 'ajax_purge_data' )
);
add_action(
	'wp_ajax_aegis_save_performance',
	array( $settings_controller, 'ajax_save_performance' )
);

add_action(
	'plugins_loaded',
	static function (): void {
		( new Migration() )->run();
	},
	20
);

add_action(
	'after_setup_theme',
	static function () use ( $settings_controller ): void {
		if ( ! class_exists( \Aegis\Plugin\Admin\Renderer::class ) ) {
			return;
		}

		( new AdminPage( $settings_controller ) )->init();
	},
	20
);
