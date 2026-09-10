<?php
/**
 * LifterLMS helpers for detection and parent gating.
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
 * LifterLMS defines class LifterLMS, LLMS(), LLMS_VERSION, or LLMS_PLUGIN_FILE.
 */
final class LifterLMS {

	/**
	 * Whether LifterLMS is loaded.
	 */
	public static function is_plugin_active(): bool {
		return class_exists( 'LifterLMS' )
			|| function_exists( 'LLMS' )
			|| defined( 'LLMS_VERSION' )
			|| defined( 'LLMS_PLUGIN_FILE' );
	}

	/**
	 * Whether the Aegis LifterLMS integration is on and the plugin is loaded.
	 */
	public static function is_enabled(): bool {
		return self::is_plugin_active()
			&& class_exists( Settings::class )
			&& Settings::is_integration_enabled( 'lifter_lms' );
	}
}
