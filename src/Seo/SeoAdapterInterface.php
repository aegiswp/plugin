<?php
/**
 * SEO plugin adapter contract.
 *
 * @package Aegis\Plugin\Seo
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Seo;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Defines the surface for third-party SEO plugin integrations.
 */
interface SeoAdapterInterface {

	/**
	 * Whether the third-party SEO plugin is installed and active.
	 */
	public static function is_active(): bool;

	/**
	 * Register WordPress hooks for schema and SEO features.
	 */
	public function register_hooks(): void;

	/**
	 * Integration settings key for this SEO plugin.
	 */
	public static function get_slug(): string;

	/**
	 * Human-readable SEO plugin name.
	 */
	public static function get_label(): string;
}
