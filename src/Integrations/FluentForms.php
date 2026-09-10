<?php
/**
 * Fluent Forms helpers for detection and parent gating.
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
 * Fluent Forms defines FLUENTFORM, FLUENTFORM_VERSION, or FluentForm\App\Modules\Form\Form.
 */
final class FluentForms {

	/**
	 * Whether Fluent Forms (free or Pro) is loaded.
	 */
	public static function is_plugin_active(): bool {
		return defined( 'FLUENTFORM' )
			|| defined( 'FLUENTFORM_VERSION' )
			|| class_exists( 'FluentForm\\App\\Modules\\Form\\Form' )
			|| function_exists( 'wpFluentForm' );
	}

	/**
	 * Whether the Aegis Fluent Forms integration is on and the plugin is loaded.
	 */
	public static function is_enabled(): bool {
		return self::is_plugin_active()
			&& class_exists( Settings::class )
			&& Settings::is_integration_enabled( 'fluent_forms' );
	}
}
