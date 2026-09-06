<?php
/**
 * Frontend injection position preview for admins.
 *
 * @package Aegis\Plugin\Injection
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Injection;

use Aegis\Plugin\Admin\Renderer;
use Aegis\Plugin\Snippets\Locations;
use Aegis\Plugin\Snippets\Storage;
use WP_Admin_Bar;
use function __;
use function _n;
use function add_action;
use function add_query_arg;
use function admin_url;
use function array_sum;
use function current_user_can;
use function esc_attr;
use function esc_html;
use function esc_url;
use function file_exists;
use function filemtime;
use function get_post_meta;
use function get_posts;
use function get_user_meta;
use function in_array;
use function is_admin;
use function is_user_logged_in;
use function number_format_i18n;
use function plugins_url;
use function remove_query_arg;
use function sanitize_key;
use function sprintf;
use function update_user_meta;
use function wp_enqueue_style;
use function wp_get_current_user;
use function wp_get_referer;
use function wp_safe_redirect;
use function wp_unslash;
use function wp_verify_nonce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shows hook and snippet injection markers on the frontend via the admin bar.
 */
final class Preview {

	public const META_HOOKS     = 'aegis_preview_hook_positions';
	public const META_SNIPPETS  = 'aegis_preview_snippet_positions';
	public const META_ADMIN_BAR = 'aegis_preview_admin_bar_shortcuts';

	/** @var array<string, int>|null */
	private static ?array $snippet_counts = null;

	/** @var array<string, int>|null */
	private static ?array $hook_counts = null;

	/**
	 * Register preview hooks.
	 */
	public function init(): void {
		add_action( 'admin_bar_menu', array( $this, 'register_admin_bar' ), 90 );
		add_action( 'init', array( $this, 'handle_toggle_request' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'template_redirect', array( $this, 'register_markers' ), 5 );
	}

	/**
	 * Whether hook position preview is enabled for the current user.
	 */
	public static function is_hooks_preview_enabled(): bool {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		return (bool) get_user_meta( get_current_user_id(), self::META_HOOKS, true );
	}

	/**
	 * Whether snippet position preview is enabled for the current user.
	 */
	public static function is_snippets_preview_enabled(): bool {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		return (bool) get_user_meta( get_current_user_id(), self::META_SNIPPETS, true );
	}

	/**
	 * Whether the frontend admin bar shortcut menu is enabled.
	 */
	public static function is_admin_bar_shortcuts_enabled(): bool {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		return (bool) get_user_meta( get_current_user_id(), self::META_ADMIN_BAR, true );
	}

	/**
	 * @param WP_Admin_Bar $admin_bar Admin bar instance.
	 */
	public function register_admin_bar( WP_Admin_Bar $admin_bar ): void {
		if ( ! current_user_can( 'manage_options' ) || is_admin() ) {
			return;
		}

		if ( ! self::is_admin_bar_shortcuts_enabled() ) {
			return;
		}

		$hooks_on      = self::is_hooks_preview_enabled();
		$snippets_on   = self::is_snippets_preview_enabled();
		$hook_count    = $this->count_active_hooks();
		$snippet_count = $this->count_active_snippets();
		$total         = $hook_count + $snippet_count;

		$icon  = Renderer::get_brand_icon_html( 20, 'ab-icon' );
		$label = esc_html(
			sprintf(
				/* translators: %s: number of active hook patterns and snippets */
				_n( '%s Position', '%s Positions', $total, 'aegis' ),
				number_format_i18n( $total )
			)
		);

		$admin_bar->add_node(
			array(
				'id'    => 'aegis-injection-preview',
				'title' => $icon . '<span class="ab-label">' . $label . '</span>',
				'href'  => admin_url( 'admin.php?page=aegis-snippets' ),
				'meta'  => array(
					'title' => _n( 'Aegis Position', 'Aegis Positions', $total, 'aegis' ),
				),
			)
		);

		$hooks_title = esc_html(
			sprintf(
				/* translators: %s: number of active hook patterns */
				_n( '%s Hook', '%s Hooks', $hook_count, 'aegis' ),
				number_format_i18n( $hook_count )
			)
		) . ' (' . esc_html( $hooks_on ? __( 'On', 'aegis' ) : __( 'Off', 'aegis' ) ) . ')';

		$snippets_title = esc_html(
			sprintf(
				/* translators: %s: number of active snippets */
				_n( '%s Snippet', '%s Snippets', $snippet_count, 'aegis' ),
				number_format_i18n( $snippet_count )
			)
		) . ' (' . esc_html( $snippets_on ? __( 'On', 'aegis' ) : __( 'Off', 'aegis' ) ) . ')';

		$admin_bar->add_node(
			array(
				'id'     => 'aegis-preview-hooks',
				'parent' => 'aegis-injection-preview',
				'title'  => $hooks_title,
				'href'   => $this->toggle_url( 'hooks' ),
				'meta'   => array(
					'title' => $hooks_on
						? __( 'Hide hook positions', 'aegis' )
						: __( 'Show hook positions', 'aegis' ),
				),
			)
		);

		$admin_bar->add_node(
			array(
				'id'     => 'aegis-preview-snippets',
				'parent' => 'aegis-injection-preview',
				'title'  => $snippets_title,
				'href'   => $this->toggle_url( 'snippets' ),
				'meta'   => array(
					'title' => $snippets_on
						? __( 'Hide snippet positions', 'aegis' )
						: __( 'Show snippet positions', 'aegis' ),
				),
			)
		);
	}

	/**
	 * Handle admin bar toggle clicks.
	 */
	public function handle_toggle_request(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['aegis_preview_toggle'] ) ) {
			return;
		}

		if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( wp_unslash( (string) $_GET['_wpnonce'] ), 'aegis_preview_toggle' ) ) {
			return;
		}

		$toggle = sanitize_key( wp_unslash( (string) $_GET['aegis_preview_toggle'] ) );
		$user_id = get_current_user_id();

		if ( $toggle === 'hooks' ) {
			update_user_meta( $user_id, self::META_HOOKS, self::is_hooks_preview_enabled() ? 0 : 1 );
		} elseif ( $toggle === 'snippets' ) {
			update_user_meta( $user_id, self::META_SNIPPETS, self::is_snippets_preview_enabled() ? 0 : 1 );
		}

		$redirect = wp_get_referer();

		if ( ! $redirect ) {
			$redirect = home_url( '/' );
		}

		$redirect = remove_query_arg( array( 'aegis_preview_toggle', '_wpnonce' ), $redirect );

		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Enqueue preview styles when a preview mode is active.
	 */
	public function enqueue_assets(): void {
		if ( is_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$needs_styles = self::is_admin_bar_shortcuts_enabled()
			|| self::is_hooks_preview_enabled()
			|| self::is_snippets_preview_enabled();

		if ( ! $needs_styles ) {
			return;
		}

		wp_enqueue_style(
			'aegis-injection-preview',
			plugins_url( 'assets/admin/injection-preview.css', \Aegis\Plugin\FILE ),
			array(),
			file_exists( \Aegis\Plugin\DIR . 'assets/admin/injection-preview.css' )
				? (string) filemtime( \Aegis\Plugin\DIR . 'assets/admin/injection-preview.css' )
				: \Aegis\Plugin\VERSION
		);
	}

	/**
	 * Register marker output on available frontend injection hooks.
	 */
	public function register_markers(): void {
		if ( is_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$hooks_on    = self::is_hooks_preview_enabled();
		$snippets_on = self::is_snippets_preview_enabled();

		if ( ! $hooks_on && ! $snippets_on ) {
			return;
		}

		$locations = $this->get_frontend_hooks();

		foreach ( $locations as $hook => $label ) {
			if ( $hook === 'wp_head' ) {
				continue;
			}

			add_action(
				$hook,
				function () use ( $hook, $label, $hooks_on, $snippets_on ): void {
					echo $this->build_marker_html( $hook, $label, $hooks_on, $snippets_on ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				},
				99999
			);
		}

		// wp_head runs in <head> — show a body-open marker instead.
		if ( isset( $locations['wp_head'] ) ) {
			add_action(
				'wp_body_open',
				function () use ( $hooks_on, $snippets_on ): void {
					echo $this->build_marker_html( 'wp_head', __( 'Document head', 'aegis' ), $hooks_on, $snippets_on ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				},
				0
			);
		}

		if ( $snippets_on ) {
			add_action(
				'wp_footer',
				function () use ( $hooks_on, $snippets_on ): void {
					echo $this->build_marker_html( 'wp_enqueue_scripts', __( 'Enqueue scripts (CSS/JS snippets)', 'aegis' ), $hooks_on, $snippets_on ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				},
				0
			);
		}
	}

	/**
	 * @return array<string, string> Hook => label.
	 */
	private function get_frontend_hooks(): array {
		$flat = array();

		foreach ( LocationRegistry::get_all() as $group => $hooks ) {
			foreach ( $hooks as $hook => $meta ) {
				if ( ( $meta['scope'] ?? 'frontend' ) !== 'frontend' ) {
					continue;
				}

				if ( $hook === 'wp_enqueue_scripts' ) {
					continue;
				}

				$flat[ $hook ] = (string) ( $meta['label'] ?? $hook );
			}
		}

		return $flat;
	}

	/**
	 * @param string $hook       Hook name.
	 * @param string $label      Human label.
	 * @param bool   $hooks_on   Show hook layer.
	 * @param bool   $snippets_on Show snippet layer.
	 */
	private function build_marker_html( string $hook, string $label, bool $hooks_on, bool $snippets_on ): string {
		$snippet_count = $this->get_snippet_counts()[ $hook ] ?? 0;
		$hook_count    = $this->get_hook_pattern_counts()[ $hook ] ?? 0;

		$classes = array( 'aegis-injection-marker' );

		if ( $hooks_on ) {
			$classes[] = 'aegis-injection-marker--hooks-on';
		}

		if ( $snippets_on ) {
			$classes[] = 'aegis-injection-marker--snippets-on';
		}

		if ( $hook_count > 0 ) {
			$classes[] = 'aegis-injection-marker--has-hooks';
		}

		if ( $snippet_count > 0 ) {
			$classes[] = 'aegis-injection-marker--has-snippets';
		}

		$meta_parts = array(
			esc_html( $label ),
		);

		if ( $hooks_on ) {
			$meta_parts[] = esc_html(
				sprintf(
					/* translators: %d: number of hook patterns */
					_n( '%d hook', '%d hooks', $hook_count, 'aegis' ),
					$hook_count
				)
			);
		}

		if ( $snippets_on ) {
			$meta_parts[] = esc_html(
				sprintf(
					/* translators: %d: number of snippets */
					_n( '%d snippet', '%d snippets', $snippet_count, 'aegis' ),
					$snippet_count
				)
			);
		}

		return sprintf(
			'<div class="%s" data-hook="%s"><span class="aegis-injection-marker__hook">%s</span><span class="aegis-injection-marker__meta">%s</span></div>',
			esc_attr( implode( ' ', $classes ) ),
			esc_attr( $hook ),
			esc_html( $hook ),
			implode( ' · ', $meta_parts )
		);
	}

	/**
	 * @return array<string, int>
	 */
	private function get_snippet_counts(): array {
		if ( self::$snippet_counts !== null ) {
			return self::$snippet_counts;
		}

		self::$snippet_counts = array();

		foreach ( Storage::get_snippets() as $snippet ) {
			if ( empty( $snippet['enabled'] ) ) {
				continue;
			}

			$type = Locations::normalize_type( (string) ( $snippet['type'] ?? 'content' ) );
			$hook = (string) ( $snippet['location'] ?? '' );

			if ( $type === Locations::TYPE_CSS ) {
				$hook = match ( $hook ) {
					'css_admin'  => 'admin_enqueue_scripts',
					'css_login'  => 'login_enqueue_scripts',
					'css_editor' => 'enqueue_block_editor_assets',
					default      => 'wp_enqueue_scripts',
				};
			} elseif ( $type === Locations::TYPE_JS ) {
				$hook = match ( $hook ) {
					'js_frontend_header' => 'wp_head',
					'js_admin_header'    => 'admin_head',
					'js_admin_footer'    => 'admin_footer',
					'js_login'           => 'login_enqueue_scripts',
					'js_editor'          => 'enqueue_block_editor_assets',
					default              => 'wp_footer',
				};
			} elseif ( in_array( $hook, array( 'shortcode', 'before_content', 'after_content', 'php_everywhere', 'php_frontend', 'php_admin', 'php_login' ), true ) ) {
				continue;
			}

			if ( $hook === '' ) {
				continue;
			}

			self::$snippet_counts[ $hook ] = ( self::$snippet_counts[ $hook ] ?? 0 ) + 1;
		}

		return self::$snippet_counts;
	}

	/**
	 * @return array<string, int>
	 */
	private function get_hook_pattern_counts(): array {
		if ( self::$hook_counts !== null ) {
			return self::$hook_counts;
		}

		self::$hook_counts = array();

		if ( ! class_exists( \Aegis\Pro\HookPatterns::class ) ) {
			return self::$hook_counts;
		}

		$patterns = get_posts(
			array(
				'post_type'      => \Aegis\Pro\HookPatterns::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'   => '_aegis_enabled',
						'value' => '1',
					),
				),
			)
		);

		foreach ( $patterns as $pattern ) {
			$hook = (string) get_post_meta( $pattern->ID, '_aegis_hook_name', true );

			if ( $hook === '' ) {
				continue;
			}

			self::$hook_counts[ $hook ] = ( self::$hook_counts[ $hook ] ?? 0 ) + 1;
		}

		return self::$hook_counts;
	}

	/**
	 * Number of enabled hook patterns.
	 */
	private function count_active_hooks(): int {
		return (int) array_sum( $this->get_hook_pattern_counts() );
	}

	/**
	 * Number of enabled snippets.
	 */
	private function count_active_snippets(): int {
		$count = 0;

		foreach ( Storage::get_snippets() as $snippet ) {
			if ( ! empty( $snippet['enabled'] ) ) {
				++$count;
			}
		}

		return $count;
	}

	private function toggle_url( string $type ): string {
		return wp_nonce_url(
			add_query_arg( 'aegis_preview_toggle', $type ),
			'aegis_preview_toggle'
		);
	}
}
