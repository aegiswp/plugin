<?php
/**
 * Google Maps settings panel in Aegis Connectors admin.
 *
 * @package Aegis\Plugin\Map
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Map;

use Aegis\Plugin\Integrations\Secrets;
use Aegis\Plugin\Integrations\Settings as IntegrationsSettings;
use function __;
use function add_action;
use function add_query_arg;
use function check_ajax_referer;
use function current_user_can;
use function defined;
use function disabled;
use function esc_attr;
use function esc_attr_e;
use function esc_html_e;
use function file_exists;
use function is_wp_error;
use function plugins_url;
use function sanitize_text_field;
use function str_starts_with;
use function wp_enqueue_script;
use function wp_remote_get;
use function wp_remote_retrieve_body;
use function wp_send_json_error;
use function wp_send_json_success;
use function wp_unslash;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders Maps integration UI and handles save/test AJAX.
 */
final class IntegrationsPanel {

	/**
	 * Register hooks.
	 */
	public function init(): void {
		add_action( 'aegis_connectors_nav_items', array( $this, 'render_nav_item' ) );
		add_action( 'aegis_connectors_maps_section', array( $this, 'render_section' ) );
		add_action( 'wp_ajax_aegis_save_google_maps', array( $this, 'ajax_save' ) );
		add_action( 'wp_ajax_aegis_test_google_maps', array( $this, 'ajax_test' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Enqueue map settings script on Aegis admin pages.
	 *
	 * @param string $hook_suffix Current admin page hook.
	 */
	public function enqueue_admin_assets( string $hook_suffix ): void {
		unset( $hook_suffix );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['page'] ) ) : '';

		if ( ! str_starts_with( $page, 'aegis-' ) ) {
			return;
		}

		$script_path = \Aegis\Plugin\DIR . 'assets/js/map-settings.js';

		if ( ! file_exists( $script_path ) ) {
			return;
		}

		wp_enqueue_script(
			'aegis-map-settings',
			plugins_url( 'assets/js/map-settings.js', \Aegis\Plugin\FILE ),
			array( 'jquery', 'aegis-admin-settings' ),
			(string) filemtime( $script_path ),
			true
		);
	}

	/**
	 * Output Maps sidebar nav link.
	 */
	public function render_nav_item(): void {
		$renderer = new \Aegis\Plugin\Admin\Renderer();
		?>
		<a href="#maps" class="aegis-nav-item">
			<?php $renderer->render_ui_icon( 'location', 'google-maps' ); ?>
			<?php esc_html_e( 'Google Maps', 'aegis' ); ?>
		</a>
		<?php
	}

	/**
	 * Output Maps settings section.
	 */
	public function render_section(): void {
		$google_maps_settings = Settings::get_settings();
		$integrations         = IntegrationsSettings::get_settings();
		$renderer             = new \Aegis\Plugin\Admin\Renderer();
		?>
		<section id="maps" class="aegis-settings-section">
			<div class="aegis-settings-section-header">
				<h2>
					<?php $renderer->render_ui_icon( 'location', 'google-maps' ); ?>
					<?php esc_html_e( 'Google Maps', 'aegis' ); ?>
				</h2>
				<p><?php esc_html_e( 'Google Maps API configuration for the Map block. Use separate browser and server keys in Google Cloud Console.', 'aegis' ); ?></p>
			</div>

			<div class="aegis-settings-grid">
				<div class="aegis-toggle-card">
					<div class="aegis-toggle-info">
						<div class="aegis-toggle-icon">
							<?php $renderer->render_ui_icon( 'location', 'google-maps' ); ?>
						</div>
						<div class="aegis-toggle-text">
							<h3><?php esc_html_e( 'Google Maps', 'aegis' ); ?></h3>
							<p><?php esc_html_e( 'Enable Google Maps provider for the Map block. OpenStreetMap remains available when disabled.', 'aegis' ); ?></p>
						</div>
					</div>
					<label class="aegis-toggle">
						<input type="checkbox" name="<?php echo esc_attr( IntegrationsSettings::OPTION . '[google_maps]' ); ?>" value="1" <?php checked( ! empty( $integrations['google_maps'] ) ); ?>>
						<span class="aegis-toggle-slider"></span>
					</label>
				</div>
			</div>

			<div class="aegis-api-config">
				<div class="aegis-api-config-header">
					<div class="aegis-api-config-title">
						<div class="aegis-api-config-heading">
							<?php $renderer->render_ui_icon( 'location', 'google-maps' ); ?>
							<span><?php esc_html_e( 'Google Maps API', 'aegis' ); ?></span>
						</div>
						<p><?php esc_html_e( 'Browser and server keys from Google Cloud Console.', 'aegis' ); ?></p>
					</div>
				</div>

				<div class="aegis-api-config-body">
				<div class="aegis-api-field-row">
					<div class="aegis-api-field-info">
						<div class="aegis-api-field-icon">
							<span class="dashicons dashicons-admin-site-alt3"></span>
						</div>
						<div class="aegis-api-field-text">
							<label><?php esc_html_e( 'Browser API Key', 'aegis' ); ?></label>
							<p><?php esc_html_e( 'Used for Static Maps and Maps JavaScript on the frontend. Restrict by HTTP referrer in Google Cloud Console.', 'aegis' ); ?></p>
						</div>
					</div>
					<div class="aegis-api-field-input">
						<input
							type="password"
							name="<?php echo esc_attr( Settings::OPTION_KEY . '[browser_api_key]' ); ?>"
							value="<?php echo esc_attr( Secrets::mask( $google_maps_settings['browser_api_key'] ) ); ?>"
							class="aegis-google-maps-field"
							placeholder="<?php esc_attr_e( 'Enter Browser API Key', 'aegis' ); ?>"
						>
					</div>
				</div>

				<div class="aegis-api-field-row">
					<div class="aegis-api-field-info">
						<div class="aegis-api-field-icon">
							<span class="dashicons dashicons-admin-network"></span>
						</div>
						<div class="aegis-api-field-text">
							<label><?php esc_html_e( 'Server API Key', 'aegis' ); ?></label>
							<p><?php esc_html_e( 'Used for geocoding in the block editor. Restrict by IP address and never expose to visitors.', 'aegis' ); ?></p>
						</div>
					</div>
					<div class="aegis-api-field-input">
						<input
							type="password"
							name="<?php echo esc_attr( Settings::OPTION_KEY . '[server_api_key]' ); ?>"
							value="<?php echo esc_attr( Secrets::mask( $google_maps_settings['server_api_key'] ) ); ?>"
							class="aegis-google-maps-field"
							placeholder="<?php esc_attr_e( 'Enter Server API Key', 'aegis' ); ?>"
						>
					</div>
				</div>

				<div class="aegis-settings-notice">
					<p>
						<strong><?php esc_html_e( 'Security Recommendation', 'aegis' ); ?></strong><br>
						<?php esc_html_e( 'Create two separate keys: a browser key with HTTP referrer restrictions (Maps JavaScript and Static Maps; optionally Directions for Pro routes), and a server key with IP restrictions for Geocoding only. Never enable Geocoding on the browser key, and never reuse the same key for both fields.', 'aegis' ); ?>
					</p>
				</div>

				<div class="aegis-api-config-footer">
					<?php
					$has_browser_key = ( $google_maps_settings['browser_api_key'] ?? '' ) !== '';
					$has_server_key  = ( $google_maps_settings['server_api_key'] ?? '' ) !== '';
					$has_any_key     = $has_browser_key || $has_server_key;
					?>
					<button type="button" class="button button-primary aegis-save-google-maps" <?php disabled( ! $has_any_key ); ?>>
						<span class="dashicons dashicons-saved"></span>
						<span class="aegis-button-label"><?php esc_html_e( 'Save API Settings', 'aegis' ); ?></span>
					</button>
					<button type="button" class="button aegis-test-google-maps" <?php disabled( ! $has_server_key ); ?>>
						<span class="dashicons dashicons-yes-alt"></span>
						<span class="aegis-button-label"><?php esc_html_e( 'Test Connection', 'aegis' ); ?></span>
					</button>
				</div>
				</div>
			</div>
		</section>
		<?php
	}

	/**
	 * AJAX: save Google Maps settings.
	 */
	public function ajax_save(): void {
		if ( ! check_ajax_referer( 'aegis_settings_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'aegis' ) ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'aegis' ) ) );
		}

		$settings = isset( $_POST['settings'] ) ? wp_unslash( $_POST['settings'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

		if ( ! is_array( $settings ) ) {
			$settings = array();
		}

		Settings::save( $settings );

		if ( class_exists( '\Aegis\Plugin\Settings\Repository' ) ) {
			\Aegis\Plugin\Settings\Repository::flush_cache();
		}

		wp_send_json_success( array( 'message' => __( 'Settings saved.', 'aegis' ) ) );
	}

	/**
	 * AJAX: test Google Maps server API key.
	 */
	public function ajax_test(): void {
		if ( ! check_ajax_referer( 'aegis_settings_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'aegis' ) ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'aegis' ) ) );
		}

		$api_key = Settings::get_server_api_key();

		if ( $api_key === '' ) {
			wp_send_json_error( array( 'message' => __( 'Connection test failed. Verify your API credentials.', 'aegis' ) ) );
		}

		$response = wp_remote_get(
			add_query_arg(
				array(
					'address' => 'Google HQ, Mountain View, CA',
					'key'     => $api_key,
				),
				'https://maps.googleapis.com/maps/api/geocode/json'
			),
			array( 'timeout' => 10 )
		);

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( array( 'message' => __( 'Connection test failed. Verify your API credentials.', 'aegis' ) ) );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['status'] ) && $body['status'] === 'OK' ) {
			wp_send_json_success( array( 'message' => __( 'Connection successful.', 'aegis' ) ) );
		}

		wp_send_json_error( array( 'message' => __( 'Connection test failed. Verify your API credentials.', 'aegis' ) ) );
	}
}
