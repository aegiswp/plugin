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
use function is_order_received_page;
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
 * Optional site-wide and frontend performance optimizations from block settings.
 *
 * Most cuts apply on the public frontend only. Disable XML-RPC is site-wide.
 */
final class FrontendOptimizations {

	public function init(): void {
		add_action( 'init', [ $this, 'bootstrap' ], 20 );
	}

	/**
	 * Register Performance hooks.
	 *
	 * @return void
	 */
	public function bootstrap(): void {
		if ( Settings::is_enabled( 'perf_disable_xmlrpc' ) ) {
			$this->disable_xmlrpc();
		}

		if (
			is_admin()
			|| wp_doing_cron()
			|| ( defined( 'REST_REQUEST' ) && REST_REQUEST )
			|| ( function_exists( 'wp_is_serving_rest_request' ) && wp_is_serving_rest_request() )
		) {
			return;
		}

		if ( Settings::is_enabled( 'perf_disable_wp_embed' ) ) {
			$this->disable_wp_embed();
		}

		if ( Settings::is_enabled( 'perf_disable_dashicons' ) && ! is_user_logged_in() ) {
			add_action( 'wp_enqueue_scripts', [ $this, 'deregister_dashicons' ], 100 );
		}

		if ( Settings::is_enabled( 'perf_disable_frontend_heartbeat' ) && ! is_user_logged_in() ) {
			$this->disable_heartbeat();
		}

		if ( Settings::is_enabled( 'perf_remove_rsd_link' ) ) {
			remove_action( 'wp_head', 'rsd_link' );
		}

		if ( Settings::is_enabled( 'perf_remove_shortlink' ) ) {
			remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
			remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
		}

		if ( Settings::is_enabled( 'perf_remove_generator' ) ) {
			remove_action( 'wp_head', 'wp_generator' );
			add_filter( 'the_generator', '__return_empty_string' );
		}

		if ( Settings::is_enabled( 'perf_remove_rest_api_links' ) ) {
			remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
			remove_action( 'template_redirect', 'rest_output_link_header', 11 );
			remove_action( 'xmlrpc_rsd_apis', 'rest_output_rsd' );
		}

		if ( Settings::is_enabled( 'perf_remove_adjacent_posts' ) ) {
			remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );
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
	 * Disable XML-RPC and strip the X-Pingback response header.
	 *
	 * @return void
	 */
	private function disable_xmlrpc(): void {
		add_filter( 'xmlrpc_enabled', '__return_false' );

		add_filter(
			'wp_headers',
			static function ( array $headers ): array {
				unset( $headers['X-Pingback'] );

				return $headers;
			}
		);
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
	 * Drop cart-fragments AJAX outside WooCommerce shopping surfaces.
	 *
	 * Keeps the script on shop, product, cart, checkout, and account pages
	 * (`is_woo_request()`). Classic mini-carts that refresh on every page
	 * should leave this toggle off.
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
			// Also covered by perf_woo_disable_cart_fragments; kept so assets-only mode still strips it.
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
	 * Load password strength meter only on account and checkout form pages.
	 *
	 * Excludes the order-received thank-you screen even though WooCommerce
	 * may report it as a checkout context.
	 *
	 * @return void
	 */
	public function dequeue_woo_password_strength(): void {
		$on_account  = function_exists( 'is_account_page' ) && is_account_page();
		$on_checkout = function_exists( 'is_checkout' ) && is_checkout();
		$on_thankyou = function_exists( 'is_order_received_page' ) && is_order_received_page();

		if ( $on_account || ( $on_checkout && ! $on_thankyou ) ) {
			return;
		}

		wp_dequeue_script( 'wc-password-strength-meter' );
		wp_dequeue_script( 'password-strength-meter' );
	}
}
