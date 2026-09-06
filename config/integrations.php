<?php
/**
 * Integrations feature bootstrap.
 *
 * @package Aegis\Plugin
 * @since   1.0.0
 */

declare( strict_types=1 );

use Aegis\Plugin\Integrations\AdminPage;
use Aegis\Plugin\Integrations\Notices;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

( new Notices() )->init();

// Theme AdminRenderer is not autoloaded until after_setup_theme.
add_action(
	'after_setup_theme',
	static function (): void {
		if ( ! class_exists( \Aegis\Plugin\Admin\Renderer::class ) ) {
			return;
		}

		$integrations_admin = new AdminPage();
		$integrations_admin->init();
	},
	20
);
