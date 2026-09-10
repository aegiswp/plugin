<?php
/**
 * Integrations settings repository.
 *
 * @package Aegis\Plugin\Integrations
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Integrations;

use function array_filter;
use function array_key_exists;
use function defined;
use function delete_option;
use function filter_var;
use function get_option;
use function in_array;
use function is_array;
use function is_bool;
use function preg_match;
use function sanitize_text_field;
use function update_option;
use function wp_parse_args;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stores integration toggles, BunnyCDN API settings, and pattern control.
 */
final class Settings {

	public const OPTION = 'aegis_integrations';

	public const BUNNYCDN_OPTION = 'aegis_bunnycdn';

	public const PATTERN_CONTROL_OPTION = 'aegis_pattern_control';

	/**
	 * Integration defaults — flat key => enabled.
	 *
	 * @var array<string, bool>
	 */
	public const INTEGRATION_DEFAULTS = [
		'advanced_custom_fields'     => false,
		'affiliate_wp'               => false,
		'aioseo'                     => false,
		'bbpress'                    => false,
		'bunny_cdn'                  => false,
		'bunny_cdn_stream_library'   => false,
		'bunny_cdn_direct_upload'    => false,
		'bunny_cdn_hls_streaming'    => false,
		'bunny_cdn_ai_transcription' => false,
		'bunny_cdn_video_thumbnails' => false,
		'co_authors_plus'            => false,
		'cap_author_schema'          => false,
		'cap_social_links'           => false,
		'cap_role_badges'            => false,
		'code_block_pro'             => false,
		'easy_digital_downloads'     => false,
		'fluent_booking'             => false,
		'fluent_forms'               => false,
		'fluent_crm'                 => false,
		'google_maps'                => false,
		'gravity_forms'              => false,
		'learndash'                  => false,
		'lifter_lms'                 => false,
		'meta_box'                   => false,
		'ninja_forms'                => false,
		'rank_math'                  => false,
		'rank_math_video_sitemap'    => false,
		'rank_math_faq_schema'       => false,
		'rank_math_event_schema'     => false,
		'rank_math_local_schema'     => false,
		'rank_math_video_schema'     => false,
		'seo_faq_schema'             => false,
		'seo_event_schema'           => false,
		'seo_local_schema'           => false,
		'seo_video_schema'           => false,
		'seo_video_sitemap'          => false,
		'seopress'                   => false,
		'sensei_lms'                 => false,
		'syntax_highlighting'        => false,
		'woocommerce'                => false,
		'wp_fusion'                  => false,
		'yoast_seo'                  => false,
	];

	/**
	 * BunnyCDN API settings defaults.
	 *
	 * Storage zone/key/region keys are retained for backward compatibility
	 * (legacy UI / `aegis_pro_bunnycdn` migration) but have no Connectors UI
	 * and are unused by Pro Stream video.
	 *
	 * @var array<string, string>
	 */
	public const BUNNYCDN_DEFAULTS = [
		'api_key'             => '',
		'cdn_pullzone'        => '',
		'cdn_hostname'        => '',
		'cdn_token_auth_key'  => '',
		'storage_zone'        => '',
		'storage_api_key'     => '',
		'storage_region'      => 'de',
		'stream_library_id'   => '',
		'stream_api_key'      => '',
		'webhook_secret'      => '',
	];

	/** @var array<int, string> Legacy storage regions kept for sanitize BC. */
	private const BUNNYCDN_STORAGE_REGIONS = [
		'de',
		'ny',
		'la',
		'sg',
		'syd',
		'uk',
		'se',
		'br',
		'jh',
	];

	/** @var array<int, string> */
	private const BUNNYCDN_SECRET_KEYS = [
		'api_key',
		'stream_api_key',
		'cdn_token_auth_key',
		'storage_api_key',
		'webhook_secret',
	];

	/**
	 * Pattern control defaults.
	 *
	 * @var array<string, bool>
	 */
	public const PATTERN_CONTROL_DEFAULTS = [
		'woocommerce_keep_patterns'   => false,
		'woocommerce_keep_templates'  => false,
		'learndash_keep_patterns'     => false,
		'lifterlms_keep_patterns'     => false,
		'sensei_keep_patterns'        => false,
		'fluentforms_keep_patterns'   => false,
		'fluentbooking_keep_patterns' => false,
		'coauthors_keep_patterns'     => false,
	];

	private static ?array $integrations_cache = null;

	private static ?array $bunnycdn_cache = null;

	/**
	 * Get integration settings merged with defaults.
	 *
	 * @return array<string, bool>
	 */
	public static function get_settings(): array {
		if ( self::$integrations_cache !== null ) {
			return self::$integrations_cache;
		}

		$stored  = get_option( self::OPTION, [] );
		$options = self::migrate_seo_settings( is_array( $stored ) ? $stored : [] );
		$merged  = [];

		foreach ( self::INTEGRATION_DEFAULTS as $key => $default ) {
			$merged[ $key ] = isset( $options[ $key ] ) ? (bool) $options[ $key ] : $default;
		}

		self::$integrations_cache = self::apply_extra_parents( self::apply_plugin_availability( $merged ) );

		return self::$integrations_cache;
	}

	/**
	 * Get BunnyCDN settings merged with defaults.
	 *
	 * @return array<string, string>
	 */
	public static function get_bunnycdn_settings(): array {
		if ( self::$bunnycdn_cache !== null ) {
			return self::$bunnycdn_cache;
		}

		$options = get_option( self::BUNNYCDN_OPTION, [] );

		self::$bunnycdn_cache = wp_parse_args(
			is_array( $options ) ? $options : [],
			self::BUNNYCDN_DEFAULTS
		);

		return self::$bunnycdn_cache;
	}

	/**
	 * Check if a specific integration is enabled.
	 *
	 * @param string $integration Integration key.
	 */
	public static function is_integration_enabled( string $integration ): bool {
		$settings = self::get_settings();

		return $settings[ $integration ] ?? ( self::INTEGRATION_DEFAULTS[ $integration ] ?? false );
	}

	/**
	 * Migrate legacy SEO option keys to plugin-agnostic seo_* keys.
	 *
	 * Rank Math keys are preserved for backward compatibility.
	 *
	 * @param array<string, mixed> $options Stored option values.
	 * @return array<string, mixed>
	 */
	public static function migrate_seo_settings( array $options ): array {
		$schema_map = [
			'seo_faq_schema'    => 'rank_math_faq_schema',
			'seo_event_schema'  => 'rank_math_event_schema',
			'seo_local_schema'  => 'rank_math_local_schema',
			'seo_video_schema'  => 'rank_math_video_schema',
			'seo_video_sitemap' => 'rank_math_video_sitemap',
		];

		foreach ( $schema_map as $new_key => $legacy_key ) {
			if ( ! array_key_exists( $new_key, $options ) && array_key_exists( $legacy_key, $options ) ) {
				$options[ $new_key ] = $options[ $legacy_key ];
			}
		}

		$plugin_map = [
			'yoast_seo' => 'yoast',
			'rank_math' => 'rank',
		];

		foreach ( $plugin_map as $new_key => $legacy_key ) {
			if ( ! array_key_exists( $new_key, $options ) && array_key_exists( $legacy_key, $options ) ) {
				$options[ $new_key ] = $options[ $legacy_key ];
			}
		}

		return $options;
	}

	/**
	 * Clear in-request caches.
	 */
	public static function flush_cache(): void {
		self::$integrations_cache = null;
		self::$bunnycdn_cache     = null;
	}

	/**
	 * Sanitize integration toggles.
	 *
	 * Keys omitted from $input keep their stored values so Integrations and
	 * Connectors can save overlapping option keys without wiping each other.
	 *
	 * @param array<string, mixed> $input Raw input.
	 * @return array<string, bool>
	 */
	public static function sanitize( array $input ): array {
		$stored = get_option( self::OPTION, [] );
		$stored = self::migrate_seo_settings( is_array( $stored ) ? $stored : [] );
		$sanitized = [];

		foreach ( self::INTEGRATION_DEFAULTS as $key => $default ) {
			if ( self::requires_active_plugin( $key ) && ! Registry::is_plugin_active( $key ) ) {
				$sanitized[ $key ] = false;
				continue;
			}

			if ( array_key_exists( $key, $input ) ) {
				$sanitized[ $key ] = self::to_bool( $input[ $key ] );
			} elseif ( array_key_exists( $key, $stored ) ) {
				$sanitized[ $key ] = (bool) $stored[ $key ];
			} else {
				$sanitized[ $key ] = $default;
			}
		}

		foreach ( self::extra_parent_map() as $extra => $parent ) {
			if ( empty( $sanitized[ $parent ] ) ) {
				$sanitized[ $extra ] = false;
			}
		}

		return $sanitized;
	}

	/**
	 * Coerce checkbox / AJAX values to bool ('0' must be false).
	 *
	 * @param mixed $value Raw value.
	 */
	private static function to_bool( mixed $value ): bool {
		if ( is_bool( $value ) ) {
			return $value;
		}

		return filter_var( $value, \FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Sanitize pattern control settings.
	 *
	 * @param array<string, mixed> $input Raw input.
	 * @return array<string, bool>
	 */
	public static function sanitize_pattern_control( array $input ): array {
		$sanitized = [];
		$has_pro   = defined( 'AEGIS_PRO_VERSION' );

		foreach ( self::PATTERN_CONTROL_DEFAULTS as $key => $default ) {
			unset( $default );

			if ( ! $has_pro || ! self::pattern_control_plugin_active( $key ) ) {
				$sanitized[ $key ] = false;
				continue;
			}

			$sanitized[ $key ] = isset( $input[ $key ] ) ? self::to_bool( $input[ $key ] ) : false;
		}

		return $sanitized;
	}

	/**
	 * Sanitize BunnyCDN API settings.
	 *
	 * @param array<string, mixed> $input Raw input.
	 * @return array<string, string>
	 */
	public static function sanitize_bunnycdn( array $input ): array {
		$existing = self::get_bunnycdn_settings();
		$merged   = Secrets::merge_on_save( $existing, $input, self::BUNNYCDN_SECRET_KEYS );
		$sanitized = [];

		foreach ( self::BUNNYCDN_DEFAULTS as $key => $default ) {
			if ( ! isset( $merged[ $key ] ) ) {
				// Partial AJAX payloads omit unchanged masked secrets — keep stored values.
				$sanitized[ $key ] = in_array( $key, self::BUNNYCDN_SECRET_KEYS, true )
					? ( $existing[ $key ] ?? $default )
					: $default;
				continue;
			}

			$value = sanitize_text_field( (string) $merged[ $key ] );

			if ( 'storage_region' === $key ) {
				$sanitized[ $key ] = in_array( $value, self::BUNNYCDN_STORAGE_REGIONS, true ) ? $value : 'de';
				continue;
			}

			if ( in_array( $key, [ 'cdn_pullzone', 'cdn_hostname', 'storage_zone' ], true ) && $value !== '' ) {
				$sanitized[ $key ] = preg_match( '/^[a-zA-Z0-9.-]+$/', $value ) ? $value : ( $existing[ $key ] ?? $default );
				continue;
			}

			if ( in_array( $key, self::BUNNYCDN_SECRET_KEYS, true ) && $value !== '' ) {
				if ( Secrets::is_masked( $value ) || ! preg_match( '/^[A-Za-z0-9_\-]+$/', $value ) ) {
					$sanitized[ $key ] = $existing[ $key ] ?? $default;
					continue;
				}
			}

			$sanitized[ $key ] = $value;
		}

		return $sanitized;
	}

	/**
	 * One-time migration from legacy Pro BunnyCDN option.
	 */
	public static function migrate_legacy_bunnycdn_option(): void {
		if ( get_option( 'aegis_bunnycdn_migrated_v1' ) ) {
			self::migrate_bunnycdn_token_key_v2();
			return;
		}

		$legacy = get_option( 'aegis_pro_bunnycdn', [] );

		if ( is_array( $legacy ) && $legacy !== [] ) {
			$current = get_option( self::BUNNYCDN_OPTION, [] );
			$current = is_array( $current ) ? $current : [];

			$mapped = array_filter(
				[
					'api_key'            => $legacy['api_key'] ?? '',
					'stream_library_id'  => $legacy['library_id'] ?? '',
					'cdn_pullzone'       => $legacy['pull_zone'] ?? '',
					// Legacy Pro stored CDN URL token auth as token_auth_key — not the Stream library AccessKey.
					'cdn_token_auth_key' => $legacy['token_auth_key'] ?? '',
					'webhook_secret'     => $legacy['webhook_secret'] ?? '',
				],
				static fn( $value ): bool => is_string( $value ) && $value !== ''
			);

			update_option( self::BUNNYCDN_OPTION, wp_parse_args( $mapped, $current ), false );
			delete_option( 'aegis_pro_bunnycdn' );
			self::flush_cache();
		}

		update_option( 'aegis_bunnycdn_migrated_v1', true, false );
		self::migrate_bunnycdn_token_key_v2();
	}

	/**
	 * Backfill CDN token auth for installs that ran the incorrect v1 map
	 * (legacy token_auth_key → stream_api_key). Copies into cdn_token_auth_key
	 * when that field is empty; does not clear stream_api_key (may already be a
	 * real library AccessKey on newer installs).
	 */
	public static function migrate_bunnycdn_token_key_v2(): void {
		if ( get_option( 'aegis_bunnycdn_token_key_v2' ) ) {
			return;
		}

		$settings = get_option( self::BUNNYCDN_OPTION, [] );
		$settings = is_array( $settings ) ? $settings : [];

		$stream = (string) ( $settings['stream_api_key'] ?? '' );
		$token  = (string) ( $settings['cdn_token_auth_key'] ?? '' );

		if ( $token === '' && $stream !== '' ) {
			$settings['cdn_token_auth_key'] = $stream;
			update_option( self::BUNNYCDN_OPTION, $settings, false );
			self::flush_cache();
		}

		update_option( 'aegis_bunnycdn_token_key_v2', true, false );
	}

	/**
	 * Force third-party integrations off when that plugin is not active.
	 *
	 * Connector keys (BunnyCDN, Google Maps) are not gated this way.
	 *
	 * @param array<string, bool> $settings Merged settings.
	 * @return array<string, bool>
	 */
	private static function apply_plugin_availability( array $settings ): array {
		foreach ( $settings as $key => $enabled ) {
			if ( $enabled && self::requires_active_plugin( $key ) && ! Registry::is_plugin_active( $key ) ) {
				$settings[ $key ] = false;
			}
		}

		return $settings;
	}

	/**
	 * Integration extras that require their parent toggle to stay on.
	 *
	 * @return array<string, string> Extra key => parent integration key.
	 */
	private static function extra_parent_map(): array {
		return [
			'cap_author_schema'            => 'co_authors_plus',
			'cap_social_links'             => 'co_authors_plus',
			'cap_role_badges'              => 'co_authors_plus',
			'bunny_cdn_stream_library'     => 'bunny_cdn',
			'bunny_cdn_direct_upload'      => 'bunny_cdn',
			'bunny_cdn_hls_streaming'      => 'bunny_cdn',
			'bunny_cdn_ai_transcription'   => 'bunny_cdn',
			'bunny_cdn_video_thumbnails'   => 'bunny_cdn',
		];
	}

	/**
	 * Force extras off when the parent integration is off.
	 *
	 * @param array<string, bool> $settings Merged settings.
	 * @return array<string, bool>
	 */
	private static function apply_extra_parents( array $settings ): array {
		foreach ( self::extra_parent_map() as $extra => $parent ) {
			if ( empty( $settings[ $parent ] ) ) {
				$settings[ $extra ] = false;
			}
		}

		return $settings;
	}

	/**
	 * Whether this option key is a third-party plugin integration that must be installed.
	 */
	private static function requires_active_plugin( string $key ): bool {
		if ( $key === 'bunny_cdn' || $key === 'google_maps' ) {
			return false;
		}

		return Registry::get( $key ) !== null;
	}

	/**
	 * Whether the third-party plugin for a pattern-control extra is active.
	 */
	private static function pattern_control_plugin_active( string $key ): bool {
		$map = array(
			'woocommerce_keep_patterns'   => 'woocommerce',
			'woocommerce_keep_templates'  => 'woocommerce',
			'learndash_keep_patterns'     => 'learndash',
			'lifterlms_keep_patterns'     => 'lifter_lms',
			'sensei_keep_patterns'        => 'sensei_lms',
			'fluentforms_keep_patterns'   => 'fluent_forms',
			'fluentbooking_keep_patterns' => 'fluent_booking',
			'coauthors_keep_patterns'     => 'co_authors_plus',
		);

		$plugin = $map[ $key ] ?? '';

		return $plugin !== '' && Registry::is_plugin_active( $plugin ) && self::is_integration_enabled( $plugin );
	}

	/**
	 * Persist inactive third-party integrations as off so leftover stored ons do not return when the plugin is installed later.
	 */
	public static function persist_inactive_plugin_toggles(): void {
		if ( get_option( 'aegis_inactive_integrations_cleared_v1' ) ) {
			return;
		}

		$stored = get_option( self::OPTION, [] );

		if ( is_array( $stored ) && $stored !== [] ) {
			update_option( self::OPTION, self::sanitize( $stored ) );
			self::flush_cache();
		}

		$patterns = get_option( self::PATTERN_CONTROL_OPTION, [] );

		if ( is_array( $patterns ) && $patterns !== [] ) {
			update_option( self::PATTERN_CONTROL_OPTION, self::sanitize_pattern_control( $patterns ) );
		}

		update_option( 'aegis_inactive_integrations_cleared_v1', true, false );
	}

	/**
	 * Integration keys owned by Aegis → Connectors (not the Integrations dashboard).
	 *
	 * @return array<int, string>
	 */
	public static function connector_toggle_keys(): array {
		return [
			'bunny_cdn',
			'bunny_cdn_stream_library',
			'bunny_cdn_direct_upload',
			'bunny_cdn_hls_streaming',
			'bunny_cdn_ai_transcription',
			'bunny_cdn_video_thumbnails',
			'google_maps',
		];
	}

	/**
	 * Reset Connectors toggles and BunnyCDN credentials without touching other integrations.
	 */
	public static function reset_connectors(): void {
		$stored = get_option( self::OPTION, [] );
		$stored = is_array( $stored ) ? $stored : [];

		foreach ( self::connector_toggle_keys() as $key ) {
			$stored[ $key ] = false;
		}

		update_option( self::OPTION, self::sanitize( $stored ), false );
		delete_option( self::BUNNYCDN_OPTION );
		self::flush_cache();
	}
}
