<?php
/**
 * AIOSEO alias file for AllInOneSEO.
 *
 * @package Aegis\Plugin\Integrations
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Integrations;

use function class_alias;
use function class_exists;
use function defined;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( AllInOneSEO::class ) ) {
	require_once __DIR__ . '/AllInOneSEO.php';
}

if ( ! class_exists( __NAMESPACE__ . '\AIOSEO', false ) ) {
	class_alias( AllInOneSEO::class, __NAMESPACE__ . '\AIOSEO' );
}
