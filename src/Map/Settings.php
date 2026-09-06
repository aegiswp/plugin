<?php
/**
 * Google Maps settings repository.
 *
 * @package Aegis\Plugin\Map
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Map;

use Aegis\Plugin\Integrations\Secrets;
use function array_merge;
use function get_option;
use function is_array;
use function preg_match;
use function sanitize_text_field;
use function update_option;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stores Google Maps API configuration in aegis_google_maps.
 */
final class Settings {

	/**
	 * Option key (unchanged for existing sites).
	 */
	public const OPTION_KEY = 'aegis_google_maps';

	/**
	 * Default settings.
	 *
	 * @var array<string, string>
	 */
	public const DEFAULTS = [
		'browser_api_key' => '',
		'server_api_key'  => '',
	];

	/** @var array<int, string> */
	private const SECRET_KEYS = [
		'browser_api_key',
		'server_api_key',
	];

	/**
	 * Get all settings merged with defaults.
	 *
	 * @return array<string, string>
	 */
	public static function get_settings(): array {
		$stored = get_option( self::OPTION_KEY, [] );
		$merged = array_merge(
			self::DEFAULTS,
			is_array( $stored ) ? $stored : []
		);

		return self::migrate_legacy_api_key( $merged );
	}

	/**
	 * Get the browser-restricted Google Maps API key for frontend use.
	 */
	public static function get_browser_api_key(): string {
		return (string) ( self::get_settings()['browser_api_key'] ?? '' );
	}

	/**
	 * Get the server-restricted Google Maps API key for geocoding proxy.
	 */
	public static function get_server_api_key(): string {
		return (string) ( self::get_settings()['server_api_key'] ?? '' );
	}

	/**
	 * @deprecated Use get_browser_api_key() instead.
	 */
	public static function get_api_key(): string {
		return self::get_browser_api_key();
	}

	/**
	 * Sanitize settings input.
	 *
	 * @param array<string, mixed> $input Raw input.
	 * @return array<string, string>
	 */
	public static function sanitize( array $input ): array {
		$existing  = self::get_settings();
		$merged    = Secrets::merge_on_save( $existing, $input, self::SECRET_KEYS );
		$sanitized = [];

		foreach ( self::DEFAULTS as $key => $default ) {
			$value = isset( $merged[ $key ] ) ? sanitize_text_field( (string) $merged[ $key ] ) : $default;

			if ( $value !== '' && ! preg_match( '/^[A-Za-z0-9_\-]+$/', $value ) ) {
				$value = $existing[ $key ] ?? $default;
			}

			$sanitized[ $key ] = $value;
		}

		return $sanitized;
	}

	/**
	 * Persist sanitized settings.
	 *
	 * @param array<string, mixed> $input Raw input.
	 */
	public static function save( array $input ): void {
		update_option( self::OPTION_KEY, self::sanitize( $input ), false );
	}

	/**
	 * Migrate legacy single api_key to browser_api_key.
	 *
	 * @param array<string, string> $settings Stored settings.
	 * @return array<string, string>
	 */
	private static function migrate_legacy_api_key( array $settings ): array {
		if ( ! empty( $settings['api_key'] ) && empty( $settings['browser_api_key'] ) ) {
			$settings['browser_api_key'] = $settings['api_key'];
		}

		return $settings;
	}
}
