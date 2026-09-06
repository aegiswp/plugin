<?php
/**
 * Frontend performance optimizations (site-wide toggles).
 *
 * @package Aegis\Plugin\Performance
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Performance;

use Aegis\Plugin\Blocks\Settings;
use function add_action;
use function add_filter;
use function is_admin;
use function is_user_logged_in;
use function remove_action;
use function wp_deregister_script;
use function wp_deregister_style;
use function wp_dequeue_script;
use function wp_dequeue_style;
use function wp_doing_cron;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Applies optional frontend-only performance optimizations from block settings.
 */
final class FrontendOptimizations {

	public function init(): void {
		add_action( 'init', [ $this, 'bootstrap' ], 20 );
	}

	/**
	 * Register hooks when running on the public frontend.
	 *
	 * @return void
	 */
	public function bootstrap(): void {
		if ( is_admin() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}

		if ( Settings::is_enabled( 'perf_disable_wp_embed' ) ) {
			$this->disable_wp_embed();
		}

		if ( Settings::is_enabled( 'perf_disable_dashicons' ) && ! is_user_logged_in() ) {
			add_action( 'wp_enqueue_scripts', [ $this, 'deregister_dashicons' ], 100 );
		}

		if ( Settings::is_enabled( 'perf_reduce_heartbeat' ) && ! is_user_logged_in() ) {
			$this->disable_heartbeat();
		}
	}

	/**
	 * Remove oEmbed discovery and the wp-embed script on the frontend.
	 *
	 * @return void
	 */
	private function disable_wp_embed(): void {
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
		remove_action( 'wp_head', 'wp_oembed_add_host_js' );

		add_action(
			'wp_enqueue_scripts',
			static function (): void {
				wp_deregister_script( 'wp-embed' );
				wp_dequeue_script( 'wp-embed' );
			},
			100
		);
	}

	/**
	 * Deregister dashicons for guests.
	 *
	 * @return void
	 */
	public function deregister_dashicons(): void {
		wp_deregister_style( 'dashicons' );
		wp_dequeue_style( 'dashicons' );
	}

	/**
	 * Disable Heartbeat on the frontend for guests.
	 *
	 * @return void
	 */
	public function disable_heartbeat(): void {
		add_action(
			'wp_enqueue_scripts',
			static function (): void {
				wp_deregister_script( 'heartbeat' );
				wp_dequeue_script( 'heartbeat' );
			},
			100
		);

		add_filter(
			'heartbeat_settings',
			static function ( array $settings ): array {
				$settings['interval'] = 120;

				return $settings;
			}
		);
	}
}
