<?php
/**
 * WooCommerce integration bootstrap.
 *
 * @package Aegis\Plugin
 * @since   1.0.0
 */

declare( strict_types=1 );

use Aegis\Plugin\Integrations\WooCommerce as WooCommerceHelper;
use Aegis\Plugin\Integrations\WooCommerce\MultiStepCheckout;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'init',
	static function (): void {
		if ( ! WooCommerceHelper::is_enabled() ) {
			return;
		}

		( new MultiStepCheckout() )->init();
	}
);
