<?php
/**
 * Integration catalog — keys, labels, sections, and plugin detection.
 *
 * @package Aegis\Plugin\Integrations
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Integrations;

use Aegis\Plugin\Map\Settings as MapSettings;
use function __;
use function class_exists;
use function defined;
use function file_exists;
use function function_exists;
use function str_replace;
use const WP_PLUGIN_DIR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Central registry of third-party plugin integrations.
 */
final class Registry {

	/** @var array<string, array{key: string, label: string, section: string, plugin_check: string, is_plugin_active: callable, plugin_files?: array<int, string>}>|null */
	private static ?array $integrations = null;

	/**
	 * Get all registered integrations.
	 *
	 * @return array<string, array{key: string, label: string, section: string, plugin_check: string, is_plugin_active: callable, plugin_files?: array<int, string>}>
	 */
	public static function get_all(): array {
		if ( self::$integrations !== null ) {
			return self::$integrations;
		}

		self::$integrations = [
			'woocommerce' => [
				'key'              => 'woocommerce',
				'label'            => __( 'WooCommerce', 'aegis' ),
				'section'          => 'ecommerce',
				'plugin_check'     => 'woocommerce',
				'plugin_files'     => [ 'woocommerce/woocommerce.php' ],
				'is_plugin_active' => static fn(): bool => class_exists( 'WooCommerce' ),
			],
			'easy_digital_downloads' => [
				'key'              => 'easy_digital_downloads',
				'label'            => __( 'Easy Digital Downloads', 'aegis' ),
				'section'          => 'ecommerce',
				'plugin_check'     => 'easy_digital_downloads',
				'plugin_files'     => [ 'easy-digital-downloads/easy-digital-downloads.php' ],
				'is_plugin_active' => static fn(): bool => class_exists( 'Easy_Digital_Downloads' ),
			],
			'wp_fusion' => [
				'key'              => 'wp_fusion',
				'label'            => __( 'WP Fusion', 'aegis' ),
				'section'          => 'crm',
				'plugin_check'     => 'wp_fusion',
				'plugin_files'     => [ 'wp-fusion/wp-fusion.php' ],
				'is_plugin_active' => static fn(): bool => function_exists( 'wpf_get_tag_id' ) || function_exists( 'wpf_has_tag' ),
			],
			'affiliate_wp' => [
				'key'              => 'affiliate_wp',
				'label'            => __( 'AffiliateWP', 'aegis' ),
				'section'          => 'ecommerce',
				'plugin_check'     => 'affiliate_wp',
				'is_plugin_active' => static fn(): bool => class_exists( 'Affiliate_WP' ),
			],
			'learndash' => [
				'key'              => 'learndash',
				'label'            => __( 'LearnDash', 'aegis' ),
				'section'          => 'lms',
				'plugin_check'     => 'learndash',
				'plugin_files'     => [ 'sfwd-lms/sfwd_lms.php' ],
				'is_plugin_active' => static fn(): bool => defined( 'LEARNDASH_VERSION' ),
			],
			'lifter_lms' => [
				'key'              => 'lifter_lms',
				'label'            => __( 'LifterLMS', 'aegis' ),
				'section'          => 'lms',
				'plugin_check'     => 'lifter_lms',
				'plugin_files'     => [ 'lifterlms/lifterlms.php' ],
				'is_plugin_active' => static fn(): bool => class_exists( 'LifterLMS' ),
			],
			'sensei_lms' => [
				'key'              => 'sensei_lms',
				'label'            => __( 'Sensei LMS', 'aegis' ),
				'section'          => 'lms',
				'plugin_check'     => 'sensei_lms',
				'plugin_files'     => [ 'sensei-lms/sensei-lms.php' ],
				'is_plugin_active' => static fn(): bool => class_exists( 'Sensei_Main' ),
			],
			'fluent_forms' => [
				'key'              => 'fluent_forms',
				'label'            => __( 'Fluent Forms', 'aegis' ),
				'section'          => 'forms',
				'plugin_check'     => 'fluent_forms',
				'plugin_files'     => [ 'fluentform/fluentform.php' ],
				'is_plugin_active' => static fn(): bool => defined( 'FLUENTFORM' ),
			],
			'fluent_booking' => [
				'key'              => 'fluent_booking',
				'label'            => __( 'Fluent Booking', 'aegis' ),
				'section'          => 'forms',
				'plugin_check'     => 'fluent_booking',
				'plugin_files'     => [ 'fluent-booking/fluent-booking.php' ],
				'is_plugin_active' => static fn(): bool => class_exists( 'FluentBooking\\App\\App' ),
			],
			'fluent_crm' => [
				'key'              => 'fluent_crm',
				'label'            => __( 'FluentCRM', 'aegis' ),
				'section'          => 'forms',
				'plugin_check'     => 'fluent_crm',
				'is_plugin_active' => static fn(): bool => defined( 'FLUENTCRM' ),
			],
			'gravity_forms' => [
				'key'              => 'gravity_forms',
				'label'            => __( 'Gravity Forms', 'aegis' ),
				'section'          => 'forms',
				'plugin_check'     => 'gravity_forms',
				'is_plugin_active' => static fn(): bool => class_exists( 'GFForms' ),
			],
			'ninja_forms' => [
				'key'              => 'ninja_forms',
				'label'            => __( 'Ninja Forms', 'aegis' ),
				'section'          => 'forms',
				'plugin_check'     => 'ninja_forms',
				'is_plugin_active' => static fn(): bool => class_exists( 'Ninja_Forms' ),
			],
			'co_authors_plus' => [
				'key'              => 'co_authors_plus',
				'label'            => __( 'Co-Authors Plus', 'aegis' ),
				'section'          => 'content',
				'plugin_check'     => 'co_authors_plus',
				'is_plugin_active' => static fn(): bool => function_exists( 'get_coauthors' ),
			],
			'bbpress' => [
				'key'              => 'bbpress',
				'label'            => __( 'bbPress', 'aegis' ),
				'section'          => 'content',
				'plugin_check'     => 'bbpress',
				'is_plugin_active' => static fn(): bool => class_exists( 'bbPress' ),
			],
			'rank_math' => [
				'key'              => 'rank_math',
				'label'            => __( 'Rank Math', 'aegis' ),
				'section'          => 'seo',
				'plugin_check'     => 'rank_math',
				'is_plugin_active' => static fn(): bool => class_exists( 'RankMath' ),
			],
			'yoast_seo' => [
				'key'              => 'yoast_seo',
				'label'            => __( 'Yoast SEO', 'aegis' ),
				'section'          => 'seo',
				'plugin_check'     => 'yoast_seo',
				'is_plugin_active' => static fn(): bool => class_exists( 'WPSEO_Options' ),
			],
			'aioseo' => [
				'key'              => 'aioseo',
				'label'            => __( 'All in One SEO', 'aegis' ),
				'section'          => 'seo',
				'plugin_check'     => 'aioseo',
				'is_plugin_active' => static fn(): bool => defined( 'AIOSEO_VERSION' ) || class_exists( 'AIOSEO\\Plugin\\AIOSEO' ),
			],
			'seopress' => [
				'key'              => 'seopress',
				'label'            => __( 'SEOPress', 'aegis' ),
				'section'          => 'seo',
				'plugin_check'     => 'seopress',
				'is_plugin_active' => static fn(): bool => defined( 'SEOPRESS_VERSION' ),
			],
			'advanced_custom_fields' => [
				'key'              => 'advanced_custom_fields',
				'label'            => __( 'Advanced Custom Fields', 'aegis' ),
				'section'          => 'developer',
				'plugin_check'     => 'acf',
				'plugin_files'     => [
					'advanced-custom-fields/acf.php',
					'advanced-custom-fields-pro/acf.php',
				],
				'is_plugin_active' => static fn(): bool => class_exists( 'ACF' ),
			],
			'meta_box' => [
				'key'              => 'meta_box',
				'label'            => __( 'Meta Box', 'aegis' ),
				'section'          => 'developer',
				'plugin_check'     => 'meta_box',
				'plugin_files'     => [ 'meta-box/meta-box.php' ],
				'is_plugin_active' => static fn(): bool => class_exists( 'RWMB_Loader' ),
			],
			'code_block_pro' => [
				'key'              => 'code_block_pro',
				'label'            => __( 'Code Block Pro', 'aegis' ),
				'section'          => 'developer',
				'plugin_check'     => 'code_block_pro',
				'is_plugin_active' => static fn(): bool => defined( 'CODE_BLOCK_PRO_VERSION' ),
			],
			'syntax_highlighting' => [
				'key'              => 'syntax_highlighting',
				'label'            => __( 'Syntax Highlighting Code Block', 'aegis' ),
				'section'          => 'developer',
				'plugin_check'     => 'syntax_highlighting',
				'is_plugin_active' => static fn(): bool => class_exists( 'Developer_Starter_Starter' ) || function_exists( 'Developer_Starter_Starter' ),
			],
			'bunny_cdn' => [
				'key'              => 'bunny_cdn',
				'label'            => __( 'BunnyCDN', 'aegis' ),
				'section'          => 'bunnycdn',
				'plugin_check'     => 'bunny_cdn',
				'is_plugin_active' => static fn(): bool => false,
			],
			'google_maps' => [
				'key'              => 'google_maps',
				'label'            => __( 'Google Maps', 'aegis' ),
				'section'          => 'maps',
				'plugin_check'     => 'google_maps',
				'is_plugin_active' => static function (): bool {
					return class_exists( MapSettings::class ) && MapSettings::get_api_key() !== '';
				},
			],
		];

		return self::$integrations;
	}

	/**
	 * Settings-page hash for an integration key.
	 */
	public static function tab_id( string $key ): string {
		$special = array(
			'bunny_cdn'    => 'bunnycdn',
			'google_maps'  => 'maps',
		);

		return $special[ $key ] ?? str_replace( '_', '-', $key );
	}

	/**
	 * Category sidebar on Aegis → Integrations (plugins stay grouped so the nav does not scroll).
	 *
	 * @return array<int, array{id: string, label: string, icon: string, description: string, plugins: array<int, array{key: string, id: string, label: string, icon: string, description: string, plugin_check: string}>}>
	 */
	public static function get_integrations_admin_sections(): array {
		$sections = array(
			array(
				'id'          => 'ecommerce',
				'label'       => __( 'E-commerce', 'aegis' ),
				'icon'        => 'cart',
				'description' => __( 'WooCommerce, Easy Digital Downloads, and affiliate tools.', 'aegis' ),
				'keys'        => array( 'woocommerce', 'easy_digital_downloads', 'affiliate_wp' ),
			),
			array(
				'id'          => 'lms',
				'label'       => __( 'LMS', 'aegis' ),
				'icon'        => 'welcome-learn-more',
				'description' => __( 'LearnDash, LifterLMS, and Sensei LMS.', 'aegis' ),
				'keys'        => array( 'learndash', 'lifter_lms', 'sensei_lms' ),
			),
			array(
				'id'          => 'forms',
				'label'       => __( 'Forms', 'aegis' ),
				'icon'        => 'feedback',
				'description' => __( 'Form builders, booking, and FluentCRM.', 'aegis' ),
				'keys'        => array( 'fluent_forms', 'fluent_booking', 'fluent_crm', 'gravity_forms', 'ninja_forms' ),
			),
			array(
				'id'          => 'content',
				'label'       => __( 'Content', 'aegis' ),
				'icon'        => 'admin-post',
				'description' => __( 'Co-Authors Plus and bbPress.', 'aegis' ),
				'keys'        => array( 'co_authors_plus', 'bbpress' ),
			),
			array(
				'id'          => 'seo',
				'label'       => __( 'SEO', 'aegis' ),
				'icon'        => 'chart-line',
				'description' => __( 'Rank Math, Yoast, All in One SEO, and SEOPress.', 'aegis' ),
				'keys'        => array( 'rank_math', 'yoast_seo', 'aioseo', 'seopress' ),
			),
			array(
				'id'          => 'developer',
				'label'       => __( 'Developer', 'aegis' ),
				'icon'        => 'admin-generic',
				'description' => __( 'Custom fields and code highlighting.', 'aegis' ),
				'keys'        => array( 'advanced_custom_fields', 'meta_box', 'code_block_pro', 'syntax_highlighting' ),
			),
			array(
				'id'          => 'crm',
				'label'       => __( 'CRM', 'aegis' ),
				'icon'        => 'tag',
				'description' => __( 'WP Fusion tags, lists, and automation.', 'aegis' ),
				'keys'        => array( 'wp_fusion' ),
			),
		);

		foreach ( $sections as &$section ) {
			$plugins = array();

			foreach ( $section['keys'] as $key ) {
				$plugin = self::plugin_admin_row( $key );

				if ( $plugin !== null ) {
					$plugins[] = $plugin;
				}
			}

			$section['plugins'] = $plugins;
			unset( $section['keys'] );
		}
		unset( $section );

		return $sections;
	}

	/**
	 * Map a plugin hash onto its Integrations category tab.
	 */
	public static function section_id_for_plugin( string $key ): string {
		$integration = self::get( $key );

		return $integration['section'] ?? self::tab_id( $key );
	}

	/**
	 * @return array{key: string, id: string, label: string, icon: string, description: string, plugin_check: string}|null
	 */
	private static function plugin_admin_row( string $key ): ?array {
		$integration = self::get( $key );

		if ( $integration === null ) {
			return null;
		}

		$meta = self::plugin_admin_meta();
		$row  = $meta[ $key ] ?? array();

		return array(
			'key'          => $key,
			'id'           => self::tab_id( $key ),
			'label'        => $integration['label'],
			'icon'         => $row['icon'] ?? 'admin-plugins',
			'description'  => $row['description'] ?? '',
			'plugin_check' => $integration['plugin_check'],
		);
	}

	/**
	 * @return array<string, array{icon: string, description: string}>
	 */
	private static function plugin_admin_meta(): array {
		return array(
			'woocommerce'            => array(
				'icon'        => 'cart',
				'description' => __( 'E-commerce platform for selling physical products and digital downloads.', 'aegis' ),
			),
			'easy_digital_downloads' => array(
				'icon'        => 'download',
				'description' => __( 'Digital commerce platform for selling downloads and subscriptions.', 'aegis' ),
			),
			'affiliate_wp'           => array(
				'icon'        => 'groups',
				'description' => __( 'Affiliate marketing and referral program management.', 'aegis' ),
			),
			'learndash'              => array(
				'icon'        => 'welcome-learn-more',
				'description' => __( 'Powerful LMS plugin for creating and selling courses.', 'aegis' ),
			),
			'lifter_lms'             => array(
				'icon'        => 'awards',
				'description' => __( 'Complete LMS solution for course creation and membership.', 'aegis' ),
			),
			'sensei_lms'             => array(
				'icon'        => 'book',
				'description' => __( 'Learning management by WooCommerce.', 'aegis' ),
			),
			'fluent_forms'           => array(
				'icon'        => 'feedback',
				'description' => __( 'Fast and lightweight form builder plugin.', 'aegis' ),
			),
			'fluent_booking'         => array(
				'icon'        => 'calendar-alt',
				'description' => __( 'Appointment and booking management system.', 'aegis' ),
			),
			'fluent_crm'             => array(
				'icon'        => 'email',
				'description' => __( 'CRM and email marketing automation for video events.', 'aegis' ),
			),
			'gravity_forms'          => array(
				'icon'        => 'feedback',
				'description' => __( 'Advanced form builder for WordPress.', 'aegis' ),
			),
			'ninja_forms'            => array(
				'icon'        => 'feedback',
				'description' => __( 'Drag-and-drop form builder plugin.', 'aegis' ),
			),
			'co_authors_plus'        => array(
				'icon'        => 'groups',
				'description' => __( 'Multiple bylines and guest authors for posts and pages.', 'aegis' ),
			),
			'bbpress'                => array(
				'icon'        => 'groups',
				'description' => __( 'Forum software for WordPress communities.', 'aegis' ),
			),
			'rank_math'              => array(
				'icon'        => 'chart-line',
				'description' => __( 'SEO plugin for optimizing your content.', 'aegis' ),
			),
			'yoast_seo'              => array(
				'icon'        => 'chart-line',
				'description' => __( 'Popular SEO plugin with schema and readability tools.', 'aegis' ),
			),
			'aioseo'                 => array(
				'icon'        => 'chart-line',
				'description' => __( 'Comprehensive SEO toolkit for WordPress.', 'aegis' ),
			),
			'seopress'               => array(
				'icon'        => 'chart-line',
				'description' => __( 'Lightweight SEO plugin with schema support.', 'aegis' ),
			),
			'advanced_custom_fields' => array(
				'icon'        => 'admin-generic',
				'description' => __( 'Custom fields and meta boxes for WordPress.', 'aegis' ),
			),
			'meta_box'               => array(
				'icon'        => 'admin-generic',
				'description' => __( 'Lightweight custom fields framework for WordPress.', 'aegis' ),
			),
			'code_block_pro'         => array(
				'icon'        => 'editor-code',
				'description' => __( 'Syntax highlighting for code blocks.', 'aegis' ),
			),
			'syntax_highlighting'    => array(
				'icon'        => 'editor-code',
				'description' => __( 'Alternative syntax highlighting for code.', 'aegis' ),
			),
			'wp_fusion'              => array(
				'icon'        => 'tag',
				'description' => __( 'CRM tags, lists, and automation via WP Fusion.', 'aegis' ),
			),
		);
	}

	/**
	 * Get a single integration by key.
	 *
	 * @param string $key Integration key.
	 * @return array{key: string, label: string, section: string, plugin_check: string, is_plugin_active: callable, plugin_files?: array<int, string>}|null
	 */
	public static function get( string $key ): ?array {
		$all = self::get_all();

		return $all[ $key ] ?? null;
	}

	/**
	 * Find an integration by plugin_check identifier.
	 *
	 * @param string $plugin_check Plugin check identifier used by Renderer.
	 * @return array{key: string, label: string, section: string, plugin_check: string, is_plugin_active: callable, plugin_files?: array<int, string>}|null
	 */
	public static function get_by_plugin_check( string $plugin_check ): ?array {
		foreach ( self::get_all() as $integration ) {
			if ( $integration['plugin_check'] === $plugin_check ) {
				return $integration;
			}
		}

		return null;
	}

	/**
	 * Check whether the third-party plugin for an integration is active.
	 *
	 * @param string $key Integration key.
	 */
	public static function is_plugin_active( string $key ): bool {
		$integration = self::get( $key );

		if ( $integration === null ) {
			return false;
		}

		return (bool) ( $integration['is_plugin_active'] )();
	}

	/**
	 * Get plugin status for admin UI (class + label).
	 *
	 * @param string $key Integration key or plugin_check identifier.
	 * @return array{class: string, label: string}
	 */
	public static function get_plugin_status( string $key ): array {
		$integration = self::get( $key ) ?? self::get_by_plugin_check( $key );

		if ( $integration === null ) {
			return [
				'class' => 'not-installed',
				'label' => __( 'Not Installed', 'aegis' ),
			];
		}

		if ( ( $integration['is_plugin_active'] )() ) {
			return [
				'class' => 'active',
				'label' => __( 'Active', 'aegis' ),
			];
		}

		if ( self::plugin_files_installed( $integration['plugin_files'] ?? [] ) ) {
			return [
				'class' => 'inactive',
				'label' => __( 'Inactive', 'aegis' ),
			];
		}

		return [
			'class' => 'not-installed',
			'label' => __( 'Not Installed', 'aegis' ),
		];
	}

	/**
	 * @param array<int, string> $plugin_files Plugin basenames relative to WP_PLUGIN_DIR.
	 */
	private static function plugin_files_installed( array $plugin_files ): bool {
		foreach ( $plugin_files as $plugin_file ) {
			if ( $plugin_file !== '' && file_exists( WP_PLUGIN_DIR . '/' . $plugin_file ) ) {
				return true;
			}
		}

		return false;
	}
}
