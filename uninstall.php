<?php
/**
 * Aegis Plugin — Uninstall
 *
 * Removes plugin data only when the site opted in under Aegis → Settings.
 *
 * @package Aegis\Plugin
 * @since   1.0.0
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

require_once __DIR__ . '/src/autoload.php';

use Aegis\Plugin\Uninstall;

$purge = static function (): void {
	if ( ! Uninstall::should_remove_data() ) {
		return;
	}

	Uninstall::purge();
};

if ( is_multisite() ) {
	$site_ids = get_sites( array( 'fields' => 'ids' ) );

	foreach ( $site_ids as $site_id ) {
		switch_to_blog( (int) $site_id );
		$purge();
		restore_current_blog();
	}

	return;
}

$purge();
