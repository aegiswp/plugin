<?php
/**
 * Admin notices for active plugins with disabled Aegis integrations.
 *
 * @package Aegis\Plugin\Integrations
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Integrations;

use function __;
use function _n;
use function add_action;
use function admin_url;
use function check_ajax_referer;
use function current_user_can;
use function esc_attr;
use function esc_html;
use function esc_js;
use function esc_url;
use function get_user_meta;
use function get_current_user_id;
use function in_array;
use function sanitize_key;
use function sanitize_text_field;
use function sprintf;
use function update_user_meta;
use function wp_create_nonce;
use function wp_send_json_error;
use function wp_send_json_success;
use function wp_unslash;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shows a combined admin notice when plugins are active but integration toggles are off.
 */
final class Notices {

	public const DISMISS_META_PREFIX = 'aegis_integration_notice_dismissed_';

	public const AJAX_ACTION = 'aegis_dismiss_integration_notice';

	public function init(): void {
		add_action( 'admin_notices', [ $this, 'render_notices' ] );
		add_action( 'wp_ajax_' . self::AJAX_ACTION, [ $this, 'ajax_dismiss' ] );
	}

	/**
	 * Render combined integration notice on admin screens.
	 */
	public function render_notices(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['page'] ) ) {
			$current_page = sanitize_text_field( wp_unslash( (string) $_GET['page'] ) );

			if ( in_array( $current_page, array( 'aegis-integrations', 'aegis-connectors' ), true ) ) {
				return;
			}
		}

		$pending = $this->get_pending_integrations();

		if ( $pending === [] ) {
			return;
		}

		$nonce = wp_create_nonce( 'aegis_settings_nonce' );
		?>
		<div class="notice notice-warning is-dismissible aegis-integration-notice" data-aegis-notice-nonce="<?php echo esc_attr( $nonce ); ?>">
			<p>
				<strong><?php esc_html_e( 'Aegis Integrations', 'aegis' ); ?></strong>
				<?php
				echo esc_html(
					sprintf(
						/* translators: %d: number of active plugins without integration enabled */
						_n(
							'%d active plugin is not connected to Aegis:',
							'%d active plugins are not connected to Aegis:',
							count( $pending ),
							'aegis'
						),
						count( $pending )
					)
				);
				?>
			</p>
			<ul style="list-style: disc; margin-left: 1.5em;">
				<?php foreach ( $pending as $integration ) : ?>
				<li>
					<a href="<?php echo esc_url( $this->get_settings_url( $integration ) ); ?>">
						<?php echo esc_html( $integration['label'] ); ?>
					</a>
				</li>
				<?php endforeach; ?>
			</ul>
			<p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=aegis-integrations' ) ); ?>">
					<?php esc_html_e( 'Manage integrations', 'aegis' ); ?>
				</a>
			</p>
		</div>
		<script>
		( function () {
			const notice = document.querySelector( '.aegis-integration-notice.is-dismissible' );
			if ( ! notice ) {
				return;
			}

			const dismissBtn = notice.querySelector( '.notice-dismiss' );
			if ( ! dismissBtn ) {
				return;
			}

			dismissBtn.addEventListener( 'click', function () {
				const pendingKeys = <?php echo wp_json_encode( array_column( $pending, 'key' ) ); ?>;
				const nonce = notice.getAttribute( 'data-aegis-notice-nonce' );
				const body = new URLSearchParams( {
					action: '<?php echo esc_js( self::AJAX_ACTION ); ?>',
					nonce: nonce,
					keys: pendingKeys.join( ',' ),
				} );

				fetch( ajaxurl, {
					method: 'POST',
					credentials: 'same-origin',
					headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
					body: body.toString(),
				} );
			} );
		} )();
		</script>
		<?php
	}

	/**
	 * AJAX handler — dismiss integration notice(s) for the current user.
	 */
	public function ajax_dismiss(): void {
		if ( ! check_ajax_referer( 'aegis_settings_nonce', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => __( 'Security check failed.', 'aegis' ) ] );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied.', 'aegis' ) ] );
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$keys_raw = isset( $_POST['keys'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['keys'] ) ) : '';
		$keys     = array_filter( array_map( 'sanitize_key', explode( ',', $keys_raw ) ) );

		if ( $keys === [] ) {
			wp_send_json_error( [ 'message' => __( 'No integrations specified.', 'aegis' ) ] );
		}

		$user_id = get_current_user_id();

		foreach ( $keys as $key ) {
			if ( Registry::get( $key ) === null ) {
				continue;
			}

			update_user_meta( $user_id, self::DISMISS_META_PREFIX . $key, '1' );
		}

		wp_send_json_success( [ 'message' => __( 'Notice dismissed.', 'aegis' ) ] );
	}

	/**
	 * Integrations that are active but disabled and not dismissed.
	 *
	 * @return array<int, array{key: string, label: string, section: string}>
	 */
	private function get_pending_integrations(): array {
		$pending  = [];
		$user_id  = get_current_user_id();
		$settings = Settings::get_settings();

		foreach ( Registry::get_all() as $integration ) {
			$key = $integration['key'];

			if ( in_array( $key, array( 'bunny_cdn', 'google_maps' ), true ) ) {
				continue;
			}

			if ( ! Registry::is_plugin_active( $key ) ) {
				continue;
			}

			if ( Settings::is_integration_enabled( $key ) ) {
				continue;
			}

			if ( get_user_meta( $user_id, self::DISMISS_META_PREFIX . $key, true ) ) {
				continue;
			}

			$pending[] = [
				'key'     => $key,
				'label'   => $integration['label'],
				'section' => $integration['section'],
			];
		}

		return $pending;
	}

	/**
	 * Build deep-link URL to a specific integration toggle.
	 *
	 * @param array{key: string, label: string, section: string} $integration Integration row.
	 */
	private function get_settings_url( array $integration ): string {
		$page = in_array( $integration['key'], array( 'bunny_cdn', 'google_maps' ), true )
			? 'aegis-connectors'
			: 'aegis-integrations';

		$hash = $page === 'aegis-connectors'
			? Registry::tab_id( $integration['key'] )
			: Registry::section_id_for_plugin( $integration['key'] );

		return admin_url(
			sprintf(
				'admin.php?page=%s#%s',
				$page,
				rawurlencode( $hash )
			)
		);
	}
}
