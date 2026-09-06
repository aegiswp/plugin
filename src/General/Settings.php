<?php
/**
 * General (media) settings repository.
 *
 * @package Aegis\Plugin\General
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\General;

use function get_option;
use function is_array;
use function update_option;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stores general theme settings in aegis_settings.
 */
final class Settings {

	public const OPTION = 'aegis_settings';

	/**
	 * @var array<string, bool>
	 */
	public const DEFAULTS = array(
		'svg_upload'           => false,
		'svg_strip_colors'     => false,
		'svg_strip_dimensions' => false,
		'svg_strip_styles'     => false,
	);

	/** @var array<string, bool>|null */
	private static ?array $cache = null;

	/**
	 * @return array<string, bool>
	 */
	public static function get_settings(): array {
		if ( null !== self::$cache ) {
			return self::$cache;
		}

		$options = get_option( self::OPTION, array() );
		$merged  = array();

		foreach ( self::DEFAULTS as $key => $default ) {
			$merged[ $key ] = isset( $options[ $key ] ) ? (bool) $options[ $key ] : $default;
		}

		self::$cache = $merged;

		return self::$cache;
	}

	/**
	 * @param array<string, mixed> $input Raw input.
	 * @return array<string, bool>
	 */
	public static function sanitize( array $input ): array {
		$sanitized = array();

		foreach ( self::DEFAULTS as $key => $default ) {
			$sanitized[ $key ] = isset( $input[ $key ] ) ? (bool) $input[ $key ] : false;
		}

		return $sanitized;
	}

	public static function is_svg_upload_enabled(): bool {
		$settings = self::get_settings();

		return $settings['svg_upload'] ?? false;
	}

	/**
	 * @param array<string, bool> $settings Sanitized settings.
	 */
	public static function save( array $settings ): void {
		update_option( self::OPTION, $settings );
		self::flush_cache();
	}

	public static function flush_cache(): void {
		self::$cache = null;
	}
}
