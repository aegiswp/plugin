<?php
/**
 * Rank Math SEO adapter.
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
 * Registers Rank Math schema integration hooks.
 */
final class RankMathAdapter implements SeoAdapterInterface {

	public static function is_active(): bool {
		return defined( 'RANK_MATH_VERSION' ) || class_exists( 'RankMath' );
	}

	public static function get_slug(): string {
		return 'rank_math';
	}

	public static function get_label(): string {
		return 'Rank Math';
	}

	public function register_hooks(): void {
		add_filter( 'rank_math/json_ld', [ $this, 'filter_json_ld' ], 20, 2 );
	}

	/**
	 * @param array<string, mixed> $data    JSON-LD graph.
	 * @param mixed                $context Rank Math context.
	 * @return array<string, mixed>
	 */
	public function filter_json_ld( array $data, $context ): array {
		return $data;
	}
}
