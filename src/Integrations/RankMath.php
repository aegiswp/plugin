<?php
/**
 * Rank Math helpers for detection and parent gating.
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
 * Rank Math defines class RankMath, function rank_math(), RANK_MATH_VERSION, RANK_MATH_FILE,
 * RANK_MATH_PRO_VERSION, or RANK_MATH_PRO_FILE.
 */
final class RankMath {

	/**
	 * Whether Rank Math is loaded.
	 */
	public static function is_plugin_active(): bool {
		return defined( 'RANK_MATH_VERSION' )
			|| class_exists( 'RankMath' )
			|| defined( 'RANK_MATH_FILE' )
			|| defined( 'RANK_MATH_PRO_VERSION' )
			|| defined( 'RANK_MATH_PRO_FILE' )
			|| function_exists( 'rank_math' );
	}

	/**
	 * Whether the Aegis Rank Math integration is on and the plugin is loaded.
	 */
	public static function is_enabled(): bool {
		return self::is_plugin_active()
			&& class_exists( Settings::class )
			&& Settings::is_integration_enabled( 'rank_math' );
	}
}

if ( ! class_exists( __NAMESPACE__ . '\Rank_Math', false ) ) {
	class_alias( RankMath::class, __NAMESPACE__ . '\Rank_Math' );
}
