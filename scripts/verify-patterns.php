<?php
/**
 * Pattern registration verification.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$names = array(
	'aegis/slider-hero',
	'aegis/modal-video',
	'aegis/form-map',
);

$errors = 0;

foreach ( $names as $name ) {
	$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( $name );
	if ( ! is_array( $pattern ) || ! is_string( $pattern['title'] ?? null ) || '' === $pattern['title'] ) {
		echo $name . ': FAIL' . PHP_EOL;
		++$errors;
		continue;
	}
	echo $name . ': OK (' . $pattern['title'] . ')' . PHP_EOL;
}

exit( $errors > 0 ? 1 : 0 );
