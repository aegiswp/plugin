<?php
/**
 * CSS snippet executor.
 *
 * @package Aegis\Plugin\Snippets\Executors
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Snippets\Executors;

use Aegis\Plugin\Snippets\Storage;
use function sanitize_html_class;
use function wp_add_inline_style;
use function wp_enqueue_style;
use function wp_register_style;
use function wp_style_is;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Inlines CSS snippets via wp_add_inline_style.
 */
final class CssExecutor {

	/**
	 * @param array<string, mixed> $snippet Snippet manifest entry.
	 */
	public function render( array $snippet ): void {
		$id     = sanitize_html_class( (string) ( $snippet['id'] ?? 'css' ) );
		$handle = 'aegis-snippet-' . ( $id !== '' ? $id : 'css' );

		if ( ! wp_style_is( $handle, 'registered' ) ) {
			wp_register_style( $handle, false, array(), \Aegis\Plugin\VERSION );
		}

		wp_enqueue_style( $handle );

		$css = Storage::read_snippet_file( $snippet );

		if ( $css !== '' ) {
			wp_add_inline_style( $handle, $css );
		}
	}
}
