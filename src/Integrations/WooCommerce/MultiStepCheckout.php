<?php
/**
 * Multi-Step Checkout
 *
 * Registers and loads multi-step checkout JS/CSS on WooCommerce checkout pages.
 *
 * @package Aegis\Plugin\Integrations\WooCommerce
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Integrations\WooCommerce;

use function add_action;
use function esc_html__;
use function is_admin;
use function is_checkout;
use function plugins_url;
use function wp_enqueue_script;
use function wp_enqueue_style;
use function wp_localize_script;
use function wp_register_script;
use function wp_register_style;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueues multi-step checkout assets on WooCommerce checkout pages.
 */
class MultiStepCheckout {

	/**
	 * Initialize hooks.
	 */
	public function init(): void {
		add_action( 'init', array( $this, 'register_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 11 );
	}

	/**
	 * Register checkout assets with WordPress.
	 */
	public function register_assets(): void {
		$base_url = plugins_url( 'assets/woocommerce/', \Aegis\Plugin\FILE );
		$version  = \Aegis\Plugin\VERSION;

		wp_register_style(
			'aegis-checkout-multi-step',
			$base_url . 'css/multi-step.css',
			array( 'wc-checkout', 'woocommerce-general' ),
			$version
		);

		wp_register_script(
			'aegis-checkout-multi-step',
			$base_url . 'js/multi-step.js',
			array( 'jquery', 'wc-checkout' ),
			$version,
			true
		);
	}

	/**
	 * Conditionally enqueue multi-step checkout assets.
	 */
	public function enqueue_assets(): void {
		if ( is_admin() ) {
			return;
		}

		if ( function_exists( 'is_checkout' ) && is_checkout() ) {
			wp_enqueue_style( 'aegis-checkout-multi-step' );
			wp_enqueue_script( 'aegis-checkout-multi-step' );

			wp_localize_script(
				'aegis-checkout-multi-step',
				'aegisCheckout',
				array(
					'continueToPayment' => esc_html__( 'Continue to Payment →', 'aegis' ),
					'reviewOrder'       => esc_html__( 'Review Order →', 'aegis' ),
				)
			);
		}
	}
}
