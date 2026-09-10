<?php
/**
 * AffiliateWP helpers for detection and parent gating.
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
 * AffiliateWP defines Affiliate_WP, affiliate_wp(), and AFFILIATEWP_VERSION.
 */
final class AffiliateWP {

	/**
	 * Whether AffiliateWP is loaded.
	 */
	public static function is_plugin_active(): bool {
		return class_exists( 'Affiliate_WP' )
			|| function_exists( 'affiliate_wp' )
			|| defined( 'AFFILIATEWP_VERSION' );
	}

	/**
	 * Whether the Aegis AffiliateWP integration is on and the plugin is loaded.
	 */
	public static function is_enabled(): bool {
		return self::is_plugin_active()
			&& class_exists( Settings::class )
			&& Settings::is_integration_enabled( 'affiliate_wp' );
	}
}
