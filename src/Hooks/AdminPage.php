<?php
/**
 * Hooks admin dashboard page.
 *
 * @package Aegis\Plugin\Hooks
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Hooks;

use function __;
use function absint;
use function add_action;
use function add_filter;
use function add_submenu_page;
use function admin_url;
use function check_ajax_referer;
use function current_user_can;
use function defined;
use function dirname;
use function do_action;
use function file_exists;
use function filemtime;
use function plugins_url;
use function sanitize_text_field;
use function str_starts_with;
use function wp_create_nonce;
use function wp_enqueue_script;
use function wp_enqueue_style;
use function wp_localize_script;
use function wp_send_json_error;
use function wp_send_json_success;
use function wp_unslash;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and renders the Hooks admin page.
 */
final class AdminPage {

	/**
	 * Register hooks.
	 */
	public function init(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_ajax_aegis_toggle_hook_instance', array( $this, 'ajax_toggle' ) );
		add_action( 'wp_ajax_aegis_delete_hook_instance', array( $this, 'ajax_delete' ) );
		add_filter( 'aegis_admin_tabs', array( $this, 'register_admin_tab' ) );
	}

	/**
	 * @param array<string, array{label: string, url: string}> $tabs Registered tabs.
	 * @return array<string, array{label: string, url: string}>
	 */
	public function register_admin_tab( array $tabs ): array {
		$tabs['hook-patterns'] = array(
			'label' => __( 'Hooks', 'aegis' ),
			'url'   => admin_url( 'admin.php?page=aegis-hook-patterns' ),
		);

		return $tabs;
	}

	public function register_menu(): void {
		add_submenu_page(
			'aegis-dashboard',
			__( 'Hooks', 'aegis' ),
			__( 'Hooks', 'aegis' ),
			'manage_options',
			'aegis-hook-patterns',
			array( $this, 'render' )
		);
	}

	/**
	 * @param string $hook_suffix Current admin page hook.
	 */
	public function enqueue_assets( string $hook_suffix ): void {
		unset( $hook_suffix );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['page'] ) ) : '';

		if ( ! str_starts_with( $page, 'aegis-' ) ) {
			return;
		}

		$plugin_dir = \Aegis\Plugin\DIR;
		$hook_css   = $plugin_dir . 'assets/admin/hook-patterns.css';
		$hook_js    = $plugin_dir . 'assets/admin/hooks-admin.js';

		wp_enqueue_style(
			'aegis-hook-patterns-admin',
			plugins_url( 'assets/admin/hook-patterns.css', \Aegis\Plugin\FILE ),
			array( 'aegis-admin-settings' ),
			file_exists( $hook_css ) ? (string) filemtime( $hook_css ) : \Aegis\Plugin\VERSION
		);

		wp_enqueue_script(
			'aegis-hooks-admin',
			plugins_url( 'assets/admin/hooks-admin.js', \Aegis\Plugin\FILE ),
			array( 'jquery' ),
			file_exists( $hook_js ) ? (string) filemtime( $hook_js ) : \Aegis\Plugin\VERSION,
			true
		);

		wp_localize_script(
			'aegis-hooks-admin',
			'aegisHooksList',
			array(
				'nonce'         => wp_create_nonce( 'aegis_manage_hook' ),
				'confirmDelete' => __( 'Move this hook pattern to Trash?', 'aegis' ),
				'copied'        => __( 'Copied', 'aegis' ),
				'copyHook'      => __( 'Copy hook name', 'aegis' ),
				'disabledLabel' => __( 'Disabled', 'aegis' ),
			)
		);
	}

	public function render(): void {
		$instances  = InstanceRepository::get_instances();
		$pro_active = defined( 'AEGIS_PRO_VERSION' );
		$cpt_ready  = InstanceRepository::post_type_ready();
		$create_url = $cpt_ready
			? admin_url( 'post-new.php?post_type=' . PatternsManager::POST_TYPE )
			: admin_url( 'admin.php?page=aegis-license' );

		$template = dirname( __DIR__, 2 ) . '/templates/hooks-admin-page.php';

		?>
		<div class="wrap aegis-admin-page">
			<?php do_action( 'aegis_admin_before_hooks_page' ); ?>
			<?php
			if ( file_exists( $template ) ) {
				include $template;
			}
			?>
		</div>
		<?php
	}

	public function ajax_toggle(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized', 'aegis' ) ) );
		}

		check_ajax_referer( 'aegis_manage_hook', 'nonce' );

		$post_id = absint( $_POST['post_id'] ?? 0 );
		$enabled = ! empty( $_POST['enabled'] );

		if ( $post_id < 1 || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to edit this hook pattern.', 'aegis' ) ) );
		}

		if ( ! InstanceRepository::set_enabled( $post_id, $enabled ) ) {
			wp_send_json_error( array( 'message' => __( 'Could not update the hook pattern.', 'aegis' ) ) );
		}

		wp_send_json_success( array( 'enabled' => $enabled ) );
	}

	public function ajax_delete(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized', 'aegis' ) ) );
		}

		check_ajax_referer( 'aegis_manage_hook', 'nonce' );

		$post_id = absint( $_POST['post_id'] ?? 0 );

		if ( $post_id < 1 || ! current_user_can( 'delete_post', $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to delete this hook pattern.', 'aegis' ) ) );
		}

		if ( ! InstanceRepository::delete_instance( $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Could not delete the hook pattern.', 'aegis' ) ) );
		}

		wp_send_json_success();
	}
}
