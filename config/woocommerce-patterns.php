<?php
/**
 * WooCommerce and TI Wishlist block pattern registration.
 *
 * @package Aegis\Plugin
 * @since   1.0.0
 */

declare( strict_types=1 );

use Aegis\Plugin\Patterns\CommercePatternRegistrar;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

CommercePatternRegistrar::init();
