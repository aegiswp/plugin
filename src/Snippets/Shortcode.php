<?php
/**
 * Shared [aegis_snippet id="…"] shortcode.
 *
 * @package Aegis\Plugin\Snippets
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Snippets;

use Aegis\Plugin\Conditionals\Evaluator;
use Aegis\Plugin\Snippets\Executors\ContentExecutor;
use function __;
use function current_user_can;
use function defined;
use function esc_html;
use function is_array;
use function is_string;
use function sanitize_text_field;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders Content snippets whose run location is Shortcode.
 */
final class Shortcode {

	private Evaluator $evaluator;

	public function __construct() {
		$this->evaluator = new Evaluator();
	}

	/**
	 * @param array<string, string>|string $atts    Shortcode attributes.
	 * @param string|null                  $content Enclosed content.
	 */
	public function render( $atts, $content = null ): string {
		$atts = is_array( $atts ) ? $atts : array();
		$id   = sanitize_text_field( (string) ( $atts['id'] ?? '' ) );

		if ( ( defined( 'AEGIS_DISABLE_SNIPPETS' ) && AEGIS_DISABLE_SNIPPETS ) || Settings::is_safe_mode() ) {
			return $this->notice( __( 'Snippets are disabled', 'aegis' ) );
		}

		if ( $id === '' ) {
			return $this->notice( __( 'Snippet not found', 'aegis' ) );
		}

		$snippet = Storage::get_snippet( $id );

		if ( $snippet === null ) {
			return $this->notice( __( 'Snippet not found', 'aegis' ) );
		}

		if ( ! empty( $snippet['last_error'] ) ) {
			return $this->notice( __( 'Snippet has an error', 'aegis' ) );
		}

		$type = Locations::normalize_type( (string) ( $snippet['type'] ?? '' ) );

		if ( $type !== Locations::TYPE_CONTENT ) {
			return $this->notice( __( 'Snippet type is not PHP Content', 'aegis' ) );
		}

		if ( (string) ( $snippet['location'] ?? '' ) !== 'shortcode' ) {
			return $this->notice( __( 'Snippet run at is not shortcode', 'aegis' ) );
		}

		if ( empty( $snippet['enabled'] ) ) {
			return $this->notice( __( 'Snippet status is not published', 'aegis' ) );
		}

		$conditions = is_array( $snippet['conditions'] ?? null ) ? $snippet['conditions'] : array();

		if ( ! $this->evaluator->should_render_conditions( $conditions ) ) {
			return $this->notice( __( 'Snippet condition is not valid', 'aegis' ) );
		}

		$output = ( new ContentExecutor() )->get_shortcode_output( $snippet, $atts, is_string( $content ) ? $content : null );

		if ( $output === null ) {
			return $this->notice( __( 'Return Data is not valid', 'aegis' ) );
		}

		return $output;
	}

	private function notice( string $message ): string {
		if ( ! current_user_can( 'manage_options' ) ) {
			return '';
		}

		return '<span class="aegis-snippet-shortcode-error">' . esc_html( $message ) . '</span>';
	}
}
