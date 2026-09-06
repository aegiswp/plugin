<?php
/**
 * Hooks feature bootstrap.
 *
 * @package Aegis\Plugin
 * @since   1.0.0
 */

declare( strict_types=1 );

use Aegis\Plugin\Hooks\AdminPage;
use Aegis\Plugin\Hooks\PatternsManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'init',
	static function (): void {
		( new PatternsManager() )->init();
		( new AdminPage() )->init();
	},
	10
);
