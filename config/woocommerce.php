<?php
/**
 * WooCommerce integration bootstrap.
 *
 * @package Aegis\Plugin
 * @since   1.0.0
 */

declare( strict_types=1 );

use Aegis\Plugin\Integrations\WooCommerce\MultiStepCheckout;
use Aegis\Plugin\Settings\Repository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'init',
	static function (): void {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		Repository::boot_if_enabled(
			'woocommerce',
			static function (): void {
				( new MultiStepCheckout() )->init();
			}
		);
	}
);
