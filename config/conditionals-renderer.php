<?php
/**
 * Post-content conditional visibility bootstrap.
 *
 * @package Aegis\Plugin
 * @since   1.0.0
 */

declare( strict_types=1 );

use Aegis\Plugin\Conditionals\PostContentRenderer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'template_redirect',
	static function (): void {
		( new PostContentRenderer() )->init();
	}
);
