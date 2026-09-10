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
use Aegis\Plugin\Integrations\WooCommerce as WooCommerceHelper;
use function add_action;
use function add_filter;
use function function_exists;
use function is_account_page;
use function is_admin;
use function is_cart;
use function is_checkout;
use function is_user_logged_in;
use function is_woocommerce;
use function remove_action;
use function wp_dequeue_script;
use function wp_dequeue_style;
use function wp_deregister_script;
use function wp_deregister_style;
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

		if ( WooCommerceHelper::is_plugin_active() ) {
			if ( Settings::is_enabled( 'perf_woo_disable_cart_fragments' ) ) {
				add_action( 'wp_enqueue_scripts', [ $this, 'dequeue_woo_cart_fragments' ], 100 );
			}

			if ( Settings::is_enabled( 'perf_woo_disable_assets_elsewhere' ) ) {
				add_action( 'wp_enqueue_scripts', [ $this, 'dequeue_woo_assets_elsewhere' ], 99 );
			}

			if ( Settings::is_enabled( 'perf_woo_disable_password_strength' ) ) {
				add_action( 'wp_print_scripts', [ $this, 'dequeue_woo_password_strength' ], 100 );
			}
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

	/**
	 * Whether the current request is a WooCommerce shopping surface.
	 */
	private function is_woo_request(): bool {
		return ( function_exists( 'is_woocommerce' ) && is_woocommerce() )
			|| ( function_exists( 'is_cart' ) && is_cart() )
			|| ( function_exists( 'is_checkout' ) && is_checkout() )
			|| ( function_exists( 'is_account_page' ) && is_account_page() );
	}

	/**
	 * Drop cart-fragments AJAX outside cart, checkout, and account.
	 *
	 * @return void
	 */
	public function dequeue_woo_cart_fragments(): void {
		if ( $this->is_woo_request() ) {
			return;
		}

		wp_dequeue_script( 'wc-cart-fragments' );
	}

	/**
	 * Dequeue WooCommerce scripts/styles on non-store pages.
	 *
	 * @return void
	 */
	public function dequeue_woo_assets_elsewhere(): void {
		if ( $this->is_woo_request() ) {
			return;
		}

		$styles = [
			'woocommerce-general',
			'woocommerce-layout',
			'woocommerce-smallscreen',
			'woocommerce-inline',
			'woocommerce-blocktheme',
			'wc-blocks-style',
			'wc-blocks-vendors-style',
			'wc-all-blocks-style',
		];

		foreach ( $styles as $handle ) {
			wp_dequeue_style( $handle );
		}

		$scripts = [
			'woocommerce',
			'wc-add-to-cart',
			'wc-cart',
			'wc-cart-fragments',
			'wc-checkout',
			'wc-single-product',
			'wc-order-attribution',
			'sourcebuster-js',
		];

		foreach ( $scripts as $handle ) {
			wp_dequeue_script( $handle );
		}
	}

	/**
	 * Load password strength meter only on account and checkout.
	 *
	 * @return void
	 */
	public function dequeue_woo_password_strength(): void {
		$keep = ( function_exists( 'is_account_page' ) && is_account_page() )
			|| ( function_exists( 'is_checkout' ) && is_checkout() );

		if ( $keep ) {
			return;
		}

		wp_dequeue_script( 'wc-password-strength-meter' );
		wp_dequeue_script( 'password-strength-meter' );
	}
}
