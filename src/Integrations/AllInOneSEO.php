<?php
/**
 * All in One SEO helpers for detection and parent gating.
 *
 * @package Aegis\Plugin\Integrations
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Integrations;

use function class_alias;
use function class_exists;
use function defined;
use function function_exists;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * All in One SEO defines class AIOSEO\Plugin\AIOSEO, function aioseo(), AIOSEO_VERSION, or AIOSEO_FILE.
 */
final class AllInOneSEO {

	/**
	 * Whether All in One SEO is loaded.
	 */
	public static function is_plugin_active(): bool {
		return defined( 'AIOSEO_VERSION' )
			|| class_exists( 'AIOSEO\\Plugin\\AIOSEO' )
			|| function_exists( 'aioseo' )
			|| defined( 'AIOSEO_FILE' );
	}

	/**
	 * Whether the Aegis All in One SEO integration is on and the plugin is loaded.
	 */
	public static function is_enabled(): bool {
		return self::is_plugin_active()
			&& class_exists( Settings::class )
			&& Settings::is_integration_enabled( 'aioseo' );
	}
}

if ( ! class_exists( __NAMESPACE__ . '\AIOSEO', false ) ) {
	class_alias( AllInOneSEO::class, __NAMESPACE__ . '\AIOSEO' );
}
