<?php
/**
 * Easy Digital Downloads helpers for detection and parent gating.
 *
 * @package Aegis\Plugin\Integrations
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Integrations;

use function class_exists;
use function defined;
use function function_exists;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * EDD free and Pro define Easy_Digital_Downloads, EDD(), and EDD_VERSION.
 */
final class EasyDigitalDownloads {

	/**
	 * Whether Easy Digital Downloads (free or Pro) is loaded.
	 */
	public static function is_plugin_active(): bool {
		return class_exists( 'Easy_Digital_Downloads' )
			|| function_exists( 'EDD' )
			|| defined( 'EDD_VERSION' );
	}

	/**
	 * Whether the Aegis EDD integration is on and the plugin is loaded.
	 */
	public static function is_enabled(): bool {
		return self::is_plugin_active()
			&& class_exists( Settings::class )
			&& Settings::is_integration_enabled( 'easy_digital_downloads' );
	}
}
