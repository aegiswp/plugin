<?php
/**
 * Block feature settings repository.
 *
 * @package Aegis\Plugin\Blocks
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Blocks;

use function array_key_exists;
use function filter_var;
use function get_option;
use function in_array;
use function is_array;
use function str_starts_with;
use function update_option;
use const FILTER_VALIDATE_BOOLEAN;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stores block feature toggles in aegis_blocks.
 */
final class Settings {

	public const OPTION = 'aegis_blocks';

	public const DEFAULTS = [
		'modal' => false,
		'modal_click' => false,
		'modal_icon' => false,
		'modal_text' => false,
		'modal_image' => false,
		'modal_exit_intent' => false,
		'modal_scroll_depth' => false,
		'modal_time_delay' => false,
		'modal_offcanvas' => false,
		'modal_fullscreen' => false,
		'modal_animations' => false,
		'modal_auto_close' => false,
		'modal_show_once' => false,
		'modal_device_visibility' => false,
		'slider' => false,
		'slider_slide' => false,
		'slider_fade' => false,
		'slider_navigation' => false,
		'slider_pagination' => false,
		'slider_loop' => false,
		'slider_keyboard' => false,
		'slider_responsive' => false,
		'slider_autoplay' => false,
		'slider_effects' => false,
		'slider_thumbnails' => false,
		'slider_lightbox' => false,
		'slider_mousewheel' => false,
		'slider_aspect_ratio' => false,
		'slider_lazy_load' => false,
		'embed_facades' => false,
		'perf_disable_wp_embed' => false,
		'perf_disable_dashicons' => false,
		'perf_reduce_heartbeat' => false,
		'perf_remove_emoji' => false,
		'perf_woo_disable_cart_fragments' => false,
		'perf_woo_disable_assets_elsewhere' => false,
		'perf_woo_disable_password_strength' => false,
		'slider_arrow_styles' => false,
		'slider_dot_styles' => false,
		'toggle' => false,
		'toggle_pill' => false,
		'toggle_switch' => false,
		'toggle_buttons' => false,
		'toggle_position' => false,
		'toggle_labels' => false,
		'toggle_url_sync' => false,
		'toggle_persist' => false,
		'toggle_animations' => false,
		'toggle_nested' => false,
		'toggle_conditional' => false,
		// @todo Uncomment for v1.0.0 release.
		// 'global_styles' => false,
		// 'global_styles_spacing' => false,
		// 'global_styles_typography' => false,
		// 'global_styles_layout' => false,
		// 'global_styles_effects' => false,
		// 'global_styles_create' => false,
		// 'global_styles_library' => false,
		// 'global_styles_transfer' => false,
		// 'global_styles_export' => false,
		'query_loop' => false,
		'query_loop_post_types' => false,
		'query_loop_taxonomy' => false,
		'query_loop_include_exclude' => false,
		'query_loop_meta_query' => false,
		'query_loop_order_meta' => false,
		'query_loop_responsive_columns' => false,
		'query_loop_gap_controls' => false,
		'query_loop_featured_first' => false,
		'query_loop_equal_height' => false,
		'query_loop_no_results' => false,
		'query_loop_extended_order' => false,
		'query_loop_advanced_meta' => false,
		'query_loop_date_query' => false,
		'query_loop_parent_child' => false,
		'query_loop_acf_integration' => false,
		'query_loop_ajax_pagination' => false,
		'query_loop_frontend_filters' => false,
		'query_loop_masonry_layout' => false,
		'query_loop_carousel_layout' => false,
		'query_loop_woocommerce' => false,
		'query_loop_performance' => false,
		'related_posts' => false,
		'related_posts_taxonomy_source' => false,
		'related_posts_orderby' => false,
		'related_posts_fallback' => false,
		'related_posts_style_variants' => false,
		'related_posts_excerpt_length' => false,
		'related_posts_image_ratio' => false,
		'video_custom_player' => false,
		'video_theater_mode' => false,
		'video_keyboard_shortcuts' => false,
		'video_schema_markup' => false,
		'video_ambient_light' => false,
		'video_thumbnail_preview' => false,
		'video_touch_gestures' => false,
		'video_playlists' => false,
		'video_sticky_player' => false,
		'video_focus_mode' => false,
		'video_save_progress' => false,
		'video_chapters' => false,
		'video_email_capture' => false,
		'video_analytics' => false,
		'video_multi_audio' => false,
		'video_privacy' => false,
		'video_sitemap' => false,
		'accordion' => false,
		'accordion_open_first' => false,
		'accordion_open_all' => false,
		'accordion_icon' => false,
		'accordion_border' => false,
		'accordion_faq_schema' => false,
		'accordion_single_open' => false,
		'counter' => false,
		'counter_prefix' => false,
		'counter_suffix' => false,
		'counter_delay' => false,
		'counter_duration' => false,
		'counter_intersection' => false,
		'countdown' => false,
		'countdown_segments' => false,
		'countdown_labels' => false,
		'countdown_separator' => false,
		'countdown_layout' => false,
		'countdown_expiry_message' => false,
		'countdown_timezone' => false,
		'countdown_schema' => false,
		'countdown_evergreen' => false,
		'countdown_animation' => false,
		'countdown_auto_restart' => false,
		'countdown_expiry_actions' => false,
		'countdown_urgency' => false,
		'icon' => false,
		'icon_gradient' => false,
		'icon_animation' => false,
		'icon_custom_svg' => false,
		'icon_gallery' => false,
		'icon_responsive' => false,
		'icon_rest_api' => false,
		'image_lightbox' => false,
		'image_lightbox_gallery_nav' => false,
		'image_lightbox_zoom' => false,
		'image_lightbox_thumbnails' => false,
		'image_lightbox_swipe' => false,
		'marquee' => false,
		'marquee_pause_hover' => false,
		'marquee_direction' => false,
		'marquee_speed' => false,
		'marquee_repeat' => false,
		'marquee_responsive_speed' => false,
		'newsletter' => false,
		'newsletter_email_validation' => false,
		'newsletter_success_message' => false,
		'newsletter_placeholder' => false,
		'svg' => false,
		'svg_markup' => false,
		'svg_mask' => false,
		'svg_onclick' => false,
		'svg_inline' => false,
		'svg_inline_file' => false,
		'map' => false,
		'map_markers' => false,
		'map_styles' => false,
		'map_controls' => false,
		'map_osm_fallback' => false,
		'map_directions' => false,
		'map_store_locator' => false,
		'map_geolocation' => false,
		'map_heatmap' => false,
		'map_drawing' => false,
		'map_kml_geojson' => false,
		'map_schema' => false,
		'map_dynamic_markers' => false,
		'map_custom_styles' => false,
		'map_custom_icons' => false,
		'map_clustering' => false,
		'image_compare' => false,
		'image_compare_orientation' => false,
		'image_compare_labels' => false,
		'image_compare_starting_point' => false,
		'image_compare_hover_start' => false,
		'image_compare_handle' => false,
		'image_compare_smoothing' => false,
		'image_compare_fluid' => false,
	];

	/**
	 * Site-wide and Query Loop performance keys. Saved from Aegis → Performance.
	 *
	 * @var array<int, string>
	 */
	public const PERFORMANCE_KEYS = [
		'embed_facades',
		'perf_disable_wp_embed',
		'perf_disable_dashicons',
		'perf_reduce_heartbeat',
		'perf_remove_emoji',
		'perf_woo_disable_cart_fragments',
		'perf_woo_disable_assets_elsewhere',
		'perf_woo_disable_password_strength',
		'query_loop_performance',
	];

	public const PARENT_BLOCK_KEYS = [
		'countdown',
		'modal',
		'related_posts',
		'slider',
		'toggle',
		'query_loop',
		'accordion',
		'counter',
		'icon',
		'image_lightbox',
		'marquee',
		'newsletter',
		'svg',
		'map',
		'image_compare',
	];

	/** @var array<string, bool>|null */
	private static ?array $cache = null;

	/**
	 * @return array<string, bool>
	 */
	public static function get_settings(): array {
		if ( self::$cache !== null ) {
			return self::$cache;
		}

		$options = get_option( self::OPTION, [] );
		$merged  = [];

		foreach ( self::DEFAULTS as $key => $default ) {
			$merged[ $key ] = isset( $options[ $key ] ) ? (bool) $options[ $key ] : $default;
		}

		self::$cache = $merged;

		return self::$cache;
	}

	public static function is_enabled( string $key ): bool {
		$settings = self::get_settings();

		if ( ! empty( $settings[ $key ] ) ) {
			return true;
		}

		if ( in_array( $key, self::PARENT_BLOCK_KEYS, true ) ) {
			$prefix = $key . '_';

			foreach ( $settings as $child => $enabled ) {
				if ( $enabled && str_starts_with( (string) $child, $prefix ) ) {
					return true;
				}
			}
		}

		return false;
	}

	public static function flush_cache(): void {
		self::$cache = null;
	}

	/**
	 * @param array<string, mixed> $input Raw input.
	 * @param bool                 $preserve_performance Keep Performance-page keys when omitted from input.
	 * @return array<string, bool>
	 */
	public static function sanitize( array $input, bool $preserve_performance = true ): array {
		$saved = [];

		if ( $preserve_performance ) {
			$stored = get_option( self::OPTION, [] );
			$saved  = is_array( $stored ) ? $stored : [];
		}

		$sanitized = [];

		foreach ( self::DEFAULTS as $key => $_default ) {
			if ( $preserve_performance && in_array( $key, self::PERFORMANCE_KEYS, true ) && ! array_key_exists( $key, $input ) ) {
				$sanitized[ $key ] = isset( $saved[ $key ] ) ? self::to_bool( $saved[ $key ] ) : false;
				continue;
			}

			$sanitized[ $key ] = self::to_bool( $input[ $key ] ?? false );
		}

		return $sanitized;
	}

	/**
	 * Merge Performance-page toggles into the stored blocks option.
	 *
	 * @param array<string, mixed> $input Raw input.
	 */
	public static function save_performance( array $input ): void {
		$stored = get_option( self::OPTION, [] );
		$stored = is_array( $stored ) ? $stored : [];

		foreach ( self::PERFORMANCE_KEYS as $key ) {
			$stored[ $key ] = self::to_bool( $input[ $key ] ?? false );
		}

		update_option( self::OPTION, $stored );
		self::flush_cache();
	}

	/**
	 * Reset only Performance-page keys to off.
	 */
	public static function reset_performance(): void {
		$stored = get_option( self::OPTION, [] );
		$stored = is_array( $stored ) ? $stored : [];

		foreach ( self::PERFORMANCE_KEYS as $key ) {
			$stored[ $key ] = false;
		}

		update_option( self::OPTION, $stored );
		self::flush_cache();
	}

	/**
	 * Reset block feature toggles without clearing Performance-page keys.
	 */
	public static function reset_block_features(): void {
		$stored = get_option( self::OPTION, [] );
		$stored = is_array( $stored ) ? $stored : [];
		$kept   = [];

		foreach ( self::PERFORMANCE_KEYS as $key ) {
			$kept[ $key ] = isset( $stored[ $key ] ) ? self::to_bool( $stored[ $key ] ) : false;
		}

		update_option( self::OPTION, $kept );
		self::flush_cache();
	}

	/**
	 * Cast AJAX checkbox values. String "0" must be off.
	 *
	 * @param mixed $value Raw input.
	 */
	private static function to_bool( $value ): bool {
		return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
	}
}

