<?php
/**
 * Verify Comments removal and AJAX dashboard fragments.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$users = get_users(
	array(
		'role'   => 'administrator',
		'number' => 1,
	)
);

if ( $users ) {
	wp_set_current_user( $users[0]->ID );
}

$loader = new \Aegis\Plugin\Admin\PageLoader( new \Aegis\Plugin\Admin\Renderer() );

$checks = array(
	'Comments Manager gone'   => ! class_exists( 'Aegis\\Plugin\\Comments\\Manager' ),
	'Comments AdminPage gone' => ! class_exists( 'Aegis\\Plugin\\Comments\\AdminPage' ),
	'no comments tab'         => ! isset( apply_filters( 'aegis_admin_tabs', array() )['comments'] ),
	'admin user'              => current_user_can( 'manage_options' ),
);

$pages = array(
	'dashboard' => admin_url( 'admin.php?page=aegis-dashboard' ),
	'blocks'    => admin_url( 'admin.php?page=aegis-blocks' ),
	'snippets'  => admin_url( 'admin.php?page=aegis-snippets&tab=settings' ),
);

foreach ( $pages as $label => $url ) {
	$fragment = $loader->load_fragment( $url );
	$ok       = is_array( $fragment )
		&& ! empty( $fragment['content'] )
		&& str_contains( (string) $fragment['content'], 'aegis-admin-page' )
		&& ! str_contains( (string) $fragment['content'], '<html' )
		&& ! str_contains( (string) $fragment['topBar'], 'Comments' )
		&& ! str_contains( (string) $fragment['content'], 'href="#comments"' );

	$checks[ $label . ' fragment' ] = $ok;
}

foreach ( $checks as $label => $ok ) {
	printf( "%s %s\n", $ok ? 'OK' : 'FAIL', $label );
}

$failed = array_filter(
	$checks,
	static function ( $ok ) {
		return ! $ok;
	}
);

echo empty( $failed ) ? "ALL_OK\n" : "FAILED\n";
