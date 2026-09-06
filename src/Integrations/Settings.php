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
		'bunny_cdn_video_watermark'  => false,
		'co_authors_plus'            => false,
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
	 * @var array<string, string>
	 */
	public const BUNNYCDN_DEFAULTS = [
		'api_key'            => '',
		'cdn_pullzone'       => '',
		'cdn_hostname'       => '',
		'storage_zone'       => '',
		'storage_api_key'    => '',
		'storage_region'     => 'de',
		'stream_library_id'  => '',
		'stream_api_key'     => '',
		'webhook_secret'     => '',
	];

	/** @var array<int, string> */
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

		self::$integrations_cache = $merged;

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
		if ( 'co_authors_plus' === $integration && defined( 'AegisCompanion\\VERSION' ) ) {
			return false;
		}

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
			if ( array_key_exists( $key, $input ) ) {
				$sanitized[ $key ] = self::to_bool( $input[ $key ] );
			} elseif ( array_key_exists( $key, $stored ) ) {
				$sanitized[ $key ] = (bool) $stored[ $key ];
			} else {
				$sanitized[ $key ] = $default;
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

		foreach ( self::PATTERN_CONTROL_DEFAULTS as $key => $default ) {
			$sanitized[ $key ] = isset( $input[ $key ] ) ? (bool) $input[ $key ] : false;
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
				$sanitized[ $key ] = $default;
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
			return;
		}

		$legacy = get_option( 'aegis_pro_bunnycdn', [] );

		if ( is_array( $legacy ) && $legacy !== [] ) {
			$current = get_option( self::BUNNYCDN_OPTION, [] );
			$current = is_array( $current ) ? $current : [];

			$mapped = array_filter(
				[
					'api_key'           => $legacy['api_key'] ?? '',
					'stream_library_id' => $legacy['library_id'] ?? '',
					'cdn_pullzone'      => $legacy['pull_zone'] ?? '',
					'stream_api_key'    => $legacy['token_auth_key'] ?? '',
					'webhook_secret'    => $legacy['webhook_secret'] ?? '',
				],
				static fn( $value ): bool => is_string( $value ) && $value !== ''
			);

			update_option( self::BUNNYCDN_OPTION, wp_parse_args( $mapped, $current ), false );
			delete_option( 'aegis_pro_bunnycdn' );
			self::flush_cache();
		}

		update_option( 'aegis_bunnycdn_migrated_v1', true, false );
	}
}
