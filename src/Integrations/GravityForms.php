<?php
/**
 * Gravity Forms helpers for detection and parent gating.
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
 * Gravity Forms defines GFForms, GFAPI, GF_MIN_WP_VERSION, or gravity_form().
 */
final class GravityForms {

	/**
	 * Whether Gravity Forms is loaded.
	 */
	public static function is_plugin_active(): bool {
		return class_exists( 'GFForms' )
			|| class_exists( 'GFAPI' )
			|| defined( 'GF_MIN_WP_VERSION' )
			|| function_exists( 'gravity_form' );
	}

	/**
	 * Whether the Aegis Gravity Forms integration is on and the plugin is loaded.
	 */
	public static function is_enabled(): bool {
		return self::is_plugin_active()
			&& class_exists( Settings::class )
			&& Settings::is_integration_enabled( 'gravity_forms' );
	}
}
