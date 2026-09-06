<?php
/**
 * Google Maps style presets and Static Maps encoding.
 *
 * @package Aegis\Plugin\Map
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Map;

use Aegis\Framework\ServiceProvider;
use function array_is_list;
use function in_array;
use function is_array;
use function is_bool;
use function is_numeric;
use function is_string;
use function json_decode;
use function preg_match;
use function preg_replace;
use function rawurlencode;
use function strtolower;
use function substr;
use function trim;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shared map style presets (must match view.js / index.js) and Static Maps `style=` encoding.
 */
final class Styles {

	/**
	 * Google Static Maps URL length limit.
	 */
	private const STATIC_URL_MAX = 8192;

	/**
	 * Style presets keyed by mapStyle attribute.
	 *
	 * @return array<string, array<int, array<string, mixed>>>
	 */
	public static function presets(): array {
		return [
			'default'   => [],
			'silver'    => [
				[ 'elementType' => 'geometry', 'stylers' => [ [ 'color' => '#f5f5f5' ] ] ],
				[ 'elementType' => 'labels.icon', 'stylers' => [ [ 'visibility' => 'off' ] ] ],
				[ 'elementType' => 'labels.text.fill', 'stylers' => [ [ 'color' => '#616161' ] ] ],
				[ 'elementType' => 'labels.text.stroke', 'stylers' => [ [ 'color' => '#f5f5f5' ] ] ],
				[ 'featureType' => 'administrative.land_parcel', 'elementType' => 'labels.text.fill', 'stylers' => [ [ 'color' => '#bdbdbd' ] ] ],
				[ 'featureType' => 'poi', 'elementType' => 'geometry', 'stylers' => [ [ 'color' => '#eeeeee' ] ] ],
				[ 'featureType' => 'poi', 'elementType' => 'labels.text.fill', 'stylers' => [ [ 'color' => '#757575' ] ] ],
				[ 'featureType' => 'road', 'elementType' => 'geometry', 'stylers' => [ [ 'color' => '#ffffff' ] ] ],
				[ 'featureType' => 'road.arterial', 'elementType' => 'labels.text.fill', 'stylers' => [ [ 'color' => '#757575' ] ] ],
				[ 'featureType' => 'road.highway', 'elementType' => 'geometry', 'stylers' => [ [ 'color' => '#dadada' ] ] ],
				[ 'featureType' => 'water', 'elementType' => 'geometry', 'stylers' => [ [ 'color' => '#c9c9c9' ] ] ],
				[ 'featureType' => 'water', 'elementType' => 'labels.text.fill', 'stylers' => [ [ 'color' => '#9e9e9e' ] ] ],
			],
			'dark'      => [
				[ 'elementType' => 'geometry', 'stylers' => [ [ 'color' => '#212121' ] ] ],
				[ 'elementType' => 'labels.icon', 'stylers' => [ [ 'visibility' => 'off' ] ] ],
				[ 'elementType' => 'labels.text.fill', 'stylers' => [ [ 'color' => '#757575' ] ] ],
				[ 'elementType' => 'labels.text.stroke', 'stylers' => [ [ 'color' => '#212121' ] ] ],
				[ 'featureType' => 'administrative', 'elementType' => 'geometry', 'stylers' => [ [ 'color' => '#757575' ] ] ],
				[ 'featureType' => 'poi', 'elementType' => 'geometry', 'stylers' => [ [ 'color' => '#181818' ] ] ],
				[ 'featureType' => 'road', 'elementType' => 'geometry.fill', 'stylers' => [ [ 'color' => '#2c2c2c' ] ] ],
				[ 'featureType' => 'road', 'elementType' => 'geometry.stroke', 'stylers' => [ [ 'color' => '#212121' ] ] ],
				[ 'featureType' => 'road.highway', 'elementType' => 'geometry', 'stylers' => [ [ 'color' => '#3c3c3c' ] ] ],
				[ 'featureType' => 'water', 'elementType' => 'geometry', 'stylers' => [ [ 'color' => '#000000' ] ] ],
				[ 'featureType' => 'water', 'elementType' => 'labels.text.fill', 'stylers' => [ [ 'color' => '#3d3d3d' ] ] ],
			],
			'retro'     => [
				[ 'elementType' => 'geometry', 'stylers' => [ [ 'color' => '#ebe3cd' ] ] ],
				[ 'elementType' => 'labels.text.fill', 'stylers' => [ [ 'color' => '#523735' ] ] ],
				[ 'elementType' => 'labels.text.stroke', 'stylers' => [ [ 'color' => '#f5f1e6' ] ] ],
				[ 'featureType' => 'administrative', 'elementType' => 'geometry.stroke', 'stylers' => [ [ 'color' => '#c9b2a6' ] ] ],
				[ 'featureType' => 'poi', 'elementType' => 'geometry', 'stylers' => [ [ 'color' => '#dfd2ae' ] ] ],
				[ 'featureType' => 'road', 'elementType' => 'geometry', 'stylers' => [ [ 'color' => '#f5f1e6' ] ] ],
				[ 'featureType' => 'road.highway', 'elementType' => 'geometry', 'stylers' => [ [ 'color' => '#f8c967' ] ] ],
				[ 'featureType' => 'road.highway', 'elementType' => 'geometry.stroke', 'stylers' => [ [ 'color' => '#e9bc62' ] ] ],
				[ 'featureType' => 'water', 'elementType' => 'geometry.fill', 'stylers' => [ [ 'color' => '#b9d3c2' ] ] ],
			],
			'night'     => [
				[ 'elementType' => 'geometry', 'stylers' => [ [ 'color' => '#242f3e' ] ] ],
				[ 'elementType' => 'labels.text.fill', 'stylers' => [ [ 'color' => '#746855' ] ] ],
				[ 'elementType' => 'labels.text.stroke', 'stylers' => [ [ 'color' => '#242f3e' ] ] ],
				[ 'featureType' => 'administrative.locality', 'elementType' => 'labels.text.fill', 'stylers' => [ [ 'color' => '#d59563' ] ] ],
				[ 'featureType' => 'poi', 'elementType' => 'labels.text.fill', 'stylers' => [ [ 'color' => '#d59563' ] ] ],
				[ 'featureType' => 'road', 'elementType' => 'geometry', 'stylers' => [ [ 'color' => '#38414e' ] ] ],
				[ 'featureType' => 'road', 'elementType' => 'geometry.stroke', 'stylers' => [ [ 'color' => '#212a37' ] ] ],
				[ 'featureType' => 'road.highway', 'elementType' => 'geometry', 'stylers' => [ [ 'color' => '#746855' ] ] ],
				[ 'featureType' => 'transit', 'elementType' => 'geometry', 'stylers' => [ [ 'color' => '#2f3948' ] ] ],
				[ 'featureType' => 'water', 'elementType' => 'geometry', 'stylers' => [ [ 'color' => '#17263c' ] ] ],
				[ 'featureType' => 'water', 'elementType' => 'labels.text.fill', 'stylers' => [ [ 'color' => '#515c6d' ] ] ],
			],
			'aubergine' => [
				[ 'elementType' => 'geometry', 'stylers' => [ [ 'color' => '#1d2c4d' ] ] ],
				[ 'elementType' => 'labels.text.fill', 'stylers' => [ [ 'color' => '#8ec3b9' ] ] ],
				[ 'elementType' => 'labels.text.stroke', 'stylers' => [ [ 'color' => '#1a3646' ] ] ],
				[ 'featureType' => 'administrative.country', 'elementType' => 'geometry.stroke', 'stylers' => [ [ 'color' => '#4b6878' ] ] ],
				[ 'featureType' => 'land_parcel', 'elementType' => 'labels.text.fill', 'stylers' => [ [ 'color' => '#64779e' ] ] ],
				[ 'featureType' => 'poi', 'elementType' => 'geometry', 'stylers' => [ [ 'color' => '#283d6a' ] ] ],
				[ 'featureType' => 'road', 'elementType' => 'geometry', 'stylers' => [ [ 'color' => '#304a7d' ] ] ],
				[ 'featureType' => 'road', 'elementType' => 'geometry.stroke', 'stylers' => [ [ 'color' => '#255763' ] ] ],
				[ 'featureType' => 'road.highway', 'elementType' => 'geometry', 'stylers' => [ [ 'color' => '#2c6675' ] ] ],
				[ 'featureType' => 'transit', 'elementType' => 'geometry', 'stylers' => [ [ 'color' => '#182d57' ] ] ],
				[ 'featureType' => 'water', 'elementType' => 'geometry', 'stylers' => [ [ 'color' => '#0e1626' ] ] ],
				[ 'featureType' => 'water', 'elementType' => 'labels.text.fill', 'stylers' => [ [ 'color' => '#4e6d70' ] ] ],
			],
		];
	}

	/**
	 * Style rules for a block: Pro custom JSON overrides presets when enabled.
	 *
	 * @param array<string, mixed> $attributes Block attributes.
	 * @return array<int, array<string, mixed>>
	 */
	public static function rules_for_block( array $attributes ): array {
		if ( ServiceProvider::is_block_enabled( 'map_custom_styles' ) ) {
			$json = $attributes['customStyleJson'] ?? '';

			if ( is_string( $json ) && $json !== '' ) {
				$decoded = json_decode( $json, true );

				if ( is_array( $decoded ) && array_is_list( $decoded ) && $decoded !== [] ) {
					return $decoded;
				}
			}
		}

		if ( ! ServiceProvider::is_block_enabled( 'map_styles' ) ) {
			return [];
		}

		$preset  = (string) ( $attributes['mapStyle'] ?? 'default' );
		$presets = self::presets();

		return $presets[ $preset ] ?? [];
	}

	/**
	 * Append Google Static Maps `style=` params. Skips styles if the URL would exceed the API limit.
	 *
	 * Do not pass `style` through `add_query_arg()` — WordPress would emit `style[0]=`.
	 *
	 * @param string                            $url   Static Maps URL.
	 * @param array<int, array<string, mixed>> $rules Google Maps JS style rules.
	 */
	public static function append_to_static_url( string $url, array $rules ): string {
		if ( $url === '' || $rules === [] ) {
			return $url;
		}

		$params = [];

		foreach ( $rules as $rule ) {
			if ( ! is_array( $rule ) ) {
				continue;
			}

			$encoded = self::rule_to_static_param( $rule );

			if ( $encoded !== '' ) {
				$params[] = $encoded;
			}
		}

		if ( $params === [] ) {
			return $url;
		}

		$styled = $url;

		foreach ( $params as $param ) {
			$styled .= '&style=' . rawurlencode( $param );
		}

		if ( strlen( $styled ) > self::STATIC_URL_MAX ) {
			return $url;
		}

		return $styled;
	}

	/**
	 * Encode one Google Maps JS style rule as a Static Maps `style` value (unencoded).
	 *
	 * @param array<string, mixed> $rule Style rule.
	 */
	public static function rule_to_static_param( array $rule ): string {
		$parts = [];

		$feature = self::sanitize_type( $rule['featureType'] ?? '' );
		if ( $feature !== '' ) {
			$parts[] = 'feature:' . $feature;
		}

		$element = self::sanitize_type( $rule['elementType'] ?? '' );
		if ( $element !== '' ) {
			$parts[] = 'element:' . $element;
		}

		$stylers = $rule['stylers'] ?? [];
		if ( is_array( $stylers ) ) {
			foreach ( $stylers as $styler ) {
				if ( ! is_array( $styler ) ) {
					continue;
				}

				foreach ( $styler as $key => $value ) {
					$encoded = self::styler_pair( (string) $key, $value );

					if ( $encoded !== null ) {
						$parts[] = $encoded;
					}
				}
			}
		}

		return implode( '|', $parts );
	}

	/**
	 * Sanitize featureType / elementType tokens.
	 */
	private static function sanitize_type( mixed $value ): string {
		if ( ! is_string( $value ) ) {
			return '';
		}

		return (string) preg_replace( '/[^a-z0-9._]/i', '', $value );
	}

	/**
	 * Encode a single styler key/value for Static Maps.
	 */
	private static function styler_pair( string $key, mixed $value ): ?string {
		$key = strtolower( $key );

		if ( $key === 'color' || $key === 'hue' ) {
			$color = self::to_static_color( is_string( $value ) ? $value : '' );

			return $color !== '' ? $key . ':' . $color : null;
		}

		if ( $key === 'invert_lightness' ) {
			$bool = self::to_static_bool( $value );

			return $bool !== null ? $key . ':' . $bool : null;
		}

		if ( $key === 'visibility' ) {
			$token = strtolower( trim( (string) $value ) );

			return in_array( $token, [ 'on', 'off', 'simplified' ], true ) ? $key . ':' . $token : null;
		}

		if ( in_array( $key, [ 'lightness', 'saturation', 'gamma', 'weight' ], true ) ) {
			return is_numeric( $value ) ? $key . ':' . (string) $value : null;
		}

		return null;
	}

	/**
	 * Convert CSS hex to Static Maps `0xRRGGBB`.
	 */
	private static function to_static_color( string $color ): string {
		$color = trim( $color );

		if ( preg_match( '/^#([0-9a-f]{3}|[0-9a-f]{6}|[0-9a-f]{8})$/i', $color, $matches ) ) {
			$hex = strtolower( $matches[1] );

			if ( strlen( $hex ) === 3 ) {
				$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
			} elseif ( strlen( $hex ) === 8 ) {
				$hex = substr( $hex, 0, 6 );
			}

			return '0x' . $hex;
		}

		if ( preg_match( '/^0x[0-9a-f]{6}$/i', $color ) ) {
			return strtolower( $color );
		}

		return '';
	}

	/**
	 * Convert a boolean-like value to `true` / `false`.
	 */
	private static function to_static_bool( mixed $value ): ?string {
		if ( is_bool( $value ) ) {
			return $value ? 'true' : 'false';
		}

		$token = strtolower( trim( (string) $value ) );

		if ( in_array( $token, [ 'true', '1' ], true ) ) {
			return 'true';
		}

		if ( in_array( $token, [ 'false', '0' ], true ) ) {
			return 'false';
		}

		return null;
	}
}
