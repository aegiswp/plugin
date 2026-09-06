<?php
/**
 * Analytics settings repository.
 *
 * @package Aegis\Plugin\Analytics
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Analytics;

use function array_merge;
use function filter_var;
use function get_option;
use function is_array;
use function sanitize_text_field;
use function update_option;
use const FILTER_VALIDATE_URL;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stores site analytics configuration in aegis_analytics.
 */
final class Settings {

	/**
	 * Option key (unchanged for existing sites).
	 */
	public const OPTION = 'aegis_analytics';

	/**
	 * Default settings.
	 *
	 * @var array<string, mixed>
	 */
	public const DEFAULTS = [
		'ga4_enabled'           => false,
		'ga4_measurement_id'    => '',
		'ga4_anonymize_ip'      => false,
		'gtm_enabled'           => false,
		'gtm_container_id'      => '',
		'clarity_enabled'       => false,
		'clarity_project_id'  => '',
		'plausible_enabled'   => false,
		'plausible_domain'    => '',
		'plausible_script_url' => '',
		'fathom_enabled'      => false,
		'fathom_site_id'      => '',
		'gdpr_consent_required' => false,
		'gdpr_respect_dnt'      => false,
		'gdpr_complianz'        => false,
		'local_scripts'         => false,
		'matomo_enabled'        => false,
		'matomo_url'            => '',
		'matomo_site_id'        => '',
		'matomo_anonymize_ip'   => false,
		'meta_pixel_enabled'    => false,
		'meta_pixel_id'         => '',
		'meta_pixel_woo_events' => false,
		'ga4_consent_mode'      => false,
		'gtm_data_layer'        => false,
		'gdpr_debug_mode'       => false,
	];

	/**
	 * Get all settings merged with defaults.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_settings(): array {
		$stored = get_option( self::OPTION, [] );

		return array_merge(
			self::DEFAULTS,
			is_array( $stored ) ? $stored : []
		);
	}

	/**
	 * Sanitize settings input.
	 *
	 * @param array<string, mixed> $input Raw input.
	 * @return array<string, mixed>
	 */
	public static function sanitize( array $input ): array {
		$defaults  = self::DEFAULTS;
		$sanitized = [];

		$booleans = [
			'ga4_enabled',
			'ga4_anonymize_ip',
			'gtm_enabled',
			'clarity_enabled',
			'plausible_enabled',
			'fathom_enabled',
			'gdpr_consent_required',
			'gdpr_respect_dnt',
			'gdpr_complianz',
			'local_scripts',
			'matomo_enabled',
			'matomo_anonymize_ip',
			'meta_pixel_enabled',
			'meta_pixel_woo_events',
			'ga4_consent_mode',
			'gtm_data_layer',
			'gdpr_debug_mode',
		];

		foreach ( $booleans as $key ) {
			$sanitized[ $key ] = ! empty( $input[ $key ] );
		}

		$text_fields = [
			'ga4_measurement_id',
			'gtm_container_id',
			'clarity_project_id',
			'plausible_domain',
			'plausible_script_url',
			'fathom_site_id',
			'matomo_url',
			'matomo_site_id',
			'meta_pixel_id',
		];

		foreach ( $text_fields as $key ) {
			$sanitized[ $key ] = isset( $input[ $key ] )
				? sanitize_text_field( (string) $input[ $key ] )
				: ( $defaults[ $key ] ?? '' );
		}

		if ( ! empty( $sanitized['matomo_url'] ) && ! filter_var( $sanitized['matomo_url'], FILTER_VALIDATE_URL ) ) {
			$sanitized['matomo_url'] = '';
		}

		if ( ! empty( $sanitized['plausible_script_url'] ) && ! filter_var( $sanitized['plausible_script_url'], FILTER_VALIDATE_URL ) ) {
			$sanitized['plausible_script_url'] = '';
		}

		return $sanitized;
	}

	/**
	 * Persist sanitized settings and handle script proxy cron.
	 *
	 * @param array<string, mixed> $input Raw input.
	 */
	public static function save( array $input ): void {
		$sanitized = self::sanitize( $input );

		update_option( self::OPTION, $sanitized, false );

		if ( class_exists( '\Aegis\Plugin\Settings\Repository' ) ) {
			\Aegis\Plugin\Settings\Repository::flush_cache();
		}

		$proxy = new ScriptProxy();

		if ( ! empty( $sanitized['local_scripts'] ) ) {
			$proxy->schedule();
			$proxy->refresh_scripts();
		} else {
			$proxy->unschedule();
		}
	}
}
