<?php
/**
 * Performance feature bootstrap.
 *
 * @package Aegis\Plugin
 * @since   1.0.0
 */

declare( strict_types=1 );

use Aegis\Plugin\Blocks\Settings;
use Aegis\Plugin\Performance\AdminPage;
use Aegis\Plugin\Performance\FrontendOptimizations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

( new FrontendOptimizations() )->init();

add_action(
	'after_setup_theme',
	static function (): void {
		if ( ! class_exists( \Aegis\Plugin\Admin\Renderer::class ) ) {
			return;
		}

		( new AdminPage() )->init();
	},
	20
);

add_filter(
	'aegis_editor_data',
	static function ( array $config ): array {
		$config['blockFeatures'] = array_merge(
			$config['blockFeatures'] ?? [],
			[
				'queryLoopPerformance' => Settings::is_enabled( 'query_loop_performance' ),
			]
		);

		return $config;
	}
);
