<?php
/**
 * All in One SEO adapter.
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
 * Registers AIOSEO schema integration hooks.
 */
final class AioseoAdapter implements SeoAdapterInterface {

	public static function is_active(): bool {
		return defined( 'AIOSEO_VERSION' ) || class_exists( 'AIOSEO\\Plugin\\AIOSEO' );
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
	 * @param array<string, mixed> $schema Schema output.
	 * @return array<string, mixed>
	 */
	public function filter_schema_output( array $schema ): array {
		return $schema;
	}
}
