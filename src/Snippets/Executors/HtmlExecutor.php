<?php
/**
 * HTML snippet executor.
 *
 * @package Aegis\Plugin\Snippets\Executors
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Snippets\Executors;

use Aegis\Plugin\Snippets\Storage;
use function add_action;
use function current_user_can;
use function echo;
use function wp_kses_post;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Outputs HTML snippets at action hooks.
 */
final class HtmlExecutor {

	/**
	 * @param array<string, mixed> $snippet Snippet manifest entry.
	 */
	public function render( array $snippet ): void {
		$html = Storage::read_snippet_file( $snippet );

		if ( $html === '' ) {
			return;
		}

		if ( current_user_can( 'unfiltered_html' ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted admins with unfiltered_html.
			echo $html;
			return;
		}

		echo wp_kses_post( $html );
	}
}
