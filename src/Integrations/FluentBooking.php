<?php
/**
 * Fluent Booking helpers for detection and parent gating.
 *
 * @package Aegis\Plugin\Integrations
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Integrations;

use function class_exists;
use function defined;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fluent Booking defines FLUENT_BOOKING_VERSION and FluentBooking\App\App.
 */
final class FluentBooking {

	/**
	 * Whether Fluent Booking is loaded.
	 */
	public static function is_plugin_active(): bool {
		return defined( 'FLUENT_BOOKING_VERSION' )
			|| class_exists( 'FluentBooking\\App\\App' );
	}

	/**
	 * Whether the Aegis Fluent Booking integration is on and the plugin is loaded.
	 */
	public static function is_enabled(): bool {
		return self::is_plugin_active()
			&& class_exists( Settings::class )
			&& Settings::is_integration_enabled( 'fluent_booking' );
	}
}
