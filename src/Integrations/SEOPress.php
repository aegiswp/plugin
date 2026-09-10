<?php
/**
 * SEOPress helpers for detection and parent gating.
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
 * SEOPress Free defines SEOPRESS_VERSION / SEOPRESS_PLUGIN_DIR_PATH / seopress_get_service().
 * SEOPress PRO defines SEOPRESS_PRO_VERSION / SEOPRESS_PRO_PLUGIN_DIR_PATH.
 */
final class SEOPress {

	/**
	 * Whether SEOPress Free or PRO is loaded.
	 */
	public static function is_plugin_active(): bool {
		return defined( 'SEOPRESS_VERSION' )
			|| defined( 'SEOPRESS_PLUGIN_DIR_PATH' )
			|| defined( 'SEOPRESS_PRO_VERSION' )
			|| defined( 'SEOPRESS_PRO_PLUGIN_DIR_PATH' )
			|| function_exists( 'seopress_get_service' );
	}

	/**
	 * Whether SEOPress PRO is loaded (video sitemap and automatic schema modules).
	 */
	public static function is_pro_active(): bool {
		return defined( 'SEOPRESS_PRO_VERSION' )
			|| defined( 'SEOPRESS_PRO_PLUGIN_DIR_PATH' )
			|| function_exists( 'seopress_pro_get_service' );
	}

	/**
	 * Whether the Aegis SEOPress integration is on and the plugin is loaded.
	 */
	public static function is_enabled(): bool {
		return self::is_plugin_active()
			&& class_exists( Settings::class )
			&& Settings::is_integration_enabled( 'seopress' );
	}
}
