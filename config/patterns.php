<?php
/**
 * Plugin block patterns that depend on aegis/* blocks.
 *
 * @package Aegis\Plugin
 * @since   1.0.0
 */

declare( strict_types=1 );

use Aegis\Framework\ServiceProvider;
use Aegis\Plugin\Patterns\FileRegistrar;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'init',
	static function (): void {
		$pattern_dir = \Aegis\Plugin\DIR . 'patterns/';

		if ( ! is_dir( $pattern_dir ) ) {
			return;
		}

		$categories = array(
			'modal'   => __( 'Modal', 'aegis' ),
			'contact' => __( 'Contact', 'aegis' ),
		);

		if ( ServiceProvider::is_block_enabled( 'slider' ) ) {
			$categories['slider'] = __( 'Slider', 'aegis' );
		}

		foreach ( $categories as $slug => $label ) {
			if ( ! \WP_Block_Pattern_Categories_Registry::get_instance()->is_registered( $slug ) ) {
				register_block_pattern_category(
					$slug,
					array(
						'label' => $label,
					)
				);
			}
		}

		$patterns = array();

		if ( ServiceProvider::is_block_enabled( 'slider' ) ) {
			$patterns['aegis/slider-hero']         = $pattern_dir . 'slider/hero.php';
			$patterns['aegis/slider-features']     = $pattern_dir . 'slider/features.php';
			$patterns['aegis/slider-portfolio']    = $pattern_dir . 'slider/portfolio.php';
			$patterns['aegis/slider-team']         = $pattern_dir . 'slider/team.php';
			$patterns['aegis/slider-testimonials'] = $pattern_dir . 'slider/testimonials.php';
		}

		if ( ServiceProvider::is_block_enabled( 'map' ) ) {
			$patterns['aegis/form-map']         = $pattern_dir . 'contact/form-map.php';
			$patterns['aegis/form-map-overlay'] = $pattern_dir . 'contact/form-map-overlay.php';
		}

		if ( ServiceProvider::is_block_enabled( 'modal' ) ) {
			$patterns['aegis/modal-video']          = $pattern_dir . 'modal/modal-video.php';
			$patterns['aegis/modal-newsletter']     = $pattern_dir . 'modal/modal-newsletter.php';
			$patterns['aegis/modal-contact']        = $pattern_dir . 'modal/modal-contact.php';
			$patterns['aegis/modal-cookie-consent'] = $pattern_dir . 'modal/modal-cookie-consent.php';
		}

		foreach ( $patterns as $name => $file ) {
			FileRegistrar::register( $name, $file );
		}
	}
);
