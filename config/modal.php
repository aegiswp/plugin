<?php
/**
 * Modal block configuration.
 *
 * @package Aegis\Plugin
 * @since   1.0.0
 */

declare( strict_types=1 );

use Aegis\Plugin\Modal\AdminPage;
use Aegis\Plugin\Modal\Block;
use Aegis\Plugin\Modal\InstanceRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$modal_block = new Block();
$modal_block->init();
InstanceRepository::init();

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
