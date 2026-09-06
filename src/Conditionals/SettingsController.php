<?php
/**
 * Conditional logic settings AJAX controller.
 *
 * @package Aegis\Plugin\Conditionals
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Conditionals;

use function __;
use function check_ajax_referer;
use function current_user_can;
use function update_option;
use function wp_send_json_error;
use function wp_send_json_success;
use function wp_unslash;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles AJAX save for conditional logic settings.
 */
final class SettingsController {

	/**
	 * Handle AJAX save settings.
	 */
	public function ajax_save_settings(): void {
		if ( ! check_ajax_referer( 'aegis_settings_nonce', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => __( 'Security check failed.', 'aegis' ) ] );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied.', 'aegis' ) ] );
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$settings  = isset( $_POST['settings'] ) ? wp_unslash( $_POST['settings'] ) : [];
		$sanitized = Settings::sanitize( is_array( $settings ) ? $settings : [] );

		update_option( Settings::OPTION, $sanitized );
		Settings::flush_cache();

		if ( class_exists( '\Aegis\Plugin\Settings\Repository' ) ) {
			\Aegis\Plugin\Settings\Repository::flush_cache();
		}

		wp_send_json_success( [ 'message' => __( 'Settings saved.', 'aegis' ) ] );
	}
}
