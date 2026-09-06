<?php
/**
 * Integrations settings AJAX controller.
 *
 * @package Aegis\Plugin\Integrations
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Integrations;

use function __;
use function check_ajax_referer;
use function current_user_can;
use function defined;
use function is_wp_error;
use function update_option;
use function wp_remote_get;
use function wp_remote_retrieve_response_code;
use function wp_send_json_error;
use function wp_send_json_success;
use function wp_unslash;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles AJAX save for integrations and BunnyCDN settings.
 */
final class SettingsController {

	/**
	 * Handle AJAX save integrations.
	 */
	public function ajax_save_integrations(): void {
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

	/**
	 * Handle AJAX save BunnyCDN settings.
	 */
	public function ajax_save_bunnycdn(): void {
		if ( ! check_ajax_referer( 'aegis_settings_nonce', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => __( 'Security check failed.', 'aegis' ) ] );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied.', 'aegis' ) ] );
		}

		if ( ! defined( 'AEGIS_PRO_VERSION' ) || ! Settings::is_integration_enabled( 'bunny_cdn' ) ) {
			wp_send_json_error( [ 'message' => __( 'BunnyCDN integration requires Aegis Pro and must be enabled.', 'aegis' ) ] );
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$settings  = isset( $_POST['settings'] ) ? wp_unslash( $_POST['settings'] ) : [];
		$sanitized = Settings::sanitize_bunnycdn( is_array( $settings ) ? $settings : [] );

		update_option( Settings::BUNNYCDN_OPTION, $sanitized, false );
		Settings::flush_cache();

		if ( class_exists( '\Aegis\Plugin\Settings\Repository' ) ) {
			\Aegis\Plugin\Settings\Repository::flush_cache();
		}

		wp_send_json_success( [ 'message' => __( 'Settings saved.', 'aegis' ) ] );
	}

	/**
	 * Handle AJAX BunnyCDN connection test.
	 */
	public function ajax_test_bunnycdn(): void {
		if ( ! check_ajax_referer( 'aegis_settings_nonce', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => __( 'Security check failed.', 'aegis' ) ] );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied.', 'aegis' ) ] );
		}

		if ( ! defined( 'AEGIS_PRO_VERSION' ) || ! Settings::is_integration_enabled( 'bunny_cdn' ) ) {
			wp_send_json_error( [ 'message' => __( 'BunnyCDN integration requires Aegis Pro and must be enabled.', 'aegis' ) ] );
		}

		$settings = Settings::get_bunnycdn_settings();

		if ( empty( $settings['api_key'] ) ) {
			wp_send_json_error( [ 'message' => __( 'Connection test failed. Verify your API credentials.', 'aegis' ) ] );
		}

		$response = wp_remote_get(
			'https://api.bunny.net/user',
			[
				'headers' => [
					'AccessKey' => $settings['api_key'],
					'Accept'    => 'application/json',
				],
				'timeout' => 15,
			]
		);

		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) >= 400 ) {
			wp_send_json_error( [ 'message' => __( 'Connection test failed. Verify your API credentials.', 'aegis' ) ] );
		}

		wp_send_json_success( [ 'message' => __( 'Connection successful.', 'aegis' ) ] );
	}
}
