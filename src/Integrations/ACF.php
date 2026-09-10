<?php
/**
 * Advanced Custom Fields helpers for detection and parent gating.
 *
 * @package Aegis\Plugin\Integrations
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Integrations;

use function class_exists;
use function function_exists;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP Fusion-style helper: plugin loaded vs Aegis integration on.
 *
 * Secure Custom Fields (WordPress.org) still defines the ACF class.
 */
final class ACF {

	/**
	 * Whether ACF, ACF PRO, or Secure Custom Fields is loaded.
	 */
	public static function is_plugin_active(): bool {
		return class_exists( 'ACF' ) || function_exists( 'get_field' );
	}

	/**
	 * Whether the Aegis ACF integration is on and the plugin is loaded.
	 */
	public static function is_enabled(): bool {
		return self::is_plugin_active()
			&& class_exists( Settings::class )
			&& Settings::is_integration_enabled( 'advanced_custom_fields' );
	}
}
