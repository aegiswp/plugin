<?php
/**
 * Plugin condition toggles on Aegis → Integrations.
 *
 * @package Aegis\Plugin\Conditionals
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Conditionals;

use Aegis\Plugin\Integrations\Settings as IntegrationsSettings;
use function __;
use function class_exists;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders block-condition sub-toggles on each plugin Integrations tab.
 */
final class IntegrationsPanel {

	/**
	 * Output condition sub-toggles for one Integrations plugin.
	 *
	 * @param \Aegis\Plugin\Admin\Renderer $renderer         Shared admin renderer.
	 * @param string                       $integration_key Registry key.
	 */
	public static function render( \Aegis\Plugin\Admin\Renderer $renderer, string $integration_key ): void {
		$toggles = self::toggles_for( $integration_key );

		if ( $toggles === array() ) {
			return;
		}

		$options   = Settings::get_settings();
		$parent_on = class_exists( IntegrationsSettings::class ) && IntegrationsSettings::is_integration_enabled( $integration_key );

		foreach ( $toggles as $toggle ) {
			$renderer->render_toggle(
				$toggle['group'],
				$toggle['key'],
				$toggle['label'],
				$toggle['desc'],
				$options,
				$toggle['icon'],
				Settings::OPTION,
				'',
				! $parent_on
			);
		}
	}

	/**
	 * Condition toggles that belong with a given Integrations plugin.
	 *
	 * @return array<int, array{group: string, key: string, label: string, desc: string, icon: string}>
	 */
	private static function toggles_for( string $integration_key ): array {
		$map = self::toggles_by_plugin();

		return $map[ $integration_key ] ?? array();
	}

	/**
	 * @return array<string, array<int, array{group: string, key: string, label: string, desc: string, icon: string}>>
	 */
	private static function toggles_by_plugin(): array {
		return array(
			'woocommerce'            => array(
				array(
					'group' => 'woocommerce',
					'key'   => 'woo_cart',
					'label' => __( 'Cart Conditions', 'aegis' ),
					'desc'  => __( 'Show or hide blocks based on cart contents, total value, item count, and product quantities.', 'aegis' ),
					'icon'  => 'cart',
				),
				array(
					'group' => 'woocommerce',
					'key'   => 'woo_customer',
					'label' => __( 'Customer History', 'aegis' ),
					'desc'  => __( 'Show or hide blocks based on lifetime spend, order count, purchase history, and recency.', 'aegis' ),
					'icon'  => 'businessperson',
				),
				array(
					'group' => 'woocommerce',
					'key'   => 'woo_product',
					'label' => __( 'Product Context', 'aegis' ),
					'desc'  => __( 'Show or hide blocks based on current product stock status, quantity, sale, or featured state.', 'aegis' ),
					'icon'  => 'products',
				),
			),
			'easy_digital_downloads' => array(
				array(
					'group' => 'edd',
					'key'   => 'edd_cart',
					'label' => __( 'Cart Conditions', 'aegis' ),
					'desc'  => __( 'Show or hide blocks based on cart contents and totals.', 'aegis' ),
					'icon'  => 'cart',
				),
				array(
					'group' => 'edd',
					'key'   => 'edd_customer',
					'label' => __( 'Customer History', 'aegis' ),
					'desc'  => __( 'Show or hide blocks based on purchase history and lifetime value.', 'aegis' ),
					'icon'  => 'businessperson',
				),
				array(
					'group' => 'edd',
					'key'   => 'edd_download',
					'label' => __( 'Download Context', 'aegis' ),
					'desc'  => __( 'Show or hide blocks on single download pages.', 'aegis' ),
					'icon'  => 'download',
				),
			),
			'affiliate_wp'           => array(
				array(
					'group' => 'affiliate_wp',
					'key'   => 'affwp_referral',
					'label' => __( 'Referral Visit', 'aegis' ),
					'desc'  => __( 'Show or hide blocks based on whether the visitor was referred by an affiliate.', 'aegis' ),
					'icon'  => 'randomize',
				),
				array(
					'group' => 'affiliate_wp',
					'key'   => 'affwp_affiliate',
					'label' => __( 'Affiliate Account', 'aegis' ),
					'desc'  => __( 'Show or hide blocks based on whether the logged-in user is an affiliate and their status.', 'aegis' ),
					'icon'  => 'id',
				),
				array(
					'group' => 'affiliate_wp',
					'key'   => 'affwp_earnings',
					'label' => __( 'Earnings', 'aegis' ),
					'desc'  => __( 'Show or hide blocks based on affiliate lifetime earnings, unpaid earnings, or referral count.', 'aegis' ),
					'icon'  => 'chart-bar',
				),
			),
			'learndash'              => array(
				array(
					'group' => 'learndash',
					'key'   => 'ld_enrollment',
					'label' => __( 'Enrollment', 'aegis' ),
					'desc'  => __( 'Show or hide blocks based on course or lesson enrollment status.', 'aegis' ),
					'icon'  => 'welcome-learn-more',
				),
				array(
					'group' => 'learndash',
					'key'   => 'ld_completion',
					'label' => __( 'Completion', 'aegis' ),
					'desc'  => __( 'Show or hide blocks based on course or lesson completion status.', 'aegis' ),
					'icon'  => 'yes-alt',
				),
				array(
					'group' => 'learndash',
					'key'   => 'ld_progress',
					'label' => __( 'Course Progress', 'aegis' ),
					'desc'  => __( 'Show or hide blocks based on course progress percentage.', 'aegis' ),
					'icon'  => 'chart-bar',
				),
				array(
					'group' => 'learndash',
					'key'   => 'ld_quiz',
					'label' => __( 'Quiz Results', 'aegis' ),
					'desc'  => __( 'Show or hide blocks based on quiz pass/fail status or score.', 'aegis' ),
					'icon'  => 'forms',
				),
				array(
					'group' => 'learndash',
					'key'   => 'ld_group',
					'label' => __( 'Group Membership', 'aegis' ),
					'desc'  => __( 'Show or hide blocks based on LearnDash group membership.', 'aegis' ),
					'icon'  => 'groups',
				),
			),
			'lifter_lms'             => array(
				array(
					'group' => 'lifterlms',
					'key'   => 'llms_enrollment',
					'label' => __( 'Enrollment', 'aegis' ),
					'desc'  => __( 'Show or hide blocks based on course enrollment status.', 'aegis' ),
					'icon'  => 'welcome-learn-more',
				),
				array(
					'group' => 'lifterlms',
					'key'   => 'llms_completion',
					'label' => __( 'Completion', 'aegis' ),
					'desc'  => __( 'Show or hide blocks based on course or lesson completion.', 'aegis' ),
					'icon'  => 'yes-alt',
				),
				array(
					'group' => 'lifterlms',
					'key'   => 'llms_progress',
					'label' => __( 'Course Progress', 'aegis' ),
					'desc'  => __( 'Show or hide blocks based on course progress percentage.', 'aegis' ),
					'icon'  => 'chart-bar',
				),
				array(
					'group' => 'lifterlms',
					'key'   => 'llms_membership',
					'label' => __( 'Membership', 'aegis' ),
					'desc'  => __( 'Show or hide blocks based on membership plan enrollment.', 'aegis' ),
					'icon'  => 'id',
				),
			),
			'sensei_lms'             => array(
				array(
					'group' => 'sensei',
					'key'   => 'sensei_enrollment',
					'label' => __( 'Enrollment', 'aegis' ),
					'desc'  => __( 'Show or hide blocks based on course enrollment status.', 'aegis' ),
					'icon'  => 'welcome-learn-more',
				),
				array(
					'group' => 'sensei',
					'key'   => 'sensei_completion',
					'label' => __( 'Completion', 'aegis' ),
					'desc'  => __( 'Show or hide blocks based on course or lesson completion.', 'aegis' ),
					'icon'  => 'yes-alt',
				),
				array(
					'group' => 'sensei',
					'key'   => 'sensei_progress',
					'label' => __( 'Course Progress', 'aegis' ),
					'desc'  => __( 'Show or hide blocks based on course progress percentage.', 'aegis' ),
					'icon'  => 'chart-bar',
				),
				array(
					'group' => 'sensei',
					'key'   => 'sensei_quiz',
					'label' => __( 'Quiz Grade', 'aegis' ),
					'desc'  => __( 'Show or hide blocks based on quiz grade for a specific lesson.', 'aegis' ),
					'icon'  => 'forms',
				),
			),
			'fluent_forms'           => array(
				array(
					'group' => 'fluentforms',
					'key'   => 'ff_submitted',
					'label' => __( 'Has Submitted', 'aegis' ),
					'desc'  => __( 'Show or hide blocks based on whether the user has submitted a specific form.', 'aegis' ),
					'icon'  => 'yes-alt',
				),
				array(
					'group' => 'fluentforms',
					'key'   => 'ff_count',
					'label' => __( 'Submission Count', 'aegis' ),
					'desc'  => __( 'Show or hide blocks based on the number of submissions for a form.', 'aegis' ),
					'icon'  => 'chart-bar',
				),
				array(
					'group' => 'fluentforms',
					'key'   => 'ff_field',
					'label' => __( 'Field Value', 'aegis' ),
					'desc'  => __( 'Show or hide blocks based on a specific field value in a submission.', 'aegis' ),
					'icon'  => 'editor-table',
				),
			),
			'fluent_booking'         => array(
				array(
					'group' => 'fluentbooking',
					'key'   => 'fb_has_booking',
					'label' => __( 'Has Booking', 'aegis' ),
					'desc'  => __( 'Show or hide blocks based on whether the user has any booking.', 'aegis' ),
					'icon'  => 'calendar-alt',
				),
				array(
					'group' => 'fluentbooking',
					'key'   => 'fb_upcoming',
					'label' => __( 'Upcoming Count', 'aegis' ),
					'desc'  => __( 'Show or hide blocks based on the number of upcoming bookings.', 'aegis' ),
					'icon'  => 'clock',
				),
				array(
					'group' => 'fluentbooking',
					'key'   => 'fb_past',
					'label' => __( 'Past Count', 'aegis' ),
					'desc'  => __( 'Show or hide blocks based on the number of past bookings.', 'aegis' ),
					'icon'  => 'backup',
				),
				array(
					'group' => 'fluentbooking',
					'key'   => 'fb_status',
					'label' => __( 'Booking Status', 'aegis' ),
					'desc'  => __( 'Show or hide blocks based on a specific booking status.', 'aegis' ),
					'icon'  => 'flag',
				),
			),
			'wp_fusion'              => array(
				array(
					'group' => 'wp_fusion',
					'key'   => 'tags',
					'label' => __( 'CRM Tags', 'aegis' ),
					'desc'  => __( 'Show or hide content based on WP Fusion tags.', 'aegis' ),
					'icon'  => 'tag',
				),
				array(
					'group' => 'wp_fusion',
					'key'   => 'lists',
					'label' => __( 'CRM Lists', 'aegis' ),
					'desc'  => __( 'Show or hide content based on WP Fusion lists.', 'aegis' ),
					'icon'  => 'list-view',
				),
			),
			'advanced_custom_fields' => array(
				array(
					'group' => 'pro_conditions',
					'key'   => 'acf_field',
					'label' => __( 'ACF Field', 'aegis' ),
					'desc'  => __( 'Show or hide blocks based on Advanced Custom Fields values.', 'aegis' ),
					'icon'  => 'database',
				),
			),
			'meta_box'               => array(
				array(
					'group' => 'pro_conditions',
					'key'   => 'metabox_field',
					'label' => __( 'Meta Box Field', 'aegis' ),
					'desc'  => __( 'Show or hide blocks based on Meta Box field values.', 'aegis' ),
					'icon'  => 'archive',
				),
			),
		);
	}
}
