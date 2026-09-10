<?php
/**
 * LearnDash helpers for detection and parent gating.
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
 * LearnDash defines LEARNDASH_VERSION, SFWD_LMS, or LEARNDASH_LMS_PLUGIN_DIR.
 */
final class LearnDash {

	/**
	 * Whether LearnDash is loaded.
	 */
	public static function is_plugin_active(): bool {
		return defined( 'LEARNDASH_VERSION' )
			|| class_exists( 'SFWD_LMS' )
			|| defined( 'LEARNDASH_LMS_PLUGIN_DIR' )
			|| function_exists( 'learndash_init' );
	}

	/**
	 * Whether the Aegis LearnDash integration is on and the plugin is loaded.
	 */
	public static function is_enabled(): bool {
		return self::is_plugin_active()
			&& class_exists( Settings::class )
			&& Settings::is_integration_enabled( 'learndash' );
	}
}
