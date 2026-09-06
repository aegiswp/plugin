<?php
/**
 * Snippet runtime loader.
 *
 * @package Aegis\Plugin\Snippets
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Snippets;

use Aegis\Plugin\Conditionals\Evaluator;
use Aegis\Plugin\Injection\LocationRegistry;
use Aegis\Plugin\Snippets\Executors\ContentExecutor;
use Aegis\Plugin\Snippets\Executors\CssExecutor;
use Aegis\Plugin\Snippets\Executors\JsExecutor;
use Aegis\Plugin\Snippets\Executors\PhpExecutor;
use function add_action;
use function add_filter;
use function add_shortcode;
use function defined;
use function get_current_screen;
use function in_the_loop;
use function is_admin;
use function is_array;
use function is_main_query;
use function wp_doing_ajax;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers enabled snippets with WordPress hooks.
 */
final class Loader {

	private Evaluator $evaluator;

	public function __construct() {
		$this->evaluator = new Evaluator();
	}

	/**
	 * Register loader hooks.
	 */
	public function init(): void {
		add_action( 'init', array( $this, 'register_shortcode' ), 0 );

		if ( defined( 'AEGIS_DISABLE_SNIPPETS' ) && AEGIS_DISABLE_SNIPPETS ) {
			return;
		}

		if ( Settings::is_safe_mode() ) {
			return;
		}

		add_action( 'init', array( $this, 'register_snippets' ), 1 );
	}

	/**
	 * Always register the shared tag so Safe Mode can explain itself to admins.
	 */
	public function register_shortcode(): void {
		add_shortcode( Locations::SHORTCODE_TAG, array( new Shortcode(), 'render' ) );
	}

	/**
	 * Register all enabled snippets.
	 */
	public function register_snippets(): void {
		foreach ( Storage::get_snippets() as $snippet ) {
			if ( empty( $snippet['enabled'] ) ) {
				continue;
			}

			$type = Locations::normalize_type( (string) ( $snippet['type'] ?? 'content' ) );

			match ( $type ) {
				Locations::TYPE_CSS => $this->register_css_snippet( $snippet ),
				Locations::TYPE_JS  => $this->register_js_snippet( $snippet ),
				Locations::TYPE_PHP => $this->register_php_snippet( $snippet ),
				default             => $this->register_content_snippet( $snippet ),
			};
		}
	}

	/**
	 * @param array<string, mixed> $snippet Snippet manifest entry.
	 */
	private function register_css_snippet( array $snippet ): void {
		$location = (string) ( $snippet['location'] ?? 'css_frontend' );
		$hook     = match ( $location ) {
			'css_admin'  => 'admin_enqueue_scripts',
			'css_login'  => 'login_enqueue_scripts',
			'css_editor' => 'enqueue_block_editor_assets',
			default      => 'wp_enqueue_scripts',
		};

		$this->add_conditioned_action(
			$hook,
			$snippet,
			static function ( array $snippet ): void {
				( new CssExecutor() )->render( $snippet );
			}
		);
	}

	/**
	 * @param array<string, mixed> $snippet Snippet manifest entry.
	 */
	private function register_js_snippet( array $snippet ): void {
		$location  = (string) ( $snippet['location'] ?? 'js_frontend_footer' );
		$in_footer = ! in_array( $location, array( 'js_frontend_header', 'js_admin_header' ), true );
		$hook      = match ( $location ) {
			'js_admin_header', 'js_admin_footer' => 'admin_enqueue_scripts',
			'js_login'  => 'login_enqueue_scripts',
			'js_editor' => 'enqueue_block_editor_assets',
			default     => 'wp_enqueue_scripts',
		};

		$this->add_conditioned_action(
			$hook,
			$snippet,
			static function ( array $snippet ) use ( $in_footer ): void {
				( new JsExecutor() )->render( $snippet, $in_footer );
			}
		);
	}

	/**
	 * @param array<string, mixed> $snippet Snippet manifest entry.
	 */
	private function register_php_snippet( array $snippet ): void {
		if ( ! Settings::is_php_enabled() ) {
			return;
		}

		$location = (string) ( $snippet['location'] ?? 'php_everywhere' );
		$run      = function () use ( $snippet ): void {
			if ( ! $this->should_run( $snippet ) ) {
				return;
			}

			( new PhpExecutor() )->render( $snippet );
		};

		switch ( $location ) {
			case 'php_everywhere':
				$run();
				break;
			case 'php_frontend':
				if ( $this->snippet_matches_scope( array_merge( $snippet, array( 'scope' => 'frontend' ) ) ) ) {
					$run();
				}
				break;
			case 'php_admin':
				if ( $this->snippet_matches_scope( array_merge( $snippet, array( 'scope' => 'admin' ) ) ) ) {
					$run();
				}
				break;
			case 'php_login':
				if ( $this->is_login_screen() ) {
					$run();
				} else {
					add_action( 'login_init', $run, (int) ( $snippet['priority'] ?? 10 ) );
				}
				break;
			default:
				if ( $location === '' || ! LocationRegistry::is_available( $location ) || ! Settings::is_location_enabled( $location ) ) {
					return;
				}

				add_action( $location, $run, (int) ( $snippet['priority'] ?? 10 ) );
				break;
		}
	}

	/**
	 * @param array<string, mixed> $snippet Snippet manifest entry.
	 */
	private function register_content_snippet( array $snippet ): void {
		$location = (string) ( $snippet['location'] ?? 'wp_head' );
		$priority = (int) ( $snippet['priority'] ?? 10 );

		if ( $location === 'shortcode' ) {
			$legacy = Locations::sanitize_shortcode( (string) ( $snippet['shortcode'] ?? '' ), '' );

			if ( $legacy !== '' && $legacy !== Locations::SHORTCODE_TAG ) {
				add_shortcode(
					$legacy,
					function ( $atts, $content = null ) use ( $snippet ): string {
						$atts         = is_array( $atts ) ? $atts : array();
						$atts['id']   = (string) ( $snippet['id'] ?? '' );

						return ( new Shortcode() )->render( $atts, $content );
					}
				);
			}

			return;
		}

		if ( $location === 'before_content' || $location === 'after_content' ) {
			add_filter(
				'the_content',
				function ( string $content ) use ( $snippet, $location ): string {
					if ( ! is_main_query() || ! in_the_loop() ) {
						return $content;
					}

					if ( ! $this->should_run( $snippet ) ) {
						return $content;
					}

					$output = ( new ContentExecutor() )->get_output( $snippet );

					return $location === 'before_content' ? $output . $content : $content . $output;
				},
				$priority
			);

			return;
		}

		if ( $location === '' || ! LocationRegistry::is_available( $location ) || ! Settings::is_location_enabled( $location ) ) {
			return;
		}

		add_action(
			$location,
			function () use ( $snippet ): void {
				if ( ! $this->should_run( $snippet ) ) {
					return;
				}

				( new ContentExecutor() )->render( $snippet );
			},
			$priority
		);
	}

	/**
	 * @param array<string, mixed> $snippet  Snippet row.
	 * @param callable             $callback Receives the snippet array.
	 */
	private function add_conditioned_action( string $hook, array $snippet, callable $callback ): void {
		add_action(
			$hook,
			function () use ( $snippet, $callback ): void {
				if ( ! $this->should_run( $snippet ) ) {
					return;
				}

				$callback( $snippet );
			},
			(int) ( $snippet['priority'] ?? 10 )
		);
	}

	/**
	 * @param array<string, mixed> $snippet Snippet row.
	 */
	private function should_run( array $snippet ): bool {
		$conditions = is_array( $snippet['conditions'] ?? null ) ? $snippet['conditions'] : array();

		return $this->evaluator->should_render_conditions( $conditions );
	}

	/**
	 * @param array<string, mixed> $snippet Snippet manifest entry.
	 */
	private function snippet_matches_scope( array $snippet ): bool {
		$scope = (string) ( $snippet['scope'] ?? Locations::scope_from_location(
			(string) ( $snippet['type'] ?? '' ),
			(string) ( $snippet['location'] ?? '' )
		) );

		return match ( $scope ) {
			'admin'  => is_admin() && ! $this->is_login_screen(),
			'login'  => $this->is_login_screen(),
			'editor' => is_admin() && $this->is_block_editor(),
			default  => ! is_admin() || wp_doing_ajax(),
		};
	}

	private function is_login_screen(): bool {
		return isset( $GLOBALS['pagenow'] ) && $GLOBALS['pagenow'] === 'wp-login.php';
	}

	private function is_block_editor(): bool {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return false;
		}

		$screen = get_current_screen();

		return $screen && $screen->is_block_editor();
	}
}
