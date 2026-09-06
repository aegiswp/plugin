<?php
/**
 * Snippets settings repository.
 *
 * @package Aegis\Plugin\Snippets
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Snippets;

use Aegis\Plugin\Injection\LocationRegistry;
use function add_action;
use function add_query_arg;
use function admin_url;
use function array_diff;
use function array_keys;
use function array_map;
use function array_unique;
use function array_values;
use function bin2hex;
use function defined;
use function function_exists;
use function get_option;
use function hash_equals;
use function home_url;
use function in_array;
use function is_array;
use function random_bytes;
use function sanitize_key;
use function sanitize_text_field;
use function update_option;
use function wp_safe_redirect;
use function wp_unslash;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Snippets feature settings.
 */
final class Settings {

	public const OPTION = 'aegis_snippets_settings';

	/** @var array<string, mixed>|null */
	private static ?array $cache = null;

	/**
	 * @return array<string, mixed>
	 */
	public static function get_settings(): array {
		if ( null !== self::$cache ) {
			return self::$cache;
		}

		$stored = get_option( self::OPTION, array() );

		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		$disabled = $stored['disabled_locations'] ?? array();

		self::$cache = array(
			'php_enabled'              => ! empty( $stored['php_enabled'] ),
			'disabled_locations'       => is_array( $disabled ) ? array_values( array_unique( array_map( 'sanitize_key', $disabled ) ) ) : array(),
			'safe_mode'                => ! empty( $stored['safe_mode'] ),
			'auto_safe_mode_on_fatal'  => ! isset( $stored['auto_safe_mode_on_fatal'] ) || ! empty( $stored['auto_safe_mode_on_fatal'] ),
			'safe_mode_token'          => self::normalize_token( (string) ( $stored['safe_mode_token'] ?? '' ) ),
		);

		if ( self::$cache['safe_mode_token'] === '' ) {
			self::$cache['safe_mode_token'] = self::generate_token();
			$stored['safe_mode_token']      = self::$cache['safe_mode_token'];
			update_option( self::OPTION, $stored );
		}

		return self::$cache;
	}

	/**
	 * @return array<int, string>
	 */
	public static function get_disabled_locations(): array {
		$settings = self::get_settings();

		return is_array( $settings['disabled_locations'] ?? null ) ? $settings['disabled_locations'] : array();
	}

	public static function is_php_enabled(): bool {
		$settings = self::get_settings();

		return ! empty( $settings['php_enabled'] );
	}

	public static function is_location_enabled( string $hook ): bool {
		$hook = sanitize_key( $hook );

		if ( $hook === '' ) {
			return false;
		}

		return ! in_array( $hook, self::get_disabled_locations(), true );
	}

	public static function is_safe_mode(): bool {
		if ( self::is_forced_by_constant() ) {
			return true;
		}

		$settings = self::get_settings();

		return ! empty( $settings['safe_mode'] );
	}

	public static function is_forced_by_constant(): bool {
		return defined( 'AEGIS_DISABLE_SNIPPETS' ) && AEGIS_DISABLE_SNIPPETS;
	}

	public static function get_safe_mode_token(): string {
		$settings = self::get_settings();
		$token    = self::normalize_token( (string) ( $settings['safe_mode_token'] ?? '' ) );

		return $token !== '' ? $token : self::generate_token();
	}

	public static function get_safe_mode_url(): string {
		return add_query_arg( 'aegis_safe_mode', self::get_safe_mode_token(), home_url( '/' ) );
	}

	/**
	 * Activate safe mode from ?aegis_safe_mode=TOKEN before snippets register.
	 */
	public static function maybe_activate_from_request(): void {
		if ( ! isset( $_GET['aegis_safe_mode'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		$provided = sanitize_text_field( wp_unslash( (string) $_GET['aegis_safe_mode'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( $provided === '' ) {
			return;
		}

		$token = self::get_safe_mode_token();

		if ( $token !== '' && hash_equals( $token, $provided ) ) {
			self::enable_safe_mode();
			add_action( 'init', array( self::class, 'redirect_after_url_activation' ), 0 );
		}
	}

	/**
	 * Send the visitor to Snippets Settings after the Safe Mode URL fires.
	 */
	public static function redirect_after_url_activation(): void {
		wp_safe_redirect( admin_url( 'admin.php?page=aegis-snippets&tab=settings' ) );
		exit;
	}

	/**
	 * Enable site-wide safe mode (snippets + hook patterns).
	 */
	public static function enable_safe_mode(): void {
		$stored = get_option( self::OPTION, array() );

		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		$stored['safe_mode'] = true;
		update_option( self::OPTION, $stored );
		self::$cache = null;
	}

	/**
	 * Turn off option-based Safe Mode. Does nothing when AEGIS_DISABLE_SNIPPETS is defined.
	 */
	public static function disable_safe_mode(): void {
		if ( self::is_forced_by_constant() ) {
			return;
		}

		$stored = get_option( self::OPTION, array() );

		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		$stored['safe_mode'] = false;
		update_option( self::OPTION, $stored );
		self::$cache = null;
	}

	/**
	 * Replace the Safe Mode URL secret. The previous URL stops working.
	 */
	public static function regenerate_safe_mode_token(): string {
		$stored = get_option( self::OPTION, array() );

		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		$token                     = self::generate_token();
		$stored['safe_mode_token'] = $token;
		update_option( self::OPTION, $stored );
		self::$cache               = null;

		return $token;
	}

	/**
	 * @return array<int, string>
	 */
	public static function get_all_location_keys(): array {
		$keys = array();

		foreach ( LocationRegistry::get_grouped_for_select() as $hooks ) {
			foreach ( array_keys( $hooks ) as $hook ) {
				$keys[] = sanitize_key( (string) $hook );
			}
		}

		return array_values( array_unique( $keys ) );
	}

	/**
	 * @param array<string, mixed> $input Raw input.
	 */
	public static function save( array $input ): void {
		$all_hooks = self::get_all_location_keys();
		$enabled   = array();

		if ( isset( $input['enabled_locations'] ) && is_array( $input['enabled_locations'] ) ) {
			foreach ( $input['enabled_locations'] as $hook ) {
				$hook = sanitize_key( (string) $hook );

				if ( $hook !== '' && in_array( $hook, $all_hooks, true ) ) {
					$enabled[] = $hook;
				}
			}
		} elseif ( ! array_key_exists( 'enabled_locations', $input ) ) {
			// Settings-only save without location fields — keep current disabled list.
			$enabled = array_values( array_diff( $all_hooks, self::get_disabled_locations() ) );
		}

		$disabled = array_values( array_diff( $all_hooks, $enabled ) );

		update_option(
			self::OPTION,
			array(
				'php_enabled'             => ! empty( $input['php_enabled'] ),
				'disabled_locations'      => $disabled,
				'safe_mode'               => ! empty( $input['safe_mode'] ),
				'auto_safe_mode_on_fatal' => ! empty( $input['auto_safe_mode_on_fatal'] ),
				'safe_mode_token'         => self::get_safe_mode_token(),
			)
		);

		self::$cache = null;
	}

	private static function generate_token(): string {
		if ( function_exists( 'wp_generate_password' ) ) {
			return self::normalize_token( \wp_generate_password( 20, false, false ) );
		}

		return self::normalize_token( bin2hex( random_bytes( 10 ) ) );
	}

	private static function normalize_token( string $token ): string {
		return sanitize_key( $token );
	}
}
