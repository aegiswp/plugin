<?php
/**
 * Yoast SEO adapter.
 *
 * @package Aegis\Plugin\Seo\Adapters
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Seo\Adapters;

use Aegis\Plugin\Seo\SeoAdapterInterface;
use function add_filter;
use function class_exists;
use function defined;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers Yoast SEO schema integration hooks.
 */
final class YoastAdapter implements SeoAdapterInterface {

	public static function is_active(): bool {
		return defined( 'WPSEO_VERSION' ) || class_exists( 'WPSEO_Options' );
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
		return $graph;
	}
}
