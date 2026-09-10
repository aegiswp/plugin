<?php
/**
 * Integration-specific injection hook definitions.
 *
 * @package Aegis\Plugin\Injection
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Injection;

use Aegis\Plugin\Integrations\Registry;
use Aegis\Plugin\Settings\Repository;
use function __;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Maps integration keys to injectable hook locations.
 */
final class IntegrationLocations {

	/**
	 * All integration hook definitions (before gating).
	 *
	 * @return array<string, array{label: string, scope: string, requires: string|null}>
	 */
	public static function get_definitions(): array {
		return array(
			'aegis_before_woocommerce_checkout' => array(
				'label'    => __( 'Before WooCommerce checkout', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'woocommerce',
			),
			'aegis_after_woocommerce_checkout' => array(
				'label'    => __( 'After WooCommerce checkout', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'woocommerce',
			),
			'aegis_before_woocommerce_cart' => array(
				'label'    => __( 'Before WooCommerce cart', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'woocommerce',
			),
			'aegis_after_woocommerce_cart' => array(
				'label'    => __( 'After WooCommerce cart', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'woocommerce',
			),
			'aegis_before_edd_download' => array(
				'label'    => __( 'Before EDD download', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'easy_digital_downloads',
			),
			'aegis_after_edd_download' => array(
				'label'    => __( 'After EDD download', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'easy_digital_downloads',
			),
			'aegis_before_affwp_dashboard' => array(
				'label'    => __( 'Before AffiliateWP dashboard', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'affiliate_wp',
			),
			'aegis_after_affwp_dashboard' => array(
				'label'    => __( 'After AffiliateWP dashboard', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'affiliate_wp',
			),
			'aegis_before_learndash_course' => array(
				'label'    => __( 'Before LearnDash course', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'learndash',
			),
			'aegis_after_learndash_course' => array(
				'label'    => __( 'After LearnDash course', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'learndash',
			),
			'aegis_before_learndash_lesson' => array(
				'label'    => __( 'Before LearnDash lesson', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'learndash',
			),
			'aegis_after_learndash_lesson' => array(
				'label'    => __( 'After LearnDash lesson', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'learndash',
			),
			'aegis_before_learndash_topic' => array(
				'label'    => __( 'Before LearnDash topic', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'learndash',
			),
			'aegis_after_learndash_topic' => array(
				'label'    => __( 'After LearnDash topic', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'learndash',
			),
			'aegis_before_learndash_quiz' => array(
				'label'    => __( 'Before LearnDash quiz', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'learndash',
			),
			'aegis_after_learndash_quiz' => array(
				'label'    => __( 'After LearnDash quiz', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'learndash',
			),
			'aegis_learndash_focus_header' => array(
				'label'    => __( 'LearnDash Focus Mode header', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'learndash',
			),
			'aegis_learndash_focus_footer' => array(
				'label'    => __( 'LearnDash Focus Mode footer', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'learndash',
			),
			'aegis_before_llms_course' => array(
				'label'    => __( 'Before LifterLMS course', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'lifter_lms',
			),
			'aegis_after_llms_course' => array(
				'label'    => __( 'After LifterLMS course', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'lifter_lms',
			),
			'aegis_before_llms_lesson' => array(
				'label'    => __( 'Before LifterLMS lesson', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'lifter_lms',
			),
			'aegis_after_llms_lesson' => array(
				'label'    => __( 'After LifterLMS lesson', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'lifter_lms',
			),
			'aegis_before_sensei_course' => array(
				'label'    => __( 'Before Sensei course', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'sensei_lms',
			),
			'aegis_after_sensei_course' => array(
				'label'    => __( 'After Sensei course', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'sensei_lms',
			),
			'aegis_before_sensei_lesson' => array(
				'label'    => __( 'Before Sensei lesson', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'sensei_lms',
			),
			'aegis_after_sensei_lesson' => array(
				'label'    => __( 'After Sensei lesson', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'sensei_lms',
			),
			'aegis_before_sensei_quiz' => array(
				'label'    => __( 'Before Sensei quiz', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'sensei_lms',
			),
			'aegis_after_sensei_quiz' => array(
				'label'    => __( 'After Sensei quiz', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'sensei_lms',
			),
			'aegis_before_fluentform' => array(
				'label'    => __( 'Before Fluent Form', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'fluent_forms',
			),
			'aegis_after_fluentform' => array(
				'label'    => __( 'After Fluent Form', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'fluent_forms',
			),
			'aegis_before_gform' => array(
				'label'    => __( 'Before Gravity Form', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'gravity_forms',
			),
			'aegis_after_gform' => array(
				'label'    => __( 'After Gravity Form', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'gravity_forms',
			),
			'aegis_before_nf_form' => array(
				'label'    => __( 'Before Ninja Form', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'ninja_forms',
			),
			'aegis_after_nf_form' => array(
				'label'    => __( 'After Ninja Form', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'ninja_forms',
			),
			'aegis_before_bbpress_forum' => array(
				'label'    => __( 'Before bbPress forum', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'bbpress',
			),
			'aegis_after_bbpress_forum' => array(
				'label'    => __( 'After bbPress forum', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'bbpress',
			),
			'aegis_before_bbpress_topic' => array(
				'label'    => __( 'Before bbPress topic', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'bbpress',
			),
			'aegis_after_bbpress_topic' => array(
				'label'    => __( 'After bbPress topic', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'bbpress',
			),
			'aegis_before_post_author' => array(
				'label'    => __( 'Before post author / Co-Authors block', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'co_authors_plus',
			),
			'aegis_after_post_author' => array(
				'label'    => __( 'After post author / Co-Authors block', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'co_authors_plus',
			),
			'aegis_before_map_block' => array(
				'label'    => __( 'Before map block', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'google_maps',
			),
			'aegis_after_map_block' => array(
				'label'    => __( 'After map block', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'google_maps',
			),
			'aegis_before_video_block' => array(
				'label'    => __( 'Before video block', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'bunny_cdn',
			),
			'aegis_after_video_block' => array(
				'label'    => __( 'After video block', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => 'bunny_cdn',
			),
		);
	}

	/**
	 * Integration hooks available for the current site configuration.
	 *
	 * @return array<string, array{label: string, scope: string, requires: string|null}>
	 */
	public static function get_available(): array {
		$available = array();

		foreach ( self::get_definitions() as $hook => $meta ) {
			$key = $meta['requires'] ?? null;

			if ( $key === null ) {
				$available[ $hook ] = $meta;
				continue;
			}

			if ( Registry::is_plugin_active( $key ) && Repository::is_integration_enabled( $key ) ) {
				$available[ $hook ] = $meta;
			}
		}

		return $available;
	}
}
