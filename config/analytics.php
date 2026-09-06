<?php
/**
 * Analytics feature bootstrap.
 *
 * @package Aegis\Plugin
 * @since   1.0.0
 */

declare( strict_types=1 );

use Aegis\Plugin\Analytics\IntegrationsPanel;
use Aegis\Plugin\Analytics\Tracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tracker = new Tracker();
$tracker->init();

$integrations = new IntegrationsPanel();
$integrations->init();
