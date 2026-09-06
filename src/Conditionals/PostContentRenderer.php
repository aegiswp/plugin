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

use function add_filter;
use function get_post_type;
use function get_the_ID;

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

				if ( ! $evaluator->should_render_pattern( $post_id ) ) {
					return '';
				}

				return $block_content;
			},
			9,
			2
		);
	}
}
