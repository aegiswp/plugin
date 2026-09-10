<?php
/**
 * Syntax Highlighting Code Block helpers for detection and parent gating.
 *
 * @package Aegis\Plugin\Integrations
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Integrations;

use function defined;
use function function_exists;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Weston Ruter’s plugin defines Syntax_Highlighting_Code_Block\PLUGIN_VERSION
 * and boots via Syntax_Highlighting_Code_Block\boot(). It extends core/code
 * (no separate block name).
 */
final class SyntaxHighlighting {

	/**
	 * Whether Syntax Highlighting Code Block is loaded.
	 */
	public static function is_plugin_active(): bool {
		return defined( 'Syntax_Highlighting_Code_Block\\PLUGIN_VERSION' )
			|| function_exists( 'Syntax_Highlighting_Code_Block\\boot' );
	}

	/**
	 * Whether the Aegis Syntax Highlighting integration is on and the plugin is loaded.
	 */
	public static function is_enabled(): bool {
		return self::is_plugin_active()
			&& class_exists( Settings::class )
			&& Settings::is_integration_enabled( 'syntax_highlighting' );
	}
}
