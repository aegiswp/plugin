<?php
/**
 * Integration catalog — keys, labels, sections, and plugin detection.
 *
 * @package Aegis\Plugin\Integrations
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Integrations;

use function __;
use function class_exists;
use function defined;
use function file_exists;
use function function_exists;
use function str_replace;
use function strnatcasecmp;
use function usort;
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
				'is_plugin_active' => static fn(): bool => WooCommerce::is_plugin_active(),
			],
			'easy_digital_downloads' => [
				'key'              => 'easy_digital_downloads',
				'label'            => __( 'Easy Digital Downloads', 'aegis' ),
				'section'          => 'ecommerce',
				'plugin_check'     => 'easy_digital_downloads',
				'plugin_files'     => [
					'easy-digital-downloads/easy-digital-downloads.php',
					'easy-digital-downloads-pro/easy-digital-downloads.php',
				],
				'is_plugin_active' => static fn(): bool => EasyDigitalDownloads::is_plugin_active(),
			],
			'wp_fusion' => [
				'key'              => 'wp_fusion',
				'label'            => __( 'WP Fusion', 'aegis' ),
				'section'          => 'crm',
				'plugin_check'     => 'wp_fusion',
				'plugin_files'     => [
					'wp-fusion/wp-fusion.php',
					'wp-fusion-lite/wp-fusion-lite.php',
				],
				'is_plugin_active' => static fn(): bool => WPFusion::is_plugin_active(),
			],
			'affiliate_wp' => [
				'key'              => 'affiliate_wp',
				'label'            => __( 'AffiliateWP', 'aegis' ),
				'section'          => 'ecommerce',
				'plugin_check'     => 'affiliate_wp',
				'plugin_files'     => [ 'affiliate-wp/affiliate-wp.php' ],
				'is_plugin_active' => static fn(): bool => AffiliateWP::is_plugin_active(),
			],
			'learndash' => [
				'key'              => 'learndash',
				'label'            => __( 'LearnDash', 'aegis' ),
				'section'          => 'lms',
				'plugin_check'     => 'learndash',
				'plugin_files'     => [
					'sfwd-lms/sfwd_lms.php',
					'learndash/sfwd_lms.php',
				],
				'is_plugin_active' => static fn(): bool => LearnDash::is_plugin_active(),
			],
			'lifter_lms' => [
				'key'              => 'lifter_lms',
				'label'            => __( 'LifterLMS', 'aegis' ),
				'section'          => 'lms',
				'plugin_check'     => 'lifter_lms',
				'plugin_files'     => [ 'lifterlms/lifterlms.php' ],
				'is_plugin_active' => static fn(): bool => LifterLMS::is_plugin_active(),
			],
			'sensei_lms' => [
				'key'              => 'sensei_lms',
				'label'            => __( 'Sensei LMS', 'aegis' ),
				'section'          => 'lms',
				'plugin_check'     => 'sensei_lms',
				'plugin_files'     => [
					'sensei-lms/sensei-lms.php',
					'sensei/sensei.php',
					'woothemes-sensei/woothemes-sensei.php',
				],
				'is_plugin_active' => static fn(): bool => SenseiLMS::is_plugin_active(),
			],
			'fluent_forms' => [
				'key'              => 'fluent_forms',
				'label'            => __( 'Fluent Forms', 'aegis' ),
				'section'          => 'forms',
				'plugin_check'     => 'fluent_forms',
				'plugin_files'     => [
					'fluentform/fluentform.php',
					'fluentformpro/fluentformpro.php',
				],
				'is_plugin_active' => static fn(): bool => FluentForms::is_plugin_active(),
			],
			'fluent_booking' => [
				'key'              => 'fluent_booking',
				'label'            => __( 'Fluent Booking', 'aegis' ),
				'section'          => 'forms',
				'plugin_check'     => 'fluent_booking',
				'plugin_files'     => [
					'fluent-booking/fluent-booking.php',
					'fluent-booking-pro/fluent-booking-pro.php',
				],
				'is_plugin_active' => static fn(): bool => FluentBooking::is_plugin_active(),
			],
			'fluent_crm' => [
				'key'              => 'fluent_crm',
				'label'            => __( 'FluentCRM', 'aegis' ),
				'section'          => 'crm',
				'plugin_check'     => 'fluent_crm',
				'plugin_files'     => [ 'fluent-crm/fluent-crm.php' ],
				'is_plugin_active' => static fn(): bool => defined( 'FLUENTCRM' ),
			],
			'gravity_forms' => [
				'key'              => 'gravity_forms',
				'label'            => __( 'Gravity Forms', 'aegis' ),
				'section'          => 'forms',
				'plugin_check'     => 'gravity_forms',
				'plugin_files'     => [ 'gravityforms/gravityforms.php' ],
				'is_plugin_active' => static fn(): bool => GravityForms::is_plugin_active(),
			],
			'ninja_forms' => [
				'key'              => 'ninja_forms',
				'label'            => __( 'Ninja Forms', 'aegis' ),
				'section'          => 'forms',
				'plugin_check'     => 'ninja_forms',
				'plugin_files'     => [ 'ninja-forms/ninja-forms.php' ],
				'is_plugin_active' => static fn(): bool => NinjaForms::is_plugin_active(),
			],
			'co_authors_plus' => [
				'key'              => 'co_authors_plus',
				'label'            => __( 'Co-Authors Plus', 'aegis' ),
				'section'          => 'content',
				'plugin_check'     => 'co_authors_plus',
				'plugin_files'     => [ 'co-authors-plus/co-authors-plus.php' ],
				'is_plugin_active' => static fn(): bool => function_exists( 'get_coauthors' ),
			],
			'bbpress' => [
				'key'              => 'bbpress',
				'label'            => __( 'bbPress', 'aegis' ),
				'section'          => 'content',
				'plugin_check'     => 'bbpress',
				'plugin_files'     => [ 'bbpress/bbpress.php' ],
				'is_plugin_active' => static fn(): bool => class_exists( 'bbPress' ),
			],
			'rank_math' => [
				'key'              => 'rank_math',
				'label'            => __( 'Rank Math', 'aegis' ),
				'section'          => 'seo',
				'plugin_check'     => 'rank_math',
				'plugin_files'     => [
					'seo-by-rank-math/rank-math.php',
					'seo-by-rank-math-pro/rank-math-pro.php',
				],
				'is_plugin_active' => static fn(): bool => RankMath::is_plugin_active(),
			],
			'yoast_seo' => [
				'key'              => 'yoast_seo',
				'label'            => __( 'Yoast SEO', 'aegis' ),
				'section'          => 'seo',
				'plugin_check'     => 'yoast_seo',
				'plugin_files'     => [
					'wordpress-seo/wp-seo.php',
					'wordpress-seo-premium/wp-seo-premium.php',
				],
				'is_plugin_active' => static fn(): bool => Yoast::is_plugin_active(),
			],
			'aioseo' => [
				'key'              => 'aioseo',
				'label'            => __( 'All in One SEO', 'aegis' ),
				'section'          => 'seo',
				'plugin_check'     => 'aioseo',
				'plugin_files'     => [
					'all-in-one-seo-pack/all_in_one_seo_pack.php',
					'all-in-one-seo-pack-pro/all_in_one_seo_pack.php',
				],
				'is_plugin_active' => static fn(): bool => AllInOneSEO::is_plugin_active(),
			],
			'seopress' => [
				'key'              => 'seopress',
				'label'            => __( 'SEOPress', 'aegis' ),
				'section'          => 'seo',
				'plugin_check'     => 'seopress',
				'plugin_files'     => [
					'wp-seopress/seopress.php',
					'wp-seopress-pro/seopress-pro.php',
				],
				'is_plugin_active' => static fn(): bool => SEOPress::is_plugin_active(),
			],
			'advanced_custom_fields' => [
				'key'              => 'advanced_custom_fields',
				'label'            => __( 'Advanced Custom Fields', 'aegis' ),
				'section'          => 'developer',
				'plugin_check'     => 'acf',
				'plugin_files'     => [
					'advanced-custom-fields/acf.php',
					'advanced-custom-fields-pro/acf.php',
					'secure-custom-fields/secure-custom-fields.php',
					'secure-custom-fields/acf.php',
				],
				'is_plugin_active' => static fn(): bool => ACF::is_plugin_active(),
			],
			'meta_box' => [
				'key'              => 'meta_box',
				'label'            => __( 'Meta Box', 'aegis' ),
				'section'          => 'developer',
				'plugin_check'     => 'meta_box',
				'plugin_files'     => [
					'meta-box/meta-box.php',
					'meta-box-aio/meta-box-aio.php',
				],
				'is_plugin_active' => static fn(): bool => MetaBox::is_plugin_active(),
			],
			'code_block_pro' => [
				'key'              => 'code_block_pro',
				'label'            => __( 'Code Block Pro', 'aegis' ),
				'section'          => 'developer',
				'plugin_check'     => 'code_block_pro',
				'plugin_files'     => [ 'code-block-pro/code-block-pro.php' ],
				'is_plugin_active' => static fn(): bool => CodeBlockPro::is_plugin_active(),
			],
			'syntax_highlighting' => [
				'key'              => 'syntax_highlighting',
				'label'            => __( 'Syntax Highlighting Code Block', 'aegis' ),
				'section'          => 'developer',
				'plugin_check'     => 'syntax_highlighting',
				'plugin_files'     => [ 'syntax-highlighting-code-block/syntax-highlighting-code-block.php' ],
				'is_plugin_active' => static fn(): bool => SyntaxHighlighting::is_plugin_active(),
			],
			'bunny_cdn' => [
				'key'              => 'bunny_cdn',
				'label'            => __( 'BunnyCDN', 'aegis' ),
				'section'          => 'bunnycdn',
				'plugin_check'     => 'bunny_cdn',
				'is_plugin_active' => static fn(): bool => BunnyCDN::is_plugin_active(),
			],
			'google_maps' => [
				'key'              => 'google_maps',
				'label'            => __( 'Google Maps', 'aegis' ),
				'section'          => 'maps',
				'plugin_check'     => 'google_maps',
				'is_plugin_active' => static fn(): bool => true,
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
				'description' => __( 'AffiliateWP, Easy Digital Downloads, and WooCommerce.', 'aegis' ),
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
				'description' => __( 'Fluent Booking, Fluent Forms, Gravity Forms, and Ninja Forms.', 'aegis' ),
				'keys'        => array( 'fluent_forms', 'fluent_booking', 'gravity_forms', 'ninja_forms' ),
			),
			array(
				'id'          => 'content',
				'label'       => __( 'Content', 'aegis' ),
				'icon'        => 'admin-post',
				'description' => __( 'bbPress and Co-Authors Plus.', 'aegis' ),
				'keys'        => array( 'co_authors_plus', 'bbpress' ),
			),
			array(
				'id'          => 'seo',
				'label'       => __( 'SEO', 'aegis' ),
				'icon'        => 'chart-line',
				'description' => __( 'All in One SEO, Rank Math, SEOPress, and Yoast SEO.', 'aegis' ),
				'keys'        => array( 'rank_math', 'yoast_seo', 'aioseo', 'seopress' ),
			),
			array(
				'id'          => 'developer',
				'label'       => __( 'Developer', 'aegis' ),
				'icon'        => 'admin-generic',
				'description' => __( 'Advanced Custom Fields, Code Block Pro, Meta Box, and Syntax Highlighting.', 'aegis' ),
				'keys'        => array( 'advanced_custom_fields', 'meta_box', 'code_block_pro', 'syntax_highlighting' ),
			),
			array(
				'id'          => 'crm',
				'label'       => __( 'CRM', 'aegis' ),
				'icon'        => 'tag',
				'description' => __( 'FluentCRM video events and WP Fusion tags, lists, and automation.', 'aegis' ),
				'keys'        => array( 'fluent_crm', 'wp_fusion' ),
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

			usort(
				$plugins,
				static function ( array $a, array $b ): int {
					return strnatcasecmp( $a['label'], $b['label'] );
				}
			);

			$section['plugins'] = $plugins;
			unset( $section['keys'] );
		}
		unset( $section );

		usort(
			$sections,
			static function ( array $a, array $b ): int {
				return strnatcasecmp( $a['label'], $b['label'] );
			}
		);

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
	 * @return array{key: string, id: string, label: string, icon: string, brand: string, description: string, plugin_check: string}|null
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
			'brand'        => $row['brand'] ?? '',
			'description'  => $row['description'] ?? '',
			'plugin_check' => $integration['plugin_check'],
		);
	}

	/**
	 * @return array<string, array{icon: string, brand?: string, description: string}>
	 */
	private static function plugin_admin_meta(): array {
		return array(
			'woocommerce'            => array(
				'icon'        => 'cart',
				'brand'       => 'woocommerce',
				'description' => __( 'Shop styling, snippet locations, commerce patterns, and Pro cart, customer, and product conditions.', 'aegis' ),
			),
			'easy_digital_downloads' => array(
				'icon'        => 'download',
				'brand'       => 'easy-digital-downloads',
				'description' => __( 'Download and checkout styling, snippet locations, and Pro cart, customer, and download conditions.', 'aegis' ),
			),
			'affiliate_wp'           => array(
				'icon'        => 'groups',
				'brand'       => 'affiliate-wp',
				'description' => __( 'Dashboard and login/register form styling, snippet locations, and Pro referral, account, and earnings conditions.', 'aegis' ),
			),
			'learndash'              => array(
				'icon'        => 'welcome-learn-more',
				'description' => __( 'Course layouts, Focus Mode chrome, pattern control, and snippet locations for LearnDash.', 'aegis' ),
			),
			'lifter_lms'             => array(
				'icon'        => 'awards',
				'description' => __( 'Course and membership layouts, snippet locations, pattern control, and Pro video progression for LifterLMS.', 'aegis' ),
			),
			'sensei_lms'             => array(
				'icon'        => 'book',
				'brand'       => 'sensei-lms',
				'description' => __( 'Course and lesson layouts, snippet locations, pattern control, and block conditions for Sensei LMS.', 'aegis' ),
			),
			'fluent_forms'           => array(
				'icon'        => 'feedback',
				'brand'       => 'fluent-forms',
				'description' => __( 'Form styling, pattern control, and Pro submission status, count, and field value conditions.', 'aegis' ),
			),
			'fluent_booking'         => array(
				'icon'        => 'calendar-alt',
				'brand'       => 'fluentbooking',
				'description' => __( 'Calendar and appointment styling, pattern control, and Pro booking status, upcoming, and past booking conditions.', 'aegis' ),
			),
			'fluent_crm'             => array(
				'icon'        => 'email',
				'description' => __( 'Email marketing CRM. With Pro, video funnels, tags, and lists.', 'aegis' ),
			),
			'gravity_forms'          => array(
				'icon'        => 'feedback',
				'description' => __( 'Form styling, default theme CSS reset, and snippet locations for Gravity Forms.', 'aegis' ),
			),
			'ninja_forms'            => array(
				'icon'        => 'feedback',
				'description' => __( 'Form styling, default theme CSS reset, and snippet locations for Ninja Forms.', 'aegis' ),
			),
			'co_authors_plus'        => array(
				'icon'        => 'groups',
				'description' => __( 'Multiple bylines and guest authors for posts and pages.', 'aegis' ),
			),
			'bbpress'                => array(
				'icon'        => 'groups',
				'brand'       => 'bbpress',
				'description' => __( 'Forum and topic styling, Page template wrapper, breadcrumb CSS, and snippet locations.', 'aegis' ),
			),
			'rank_math'              => array(
				'icon'        => 'chart-line',
				'brand'       => 'rankmath',
				'description' => __( 'FAQ, Event, Local Business, and Video schema delegation, plus Pro video sitemaps.', 'aegis' ),
			),
			'yoast_seo'              => array(
				'icon'        => 'chart-line',
				'description' => __( 'FAQ, Event, Local Business, and Video schema delegation, plus Pro video sitemaps.', 'aegis' ),
			),
			'aioseo'                 => array(
				'icon'        => 'chart-line',
				'description' => __( 'FAQ, Event, Local Business, and Video schema delegation, plus Pro video sitemaps.', 'aegis' ),
			),
			'seopress'               => array(
				'icon'        => 'chart-line',
				'description' => __( 'FAQ, Event, Local Business, and Video schema delegation, plus Pro video sitemaps.', 'aegis' ),
			),
			'advanced_custom_fields' => array(
				'icon'        => 'admin-generic',
				'brand'       => 'acf',
				'description' => __( 'Field visibility, Query Loop pickers, and featured-image sources.', 'aegis' ),
			),
			'meta_box'               => array(
				'icon'        => 'admin-generic',
				'description' => __( 'Field visibility, Query Loop pickers, featured-image sources, and form styling.', 'aegis' ),
			),
			'code_block_pro'         => array(
				'icon'        => 'editor-code',
				'description' => __( 'Theme radius and monospace overlay for Kevin Batdorf’s code block.', 'aegis' ),
			),
			'syntax_highlighting'    => array(
				'icon'        => 'editor-code',
				'description' => __( 'Theme radius, padding, and line-number overlay for Weston Ruter’s highlighted Code block.', 'aegis' ),
			),
			'wp_fusion'              => array(
				'icon'        => 'tag',
				'description' => __( 'CRM tags and lists via WP Fusion.', 'aegis' ),
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
