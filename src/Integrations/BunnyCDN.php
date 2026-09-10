<?php
/**
 * BunnyCDN helpers for connector gating.
 *
 * @package Aegis\Plugin\Integrations
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Integrations;

use function class_exists;
use function defined;
use function in_array;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BunnyCDN is a SaaS connector (not a WordPress plugin).
 * The parent toggle and Pro extras live on Aegis → Connectors → BunnyCDN.
 */
final class BunnyCDN {

	/** @var array<int, string> */
	public const EXTRAS = [
		'bunny_cdn_stream_library',
		'bunny_cdn_direct_upload',
		'bunny_cdn_hls_streaming',
		'bunny_cdn_ai_transcription',
		'bunny_cdn_video_thumbnails',
	];

	/**
	 * Connector is always available (credentials are configured in Connectors).
	 */
	public static function is_plugin_active(): bool {
		return true;
	}

	/**
	 * Whether the BunnyCDN connector toggle is on.
	 */
	public static function is_enabled(): bool {
		return class_exists( Settings::class )
			&& Settings::is_integration_enabled( 'bunny_cdn' );
	}

	/**
	 * Whether a BunnyCDN Pro extra is on (implies parent is on after sanitize).
	 */
	public static function is_extra_enabled( string $extra ): bool {
		if ( ! self::is_enabled() || ! in_array( $extra, self::EXTRAS, true ) ) {
			return false;
		}

		return Settings::is_integration_enabled( $extra );
	}
}
