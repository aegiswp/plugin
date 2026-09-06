<?php
/**
 * AJAX loader for Aegis admin screens (no full wp-admin HTML).
 *
 * @package Aegis\Plugin\Admin
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Admin;

use WP_Error;
use function __;
use function add_action;
use function admin_url;
use function apply_filters;
use function check_ajax_referer;
use function current_user_can;
use function did_action;
use function do_action;
use function esc_url_raw;
use function get_bloginfo;
use function get_plugin_page_hook;
use function has_action;
use function is_array;
use function is_string;
use function is_wp_error;
use function ob_get_clean;
use function ob_start;
use function parse_str;
use function rtrim;
use function sanitize_key;
use function sanitize_text_field;
use function sprintf;
use function str_ends_with;
use function str_starts_with;
use function strtolower;
use function wp_parse_url;
use function wp_send_json_error;
use function wp_send_json_success;
use function wp_unslash;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders Aegis dashboard pages as HTML fragments for in-admin navigation.
 */
final class PageLoader {

	private Renderer $renderer;

	public function __construct( Renderer $renderer ) {
		$this->renderer = $renderer;
	}

	public function init(): void {
		add_action( 'wp_ajax_aegis_load_admin_page', array( $this, 'ajax_load' ) );
	}

	public function ajax_load(): void {
		check_ajax_referer( 'aegis_settings_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Forbidden.', 'aegis' ) ), 403 );
		}

		$url = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( (string) $_POST['url'] ) ) : '';
		$result = $this->load_fragment( $url );

		if ( is_wp_error( $result ) ) {
			$status = (int) $result->get_error_data();
			wp_send_json_error(
				array( 'message' => $result->get_error_message() ),
				$status > 0 ? $status : 400
			);
		}

		wp_send_json_success( $result );
	}

	/**
	 * Render an Aegis admin screen as fragment HTML.
	 *
	 * @return array{page: string, title: string, topBar: string, content: string}|WP_Error
	 */
	public function load_fragment( string $url ) {
		if ( $url === '' || ! $this->is_allowed_url( $url ) ) {
			return new WP_Error( 'aegis_invalid_page', __( 'Invalid page.', 'aegis' ), 400 );
		}

		$args = $this->query_args( $url );
		$page = sanitize_key( (string) ( $args['page'] ?? '' ) );

		if ( $page === '' || ! str_starts_with( $page, 'aegis-' ) || isset( $args['edit'] ) ) {
			return new WP_Error( 'aegis_invalid_page', __( 'Invalid page.', 'aegis' ), 400 );
		}

		$this->prime_request( $page, $args );
		$this->ensure_admin_menu();

		$hook = $this->resolve_hook( $page );

		if ( $hook === '' ) {
			return new WP_Error( 'aegis_unknown_page', __( 'Unknown page.', 'aegis' ), 404 );
		}

		ob_start();
		$this->renderer->render_top_bar();
		$top_bar = (string) ob_get_clean();

		ob_start();
		do_action( $hook );
		$content = (string) ob_get_clean();

		if ( $content === '' ) {
			return new WP_Error( 'aegis_empty_page', __( 'Empty page.', 'aegis' ), 500 );
		}

		return array(
			'page'    => $page,
			'title'   => $this->document_title( $page ),
			'topBar'  => $top_bar,
			'content' => $content,
		);
	}

	/**
	 * @param array<string, string> $args Parsed query args.
	 */
	private function prime_request( string $page, array $args ): void {
		global $pagenow, $plugin_page, $hook_suffix, $parent_file, $submenu_file;

		$pagenow      = 'admin.php';
		$plugin_page  = $page;
		$hook_suffix  = $page;
		$parent_file  = 'aegis-dashboard';
		$submenu_file = $page === 'aegis-dashboard' ? 'aegis-dashboard' : $page;

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Destination query copied from the requested admin URL.
		foreach ( array_keys( $_GET ) as $key ) {
			if ( $key === 'action' || $key === 'nonce' ) {
				continue;
			}

			unset( $_GET[ $key ], $_REQUEST[ $key ] );
		}

		$_GET['page']     = $page;
		$_REQUEST['page'] = $page;

		foreach ( $args as $key => $value ) {
			if ( $key === 'page' || $key === 'edit' || $value === '' ) {
				continue;
			}

			$safe              = sanitize_text_field( $value );
			$_GET[ $key ]      = $safe;
			$_REQUEST[ $key ]  = $safe;
		}
	}

	private function ensure_admin_menu(): void {
		if ( ! function_exists( 'add_menu_page' ) || ! function_exists( 'get_plugin_page_hook' ) ) {
			require_once ABSPATH . 'wp-admin/includes/admin.php';
		}

		if ( did_action( 'admin_menu' ) ) {
			return;
		}

		do_action( 'admin_menu', '' );
	}

	private function resolve_hook( string $page ): string {
		$parents = array( '', 'aegis-dashboard', 'admin.php' );

		foreach ( $parents as $parent ) {
			$hook = get_plugin_page_hook( $page, $parent );

			if ( is_string( $hook ) && $hook !== '' && has_action( $hook ) ) {
				return $hook;
			}
		}

		$candidates = array(
			'toplevel_page_' . $page,
			'aegis_page_' . $page,
			'aegis-dashboard_page_' . $page,
			'admin_page_' . $page,
		);

		foreach ( $candidates as $hook ) {
			if ( has_action( $hook ) ) {
				return $hook;
			}
		}

		return '';
	}

	private function is_allowed_url( string $url ): bool {
		$home = wp_parse_url( admin_url( 'admin.php' ) );
		$dest = wp_parse_url( $url );

		if ( ! is_array( $home ) || ! is_array( $dest ) ) {
			return false;
		}

		$home_host = isset( $home['host'] ) ? strtolower( (string) $home['host'] ) : '';
		$dest_host = isset( $dest['host'] ) ? strtolower( (string) $dest['host'] ) : '';

		if ( $home_host === '' || $home_host !== $dest_host ) {
			return false;
		}

		$dest_path = isset( $dest['path'] ) ? (string) $dest['path'] : '';

		return str_ends_with( rtrim( $dest_path, '/' ), 'admin.php' );
	}

	/**
	 * @return array<string, string>
	 */
	private function query_args( string $url ): array {
		$parsed = wp_parse_url( $url );
		$query  = is_array( $parsed ) && isset( $parsed['query'] ) ? (string) $parsed['query'] : '';
		$args   = array();

		parse_str( $query, $args );

		$out = array();

		foreach ( $args as $key => $value ) {
			if ( ! is_string( $key ) || ! is_string( $value ) ) {
				continue;
			}

			$out[ sanitize_key( $key ) ] = $value;
		}

		return $out;
	}

	private function document_title( string $page ): string {
		$tabs  = apply_filters(
			'aegis_admin_tabs',
			array(
				'dashboard' => array(
					'label' => __( 'Dashboard', 'aegis' ),
					'url'   => admin_url( 'admin.php?page=aegis-dashboard' ),
				),
			)
		);
		$label = '';

		foreach ( $tabs as $tab ) {
			if ( ! is_array( $tab ) || empty( $tab['url'] ) || empty( $tab['label'] ) ) {
				continue;
			}

			$tab_query = wp_parse_url( (string) $tab['url'], PHP_URL_QUERY );
			$tab_args  = array();

			if ( is_string( $tab_query ) ) {
				parse_str( $tab_query, $tab_args );
			}

			if ( isset( $tab_args['page'] ) && (string) $tab_args['page'] === $page ) {
				$label = (string) $tab['label'];
				break;
			}
		}

		if ( $label === '' ) {
			$label = __( 'Aegis', 'aegis' );
		}

		return sprintf(
			'%s ‹ %s — WordPress',
			$label,
			(string) get_bloginfo( 'name' )
		);
	}
}
