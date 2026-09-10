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
use function array_intersect_key;
use function array_key_exists;
use function array_merge;
use function delete_option;
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
	 * One-shot migration flag for legacy `api_key` / Fieldify leftovers.
	 */
	public const MIGRATION_FLAG = 'aegis_google_maps_migrated_v1';

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

		return array_intersect_key(
			self::migrate_legacy_api_key( $merged ),
			self::DEFAULTS
		);
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
			// Partial AJAX payloads omit unchanged masked secrets — keep stored values.
			if ( ! array_key_exists( $key, $input ) && in_array( $key, self::SECRET_KEYS, true ) ) {
				$sanitized[ $key ] = $existing[ $key ] ?? $default;
				continue;
			}

			$value = isset( $merged[ $key ] ) ? sanitize_text_field( (string) $merged[ $key ] ) : $default;

			if ( $value !== '' && ! preg_match( '/^[A-Za-z0-9_\-]+$/', $value ) ) {
				$value = $existing[ $key ] ?? $default;
			}

			$sanitized[ $key ] = $value;
		}

		// Prevent accidental reuse of one Google key for both browser and server roles.
		if (
			$sanitized['browser_api_key'] !== ''
			&& $sanitized['browser_api_key'] === $sanitized['server_api_key']
		) {
			$sanitized['server_api_key'] = $existing['server_api_key'] ?? '';
			if ( $sanitized['server_api_key'] === $sanitized['browser_api_key'] ) {
				$sanitized['server_api_key'] = '';
			}
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
	 * Clear Connectors Google Maps API credentials.
	 */
	public static function reset(): void {
		delete_option( self::OPTION_KEY );
	}

	/**
	 * Persist legacy `api_key` and Fieldify `aegis[googleMaps]` into Connectors keys once.
	 */
	public static function migrate_legacy_options(): void {
		if ( get_option( self::MIGRATION_FLAG ) ) {
			return;
		}

		$stored = get_option( self::OPTION_KEY, [] );
		if ( ! is_array( $stored ) ) {
			$stored = [];
		}

		$changed = false;

		if ( ! empty( $stored['api_key'] ) && empty( $stored['browser_api_key'] ) ) {
			$stored['browser_api_key'] = sanitize_text_field( (string) $stored['api_key'] );
			$changed                  = true;
		}

		if ( array_key_exists( 'api_key', $stored ) ) {
			unset( $stored['api_key'] );
			$changed = true;
		}

		$aegis = get_option( 'aegis', [] );
		if ( is_array( $aegis ) && array_key_exists( 'googleMaps', $aegis ) ) {
			$legacy = sanitize_text_field( (string) $aegis['googleMaps'] );
			if ( $legacy !== '' && empty( $stored['browser_api_key'] ) ) {
				$stored['browser_api_key'] = $legacy;
				$changed                  = true;
			}
			unset( $aegis['googleMaps'] );
			update_option( 'aegis', $aegis, false );
		}

		if ( $changed ) {
			$clean = array_intersect_key(
				array_merge( self::DEFAULTS, $stored ),
				self::DEFAULTS
			);
			update_option( self::OPTION_KEY, $clean, false );
		}

		update_option( self::MIGRATION_FLAG, true, false );
	}

	/**
	 * Migrate legacy single api_key to browser_api_key (in-memory fallback).
	 *
	 * @param array<string, string> $settings Stored settings.
	 * @return array<string, string>
	 */
	private static function migrate_legacy_api_key( array $settings ): array {
		if ( ! empty( $settings['api_key'] ) && empty( $settings['browser_api_key'] ) ) {
			$settings['browser_api_key'] = $settings['api_key'];
		}

		unset( $settings['api_key'] );

		return $settings;
	}
}
