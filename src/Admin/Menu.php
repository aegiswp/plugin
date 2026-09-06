<?php
/**
 * Admin Menu
 *
 * Dashboard menu and shared admin asset enqueueing.
 *
 * @package Aegis
 * @since   1.0.0
 */

declare(strict_types=1);

namespace Aegis\Plugin\Admin;

use function __;
use function admin_url;
use function add_action;
use function add_filter;
use function add_menu_page;
use function add_submenu_page;
use function apply_filters;
use function check_ajax_referer;
use function current_user_can;
use function esc_html__;
use function file_exists;
use function filemtime;
use function get_current_user_id;
use function is_array;
use function plugins_url;
use function sanitize_text_field;
use function str_starts_with;
use function update_user_meta;
use function wp_add_inline_style;
use function wp_create_nonce;
use function wp_enqueue_script;
use function wp_enqueue_style;
use function wp_localize_script;
use function wp_parse_url;
use function wp_register_script;
use function wp_register_style;
use function wp_send_json_error;
use function wp_send_json_success;
use function wp_unslash;

/**
 * Admin Menu class.
 */
class Menu {

	/**
	 * Renderer instance for page callbacks.
	 */
	private Renderer $renderer;

	/**
	 * Constructor.
	 *
	 * @param Renderer $renderer Renderer instance.
	 */
	public function __construct( Renderer $renderer ) {
		$this->renderer = $renderer;
	}

	/**
	 * Initialize the settings page.
	 */
	public function init(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ), 9 );
		add_action( 'admin_menu', array( $this, 'reorder_submenus' ), 1001 );
		add_action( 'in_admin_header', array( $this, 'render_top_bar' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_menu_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_filter( 'parent_file', array( $this, 'fix_admin_parent_file' ) );
		add_filter( 'submenu_file', array( $this, 'fix_admin_submenu_file' ) );
		add_filter( 'admin_body_class', array( $this, 'admin_body_class' ) );
		add_filter( 'aegis_admin_tabs', array( AdminTabs::class, 'sort' ), 999 );
		add_action( 'wp_ajax_aegis_dismiss_getting_started', array( $this, 'ajax_dismiss_getting_started' ) );
		( new PageLoader( $this->renderer ) )->init();
	}

	/**
	 * Persist dismissal of the dashboard Getting Started section for the current user.
	 */
	public function ajax_dismiss_getting_started(): void {
		if ( ! check_ajax_referer( 'aegis_settings_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'aegis' ) ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'aegis' ) ) );
		}

		update_user_meta( get_current_user_id(), Renderer::GETTING_STARTED_DISMISSED_META, '1' );

		wp_send_json_success();
	}

	/**
	 * Add a body class on Aegis plugin admin screens.
	 *
	 * @param string $classes Space-separated admin body classes.
	 */
	public function admin_body_class( string $classes ): string {
		if ( $this->is_aegis_admin_screen() ) {
			$classes .= ' aegis-plugin-admin';
		}

		return $classes;
	}

	/**
	 * Output the full-bleed Aegis header on plugin admin screens.
	 */
	public function render_top_bar(): void {
		if ( ! $this->is_aegis_admin_screen() ) {
			return;
		}

		$this->renderer->render_top_bar();
	}

	/**
	 * Whether the current screen is an Aegis plugin settings page.
	 */
	private function is_aegis_admin_screen(): bool {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['page'] ) ) : '';

		return str_starts_with( $page, 'aegis-' );
	}

	/**
	 * Reorder Aegis submenu items to match the sorted aegis_admin_tabs list.
	 */
	public function reorder_submenus(): void {
		global $submenu;

		if ( ! isset( $submenu['aegis-dashboard'] ) ) {
			return;
		}

		$tabs = apply_filters(
			'aegis_admin_tabs',
			array(
				'dashboard' => array(
					'label' => __( 'Dashboard', 'aegis' ),
					'url'   => admin_url( 'admin.php?page=aegis-dashboard' ),
				),
			)
		);

		$desired_order = array();

		foreach ( $tabs as $tab ) {
			if ( ! is_array( $tab ) || empty( $tab['url'] ) ) {
				continue;
			}

			$query = array();
			parse_str( (string) wp_parse_url( $tab['url'], PHP_URL_QUERY ), $query );

			if ( ! empty( $query['page'] ) && $query['page'] !== 'aegis-dashboard' ) {
				$desired_order[] = (string) $query['page'];
			}
		}

		$items_by_slug = array();

		foreach ( $submenu['aegis-dashboard'] as $item ) {
			if ( isset( $item[2] ) ) {
				$items_by_slug[ $item[2] ] = $item;
			}
		}

		$reordered = array();

		// Match core menus (Appearance → Themes): landing page is first submenu with the same slug.
		if ( isset( $items_by_slug['aegis-dashboard'] ) ) {
			$dashboard_item    = $items_by_slug['aegis-dashboard'];
			$dashboard_item[0] = esc_html__( 'Dashboard', 'aegis' );
			$reordered[]       = $dashboard_item;
			unset( $items_by_slug['aegis-dashboard'] );
		}

		foreach ( $desired_order as $slug ) {
			if ( isset( $items_by_slug[ $slug ] ) ) {
				$reordered[] = $items_by_slug[ $slug ];
				unset( $items_by_slug[ $slug ] );
			}
		}

		foreach ( $items_by_slug as $item ) {
			$reordered[] = $item;
		}

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Intentional submenu ordering.
		$submenu['aegis-dashboard'] = $reordered;
	}

	/**
	 * Fix parent file for admin menu highlighting.
	 *
	 * @param string $parent_file The parent file.
	 */
	public function fix_admin_parent_file( string $parent_file ): string {
		global $pagenow;

		if ( $pagenow === 'admin.php' ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['page'] ) ) : '';

			if ( str_starts_with( $page, 'aegis-' ) ) {
				return 'aegis-dashboard';
			}
		}

		return $parent_file;
	}

	/**
	 * Fix submenu file for admin menu highlighting.
	 *
	 * @param string|null $submenu_file The submenu file.
	 */
	public function fix_admin_submenu_file( ?string $submenu_file ): ?string {
		global $pagenow;

		if ( $pagenow === 'admin.php' ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['page'] ) ) : '';

			if ( str_starts_with( $page, 'aegis-' ) ) {
				return $page;
			}
		}

		return $submenu_file;
	}

	/**
	 * Register admin menu.
	 */
	public function register_menu(): void {
		$icon = Renderer::BRAND_ICON_DATA_URI;

		add_menu_page(
			esc_html__( 'Aegis', 'aegis' ),
			esc_html__( 'Aegis', 'aegis' ),
			'manage_options',
			'aegis-dashboard',
			array( $this->renderer, 'render_dashboard_page' ),
			$icon,
			59
		);

		// Register before other submenus so WordPress does not auto-insert a duplicate parent item.
		add_submenu_page(
			'aegis-dashboard',
			esc_html__( 'Dashboard', 'aegis' ),
			esc_html__( 'Dashboard', 'aegis' ),
			'manage_options',
			'aegis-dashboard',
			array( $this->renderer, 'render_dashboard_page' )
		);
	}

	/**
	 * Hide the dashboard submenu entry on all admin screens.
	 *
	 * Core keeps a same-slug first submenu (Appearance → Themes) for routing; Aegis uses
	 * the same pattern but hides the duplicate because the top-level item already opens it.
	 */
	public function enqueue_admin_menu_styles(): void {
		wp_register_style( 'aegis-admin-menu', false );
		wp_enqueue_style( 'aegis-admin-menu' );
		wp_add_inline_style(
			'aegis-admin-menu',
			'#adminmenu li#toplevel_page_aegis-dashboard .wp-submenu li:has(> a[href*="page=aegis-dashboard"]) { display: none; }'
		);
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook_suffix Current admin page hook.
	 */
	public function enqueue_assets( string $hook_suffix ): void {
		if ( ! str_contains( $hook_suffix, 'aegis' ) ) {
			return;
		}

		$plugin_dir = \Aegis\Plugin\DIR;
		$plugin_url = plugins_url( '', \Aegis\Plugin\FILE );
		$asset_file = $plugin_dir . 'assets/admin/build/index.asset.php';

		if ( file_exists( $asset_file ) ) {
			$asset = require $asset_file;
			$js    = $plugin_dir . 'assets/admin/src/js/admin-settings.js';
			$css   = $plugin_dir . 'assets/admin/src/css/admin-settings.css';
			$shell = $plugin_dir . 'assets/admin/src/css/admin-shell.css';

			wp_register_style(
				'aegis-admin-shell',
				$plugin_url . '/assets/admin/src/css/admin-shell.css',
				array( 'dashicons', 'wp-components', 'wp-theme' ),
				file_exists( $shell ) ? (string) filemtime( $shell ) : (string) $asset['version']
			);

			wp_register_style(
				'aegis-admin-settings',
				$plugin_url . '/assets/admin/src/css/admin-settings.css',
				array( 'aegis-admin-shell' ),
				file_exists( $css ) ? (string) filemtime( $css ) : (string) $asset['version']
			);

			wp_register_script(
				'aegis-admin-settings',
				$plugin_url . '/assets/admin/src/js/admin-settings.js',
				array_merge( is_array( $asset['dependencies'] ?? null ) ? $asset['dependencies'] : array(), array( 'jquery' ) ),
				file_exists( $js ) ? (string) filemtime( $js ) : (string) $asset['version'],
				true
			);

			wp_enqueue_style( 'aegis-admin-settings' );
			wp_enqueue_script( 'aegis-admin-settings' );
		} else {
			wp_register_style(
				'aegis-admin-settings',
				$plugin_url . '/assets/admin/src/css/admin-settings.css',
				array( 'dashicons', 'wp-components', 'wp-theme' ),
				(string) time()
			);

			wp_register_style(
				'aegis-admin-shell',
				$plugin_url . '/assets/admin/src/css/admin-shell.css',
				array( 'aegis-admin-settings' ),
				(string) time()
			);

			wp_register_script(
				'aegis-admin-settings',
				$plugin_url . '/assets/admin/src/js/admin-settings.js',
				array( 'jquery' ),
				(string) time(),
				true
			);

			wp_enqueue_style( 'aegis-admin-settings' );
			wp_enqueue_style( 'aegis-admin-shell' );
			wp_enqueue_script( 'aegis-admin-settings' );
		}

		$menu_active_css = '
			#adminmenu li.toplevel_page_aegis-dashboard > a.menu-top,
			#adminmenu li.toplevel_page_aegis-dashboard > a.menu-top:hover,
			#adminmenu li.toplevel_page_aegis-dashboard > a.menu-top:focus,
			#adminmenu li.toplevel_page_aegis-dashboard.current > a.menu-top,
			#adminmenu li.toplevel_page_aegis-dashboard.wp-has-current-submenu > a.menu-top,
			#adminmenu li.toplevel_page_aegis-dashboard.wp-menu-open > a.menu-top {
				background-color: var(--wpds-color-background-interactive-brand-strong, var(--wp-admin-theme-color, #3858e9)) !important;
				color: var(--wpds-color-foreground-interactive-brand-strong, #fff) !important;
			}
			#adminmenu li.toplevel_page_aegis-dashboard > a.menu-top .wp-menu-image:before,
			#adminmenu li.toplevel_page_aegis-dashboard > a.menu-top:hover .wp-menu-image:before,
			#adminmenu li.toplevel_page_aegis-dashboard > a.menu-top:focus .wp-menu-image:before,
			#adminmenu li.toplevel_page_aegis-dashboard.current > a.menu-top .wp-menu-image:before,
			#adminmenu li.toplevel_page_aegis-dashboard.wp-has-current-submenu > a.menu-top .wp-menu-image:before,
			#adminmenu li.toplevel_page_aegis-dashboard.wp-menu-open > a.menu-top .wp-menu-image:before {
				color: var(--wpds-color-foreground-interactive-brand-strong, #fff) !important;
			}
			#adminmenu li.toplevel_page_aegis-dashboard > a.menu-top .wp-menu-image svg,
			#adminmenu li.toplevel_page_aegis-dashboard > a.menu-top .wp-menu-image svg path {
				fill: currentColor;
			}
			#adminmenu li.toplevel_page_aegis-dashboard.current > a.menu-top .wp-menu-image svg,
			#adminmenu li.toplevel_page_aegis-dashboard.current > a.menu-top .wp-menu-image svg path,
			#adminmenu li.toplevel_page_aegis-dashboard.wp-has-current-submenu > a.menu-top .wp-menu-image svg,
			#adminmenu li.toplevel_page_aegis-dashboard.wp-has-current-submenu > a.menu-top .wp-menu-image svg path,
			#adminmenu li.toplevel_page_aegis-dashboard.wp-menu-open > a.menu-top .wp-menu-image svg,
			#adminmenu li.toplevel_page_aegis-dashboard.wp-menu-open > a.menu-top .wp-menu-image svg path {
				fill: var(--wpds-color-foreground-interactive-brand-strong, #fff) !important;
			}
		';
		wp_add_inline_style( 'aegis-admin-settings', $menu_active_css );

		wp_localize_script(
			'aegis-admin-settings',
			'aegisAdmin',
			array(
				'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
				'nonce'       => wp_create_nonce( 'aegis_settings_nonce' ),
				'saving'      => __( 'Saving...', 'aegis' ),
				'saved'       => __( 'Settings saved.', 'aegis' ),
				'error'       => __( 'Error saving settings.', 'aegis' ),
				'purgePrompt' => __( 'This permanently deletes all Aegis data. Type DELETE to confirm.', 'aegis' ),
				'loadError'   => __( 'Could not load that screen. Reloading…', 'aegis' ),
			)
		);

		$hook_css  = $plugin_dir . 'assets/admin/hook-patterns.css';
		$modal_css = $plugin_dir . 'assets/admin/modals.css';

		wp_enqueue_style(
			'aegis-hook-patterns-admin',
			plugins_url( 'assets/admin/hook-patterns.css', \Aegis\Plugin\FILE ),
			array( 'aegis-admin-settings' ),
			file_exists( $hook_css ) ? (string) filemtime( $hook_css ) : \Aegis\Plugin\VERSION
		);

		wp_enqueue_style(
			'aegis-modals-admin',
			plugins_url( 'assets/admin/modals.css', \Aegis\Plugin\FILE ),
			array( 'aegis-hook-patterns-admin' ),
			file_exists( $modal_css ) ? (string) filemtime( $modal_css ) : \Aegis\Plugin\VERSION
		);
	}
}
