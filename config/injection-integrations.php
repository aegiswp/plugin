<?php
/**
 * Integration injection hook firing.
 *
 * @package Aegis\Plugin
 * @since   1.0.0
 */

declare( strict_types=1 );

use Aegis\Plugin\Injection\IntegrationInjector;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'init',
	static function (): void {
		( new IntegrationInjector() )->init();
	},
	20
);
