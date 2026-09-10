<?php
/**
 * Map block and Google Maps settings configuration.
 *
 * @package Aegis\Plugin\Config
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Config;

use Aegis\Plugin\Map\Block;
use Aegis\Plugin\Map\IntegrationsPanel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$map_block = new Block();
$map_block->init();

( new IntegrationsPanel() )->init();

\Aegis\Plugin\Map\Settings::migrate_legacy_options();
