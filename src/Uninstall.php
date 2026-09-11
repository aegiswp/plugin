<?php
/**
 * Plugin data purge used by uninstall.php and the Settings danger zone.
 *
 * @package Aegis\Plugin
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin;

use function class_exists;
use function delete_metadata;
use function delete_option;
use function delete_transient;
use function do_action;
use function get_option;
use function is_array;
use function is_dir;
use function is_link;
use function realpath;
use function rmdir;
use function scandir;
use function str_starts_with;
use function unlink;
use function update_option;
use function wp_clear_scheduled_hook;
use function wp_delete_post;
use function wp_upload_dir;
use const DIRECTORY_SEPARATOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Removes Aegis options, files, posts, cron, and user meta.
 */
final class Uninstall {

	public const REMOVE_DATA_OPTION = 'aegis_remove_data_on_uninstall';

	/**
	 * Whether the site opted into deleting data on uninstall.
	 */
	public static function should_remove_data(): bool {
		return (bool) get_option( self::REMOVE_DATA_OPTION );
	}

	public static function set_remove_data( bool $remove ): void {
		if ( $remove ) {
			update_option( self::REMOVE_DATA_OPTION, '1', false );
			return;
		}

		delete_option( self::REMOVE_DATA_OPTION );
	}

	/**
	 * Delete all Aegis data for the current site.
	 *
	 * Pro tables and license data are removed when Pro is inactive (leftovers)
	 * or via {@see 'aegis_uninstall_purge'} when Pro is still loaded (Delete now).
	 */
	public static function purge(): void {
		self::purge_options();
		self::purge_hook_patterns();
		self::purge_user_meta();
		self::purge_transients_and_cron();
		self::purge_upload_directory( 'aegis-snippets' );
		self::purge_upload_directory( 'aegis-analytics' );

		if ( defined( 'AEGIS_PRO_VERSION' ) || class_exists( \Aegis\Pro\Uninstall::class ) ) {
			do_action( 'aegis_uninstall_purge' );
			return;
		}

		self::purge_pro_leftovers();
	}

	/**
	 * @return array<int, string>
	 */
	public static function option_names(): array {
		return array(
			'aegis_google_maps',
			'aegis_google_maps_migrated_v1',
			'aegis_analytics',
			'aegis_integrations',
			'aegis_bunnycdn',
			'aegis_bunnycdn_migrated_v1',
			'aegis_bunnycdn_token_key_v2',
			'aegis_bunny_transcribe_intent',
			'aegis_pro_bunnycdn',
			'aegis_pattern_control',
			'aegis_settings',
			'aegis_conditional_logic',
			'aegis_conditional_permissions',
			'aegis_blocks',
			'aegis_snippets_settings',
			'aegis_integrations_defaults_v2',
			'aegis_blocks_defaults_v2',
			'aegis_opt_in_defaults_v3',
			'aegis_inactive_integrations_cleared_v1',
			'aegis_map_pro_toggles_v1',
			'aegis_marquee_pro_toggles_v1',
			'aegis_modal_pro_toggles_v1',
			'aegis_image_compare_toggles_v1',
			'aegis_emoji_perf_migrated_v1',
			'aegis_perf_heartbeat_key_v1',
			'aegis_matomo_privacy_mode_v1',
			self::REMOVE_DATA_OPTION,
		);
	}

	/**
	 * Drop Pro leftovers when the Pro plugin is not loaded.
	 */
	public static function purge_pro_leftovers(): void {
		global $wpdb;

		foreach ( array(
			'aegis_hook_patterns',
			'aegis_visibility_presets',
			'aegis_global_classes',
			'aegis_video_analytics_db_version',
			'aegis_pro_mailchimp_api_key',
			'aegis_pro_convertkit_api_key',
			'aegis_pro_license_key',
			'aegis-pro_license_key',
			'aegis-pro_license_data',
			'aegis',
		) as $option ) {
			delete_option( $option );
		}

		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'aegis_video_analytics' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'aegis_video_user_analytics' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	private static function purge_options(): void {
		foreach ( self::option_names() as $option ) {
			delete_option( (string) $option );
		}
	}

	private static function purge_hook_patterns(): void {
		global $wpdb;

		$ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE post_type = %s",
				'aegis_hook_pattern'
			)
		);

		if ( ! is_array( $ids ) ) {
			return;
		}

		foreach ( $ids as $id ) {
			wp_delete_post( (int) $id, true );
		}
	}

	private static function purge_user_meta(): void {
		delete_metadata( 'user', 0, 'aegis_preview_hook_positions', '', true );
		delete_metadata( 'user', 0, 'aegis_preview_snippet_positions', '', true );
		delete_metadata( 'user', 0, 'aegis_preview_admin_bar_shortcuts', '', true );
		delete_metadata( 'user', 0, 'aegis_dashboard_getting_started_dismissed', '', true );

		foreach ( array(
			'advanced_custom_fields',
			'affiliate_wp',
			'aioseo',
			'bbpress',
			'bunny_cdn',
			'co_authors_plus',
			'code_block_pro',
			'easy_digital_downloads',
			'fluent_booking',
			'fluent_forms',
			'fluent_crm',
			'google_maps',
			'gravity_forms',
			'learndash',
			'lifter_lms',
			'meta_box',
			'ninja_forms',
			'rank_math',
			'seopress',
			'sensei_lms',
			'syntax_highlighting',
			'woocommerce',
			'wp_fusion',
			'yoast_seo',
		) as $key ) {
			delete_metadata( 'user', 0, 'aegis_integration_notice_dismissed_' . $key, '', true );
		}
	}

	private static function purge_transients_and_cron(): void {
		delete_transient( 'aegis_injection_locations' );
		delete_transient( 'aegis_modal_block_count' );
		delete_transient( 'aegis_modal_instances' );
		delete_transient( 'aegis_modal_block_count_v2' );
		delete_transient( 'aegis_modal_instances_v2' );
		delete_transient( 'aegis_modal_block_count_v3' );
		delete_transient( 'aegis_modal_instances_v3' );
		delete_transient( 'aegis_modal_block_count_v4' );
		delete_transient( 'aegis_modal_instances_v4' );
		delete_transient( 'aegis_available_hooks' );
		wp_clear_scheduled_hook( 'aegis_analytics_refresh_scripts' );
		wp_clear_scheduled_hook( 'aegis_pro_analytics_cleanup' );
	}

	private static function purge_upload_directory( string $subdir ): void {
		$upload = wp_upload_dir();
		$base   = isset( $upload['basedir'] ) ? (string) $upload['basedir'] : '';

		if ( $base === '' ) {
			return;
		}

		$dir = realpath( $base . '/' . $subdir );

		if ( false === $dir ) {
			return;
		}

		$base_real = realpath( $base );

		if ( false === $base_real || ! str_starts_with( $dir, $base_real ) ) {
			return;
		}

		self::delete_directory( $dir );
	}

	private static function delete_directory( string $dir ): void {
		if ( ! is_dir( $dir ) || is_link( $dir ) ) {
			return;
		}

		$entries = scandir( $dir );

		if ( ! is_array( $entries ) ) {
			return;
		}

		foreach ( $entries as $entry ) {
			if ( '.' === $entry || '..' === $entry ) {
				continue;
			}

			$path = $dir . DIRECTORY_SEPARATOR . $entry;

			if ( is_dir( $path ) && ! is_link( $path ) ) {
				self::delete_directory( $path );
				continue;
			}

			unlink( $path );
		}

		rmdir( $dir );
	}
}
