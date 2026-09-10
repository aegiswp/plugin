<?php
/**
 * Multi-Step Checkout
 *
 * Registers and loads multi-step checkout JS/CSS on the multi-step checkout template.
 *
 * @package Aegis\Plugin\Integrations\WooCommerce
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Integrations\WooCommerce;

use function add_action;
use function esc_html__;
use function function_exists;
use function get_page_template_slug;
use function get_post;
use function is_admin;
use function is_checkout;
use function is_string;
use function plugins_url;
use function str_contains;
use function wp_enqueue_script;
use function wp_enqueue_style;
use function wp_localize_script;
use function wp_register_script;
use function wp_register_style;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueues multi-step checkout JS/CSS on the multi-step checkout template.
 */
class MultiStepCheckout {

	/**
	 * Initialize hooks.
	 */
	public function init(): void {
		$this->register_assets();
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
			array(),
			$version
		);

		wp_register_script(
			'aegis-checkout-multi-step',
			$base_url . 'js/multi-step.js',
			array(),
			$version,
			true
		);
	}

	/**
	 * Conditionally enqueue multi-step checkout assets.
	 */
	public function enqueue_assets(): void {
		if ( is_admin() || ! $this->is_multi_step_checkout() ) {
			return;
		}

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

	/**
	 * Whether the current request is the multi-step checkout template.
	 */
	private function is_multi_step_checkout(): bool {
		if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
			return false;
		}

		$slug = (string) get_page_template_slug();

		if ( str_contains( $slug, 'checkout-multi-step' ) ) {
			return true;
		}

		$template_id = $GLOBALS['_wp_current_template_id'] ?? '';

		if ( is_string( $template_id ) && str_contains( $template_id, 'checkout-multi-step' ) ) {
			return true;
		}

		$post = get_post();

		return $post instanceof \WP_Post
			&& str_contains( (string) $post->post_content, 'aegis-checkout-multi-step' );
	}
}
