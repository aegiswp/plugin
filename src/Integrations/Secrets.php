<?php
/**
 * Secret field masking, merge-on-save, and optional encryption helpers.
 *
 * @package Aegis\Plugin\Integrations
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Integrations;

use function base64_decode;
use function base64_encode;
use function hash;
use function is_string;
use function openssl_decrypt;
use function openssl_encrypt;
use function str_repeat;
use function strlen;
use function substr;
use function wp_salt;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Utilities for handling sensitive integration settings.
 */
final class Secrets {

	public const MASK_CHAR = '•';

	/**
	 * Mask a secret value, leaving the last few characters visible.
	 *
	 * @param string $value   Raw secret value.
	 * @param int    $visible Number of trailing characters to reveal.
	 */
	public static function mask( string $value, int $visible = 4 ): string {
		if ( $value === '' ) {
			return '';
		}

		$length = strlen( $value );

		if ( $length <= $visible ) {
			return str_repeat( self::MASK_CHAR, $length );
		}

		return str_repeat( self::MASK_CHAR, $length - $visible ) . substr( $value, -$visible );
	}

	/**
	 * Determine whether a value is a masked placeholder.
	 *
	 * @param string $value Candidate value.
	 */
	public static function is_masked( string $value ): bool {
		if ( $value === '' ) {
			return false;
		}

		// Partial masks (e.g. ••••abcd) are placeholders from the admin UI.
		return str_contains( $value, self::MASK_CHAR );
	}

	/**
	 * Merge incoming settings with existing secrets, preserving stored values when masked or empty.
	 *
	 * @param array<string, mixed> $existing    Stored settings.
	 * @param array<string, mixed> $incoming    Submitted settings.
	 * @param array<int, string>   $secret_keys Keys that should be preserved when masked.
	 * @return array<string, mixed>
	 */
	public static function merge_on_save( array $existing, array $incoming, array $secret_keys ): array {
		$merged = $incoming;

		foreach ( $secret_keys as $key ) {
			if ( ! is_string( $key ) || ! array_key_exists( $key, $incoming ) ) {
				continue;
			}

			$incoming_value = is_string( $incoming[ $key ] ) ? $incoming[ $key ] : (string) $incoming[ $key ];

			if (
				$incoming_value === ''
				|| self::is_masked( $incoming_value )
				|| ( isset( $existing[ $key ] ) && is_string( $existing[ $key ] ) && $incoming_value === self::mask( $existing[ $key ] ) )
			) {
				if ( isset( $existing[ $key ] ) ) {
					$merged[ $key ] = $existing[ $key ];
				}
			}
		}

		return $merged;
	}

	/**
	 * Encrypt a value using wp_salt as the key source.
	 *
	 * @param string $value Plaintext value.
	 */
	public static function encrypt( string $value ): string {
		if ( $value === '' ) {
			return '';
		}

		$key    = hash( 'sha256', wp_salt( 'auth' ), true );
		$iv     = substr( hash( 'sha256', wp_salt( 'secure_auth' ), true ), 0, 16 );
		$cipher = openssl_encrypt( $value, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv );

		if ( $cipher === false ) {
			return $value;
		}

		return base64_encode( $cipher );
	}

	/**
	 * Decrypt a value encrypted with encrypt().
	 *
	 * @param string $value Encrypted value.
	 */
	public static function decrypt( string $value ): string {
		if ( $value === '' ) {
			return '';
		}

		$key     = hash( 'sha256', wp_salt( 'auth' ), true );
		$iv      = substr( hash( 'sha256', wp_salt( 'secure_auth' ), true ), 0, 16 );
		$decoded = base64_decode( $value, true );

		if ( $decoded === false ) {
			return $value;
		}

		$plain = openssl_decrypt( $decoded, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv );

		return is_string( $plain ) ? $plain : $value;
	}
}
