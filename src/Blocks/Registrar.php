<?php
/**
 * Registers plugin-owned block metadata collections.
 *
 * Map and Modal blocks are registered by Map\Block and Modal\Block.
 * Theme-owned blocks (countdown, slider, toggle, related-posts) register from the theme.
 *
 * @package Aegis\Plugin\Blocks
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Blocks;

use function add_action;
use function function_exists;
use function is_readable;
use function plugin_dir_path;
use function wp_register_block_metadata_collection;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers plugin block metadata for map and modal only.
 */
final class Registrar {

	/**
	 * Boot block metadata registration on init (priority 9, before Pro enhancements at 10).
	 */
	public static function init(): void {
		add_action( 'init', array( self::class, 'register' ), 9 );
	}

	/**
	 * Register metadata collection for plugin-owned block directories.
	 */
	public static function register(): void {
		if ( ! function_exists( 'wp_register_block_metadata_collection' ) ) {
			return;
		}

		$blocks_dir = plugin_dir_path( \Aegis\Plugin\FILE ) . 'src/Blocks';
		$manifest   = $blocks_dir . '/blocks-manifest.php';

		if ( is_readable( $manifest ) ) {
			wp_register_block_metadata_collection( $blocks_dir, $manifest );
		}
	}
}
