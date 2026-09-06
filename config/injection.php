<?php
/**
 * Injection location registry bootstrap.
 *
 * @package Aegis\Plugin
 * @since   1.0.0
 */

declare( strict_types=1 );

use Aegis\Plugin\Injection\LocationRegistry;
use Aegis\Plugin\Injection\Preview;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'save_post_wp_template_part',
	static function (): void {
		LocationRegistry::flush_cache();
	}
);

add_action(
	'init',
	static function (): void {
		( new Preview() )->init();
	},
	15
);

require_once \Aegis\Plugin\DIR . 'config/injection-integrations.php';
