<?php
/**
 * Modal block registration.
 *
 * @package Aegis\Plugin\Modal
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Modal;

use Aegis\Framework\ServiceProvider;
use function add_action;
use function current_user_can;
use function file_exists;
use function plugin_dir_path;
use function register_block_type;
use function wp_localize_script;
use function wp_script_is;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers aegis/modal from the plugin.
 */
final class Block {

	/**
	 * Absolute path to the modal block directory.
	 */
	private function block_path(): string {
		return plugin_dir_path( \Aegis\Plugin\FILE ) . 'src/Blocks/modal';
	}

	/**
	 * Register hooks.
	 */
	public function init(): void {
		add_action( 'init', array( $this, 'register_block' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_data' ) );
	}

	/**
	 * Register the modal block with WordPress.
	 */
	public function register_block(): void {
		if ( ! ServiceProvider::is_block_enabled( 'modal' ) ) {
			return;
		}

		$block_path = $this->block_path();

		if ( ! file_exists( $block_path . '/block.json' ) ) {
			return;
		}

		register_block_type( $block_path );
	}

	/**
	 * Enqueue editor data for the modal block.
	 */
	public function enqueue_editor_data(): void {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		$handle = 'aegis-modal-editor-script';

		if ( ! wp_script_is( $handle, 'registered' ) ) {
			return;
		}

		wp_localize_script(
			$handle,
			'aegisModalEditor',
			array(
				'features' => array(
					'click'             => ServiceProvider::is_block_enabled( 'modal_click' ),
					'icon'              => ServiceProvider::is_block_enabled( 'modal_icon' ),
					'text'              => ServiceProvider::is_block_enabled( 'modal_text' ),
					'image'             => ServiceProvider::is_block_enabled( 'modal_image' ),
					'offcanvas'         => ServiceProvider::is_block_enabled( 'modal_offcanvas' ),
					'fullscreen'        => ServiceProvider::is_block_enabled( 'modal_fullscreen' ),
					'animations'        => ServiceProvider::is_block_enabled( 'modal_animations' ),
					'exitIntent'        => ServiceProvider::is_block_enabled( 'modal_exit_intent' ),
					'scrollDepth'       => ServiceProvider::is_block_enabled( 'modal_scroll_depth' ),
					'timeDelay'         => ServiceProvider::is_block_enabled( 'modal_time_delay' ),
					'autoClose'         => ServiceProvider::is_block_enabled( 'modal_auto_close' ),
					'showOnce'          => ServiceProvider::is_block_enabled( 'modal_show_once' ),
					'deviceVisibility'  => ServiceProvider::is_block_enabled( 'modal_device_visibility' ),
				),
			)
		);
	}
}
