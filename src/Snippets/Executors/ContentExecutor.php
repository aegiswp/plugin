<?php
/**
 * Content snippet executor (HTML, mixed PHP, shortcodes, the_content).
 *
 * @package Aegis\Plugin\Snippets\Executors
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Snippets\Executors;

use Aegis\Plugin\Snippets\Settings;
use Aegis\Plugin\Snippets\Storage;
use function current_user_can;
use function is_float;
use function is_int;
use function is_string;
use function ob_get_clean;
use function ob_start;
use function str_contains;
use function trim;
use function wp_kses_post;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders content snippets as HTML, or includes them when they contain PHP.
 */
final class ContentExecutor {

	/**
	 * @param array<string, mixed> $snippet Snippet manifest entry.
	 */
	public function render( array $snippet ): void {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted admin snippets.
		echo $this->get_output( $snippet );
	}

	/**
	 * @param array<string, mixed> $snippet Snippet manifest entry.
	 */
	public function get_output( array $snippet ): string {
		$body = Storage::read_snippet_file( $snippet );

		if ( $body === '' ) {
			return '';
		}

		if ( str_contains( $body, '<?php' ) && Settings::is_php_enabled() ) {
			ob_start();
			( new PhpExecutor() )->execute( $snippet );

			return (string) ob_get_clean();
		}

		if ( current_user_can( 'unfiltered_html' ) ) {
			return $body;
		}

		return wp_kses_post( $body );
	}

	/**
	 * Shortcode output: echoed markup, or a string/number return value.
	 *
	 * @param array<string, mixed>  $snippet Snippet manifest entry.
	 * @param array<string, string> $atts    Shortcode attributes, including `id`.
	 * @return string|null Null when the snippet produced nothing valid.
	 */
	public function get_shortcode_output( array $snippet, array $atts, ?string $content ): ?string {
		$body = Storage::read_snippet_file( $snippet );

		if ( str_contains( $body, '<?php' ) ) {
			if ( ! Settings::is_php_enabled() ) {
				return null;
			}

			ob_start();
			$returned = ( new PhpExecutor() )->execute(
				$snippet,
				array(
					'atts'    => $atts,
					'content' => $content,
				)
			);
			$printed = (string) ob_get_clean();

			if ( trim( $printed ) !== '' ) {
				return $printed;
			}

			if ( is_string( $returned ) || is_float( $returned ) || ( is_int( $returned ) && $returned !== 1 ) ) {
				return (string) $returned;
			}

			return null;
		}

		if ( trim( $body ) === '' ) {
			return null;
		}

		if ( current_user_can( 'unfiltered_html' ) ) {
			return $body;
		}

		return wp_kses_post( $body );
	}
}
