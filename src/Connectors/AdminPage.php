<?php
/**
 * Connectors admin dashboard page.
 *
 * @package Aegis\Plugin\Connectors
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Connectors;

use Aegis\Plugin\Integrations\Settings;
use function __;
use function add_action;
use function add_filter;
use function add_submenu_page;
use function admin_url;
use function current_user_can;
use function do_action;
use function esc_html__;
use function file_exists;
use function filemtime;
use function is_string;
use function plugins_url;
use function sanitize_text_field;
use function str_starts_with;
use function wp_die;
use function wp_enqueue_script;
use function wp_unslash;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and renders the Connectors admin page.
 */
final class AdminPage {

	/** @var \Aegis\Plugin\Admin\Renderer|null */
	private $renderer = null;

	private function renderer(): \Aegis\Plugin\Admin\Renderer {
		if ( null === $this->renderer ) {
			$this->renderer = new \Aegis\Plugin\Admin\Renderer();
		}

		return $this->renderer;
	}

	public function init(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_filter( 'aegis_admin_tabs', array( $this, 'register_admin_tab' ) );
	}

	/**
	 * Enqueue BunnyCDN connector script on Aegis admin pages.
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

		$script_path = \Aegis\Plugin\DIR . 'assets/js/bunnycdn-settings.js';

		if ( ! file_exists( $script_path ) ) {
			return;
		}

		wp_enqueue_script(
			'aegis-bunnycdn-settings',
			plugins_url( 'assets/js/bunnycdn-settings.js', \Aegis\Plugin\FILE ),
			array( 'jquery', 'aegis-admin-settings' ),
			(string) filemtime( $script_path ),
			true
		);
	}

	/**
	 * @param array<string, array{label: string, url: string}> $tabs Registered tabs.
	 * @return array<string, array{label: string, url: string}>
	 */
	public function register_admin_tab( array $tabs ): array {
		$tabs['connectors'] = array(
			'label' => __( 'Connectors', 'aegis' ),
			'url'   => admin_url( 'admin.php?page=aegis-connectors' ),
		);

		return $tabs;
	}

	public function register_menu(): void {
		$hook = add_submenu_page(
			'aegis-dashboard',
			__( 'Connectors', 'aegis' ),
			__( 'Connectors', 'aegis' ),
			'manage_options',
			'aegis-connectors',
			array( $this, 'render' )
		);

		if ( is_string( $hook ) && $hook !== '' ) {
			add_action(
				"load-{$hook}",
				static function (): void {
					global $title;
					$title = __( 'Connectors', 'aegis' );
				}
			);
		}
	}

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'aegis' ) );
		}

		$options           = Settings::get_settings();
		$bunnycdn_settings = Settings::get_bunnycdn_settings();
		$renderer          = $this->renderer();
		$template          = dirname( __DIR__, 2 ) . '/templates/connectors-admin-page.php';

		?>
		<div class="wrap aegis-admin-page">
			<?php do_action( 'aegis_admin_before_connectors_page' ); ?>
			<?php
			if ( file_exists( $template ) ) {
				include $template;
			}
			?>
		</div>
		<?php
	}
}
