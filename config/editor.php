<?php
/**
 * Block editor extensions bootstrap.
 *
 * @package Aegis\Plugin
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'enqueue_block_editor_assets',
	static function (): void {
		$asset_file = \Aegis\Plugin\DIR . 'assets/editor/build/video-editor.tsx.asset.php';

		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset  = require $asset_file;
		$handle = 'aegis-video-editor';

		wp_register_script(
			$handle,
			plugins_url( 'assets/editor/build/video-editor.tsx.js', \Aegis\Plugin\FILE ),
			$asset['dependencies'] ?? array(),
			$asset['version'] ?? \Aegis\Plugin\VERSION,
			true
		);

		wp_enqueue_script( $handle );

		wp_localize_script(
			$handle,
			'aegisVideo',
			array(
				'isPro' => class_exists( 'AegisPro\\Video\\BunnyCDN' ),
			)
		);

		wp_set_script_translations( $handle, 'aegis' );
	},
	20
);

// Load selector CSS inside the iframed canvas (enqueue_block_editor_assets does not).
add_action(
	'enqueue_block_assets',
	static function (): void {
		if ( ! is_admin() ) {
			return;
		}

		$css_file = \Aegis\Plugin\DIR . 'assets/editor/build/video-editor.tsx.css';

		if ( ! file_exists( $css_file ) ) {
			return;
		}

		$asset_file = \Aegis\Plugin\DIR . 'assets/editor/build/video-editor.tsx.asset.php';
		$version    = \Aegis\Plugin\VERSION;

		if ( file_exists( $asset_file ) ) {
			$asset   = require $asset_file;
			$version = $asset['version'] ?? $version;
		}

		wp_enqueue_style(
			'aegis-video-editor',
			plugins_url( 'assets/editor/build/video-editor.tsx.css', \Aegis\Plugin\FILE ),
			array(),
			$version
		);
	},
	20
);
