<?php
/**
 * Yoast SEO helpers for detection and parent gating.
 *
 * @package Aegis\Plugin\Integrations
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Integrations;

use function class_exists;
use function defined;
use function function_exists;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Yoast SEO Free defines WPSEO_VERSION / WPSEO_FILE / WPSEO_Options / YoastSEO().
 * Premium defines WPSEO_PREMIUM_FILE / WPSEO_PREMIUM_PATH / WPSEO_Premium.
 * Video SEO addon defines WPSEO_VIDEO_FILE / WPSEO_Video_Sitemap.
 */
final class Yoast {

	/**
	 * Whether Yoast SEO Free or Premium is loaded.
	 */
	public static function is_plugin_active(): bool {
		return defined( 'WPSEO_VERSION' )
			|| defined( 'WPSEO_FILE' )
			|| class_exists( 'WPSEO_Options' )
			|| function_exists( 'YoastSEO' )
			|| function_exists( 'yoast_breadcrumb' )
			|| self::is_premium_active()
			|| self::is_video_seo_active();
	}

	/**
	 * Whether Yoast SEO Premium is loaded.
	 */
	public static function is_premium_active(): bool {
		return defined( 'WPSEO_PREMIUM_FILE' )
			|| defined( 'WPSEO_PREMIUM_PATH' )
			|| class_exists( 'WPSEO_Premium' );
	}

	/**
	 * Whether the Yoast Video SEO addon is loaded (video sitemap / indexing).
	 */
	public static function is_video_seo_active(): bool {
		return defined( 'WPSEO_VIDEO_FILE' )
			|| class_exists( 'WPSEO_Video_Sitemap' );
	}

	/**
	 * Whether the Aegis Yoast SEO integration is on and the plugin is loaded.
	 */
	public static function is_enabled(): bool {
		return self::is_plugin_active()
			&& class_exists( Settings::class )
			&& Settings::is_integration_enabled( 'yoast_seo' );
	}
}
