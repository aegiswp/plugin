<?php
/**
 * Conditionals feature bootstrap.
 *
 * @package Aegis\Plugin
 * @since   1.0.0
 */

declare( strict_types=1 );

use Aegis\Plugin\Conditionals\AdminPage;
use Aegis\Plugin\Conditionals\Capabilities;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

( new Capabilities() )->init();

// Theme AdminRenderer is not autoloaded until after_setup_theme.
add_action(
	'after_setup_theme',
	static function (): void {
		if ( ! class_exists( \Aegis\Plugin\Admin\Renderer::class ) ) {
			return;
		}

		$conditionals_admin = new AdminPage();
		$conditionals_admin->init();
	},
	20
);
