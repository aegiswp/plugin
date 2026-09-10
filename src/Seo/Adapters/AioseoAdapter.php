<?php
/**
 * All in One SEO adapter.
 *
 * @package Aegis\Plugin\Seo\Adapters
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Seo\Adapters;

use Aegis\Plugin\Integrations\AllInOneSEO;
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
 * Registers AIOSEO schema integration hooks.
 */
final class AioseoAdapter implements SeoAdapterInterface {

	public static function is_active(): bool {
		if ( class_exists( AllInOneSEO::class ) ) {
			return AllInOneSEO::is_plugin_active();
		}

		return defined( 'AIOSEO_VERSION' )
			|| class_exists( 'AIOSEO\\Plugin\\AIOSEO' )
			|| function_exists( 'aioseo' )
			|| defined( 'AIOSEO_FILE' );
	}

	public static function get_slug(): string {
		return 'aioseo';
	}

	public static function get_label(): string {
		return 'All in One SEO';
	}

	public function register_hooks(): void {
		add_filter( 'aioseo_schema_output', [ $this, 'filter_schema_output' ], 20, 1 );
	}

	/**
	 * @param array<int|string, mixed> $schema Schema output graphs.
	 * @return array<int|string, mixed>
	 */
	public function filter_schema_output( array $schema ): array {
		return apply_filters( 'aegis_aioseo_schema_output', $schema );
	}
}
