<?php
/**
 * Shared [aegis_modal id="…"] shortcode.
 *
 * @package Aegis\Plugin\Modal
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Modal;

use Aegis\Framework\ServiceProvider;
use function __;
use function add_action;
use function add_shortcode;
use function current_user_can;
use function esc_html;
use function is_array;
use function render_block;
use function sanitize_text_field;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders a stored Modal block by modalId outside its host post.
 */
final class Shortcode {

	public const TAG = 'aegis_modal';

	/**
	 * Modal IDs currently rendering (guards recursive shortcodes).
	 *
	 * @var array<string, true>
	 */
	private static array $rendering = array();

	public static function init(): void {
		add_action( 'init', array( self::class, 'register' ), 0 );
	}

	public static function register(): void {
		add_shortcode( self::TAG, array( new self(), 'render' ) );
	}

	public static function markup( string $modal_id ): string {
		$modal_id = trim( $modal_id );

		if ( $modal_id === '' ) {
			$modal_id = 'modal';
		}

		return '[' . self::TAG . ' id="' . $modal_id . '"]';
	}

	/**
	 * @param array<string, string>|string $atts Shortcode attributes.
	 */
	public function render( $atts ): string {
		$atts     = is_array( $atts ) ? $atts : array();
		$modal_id = sanitize_text_field( (string) ( $atts['id'] ?? '' ) );

		if ( $modal_id === '' ) {
			return $this->notice( __( 'Modal not found', 'aegis' ) );
		}

		if ( isset( self::$rendering[ $modal_id ] ) ) {
			return '';
		}

		if ( ! ServiceProvider::is_block_enabled( 'modal' ) ) {
			return $this->notice( __( 'Modal block is disabled', 'aegis' ) );
		}

		$found = InstanceRepository::find_block_by_modal_id( $modal_id );

		if ( $found === null ) {
			return $this->notice( __( 'Modal not found', 'aegis' ) );
		}

		if ( empty( $found['enabled'] ) ) {
			return $this->notice( __( 'Modal is disabled', 'aegis' ) );
		}

		$block = $found['block'];

		if ( ! is_array( $block ) ) {
			return $this->notice( __( 'Modal not found', 'aegis' ) );
		}

		self::$rendering[ $modal_id ] = true;

		try {
			$html = render_block( $block );
		} finally {
			unset( self::$rendering[ $modal_id ] );
		}

		return is_string( $html ) ? $html : '';
	}

	private function notice( string $message ): string {
		if ( ! current_user_can( 'manage_options' ) ) {
			return '';
		}

		return '<span class="aegis-modal-shortcode-error">' . esc_html( $message ) . '</span>';
	}
}
