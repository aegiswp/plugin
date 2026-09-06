<?php
/**
 * Custom block registration bootstrap.
 *
 * @package Aegis\Plugin
 * @since   1.0.0
 */

declare( strict_types=1 );

use Aegis\Plugin\Blocks\Registrar;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

Registrar::init();
