<?php
/**
 * Code Block Pro helpers for detection and parent gating.
 *
 * @package Aegis\Plugin\Integrations
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Integrations;

use WP_Block_Type_Registry;
use function class_exists;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Kevin Batdorf Code Block Pro does not define CODE_BLOCK_PRO_VERSION.
 * It loads `CBPRouter` immediately and registers `kevinbatdorf/code-block-pro` on init.
 */
final class CodeBlockPro {

	/**
	 * Whether Code Block Pro is loaded.
	 */
	public static function is_plugin_active(): bool {
		if ( class_exists( 'CBPRouter' ) ) {
			return true;
		}

		return class_exists( WP_Block_Type_Registry::class )
			&& WP_Block_Type_Registry::get_instance()->is_registered( 'kevinbatdorf/code-block-pro' );
	}

	/**
	 * Whether the Aegis Code Block Pro integration is on and the plugin is loaded.
	 */
	public static function is_enabled(): bool {
		return self::is_plugin_active()
			&& class_exists( Settings::class )
			&& Settings::is_integration_enabled( 'code_block_pro' );
	}
}
