<?php
/**
 * Analytics settings panel in Aegis Connectors admin.
 *
 * @package Aegis\Plugin\Analytics
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Analytics;

use function __;
use function add_action;
use function check_ajax_referer;
use function current_user_can;
use function esc_attr;
use function esc_html;
use function file_exists;
use function is_array;
use function wp_send_json_error;
use function wp_send_json_success;
use function wp_unslash;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders Analytics integration UI and handles save AJAX.
 */
final class IntegrationsPanel {

	/**
	 * Register hooks.
	 */
	public function init(): void {
		add_action( 'aegis_connectors_nav_items', [ $this, 'render_nav_item' ] );
		add_action( 'aegis_connectors_analytics_section', [ $this, 'render_section' ] );
		add_action( 'wp_ajax_aegis_save_analytics', [ $this, 'ajax_save' ] );
	}

	/**
	 * Connectors sidebar items for each analytics provider.
	 *
	 * @return array<int, array{id: string, label: string, icon: string}>
	 */
	private function nav_items(): array {
		return [
			[
				'id'    => 'ga4',
				'label' => __( 'Google Analytics', 'aegis' ),
				'icon'  => 'chart-bar',
			],
			[
				'id'    => 'gtm',
				'label' => __( 'Tag Manager', 'aegis' ),
				'icon'  => 'tag',
			],
			[
				'id'    => 'clarity',
				'label' => __( 'Clarity', 'aegis' ),
				'icon'  => 'visibility',
			],
			[
				'id'    => 'plausible',
				'label' => __( 'Plausible', 'aegis' ),
				'icon'  => 'chart-line',
			],
			[
				'id'    => 'fathom',
				'label' => __( 'Fathom', 'aegis' ),
				'icon'  => 'chart-area',
			],
			[
				'id'    => 'matomo',
				'label' => __( 'Matomo', 'aegis' ),
				'icon'  => 'chart-pie',
			],
			[
				'id'    => 'meta-pixel',
				'label' => __( 'Meta Pixel', 'aegis' ),
				'icon'  => 'megaphone',
			],
			[
				'id'    => 'privacy',
				'label' => __( 'Privacy', 'aegis' ),
				'icon'  => 'shield',
			],
		];
	}

	/**
	 * Output Analytics sidebar nav links.
	 */
	public function render_nav_item(): void {
		foreach ( $this->nav_items() as $item ) {
			?>
		<a href="#<?php echo esc_attr( $item['id'] ); ?>" class="aegis-nav-item">
			<span class="dashicons dashicons-<?php echo esc_attr( $item['icon'] ); ?>"></span>
			<?php echo esc_html( $item['label'] ); ?>
		</a>
			<?php
		}
	}

	/**
	 * Output Analytics settings sections (one tab per provider).
	 */
	public function render_section(): void {
		$options       = Settings::get_settings();
		$pro_active    = defined( 'AEGIS_PRO_VERSION' );
		$has_complianz = defined( 'CMPLZ_VERSION' );

		$template = \Aegis\Plugin\DIR . 'templates/integrations-analytics-section.php';

		if ( ! file_exists( $template ) ) {
			return;
		}

		include $template;
	}

	/**
	 * AJAX: save analytics settings.
	 */
	public function ajax_save(): void {
		if ( ! check_ajax_referer( 'aegis_settings_nonce', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => __( 'Security check failed.', 'aegis' ) ] );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied.', 'aegis' ) ] );
		}

		$settings = isset( $_POST['settings'] ) ? wp_unslash( $_POST['settings'] ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

		if ( ! is_array( $settings ) ) {
			$settings = [];
		}

		Settings::save( $settings );

		wp_send_json_success( [ 'message' => __( 'Analytics settings saved.', 'aegis' ) ] );
	}
}
