<?php
/**
 * Code Snippets feature bootstrap.
 *
 * @package Aegis\Plugin
 * @since   1.0.0
 */

declare( strict_types=1 );

use Aegis\Plugin\Snippets\Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

( new Manager() )->init();
