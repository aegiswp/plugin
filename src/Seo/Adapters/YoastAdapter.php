<?php
/**
 * Yoast SEO adapter.
 *
 * @package Aegis\Plugin\Seo\Adapters
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Seo\Adapters;

use Aegis\Plugin\Integrations\Yoast;
use Aegis\Plugin\Seo\SeoAdapterInterface;
use function add_filter;
use function apply_filters;
use function class_exists;
use function defined;
use function function_exists;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers Yoast SEO schema integration hooks.
 */
final class YoastAdapter implements SeoAdapterInterface {

	public static function is_active(): bool {
		if ( class_exists( Yoast::class ) ) {
			return Yoast::is_plugin_active();
		}

		return defined( 'WPSEO_VERSION' )
			|| defined( 'WPSEO_FILE' )
			|| class_exists( 'WPSEO_Options' )
			|| function_exists( 'YoastSEO' )
			|| function_exists( 'yoast_breadcrumb' )
			|| defined( 'WPSEO_PREMIUM_FILE' )
			|| defined( 'WPSEO_VIDEO_FILE' );
	}

	public static function get_slug(): string {
		return 'yoast_seo';
	}

	public static function get_label(): string {
		return 'Yoast SEO';
	}

	public function register_hooks(): void {
		add_filter( 'wpseo_schema_graph', [ $this, 'filter_schema_graph' ], 20, 2 );
	}

	/**
	 * @param array<int, array<string, mixed>> $graph   Schema graph.
	 * @param mixed                            $context Yoast context object.
	 * @return array<int, array<string, mixed>>
	 */
	public function filter_schema_graph( array $graph, $context ): array {
		/**
		 * Filters the Yoast SEO schema graph before output.
		 *
		 * @param array<int, array<string, mixed>> $graph   Schema graph.
		 * @param mixed                            $context Yoast context object.
		 */
		return apply_filters( 'aegis_yoast_schema_graph', $graph, $context );
	}
}
