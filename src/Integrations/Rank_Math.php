<?php
/**
 * Rank_Math alias file for RankMath.
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

if ( ! class_exists( RankMath::class ) ) {
	require_once __DIR__ . '/RankMath.php';
}

if ( ! class_exists( __NAMESPACE__ . '\Rank_Math', false ) ) {
	class_alias( RankMath::class, __NAMESPACE__ . '\Rank_Math' );
}
