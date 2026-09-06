<?php
/**
 * Admin dashboard shell bootstrap (menu + shared renderer).
 *
 * @package Aegis\Plugin
 * @since   1.0.0
 */

declare( strict_types=1 );

use Aegis\Plugin\Admin\Menu;
use Aegis\Plugin\Admin\Renderer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'after_setup_theme',
	static function (): void {
		if ( ! class_exists( Renderer::class ) ) {
			return;
		}

		$renderer = new Renderer();
		( new Menu( $renderer ) )->init();
	},
	15
);
