<?php
/**
 * Analytics settings repository.
 *
 * @package Aegis\Plugin\Analytics
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Analytics;

use function array_key_exists;
use function array_map;
use function array_merge;
use function __;
use function explode;
use function filter_var;
use function get_option;
use function implode;
use function in_array;
use function is_array;
use function ltrim;
use function preg_match;
use function preg_replace;
use function rtrim;
use function sanitize_text_field;
use function str_starts_with;
use function strtolower;
use function strtoupper;
use function substr;
use function trim;
use function update_option;
use function wp_parse_url;
use const FILTER_VALIDATE_URL;
use const PHP_URL_HOST;
use const PHP_URL_SCHEME;

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
		'clarity_project_id'    => '',
		'plausible_enabled'     => false,
		'plausible_domain'      => '',
		'plausible_script_url'  => '',
		'fathom_enabled'        => false,
		'fathom_site_id'        => '',
		'fathom_script_url'     => '',
		'gdpr_consent_required' => false,
		'gdpr_respect_dnt'      => false,
		'gdpr_complianz'        => false,
		'local_scripts'         => false,
		'matomo_enabled'        => false,
		'matomo_url'            => '',
		'matomo_site_id'        => '',
		'matomo_privacy_mode'   => false,
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
		$stored = is_array( $stored ) ? $stored : [];

		// Legacy key from before Privacy Mode rename.
		if ( ! array_key_exists( 'matomo_privacy_mode', $stored )
			&& array_key_exists( 'matomo_anonymize_ip', $stored )
		) {
			$stored['matomo_privacy_mode'] = ! empty( $stored['matomo_anonymize_ip'] );
		}

		unset( $stored['matomo_anonymize_ip'] );

		return array_merge( self::DEFAULTS, $stored );
	}

	/**
	 * Normalize a Clarity Project ID (lowercase alphanumeric, 6–16 chars).
	 *
	 * @param string $id Raw or stored ID.
	 * @return string Normalized ID, or empty string when invalid.
	 */
	public static function normalize_clarity_project_id( string $id ): string {
		$id = strtolower( trim( $id ) );

		return preg_match( '/^[a-z0-9]{6,16}$/', $id ) ? $id : '';
	}

	/**
	 * Normalize a Plausible data-domain value (host or comma-separated hosts).
	 *
	 * Strips scheme, path, and leading www. Matches Plausible site domain rules.
	 *
	 * @param string $domain Raw or stored domain(s).
	 * @return string Normalized domain list, or empty string when invalid.
	 */
	public static function normalize_plausible_domain( string $domain ): string {
		$domain = trim( $domain );

		if ( $domain === '' ) {
			return '';
		}

		$parts      = array_map( 'trim', explode( ',', $domain ) );
		$normalized = [];

		foreach ( $parts as $part ) {
			if ( $part === '' ) {
				continue;
			}

			// Allow pasting full URLs per host (including comma-separated lists).
			$part = (string) preg_replace( '#^https?://#i', '', $part );
			$part = (string) preg_replace( '#[/?#].*$#', '', $part );
			$part = strtolower( rtrim( $part, '/' ) );

			if ( str_starts_with( $part, 'www.' ) ) {
				$part = substr( $part, 4 );
			}

			if ( $part === '' ) {
				continue;
			}

			// Hostname with at least one dot, or localhost (self-hosted / local).
			if ( ! preg_match(
				'/^(?:(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,}|localhost)$/',
				$part
			) ) {
				return '';
			}

			$normalized[] = $part;
		}

		return $normalized !== [] ? implode( ',', $normalized ) : '';
	}

	/**
	 * Normalize an optional Plausible script URL (http/https only).
	 *
	 * @param string $url Raw or stored URL.
	 * @return string Normalized URL, or empty string when invalid/empty.
	 */
	public static function normalize_plausible_script_url( string $url ): string {
		$url = trim( $url );

		if ( $url === '' ) {
			return '';
		}

		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return '';
		}

		$scheme = strtolower( (string) wp_parse_url( $url, PHP_URL_SCHEME ) );

		if ( ! in_array( $scheme, [ 'http', 'https' ], true ) ) {
			return '';
		}

		return $url;
	}

	/**
	 * Normalize a Fathom Site ID (uppercase alphanumeric, 5–10 chars).
	 *
	 * @param string $id Raw or stored Site ID.
	 * @return string Normalized ID, or empty string when invalid.
	 */
	public static function normalize_fathom_site_id( string $id ): string {
		$id = strtoupper( trim( $id ) );

		return preg_match( '/^[A-Z0-9]{5,10}$/', $id ) ? $id : '';
	}

	/**
	 * Normalize an optional Fathom script URL (http/https only).
	 *
	 * @param string $url Raw or stored URL.
	 * @return string Normalized URL, or empty string when invalid/empty.
	 */
	public static function normalize_fathom_script_url( string $url ): string {
		return self::normalize_plausible_script_url( $url );
	}

	/**
	 * Normalize a Matomo instance URL (http/https, no trailing slash).
	 *
	 * Accepts full URLs, protocol-relative (`//host/…`), or bare host/path
	 * (https is assumed).
	 *
	 * @param string $url Raw or stored URL.
	 * @return string Normalized URL, or empty string when invalid.
	 */
	public static function normalize_matomo_url( string $url ): string {
		$url = trim( $url );

		if ( $url === '' ) {
			return '';
		}

		if ( str_starts_with( $url, '//' ) ) {
			$url = 'https:' . $url;
		} elseif ( preg_match( '#^[a-z][a-z0-9+.-]*:#i', $url ) ) {
			// Explicit scheme: only http(s) is valid (do not rewrite ftp:// → https://ftp://…).
			if ( ! preg_match( '#^https?://#i', $url ) ) {
				return '';
			}
		} else {
			// Bare host/path — assume https.
			$url = 'https://' . ltrim( $url, '/' );
		}

		$url = self::normalize_plausible_script_url( $url );

		if ( $url === '' ) {
			return '';
		}

		$host = wp_parse_url( $url, PHP_URL_HOST );

		if ( ! is_string( $host ) || $host === '' ) {
			return '';
		}

		return rtrim( $url, '/' );
	}

	/**
	 * Normalize a Matomo site ID (positive integer as string).
	 *
	 * @param string $id Raw or stored site ID.
	 * @return string Normalized ID, or empty string when invalid.
	 */
	public static function normalize_matomo_site_id( string $id ): string {
		$id = trim( $id );

		return preg_match( '/^[1-9][0-9]{0,9}$/', $id ) ? $id : '';
	}

	/**
	 * Normalize a Meta Pixel ID (digits only, 5–20 chars).
	 *
	 * @param string $id Raw or stored Pixel ID.
	 * @return string Normalized ID, or empty string when invalid.
	 */
	public static function normalize_meta_pixel_id( string $id ): string {
		$id = trim( $id );

		return preg_match( '/^\d{5,20}$/', $id ) ? $id : '';
	}

	/**
	 * Sanitize settings input.
	 *
	 * Fields omitted from the request (e.g. Pro-only Meta Pixel inputs when
	 * Pro is inactive) keep their previously stored values.
	 *
	 * @param array<string, mixed> $input    Raw input.
	 * @param array<int, string>|null $warnings Optional list of user-facing warnings.
	 * @return array<string, mixed>
	 */
	public static function sanitize( array $input, ?array &$warnings = null ): array {
		$defaults  = self::DEFAULTS;
		$existing  = self::get_settings();
		$sanitized = [];
		$notes     = [];

		// Accept legacy field name from older exports / POSTs.
		if ( array_key_exists( 'matomo_anonymize_ip', $input ) && ! array_key_exists( 'matomo_privacy_mode', $input ) ) {
			$input['matomo_privacy_mode'] = $input['matomo_anonymize_ip'];
		}

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
			'matomo_privacy_mode',
			'meta_pixel_enabled',
			'meta_pixel_woo_events',
			'ga4_consent_mode',
			'gtm_data_layer',
			'gdpr_debug_mode',
		];

		foreach ( $booleans as $key ) {
			if ( array_key_exists( $key, $input ) ) {
				$sanitized[ $key ] = ! empty( $input[ $key ] );
			} else {
				$sanitized[ $key ] = ! empty( $existing[ $key ] );
			}
		}

		$text_fields = [
			'ga4_measurement_id',
			'gtm_container_id',
			'clarity_project_id',
			'plausible_domain',
			'plausible_script_url',
			'fathom_site_id',
			'fathom_script_url',
			'matomo_url',
			'matomo_site_id',
			'meta_pixel_id',
		];

		foreach ( $text_fields as $key ) {
			if ( array_key_exists( $key, $input ) ) {
				$sanitized[ $key ] = sanitize_text_field( (string) $input[ $key ] );
			} else {
				$sanitized[ $key ] = (string) ( $existing[ $key ] ?? $defaults[ $key ] ?? '' );
			}
		}

		if ( array_key_exists( 'ga4_measurement_id', $input )
			&& $sanitized['ga4_measurement_id'] !== ''
			&& ! preg_match( '/^G-[A-Z0-9]+$/i', $sanitized['ga4_measurement_id'] )
		) {
			$sanitized['ga4_measurement_id'] = (string) ( $existing['ga4_measurement_id'] ?? '' );
			$notes[] = __( 'Invalid GA4 Measurement ID was ignored. Use a G-XXXXXXXXXX value.', 'aegis' );
		}

		if ( array_key_exists( 'gtm_container_id', $input )
			&& $sanitized['gtm_container_id'] !== ''
			&& ! preg_match( '/^GTM-[A-Z0-9]+$/i', $sanitized['gtm_container_id'] )
		) {
			$sanitized['gtm_container_id'] = (string) ( $existing['gtm_container_id'] ?? '' );
			$notes[] = __( 'Invalid GTM Container ID was ignored. Use a GTM-XXXXXXX value.', 'aegis' );
		}

		if ( array_key_exists( 'clarity_project_id', $input ) ) {
			if ( $sanitized['clarity_project_id'] === '' ) {
				$sanitized['clarity_project_id'] = '';
			} else {
				$clarity_id = self::normalize_clarity_project_id( $sanitized['clarity_project_id'] );
				if ( $clarity_id === '' ) {
					// Keep a previously valid ID on typo; clear if stored value is also invalid.
					$sanitized['clarity_project_id'] = self::normalize_clarity_project_id(
						(string) ( $existing['clarity_project_id'] ?? '' )
					);
					$notes[] = __( 'Invalid Clarity Project ID was ignored. Use 6–16 letters or digits from the Clarity dashboard.', 'aegis' );
				} else {
					$sanitized['clarity_project_id'] = $clarity_id;
				}
			}
		}

		if ( array_key_exists( 'plausible_domain', $input ) ) {
			if ( $sanitized['plausible_domain'] === '' ) {
				$sanitized['plausible_domain'] = '';
			} else {
				$plausible_domain = self::normalize_plausible_domain( $sanitized['plausible_domain'] );
				if ( $plausible_domain === '' ) {
					$sanitized['plausible_domain'] = self::normalize_plausible_domain(
						(string) ( $existing['plausible_domain'] ?? '' )
					);
					$notes[] = __( 'Invalid Plausible domain was ignored. Use a hostname like example.com (comma-separated for multiple).', 'aegis' );
				} else {
					$sanitized['plausible_domain'] = $plausible_domain;
				}
			}
		}

		if ( array_key_exists( 'plausible_script_url', $input ) ) {
			if ( $sanitized['plausible_script_url'] === '' ) {
				$sanitized['plausible_script_url'] = '';
			} else {
				$plausible_url = self::normalize_plausible_script_url( $sanitized['plausible_script_url'] );
				if ( $plausible_url === '' ) {
					$sanitized['plausible_script_url'] = self::normalize_plausible_script_url(
						(string) ( $existing['plausible_script_url'] ?? '' )
					);
					$notes[] = __( 'Invalid Plausible script URL was ignored. Use an http(s) URL.', 'aegis' );
				} else {
					$sanitized['plausible_script_url'] = $plausible_url;
				}
			}
		}

		if ( array_key_exists( 'fathom_site_id', $input ) ) {
			if ( $sanitized['fathom_site_id'] === '' ) {
				$sanitized['fathom_site_id'] = '';
			} else {
				$fathom_id = self::normalize_fathom_site_id( $sanitized['fathom_site_id'] );
				if ( $fathom_id === '' ) {
					$sanitized['fathom_site_id'] = self::normalize_fathom_site_id(
						(string) ( $existing['fathom_site_id'] ?? '' )
					);
					$notes[] = __( 'Invalid Fathom Site ID was ignored. Use 5–10 letters or digits from the Fathom dashboard (e.g. ABCDEFG).', 'aegis' );
				} else {
					$sanitized['fathom_site_id'] = $fathom_id;
				}
			}
		}

		if ( array_key_exists( 'fathom_script_url', $input ) ) {
			if ( $sanitized['fathom_script_url'] === '' ) {
				$sanitized['fathom_script_url'] = '';
			} else {
				$fathom_url = self::normalize_fathom_script_url( $sanitized['fathom_script_url'] );
				if ( $fathom_url === '' ) {
					$sanitized['fathom_script_url'] = self::normalize_fathom_script_url(
						(string) ( $existing['fathom_script_url'] ?? '' )
					);
					$notes[] = __( 'Invalid Fathom script URL was ignored. Use an http(s) URL.', 'aegis' );
				} else {
					$sanitized['fathom_script_url'] = $fathom_url;
				}
			}
		}

		if ( array_key_exists( 'matomo_url', $input ) ) {
			if ( $sanitized['matomo_url'] === '' ) {
				$sanitized['matomo_url'] = '';
			} else {
				$matomo_url = self::normalize_matomo_url( $sanitized['matomo_url'] );
				if ( $matomo_url === '' ) {
					$sanitized['matomo_url'] = self::normalize_matomo_url(
						(string) ( $existing['matomo_url'] ?? '' )
					);
					$notes[] = __( 'Invalid Matomo URL was ignored. Use an http(s) URL or host/path to your Matomo instance.', 'aegis' );
				} else {
					$sanitized['matomo_url'] = $matomo_url;
				}
			}
		}

		if ( array_key_exists( 'matomo_site_id', $input ) ) {
			if ( $sanitized['matomo_site_id'] === '' ) {
				$sanitized['matomo_site_id'] = '';
			} else {
				$matomo_site_id = self::normalize_matomo_site_id( $sanitized['matomo_site_id'] );
				if ( $matomo_site_id === '' ) {
					$sanitized['matomo_site_id'] = self::normalize_matomo_site_id(
						(string) ( $existing['matomo_site_id'] ?? '' )
					);
					$notes[] = __( 'Invalid Matomo Site ID was ignored. Use a positive integer from your Matomo dashboard.', 'aegis' );
				} else {
					$sanitized['matomo_site_id'] = $matomo_site_id;
				}
			}
		}

		if ( array_key_exists( 'meta_pixel_id', $input ) ) {
			if ( $sanitized['meta_pixel_id'] === '' ) {
				$sanitized['meta_pixel_id'] = '';
			} else {
				$meta_pixel_id = self::normalize_meta_pixel_id( $sanitized['meta_pixel_id'] );
				if ( $meta_pixel_id === '' ) {
					$sanitized['meta_pixel_id'] = self::normalize_meta_pixel_id(
						(string) ( $existing['meta_pixel_id'] ?? '' )
					);
					$notes[] = __( 'Invalid Meta Pixel ID was ignored. Use the numeric Pixel ID from Meta Events Manager.', 'aegis' );
				} else {
					$sanitized['meta_pixel_id'] = $meta_pixel_id;
				}
			}
		}

		if ( ! empty( $sanitized['ga4_enabled'] ) && $sanitized['ga4_measurement_id'] === '' ) {
			$notes[] = __( 'Google Analytics is enabled but no Measurement ID is set.', 'aegis' );
		}

		if ( ! empty( $sanitized['gtm_enabled'] ) && $sanitized['gtm_container_id'] === '' ) {
			$notes[] = __( 'Google Tag Manager is enabled but no Container ID is set.', 'aegis' );
		}

		if ( ! empty( $sanitized['clarity_enabled'] )
			&& self::normalize_clarity_project_id( (string) ( $sanitized['clarity_project_id'] ?? '' ) ) === ''
		) {
			$notes[] = __( 'Microsoft Clarity is enabled but no Project ID is set.', 'aegis' );
		}

		if ( ! empty( $sanitized['plausible_enabled'] )
			&& self::normalize_plausible_domain( (string) ( $sanitized['plausible_domain'] ?? '' ) ) === ''
		) {
			$notes[] = __( 'Plausible is enabled but no domain is set.', 'aegis' );
		}

		if ( ! empty( $sanitized['fathom_enabled'] )
			&& self::normalize_fathom_site_id( (string) ( $sanitized['fathom_site_id'] ?? '' ) ) === ''
		) {
			$notes[] = __( 'Fathom is enabled but no Site ID is set.', 'aegis' );
		}

		if ( ! empty( $sanitized['matomo_enabled'] ) ) {
			$matomo_url_ok = self::normalize_matomo_url( (string) ( $sanitized['matomo_url'] ?? '' ) ) !== '';
			$matomo_id_ok  = self::normalize_matomo_site_id( (string) ( $sanitized['matomo_site_id'] ?? '' ) ) !== '';

			if ( ! $matomo_url_ok && ! $matomo_id_ok ) {
				$notes[] = __( 'Matomo is enabled but URL and Site ID are missing or invalid.', 'aegis' );
			} elseif ( ! $matomo_url_ok ) {
				$notes[] = __( 'Matomo is enabled but no valid Matomo URL is set.', 'aegis' );
			} elseif ( ! $matomo_id_ok ) {
				$notes[] = __( 'Matomo is enabled but no valid Site ID is set.', 'aegis' );
			}
		}

		if ( ! empty( $sanitized['meta_pixel_enabled'] )
			&& self::normalize_meta_pixel_id( (string) ( $sanitized['meta_pixel_id'] ?? '' ) ) === ''
		) {
			$notes[] = __( 'Meta Pixel is enabled but no valid Pixel ID is set.', 'aegis' );
		}

		if ( ! empty( $sanitized['ga4_consent_mode'] ) ) {
			$consent_ga4 = ! empty( $sanitized['ga4_enabled'] ) && (string) ( $sanitized['ga4_measurement_id'] ?? '' ) !== '';
			$consent_gtm = ! empty( $sanitized['gtm_enabled'] ) && (string) ( $sanitized['gtm_container_id'] ?? '' ) !== '';
			if ( ! $consent_ga4 && ! $consent_gtm ) {
				$notes[] = __( 'Consent Mode v2 is enabled but neither GA4 nor GTM is configured. Defaults only apply when GA4 and/or GTM load.', 'aegis' );
			}
		}

		if ( ! empty( $sanitized['gdpr_consent_required'] ) && empty( $sanitized['gdpr_complianz'] ) ) {
			$notes[] = __( 'Require Consent needs Complianz Integration and the Complianz plugin; scripts load normally until both are active.', 'aegis' );
		}

		if ( ! empty( $sanitized['gdpr_complianz'] ) && ! defined( 'CMPLZ_VERSION' ) ) {
			$notes[] = __( 'Complianz Integration is enabled but the Complianz plugin is not active.', 'aegis' );
		}

		if ( ! empty( $sanitized['gdpr_consent_required'] )
			&& ! empty( $sanitized['gdpr_complianz'] )
			&& ! defined( 'CMPLZ_VERSION' )
		) {
			$notes[] = __( 'Require Consent has no effect until the Complianz plugin is active.', 'aegis' );
		}

		if ( ! empty( $sanitized['local_scripts'] ) ) {
			$local_ga4 = ! empty( $sanitized['ga4_enabled'] ) && (string) ( $sanitized['ga4_measurement_id'] ?? '' ) !== '';
			$local_gtm = ! empty( $sanitized['gtm_enabled'] ) && (string) ( $sanitized['gtm_container_id'] ?? '' ) !== '';
			if ( ! $local_ga4 && ! $local_gtm ) {
				$notes[] = __( 'Local Script Loading is enabled but neither GA4 nor GTM is configured. Only those scripts are proxied.', 'aegis' );
			}
		}

		if ( ! empty( $sanitized['ga4_enabled'] ) && ! empty( $sanitized['gtm_enabled'] )
			&& $sanitized['ga4_measurement_id'] !== '' && $sanitized['gtm_container_id'] !== ''
		) {
			$notes[] = __( 'Both GA4 and GTM are enabled. Avoid firing the same Google Analytics tags from both, or traffic may be double-counted.', 'aegis' );
		}

		if ( null !== $warnings ) {
			foreach ( $notes as $note ) {
				$warnings[] = $note;
			}
		}

		return $sanitized;
	}

	/**
	 * Persist sanitized settings and handle script proxy cron.
	 *
	 * @param array<string, mixed> $input Raw input.
	 * @return array<int, string> User-facing warnings (empty when none).
	 */
	public static function save( array $input ): array {
		$warnings  = [];
		$sanitized = self::sanitize( $input, $warnings );

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

		return $warnings;
	}
}
