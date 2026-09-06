<?php
/**
 * Blocks settings feature bootstrap.
 *
 * @package Aegis\Plugin
 * @since   1.0.0
 */

declare( strict_types=1 );

use Aegis\Plugin\Blocks\AdminPage;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Theme AdminRenderer is not autoloaded until after_setup_theme.
add_action(
	'after_setup_theme',
	static function (): void {
		if ( ! class_exists( \Aegis\Plugin\Admin\Renderer::class ) ) {
			return;
		}

		$blocks_admin = new AdminPage();
		$blocks_admin->init();
	},
	20
);
