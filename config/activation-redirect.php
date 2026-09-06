<?php
/**
 * Redirect to the Aegis dashboard after companion onboarding activation.
 *
 * @package Aegis\Plugin
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const AEGIS_WELCOME_QUERY_ARG       = 'aegis_welcome';
const AEGIS_DASHBOARD_REDIRECT_KEY  = 'aegis_redirect_dashboard_';

add_action(
	'activated_plugin',
	static function ( string $plugin ): void {
		if ( $plugin !== 'aegis/aegis.php' ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( empty( $_GET[ AEGIS_WELCOME_QUERY_ARG ] ) ) {
			return;
		}

		set_transient(
			AEGIS_DASHBOARD_REDIRECT_KEY . get_current_user_id(),
			'1',
			5 * MINUTE_IN_SECONDS
		);
	},
	10,
	1
);

add_action(
	'admin_init',
	static function (): void {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return;
		}

		$transient_key = AEGIS_DASHBOARD_REDIRECT_KEY . $user_id;
		if ( ! get_transient( $transient_key ) ) {
			return;
		}

		delete_transient( $transient_key );

		wp_safe_redirect( admin_url( 'admin.php?page=aegis-dashboard' ) );
		exit;
	},
	1
);
