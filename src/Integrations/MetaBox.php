<?php
/**
 * Meta Box helpers for detection and parent gating.
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
 * Meta Box AIO still defines RWMB_Loader / rwmb_meta().
 */
final class MetaBox {

	/**
	 * Whether Meta Box or Meta Box AIO is loaded.
	 */
	public static function is_plugin_active(): bool {
		return class_exists( 'RWMB_Loader' ) || function_exists( 'rwmb_meta' );
	}

	/**
	 * Whether the Aegis Meta Box integration is on and the plugin is loaded.
	 */
	public static function is_enabled(): bool {
		return self::is_plugin_active()
			&& class_exists( Settings::class )
			&& Settings::is_integration_enabled( 'meta_box' );
	}
}
