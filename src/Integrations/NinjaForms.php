<?php
/**
 * Ninja Forms helpers for detection and parent gating.
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
 * Ninja Forms defines Ninja_Forms, NF_PLUGIN_VERSION, or NF_VERSION.
 */
final class NinjaForms {

	/**
	 * Whether Ninja Forms is loaded.
	 */
	public static function is_plugin_active(): bool {
		return class_exists( 'Ninja_Forms' )
			|| function_exists( 'Ninja_Forms' )
			|| defined( 'NF_PLUGIN_VERSION' )
			|| defined( 'NF_VERSION' );
	}

	/**
	 * Whether the Aegis Ninja Forms integration is on and the plugin is loaded.
	 */
	public static function is_enabled(): bool {
		return self::is_plugin_active()
			&& class_exists( Settings::class )
			&& Settings::is_integration_enabled( 'ninja_forms' );
	}
}
