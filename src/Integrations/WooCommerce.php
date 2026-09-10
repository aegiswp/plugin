<?php
/**
 * WooCommerce helpers for detection and parent gating.
 *
 * @package Aegis\Plugin\Integrations
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Integrations;

use function class_exists;
use function defined;
use function function_exists;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce defines WooCommerce, WC(), and WC_VERSION.
 */
final class WooCommerce {

	/**
	 * Whether WooCommerce is loaded.
	 */
	public static function is_plugin_active(): bool {
		return class_exists( 'WooCommerce' )
			|| function_exists( 'WC' )
			|| defined( 'WC_VERSION' );
	}

	/**
	 * Whether the Aegis WooCommerce integration is on and the plugin is loaded.
	 */
	public static function is_enabled(): bool {
		return self::is_plugin_active()
			&& class_exists( Settings::class )
			&& Settings::is_integration_enabled( 'woocommerce' );
	}
}
