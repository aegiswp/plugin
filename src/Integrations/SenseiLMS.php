<?php
/**
 * Sensei LMS helpers for detection and parent gating.
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
 * Sensei LMS defines class Sensei_Main, function Sensei(), class Sensei, SENSEI_VERSION, or SENSEI_PLUGIN_FILE.
 */
final class SenseiLMS {

	/**
	 * Whether Sensei LMS is loaded.
	 */
	public static function is_plugin_active(): bool {
		return class_exists( 'Sensei_Main' )
			|| function_exists( 'Sensei' )
			|| class_exists( 'Sensei' )
			|| defined( 'SENSEI_VERSION' )
			|| defined( 'SENSEI_PLUGIN_FILE' );
	}

	/**
	 * Whether the Aegis Sensei LMS integration is on and the plugin is loaded.
	 */
	public static function is_enabled(): bool {
		return self::is_plugin_active()
			&& class_exists( Settings::class )
			&& Settings::is_integration_enabled( 'sensei_lms' );
	}
}
