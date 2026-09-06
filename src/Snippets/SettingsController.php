<?php
/**
 * Snippets settings AJAX controller.
 *
 * @package Aegis\Plugin\Snippets
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Snippets;

use Aegis\Plugin\Injection\Preview;
use function __;
use function check_ajax_referer;
use function current_user_can;
use function get_current_user_id;
use function is_array;
use function is_user_logged_in;
use function update_user_meta;
use function wp_send_json_error;
use function wp_send_json_success;
use function wp_unslash;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles AJAX save for snippet feature settings.
 */
final class SettingsController {

	/**
	 * Save snippet settings via AJAX.
	 */
	public function ajax_save_settings(): void {
		if ( ! check_ajax_referer( 'aegis_settings_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'aegis' ) ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'aegis' ) ) );
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$input = isset( $_POST['settings'] ) ? wp_unslash( $_POST['settings'] ) : array();

		if ( ! is_array( $input ) ) {
			$input = array();
		}

		$this->persist_settings( $input );

		wp_send_json_success( array( 'message' => __( 'Settings saved.', 'aegis' ) ) );
	}

	/**
	 * @param array<string, mixed> $input Raw settings input.
	 */
	public function persist_settings( array $input ): void {
		$save_input = array(
			'php_enabled'             => ! empty( $input['php_enabled'] ),
			'safe_mode'               => ! empty( $input['safe_mode'] ),
			'auto_safe_mode_on_fatal' => ! empty( $input['auto_safe_mode_on_fatal'] ),
			'enabled_locations'       => $input['enabled_locations'] ?? null,
		);

		if ( ! array_key_exists( 'enabled_locations', $input ) ) {
			unset( $save_input['enabled_locations'] );
		}

		Settings::save( $save_input );

		if ( is_user_logged_in() ) {
			$user_id = get_current_user_id();
			update_user_meta( $user_id, Preview::META_HOOKS, ! empty( $input['preview_hooks'] ) ? 1 : 0 );
			update_user_meta( $user_id, Preview::META_SNIPPETS, ! empty( $input['preview_snippets'] ) ? 1 : 0 );
			update_user_meta( $user_id, Preview::META_ADMIN_BAR, ! empty( $input['preview_admin_bar'] ) ? 1 : 0 );
		}
	}
}
