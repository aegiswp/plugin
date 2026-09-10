<?php
/**
 * Conditional logic settings repository.
 *
 * @package Aegis\Plugin\Conditionals
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Conditionals;

use Aegis\Plugin\Integrations\Registry;
use Aegis\Plugin\Integrations\Settings as IntegrationsSettings;
use function defined;
use function filter_var;
use function get_option;
use function is_array;
use function array_fill_keys;
use function array_key_exists;
use function is_bool;
use function is_numeric;
use function is_string;
use function timezone_identifiers_list;
use function trim;
use function update_option;
use const FILTER_VALIDATE_BOOLEAN;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stores conditional logic feature toggles in aegis_conditional_logic.
 */
final class Settings {

	/**
	 * Option key (unchanged for existing sites).
	 */
	public const OPTION = 'aegis_conditional_logic';

	/**
	 * Default settings — nested group => feature => enabled.
	 *
	 * @var array<string, array<string, bool>>
	 */
	public const DEFAULTS = [
		'visibility'      => [
			'screen_size'        => false,
			'custom_breakpoints' => false,
			'page_type'          => false,
			'browser_device'     => false,
			'lockdown'           => false,
			'query_string'       => false,
			'specific_users'     => false,
		],
		'accessibility'   => [
			'reduced_motion'      => false,
			'screen_reader_only'  => false,
			'color_scheme'        => false,
			'high_contrast'       => false,
			'forced_colors'       => false,
		],
		'user'            => [
			'user_status'     => false,
			'user_role'       => false,
			'user_capability' => false,
		],
		'schedule'        => [
			'date_time'  => false,
			'days_of_week' => false,
			'time_range'   => false,
			'timezone'     => false,
		],
		'pro_conditions'  => [
			'cookie'             => false,
			'referral'           => false,
			'acf_field'          => false,
			'metabox_field'      => false,
			'post_meta'          => false,
			'user_meta'          => false,
			'advanced_location'  => false,
		],
		'woocommerce'     => [
			'woo_cart'      => false,
			'woo_customer'  => false,
			'woo_product'   => false,
		],
		'learndash'       => [
			'ld_enrollment' => false,
			'ld_completion' => false,
			'ld_progress'   => false,
			'ld_quiz'       => false,
			'ld_group'      => false,
		],
		'lifterlms'       => [
			'llms_enrollment' => false,
			'llms_completion' => false,
			'llms_progress'   => false,
			'llms_membership' => false,
		],
		'sensei'          => [
			'sensei_enrollment' => false,
			'sensei_completion' => false,
			'sensei_progress'   => false,
			'sensei_quiz'       => false,
		],
		'fluentforms'     => [
			'ff_submitted' => false,
			'ff_count'     => false,
			'ff_field'     => false,
		],
		'fluentbooking'   => [
			'fb_has_booking' => false,
			'fb_upcoming'    => false,
			'fb_past'        => false,
			'fb_status'      => false,
		],
		'wp_fusion'       => [
			'tags'  => false,
			'lists' => false,
		],
		'edd'             => [
			'edd_cart'     => false,
			'edd_customer' => false,
			'edd_download' => false,
		],
		'affiliate_wp'    => [
			'affwp_referral'  => false,
			'affwp_affiliate' => false,
			'affwp_earnings'  => false,
		],
		'image_source'    => [
			'image_source_order' => false,
			'acf'                => false,
			'metabox'            => false,
		],
	];

	/**
	 * Conditional groups that require a third-party plugin (Integrations Registry key).
	 *
	 * @var array<string, string>
	 */
	public const PLUGIN_GROUPS = [
		'woocommerce'   => 'woocommerce',
		'learndash'     => 'learndash',
		'lifterlms'     => 'lifter_lms',
		'sensei'        => 'sensei_lms',
		'fluentforms'   => 'fluent_forms',
		'fluentbooking' => 'fluent_booking',
		'wp_fusion'     => 'wp_fusion',
		'edd'           => 'easy_digital_downloads',
		'affiliate_wp'  => 'affiliate_wp',
	];

	/**
	 * Per-key plugin requirements inside mixed groups.
	 *
	 * @var array<string, array<string, string>>
	 */
	public const PLUGIN_KEYS = [
		'pro_conditions' => [
			'acf_field'     => 'advanced_custom_fields',
			'metabox_field' => 'meta_box',
		],
		'image_source'   => [
			'acf'     => 'advanced_custom_fields',
			'metabox' => 'meta_box',
		],
	];

	/**
	 * Cached settings for the current request.
	 *
	 * @var array<string, array<string, bool>>|null
	 */
	private static ?array $cache = null;

	/**
	 * IANA timezone identifiers keyed for lookup.
	 *
	 * @var array<string, true>|null
	 */
	private static ?array $timezone_ids = null;

	/**
	 * Get settings merged with defaults.
	 *
	 * @return array<string, array<string, bool>>
	 */
	public static function get_settings(): array {
		if ( self::$cache !== null ) {
			return self::$cache;
		}

		$options = get_option( self::OPTION, [] );
		$merged  = [];

		foreach ( self::DEFAULTS as $group => $defaults ) {
			$merged[ $group ] = [];
			foreach ( $defaults as $key => $default ) {
				$merged[ $group ][ $key ] = isset( $options[ $group ][ $key ] )
					? (bool) $options[ $group ][ $key ]
					: $default;
			}
		}

		self::$cache = self::apply_availability( self::inherit_image_source_legacy( $merged, $options ) );

		return self::$cache;
	}

	/**
	 * Whether this conditional feature requires Aegis Pro.
	 */
	public static function is_pro_feature( string $group, string $key = '' ): bool {
		unset( $key );

		return $group === 'pro_conditions' || $group === 'image_source';
	}

	/**
	 * Whether Aegis Pro is active.
	 */
	public static function is_pro_active(): bool {
		return defined( 'AEGIS_PRO_VERSION' );
	}

	/**
	 * Registry key required to enable a conditional feature, or empty if none.
	 */
	public static function plugin_check_for( string $group, string $key = '' ): string {
		if ( $key !== '' && isset( self::PLUGIN_KEYS[ $group ][ $key ] ) ) {
			return self::PLUGIN_KEYS[ $group ][ $key ];
		}

		return self::PLUGIN_GROUPS[ $group ] ?? '';
	}

	/**
	 * Whether the third-party plugin for this feature is installed and active.
	 */
	public static function is_plugin_requirement_met( string $group, string $key = '' ): bool {
		$plugin_check = self::plugin_check_for( $group, $key );

		if ( $plugin_check === '' ) {
			return true;
		}

		return Registry::is_plugin_active( $plugin_check );
	}

	/**
	 * Settings payload for block editor scripts.
	 *
	 * @return array<string, array<string, bool>>
	 */
	public static function get_for_editor(): array {
		return self::get_settings();
	}

	/**
	 * IANA timezone options for the Schedule timezone extra.
	 *
	 * @return array<int, array{value: string, label: string}>
	 */
	public static function timezone_choices(): array {
		$choices = array();

		foreach ( timezone_identifiers_list() as $zone ) {
			$choices[] = array(
				'value' => $zone,
				'label' => $zone,
			);
		}

		return $choices;
	}

	/**
	 * Whether a timezone string is a PHP IANA identifier.
	 *
	 * Abbreviations (EST) and offsets (UTC+2) are not IANA and return false.
	 */
	public static function is_valid_timezone( string $zone ): bool {
		$zone = trim( $zone );
		if ( $zone === '' ) {
			return false;
		}

		if ( self::$timezone_ids === null ) {
			self::$timezone_ids = array_fill_keys( timezone_identifiers_list(), true );
		}

		return isset( self::$timezone_ids[ $zone ] );
	}

	/**
	 * Whether a nested extra is currently enabled.
	 */
	public static function is_enabled( string $group, string $key ): bool {
		$settings = self::get_settings();

		return ! empty( $settings[ $group ][ $key ] );
	}

	/**
	 * Clear the in-request settings cache.
	 */
	public static function flush_cache(): void {
		self::$cache = null;
	}

	/**
	 * Sanitize conditional logic settings input.
	 *
	 * Keys omitted from $input keep their stored values so Conditionals and
	 * Integrations can save overlapping groups without wiping each other.
	 *
	 * @param array<string, mixed> $input Raw input.
	 * @return array<string, array<string, bool>>
	 */
	public static function sanitize( array $input ): array {
		$stored = get_option( self::OPTION, [] );
		$stored = is_array( $stored ) ? $stored : [];
		$sanitized = [];

		foreach ( self::DEFAULTS as $group => $options ) {
			$sanitized[ $group ] = [];
			$posted_group        = isset( $input[ $group ] ) && is_array( $input[ $group ] ) ? $input[ $group ] : null;

			foreach ( $options as $key => $default ) {
				if ( ! self::is_feature_available( $group, $key ) ) {
					$sanitized[ $group ][ $key ] = false;
					continue;
				}

				if ( $posted_group !== null && array_key_exists( $key, $posted_group ) ) {
					$sanitized[ $group ][ $key ] = self::to_bool( $posted_group[ $key ] );
					continue;
				}

				$sanitized[ $group ][ $key ] = isset( $stored[ $group ] ) && array_key_exists( $key, $stored[ $group ] )
					? self::to_bool( $stored[ $group ][ $key ] )
					: $default;
			}
		}

		return $sanitized;
	}

	/**
	 * Sites that only stored the combined Image Source toggle inherit ACF/Meta Box from it.
	 *
	 * @param array<string, array<string, bool>> $merged  Merged settings.
	 * @param array<string, mixed>               $options Raw option value.
	 * @return array<string, array<string, bool>>
	 */
	private static function inherit_image_source_legacy( array $merged, array $options ): array {
		$stored = isset( $options['image_source'] ) && is_array( $options['image_source'] )
			? $options['image_source']
			: array();

		if ( $stored === array() || ! isset( $merged['image_source'] ) ) {
			return $merged;
		}

		$legacy = ! empty( $merged['image_source']['image_source_order'] );

		foreach ( array( 'acf', 'metabox' ) as $key ) {
			if ( ! array_key_exists( $key, $stored ) ) {
				$merged['image_source'][ $key ] = $legacy;
			}
		}

		return $merged;
	}

	/**
	 * Whether a feature can be enabled right now (plugin + Pro gates).
	 */
	private static function is_feature_available( string $group, string $key ): bool {
		if ( ! self::is_plugin_requirement_met( $group, $key ) ) {
			return false;
		}

		if ( self::is_pro_feature( $group, $key ) && ! self::is_pro_active() ) {
			return false;
		}

		$plugin_check = self::plugin_check_for( $group, $key );

		if ( $plugin_check !== '' && Registry::get( $plugin_check ) !== null && ! IntegrationsSettings::is_integration_enabled( $plugin_check ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Force gated features off when their requirements are not met.
	 *
	 * @param array<string, array<string, bool>> $settings Merged settings.
	 * @return array<string, array<string, bool>>
	 */
	private static function apply_availability( array $settings ): array {
		foreach ( $settings as $group => $options ) {
			foreach ( $options as $key => $enabled ) {
				if ( $enabled && ! self::is_feature_available( $group, $key ) ) {
					$settings[ $group ][ $key ] = false;
				}
			}
		}

		return $settings;
	}

	/**
	 * @param mixed $value Raw posted value.
	 */
	private static function to_bool( $value ): bool {
		if ( is_bool( $value ) ) {
			return $value;
		}

		if ( is_numeric( $value ) ) {
			return (int) $value === 1;
		}

		if ( is_string( $value ) ) {
			return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
		}

		return false;
	}
}
