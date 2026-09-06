<?php
/**
 * Visibility Presets admin bootstrap.
 *
 * @package Aegis\Plugin
 * @since   1.0.0
 */

declare( strict_types=1 );

use Aegis\Plugin\VisibilityPresets\AdminPage;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'init',
	static function (): void {
		( new AdminPage() )->init();
	},
	10
);
