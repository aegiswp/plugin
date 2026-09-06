<?php
/**
 * Quick block registration verification (studio wp eval-file).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$checks = array(
	'Registrar class' => class_exists( 'Aegis\\Plugin\\Blocks\\Registrar' ),
	'Renderer class'  => class_exists( 'Aegis\\Plugin\\Admin\\Renderer' ),
	'Evaluator class' => class_exists( 'Aegis\\Plugin\\Conditionals\\Evaluator' ),
);

$theme_blocks = array(
	'aegis/countdown',
	'aegis/related-posts',
	'aegis/slider',
	'aegis/slide',
	'aegis/toggle',
	'aegis/toggle-content',
);

$plugin_blocks = array(
	'aegis/map',
	'aegis/modal',
);

$registry = WP_Block_Type_Registry::get_instance();

foreach ( $theme_blocks as $block ) {
	$checks[ 'theme block ' . $block ] = $registry->is_registered( $block );
}

foreach ( $plugin_blocks as $block ) {
	$checks[ 'plugin block ' . $block ] = $registry->is_registered( $block );
}

foreach ( $checks as $label => $ok ) {
	echo $label . ': ' . ( $ok ? 'OK' : 'FAIL' ) . PHP_EOL;
}
