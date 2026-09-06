<?php
/**
 * JavaScript snippet executor.
 *
 * @package Aegis\Plugin\Snippets\Executors
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Snippets\Executors;

use Aegis\Plugin\Snippets\Storage;
use function sanitize_html_class;
use function wp_add_inline_script;
use function wp_enqueue_script;
use function wp_register_script;
use function wp_script_is;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Inlines JS snippets via wp_add_inline_script.
 */
final class JsExecutor {

	/**
	 * @param array<string, mixed> $snippet  Snippet manifest entry.
	 * @param bool                 $in_footer Whether to print in the footer.
	 */
	public function render( array $snippet, bool $in_footer = true ): void {
		$id     = sanitize_html_class( (string) ( $snippet['id'] ?? 'js' ) );
		$handle = 'aegis-snippet-' . ( $id !== '' ? $id : 'js' );

		if ( ! wp_script_is( $handle, 'registered' ) ) {
			wp_register_script( $handle, false, array(), \Aegis\Plugin\VERSION, $in_footer );
		}

		wp_enqueue_script( $handle );

		$js = Storage::read_snippet_file( $snippet );

		if ( $js !== '' ) {
			wp_add_inline_script( $handle, $js );
		}
	}
}
