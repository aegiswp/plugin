<?php
/**
 * User-agent helpers for conditional logic device rules.
 *
 * @package Aegis\Plugin\Utilities
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Utilities;

use function preg_match;
use function str_contains;
use function strtolower;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Matches HTTP user-agent strings against device and browser rule keys.
 */
final class UserAgent {

	/**
	 * Whether the user agent matches a device rule.
	 *
	 * @param string $user_agent Raw user-agent header value.
	 * @param string $device     Device key: mobile, tablet, desktop.
	 */
	public static function matches_device( string $user_agent, string $device ): bool {
		$ua     = strtolower( $user_agent );
		$device = strtolower( $device );

		$is_mobile  = (bool) preg_match( '/mobile|android|iphone|ipod|blackberry|iemobile|opera mini/i', $ua );
		$is_tablet  = (bool) preg_match( '/ipad|tablet|playbook|silk|(android(?!.*mobile))/i', $ua );
		$is_desktop = ! $is_mobile && ! $is_tablet;

		return match ( $device ) {
			'mobile'  => $is_mobile && ! $is_tablet,
			'tablet'  => $is_tablet,
			'desktop' => $is_desktop,
			default   => false,
		};
	}

	/**
	 * Whether the user agent matches a browser or OS rule key.
	 *
	 * @param string $user_agent Raw user-agent header value.
	 * @param string $key        Rule key from the conditions UI.
	 */
	public static function matches_browser( string $user_agent, string $key ): bool {
		$key = strtolower( $key );

		if ( $key === '' ) {
			return false;
		}

		if ( in_array( $key, array( 'mobile', 'tablet', 'desktop' ), true ) ) {
			return self::matches_device( $user_agent, $key );
		}

		$ua = strtolower( $user_agent );

		return match ( $key ) {
			'ios'     => (bool) preg_match( '/iphone|ipod|ipad/i', $ua ),
			'android' => str_contains( $ua, 'android' ),
			'windows' => str_contains( $ua, 'windows' ),
			'macos'   => str_contains( $ua, 'mac os' ) || str_contains( $ua, 'macintosh' ),
			'linux'   => str_contains( $ua, 'linux' ) && ! str_contains( $ua, 'android' ),
			'chrome'  => str_contains( $ua, 'chrome' ) && ! str_contains( $ua, 'edg' ),
			'firefox' => str_contains( $ua, 'firefox' ),
			'safari'  => str_contains( $ua, 'safari' ) && ! str_contains( $ua, 'chrome' ),
			'edge'    => str_contains( $ua, 'edg' ),
			default   => false,
		};
	}
}
