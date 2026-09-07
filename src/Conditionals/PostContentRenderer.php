<?php
/**
 * Post Conditions Renderer
 *
 * Evaluates page/post-level conditional logic stored in `_aegis_conditions`
 * meta and hides post content when conditions are not met.
 *
 * @package    Aegis\Plugin\Conditionals
 * @since      1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Conditionals;

use Aegis\Framework\BlockSettings\Visibility;
use function add_filter;
use function class_exists;
use function get_post_meta;
use function get_post_type;
use function get_the_ID;
use function is_array;
use function is_string;
use function json_decode;

/**
 * Registers the post-content conditions filter on the frontend.
 */
class PostContentRenderer {

	/**
	 * Conditions evaluator.
	 */
	private Evaluator $evaluator;

	/**
	 * Initialize the renderer.
	 */
	public function init(): void {
		$this->evaluator = new Evaluator();
		$this->register_post_conditions();
	}

	/**
	 * Register a render_block filter that evaluates page/post-level
	 * conditional logic stored in `_aegis_conditions` meta.
	 */
	private function register_post_conditions(): void {
		$evaluator = $this->evaluator;

		add_filter(
			'render_block',
			static function ( string $block_content, array $block ) use ( $evaluator ): string {
				if ( ( $block['blockName'] ?? '' ) !== 'core/post-content' ) {
					return $block_content;
				}

				$post_id = get_the_ID();
				if ( ! $post_id || get_post_type( $post_id ) === 'aegis_hook_pattern' ) {
					return $block_content;
				}

				$conditions = self::conditions_for_post( $post_id );

				if ( $conditions === array() ) {
					return $block_content;
				}

				if ( ! $evaluator->should_render_conditions( $conditions ) ) {
					return '';
				}

				if ( class_exists( Visibility::class ) ) {
					return Visibility::apply_classes( $block_content, $conditions );
				}

				return $block_content;
			},
			9,
			2
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	private static function conditions_for_post( int $post_id ): array {
		$raw = get_post_meta( $post_id, '_aegis_conditions', true );

		if ( is_array( $raw ) ) {
			return $raw;
		}

		if ( is_string( $raw ) && $raw !== '' ) {
			$decoded = json_decode( $raw, true );

			return is_array( $decoded ) ? $decoded : array();
		}

		return array();
	}
}
