<?php
/**
 * SEOPress adapter.
 *
 * @package Aegis\Plugin\Seo\Adapters
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Seo\Adapters;

use Aegis\Plugin\Seo\SeoAdapterInterface;
use function add_filter;
use function defined;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers SEOPress schema integration hooks.
 */
final class SeopressAdapter implements SeoAdapterInterface {

	public static function is_active(): bool {
		return defined( 'SEOPRESS_VERSION' );
	}

	public static function get_slug(): string {
		return 'seopress';
	}

	public static function get_label(): string {
		return 'SEOPress';
	}

	public function register_hooks(): void {
		add_filter( 'seopress_schemas_auto_schema', [ $this, 'filter_auto_schema' ], 20, 2 );
	}

	/**
	 * @param array<string, mixed> $schema Schema data.
	 * @param mixed                $context SEOPress context.
	 * @return array<string, mixed>
	 */
	public function filter_auto_schema( array $schema, $context ): array {
		return $schema;
	}
}
