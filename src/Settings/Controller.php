<?php
/**
 * AJAX and sanitization for Aegis admin settings.
 *
 * @package Aegis\Plugin\Settings
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Settings;

use Aegis\Plugin\Blocks\Settings as BlocksSettings;
use Aegis\Plugin\Conditionals\Settings as ConditionalsSettings;
use Aegis\Plugin\General\Settings as GeneralSettings;
use Aegis\Plugin\Integrations\Settings as IntegrationsSettings;
use Aegis\Plugin\Uninstall;
use function __;
use function check_ajax_referer;
use function current_time;
use function current_user_can;
use function delete_option;
use function get_option;
use function is_array;
use function json_decode;
use function sanitize_text_field;
use function update_option;
use function wp_send_json_error;
use function wp_send_json_success;
use function wp_unslash;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings AJAX controller.
 */
final class Controller {

	public function ajax_save_general_settings(): void {
		if ( ! check_ajax_referer( 'aegis_settings_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'aegis' ) ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'aegis' ) ) );
		}

		$settings  = isset( $_POST['settings'] ) ? wp_unslash( $_POST['settings'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$sanitized = $this->sanitize_general_settings( is_array( $settings ) ? $settings : array() );

		GeneralSettings::save( $sanitized );

		$remove_data = isset( $_POST['removeData'] ) && '1' === (string) wp_unslash( $_POST['removeData'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		Uninstall::set_remove_data( $remove_data );

		wp_send_json_success( array( 'message' => __( 'Settings saved.', 'aegis' ) ) );
	}

	public function ajax_export_settings(): void {
		if ( ! check_ajax_referer( 'aegis_settings_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'aegis' ) ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'aegis' ) ) );
		}

		wp_send_json_success(
			array(
				'conditional_logic' => Repository::get_settings(),
				'integrations'      => Repository::get_integration_settings(),
				'blocks'            => get_option( BlocksSettings::OPTION, BlocksSettings::DEFAULTS ),
				'general'           => Repository::get_general_settings(),
				'version'           => '1.0.0',
				'exported_at'       => current_time( 'mysql' ),
			)
		);
	}

	public function ajax_import_settings(): void {
		if ( ! check_ajax_referer( 'aegis_settings_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'aegis' ) ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'aegis' ) ) );
		}

		$settings_json = isset( $_POST['settings'] ) ? wp_unslash( $_POST['settings'] ) : '';
		$settings      = json_decode( $settings_json, true );

		if ( ! is_array( $settings ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid settings data.', 'aegis' ) ) );
		}

		if ( isset( $settings['conditional_logic'] ) && is_array( $settings['conditional_logic'] ) ) {
			update_option(
				ConditionalsSettings::OPTION,
				$this->sanitize_settings( $settings['conditional_logic'] )
			);
		}

		if ( isset( $settings['integrations'] ) && is_array( $settings['integrations'] ) ) {
			update_option(
				IntegrationsSettings::OPTION,
				$this->sanitize_integrations( $settings['integrations'] )
			);
		}

		if ( isset( $settings['blocks'] ) && is_array( $settings['blocks'] ) ) {
			update_option(
				BlocksSettings::OPTION,
				$this->sanitize_blocks( $settings['blocks'] )
			);
		}

		if ( isset( $settings['general'] ) && is_array( $settings['general'] ) ) {
			GeneralSettings::save( $this->sanitize_general_settings( $settings['general'] ) );
		}

		Repository::flush_cache();

		wp_send_json_success( array( 'message' => __( 'Settings imported successfully.', 'aegis' ) ) );
	}

	public function ajax_reset_settings(): void {
		if ( ! check_ajax_referer( 'aegis_settings_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'aegis' ) ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'aegis' ) ) );
		}

		$group = isset( $_POST['group'] ) ? sanitize_text_field( wp_unslash( $_POST['group'] ) ) : '';

		switch ( $group ) {
			case 'conditionals':
				delete_option( ConditionalsSettings::OPTION );
				break;
			case 'integrations':
				delete_option( IntegrationsSettings::OPTION );
				delete_option( IntegrationsSettings::PATTERN_CONTROL_OPTION );
				break;
			case 'blocks':
				BlocksSettings::reset_block_features();
				break;
			case 'performance':
				BlocksSettings::reset_performance();
				break;
			case 'general':
				delete_option( GeneralSettings::OPTION );
				break;
			default:
				wp_send_json_error( array( 'message' => __( 'Invalid settings group.', 'aegis' ) ) );
		}

		Repository::flush_cache();

		wp_send_json_success( array( 'message' => __( 'Settings reset to defaults.', 'aegis' ) ) );
	}

	public function ajax_purge_data(): void {
		if ( ! check_ajax_referer( 'aegis_settings_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'aegis' ) ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'aegis' ) ) );
		}

		$confirm = isset( $_POST['confirm'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['confirm'] ) ) : '';

		if ( 'DELETE' !== $confirm ) {
			wp_send_json_error( array( 'message' => __( 'Type DELETE to confirm.', 'aegis' ) ) );
		}

		Uninstall::purge();

		wp_send_json_success( array( 'message' => __( 'All Aegis data has been deleted.', 'aegis' ) ) );
	}

	public function ajax_save_performance(): void {
		if ( ! check_ajax_referer( 'aegis_settings_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'aegis' ) ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'aegis' ) ) );
		}

		$settings = isset( $_POST['settings'] ) ? wp_unslash( $_POST['settings'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		BlocksSettings::save_performance( is_array( $settings ) ? $settings : array() );
		Repository::flush_cache();

		wp_send_json_success( array( 'message' => __( 'Settings saved.', 'aegis' ) ) );
	}

	/**
	 * @param array<string, mixed> $input Input settings.
	 * @return array<string, array<string, bool>>
	 */
	public function sanitize_settings( array $input ): array {
		return ConditionalsSettings::sanitize( $input );
	}

	/**
	 * @param array<string, mixed> $input Input settings.
	 * @return array<string, bool>
	 */
	public function sanitize_integrations( array $input ): array {
		return IntegrationsSettings::sanitize( $input );
	}

	/**
	 * @param array<string, mixed> $input Input settings.
	 * @return array<string, bool>
	 */
	public function sanitize_blocks( array $input ): array {
		return BlocksSettings::sanitize( $input );
	}

	/**
	 * @param array<string, mixed> $input Input settings.
	 * @return array<string, bool>
	 */
	public function sanitize_pattern_control( array $input ): array {
		return IntegrationsSettings::sanitize_pattern_control( $input );
	}

	/**
	 * @param array<string, mixed> $input Input settings.
	 * @return array<string, string>
	 */
	public function sanitize_bunnycdn( array $input ): array {
		return IntegrationsSettings::sanitize_bunnycdn( $input );
	}

	/**
	 * @param array<string, mixed> $input Input settings.
	 * @return array<string, bool>
	 */
	public function sanitize_general_settings( array $input ): array {
		return GeneralSettings::sanitize( $input );
	}
}
