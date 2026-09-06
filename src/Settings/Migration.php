<?php
/**
 * One-time data migrations for Aegis admin settings.
 *
 * @package Aegis\Plugin\Settings
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Settings;

use Aegis\Plugin\Blocks\Settings as BlocksSettings;
use Aegis\Plugin\General\Settings as GeneralSettings;
use Aegis\Plugin\Integrations\Settings as IntegrationsSettings;
use function array_key_exists;
use function get_option;
use function is_array;
use function update_option;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings migration runner.
 */
final class Migration {

	/**
	 * Block keys that defaulted to on before opt-in defaults.
	 *
	 * @var array<int, string>
	 */
	private const LEGACY_ENABLED_BLOCKS = [
		'modal',
		'modal_click',
		'modal_icon',
		'modal_text',
		'modal_image',
		'modal_offcanvas',
		'modal_fullscreen',
		'modal_animations',
		'slider',
		'slider_slide',
		'slider_fade',
		'slider_navigation',
		'slider_pagination',
		'slider_loop',
		'slider_keyboard',
		'slider_responsive',
		'embed_facades',
		'toggle',
		'toggle_pill',
		'toggle_switch',
		'toggle_buttons',
		'toggle_position',
		'toggle_labels',
		'query_loop',
		'query_loop_post_types',
		'query_loop_taxonomy',
		'query_loop_include_exclude',
		'query_loop_meta_query',
		'query_loop_order_meta',
		'query_loop_responsive_columns',
		'query_loop_gap_controls',
		'query_loop_featured_first',
		'query_loop_equal_height',
		'query_loop_no_results',
		'query_loop_extended_order',
		'related_posts',
		'related_posts_taxonomy_source',
		'related_posts_orderby',
		'related_posts_fallback',
		'related_posts_style_variants',
		'related_posts_excerpt_length',
		'related_posts_image_ratio',
		'video_custom_player',
		'video_theater_mode',
		'video_keyboard_shortcuts',
		'video_schema_markup',
		'video_ambient_light',
		'video_thumbnail_preview',
		'video_touch_gestures',
		'video_playlists',
		'video_sitemap',
		'accordion',
		'accordion_open_first',
		'accordion_open_all',
		'accordion_icon',
		'accordion_border',
		'accordion_faq_schema',
		'accordion_single_open',
		'counter',
		'counter_prefix',
		'counter_suffix',
		'counter_delay',
		'counter_duration',
		'counter_intersection',
		'countdown',
		'countdown_segments',
		'countdown_labels',
		'countdown_separator',
		'countdown_layout',
		'countdown_expiry_message',
		'countdown_timezone',
		'countdown_schema',
		'countdown_evergreen',
		'countdown_animation',
		'countdown_auto_restart',
		'countdown_expiry_actions',
		'countdown_urgency',
		'icon',
		'icon_gradient',
		'icon_animation',
		'icon_custom_svg',
		'icon_gallery',
		'icon_responsive',
		'icon_rest_api',
		'image_lightbox',
		'image_lightbox_gallery_nav',
		'image_lightbox_zoom',
		'image_lightbox_thumbnails',
		'image_lightbox_swipe',
		'marquee',
		'marquee_pause_hover',
		'marquee_direction',
		'marquee_speed',
		'marquee_repeat',
		'newsletter',
		'newsletter_email_validation',
		'newsletter_success_message',
		'newsletter_placeholder',
		'svg',
		'svg_markup',
		'svg_mask',
		'svg_onclick',
		'svg_inline',
		'svg_inline_file',
		'map',
		'map_markers',
		'map_styles',
		'map_controls',
		'map_osm_fallback',
		'map_schema',
	];

	/**
	 * Integration keys that defaulted to on before opt-in defaults.
	 *
	 * @var array<int, string>
	 */
	private const LEGACY_ENABLED_INTEGRATIONS = [
		'rank_math',
		'woocommerce',
	];

	public function run(): void {
		$this->migrate_opt_in_defaults_v3();
		$this->migrate_map_pro_toggles_v1();
		$this->migrate_marquee_pro_toggles_v1();
		$this->migrate_modal_pro_toggles_v1();
		$this->migrate_image_compare_toggles_v1();
		$this->migrate_legacy_emoji_option();
		IntegrationsSettings::migrate_legacy_bunnycdn_option();
	}

	/**
	 * Keep previously implicit-on features enabled on existing sites.
	 *
	 * Fresh installs have no Aegis options, so they keep all-off defaults.
	 */
	private function migrate_opt_in_defaults_v3(): void {
		if ( get_option( 'aegis_opt_in_defaults_v3' ) ) {
			return;
		}

		if ( $this->site_has_existing_aegis_data() ) {
			$this->snapshot_legacy_enabled( BlocksSettings::OPTION, self::LEGACY_ENABLED_BLOCKS );
			$this->snapshot_legacy_enabled( IntegrationsSettings::OPTION, self::LEGACY_ENABLED_INTEGRATIONS );
			$this->snapshot_legacy_enabled( GeneralSettings::OPTION, array( 'svg_upload' ) );
		}

		update_option( 'aegis_opt_in_defaults_v3', true, false );
	}

	/**
	 * Enable Pro Map extras that previously ran without admin toggles.
	 */
	private function migrate_map_pro_toggles_v1(): void {
		if ( get_option( 'aegis_map_pro_toggles_v1' ) ) {
			return;
		}

		if ( defined( 'AEGIS_PRO_VERSION' ) && $this->site_has_existing_aegis_data() ) {
			$this->snapshot_legacy_enabled(
				BlocksSettings::OPTION,
				array(
					'map_directions',
					'map_store_locator',
					'map_geolocation',
					'map_heatmap',
					'map_drawing',
					'map_kml_geojson',
					'map_dynamic_markers',
					'map_custom_styles',
					'map_custom_icons',
					'map_clustering',
				)
			);
		}

		update_option( 'aegis_map_pro_toggles_v1', true, false );
	}

	/**
	 * Enable Pro Marquee extras that previously ran without admin toggles.
	 */
	private function migrate_marquee_pro_toggles_v1(): void {
		if ( get_option( 'aegis_marquee_pro_toggles_v1' ) ) {
			return;
		}

		if ( defined( 'AEGIS_PRO_VERSION' ) && $this->site_has_existing_aegis_data() ) {
			$this->snapshot_legacy_enabled(
				BlocksSettings::OPTION,
				array(
					'marquee_responsive_speed',
				)
			);
		}

		update_option( 'aegis_marquee_pro_toggles_v1', true, false );
	}

	/**
	 * Enable Pro Modal extras that previously ran without admin toggles.
	 */
	private function migrate_modal_pro_toggles_v1(): void {
		if ( get_option( 'aegis_modal_pro_toggles_v1' ) ) {
			return;
		}

		if ( defined( 'AEGIS_PRO_VERSION' ) && $this->site_has_existing_aegis_data() ) {
			$this->snapshot_legacy_enabled(
				BlocksSettings::OPTION,
				array(
					'modal_exit_intent',
					'modal_scroll_depth',
					'modal_time_delay',
					'modal_auto_close',
					'modal_show_once',
					'modal_device_visibility',
				)
			);
		}

		update_option( 'aegis_modal_pro_toggles_v1', true, false );
	}

	/**
	 * Enable Image Compare extras that previously had no admin toggles.
	 *
	 * The block always registered when Pro was active. Keep extras on for
	 * existing sites so the inserter and inspector controls stay available.
	 */
	private function migrate_image_compare_toggles_v1(): void {
		if ( get_option( 'aegis_image_compare_toggles_v1' ) ) {
			return;
		}

		if ( defined( 'AEGIS_PRO_VERSION' ) && $this->site_has_existing_aegis_data() ) {
			$this->snapshot_legacy_enabled(
				BlocksSettings::OPTION,
				array(
					'image_compare_orientation',
					'image_compare_labels',
					'image_compare_starting_point',
					'image_compare_hover_start',
					'image_compare_handle',
				)
			);
			BlocksSettings::flush_cache();
		}

		update_option( 'aegis_image_compare_toggles_v1', true, false );
	}

	private function site_has_existing_aegis_data(): bool {
		$markers = array(
			'aegis_blocks_defaults_v2',
			'aegis_integrations_defaults_v2',
			BlocksSettings::OPTION,
			IntegrationsSettings::OPTION,
			GeneralSettings::OPTION,
			'aegis_conditional_logic',
			'aegis_conditional_permissions',
			'aegis_analytics',
			'aegis_snippets_settings',
		);

		foreach ( $markers as $option ) {
			if ( false !== get_option( $option, false ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param array<int, string> $legacy_on_keys
	 */
	private function snapshot_legacy_enabled( string $option, array $legacy_on_keys ): void {
		$saved = get_option( $option );
		$saved = is_array( $saved ) ? $saved : array();
		$updated = false;

		foreach ( $legacy_on_keys as $key ) {
			if ( ! array_key_exists( $key, $saved ) ) {
				$saved[ $key ] = true;
				$updated       = true;
			}
		}

		if ( $updated ) {
			update_option( $option, $saved );
		}
	}

	/**
	 * Copy Pro Fieldify emoji removal into aegis_blocks.
	 *
	 * Legacy Pro treated an unset Fieldify key as on.
	 */
	private function migrate_legacy_emoji_option(): void {
		if ( get_option( 'aegis_emoji_perf_migrated_v1' ) ) {
			return;
		}

		$blocks = get_option( BlocksSettings::OPTION, array() );
		$blocks = is_array( $blocks ) ? $blocks : array();

		if ( ! array_key_exists( 'perf_remove_emoji', $blocks ) ) {
			$aegis = get_option( 'aegis', array() );
			$aegis = is_array( $aegis ) ? $aegis : array();

			if ( array_key_exists( 'removeEmojiScripts', $aegis ) ) {
				$blocks['perf_remove_emoji'] = (bool) $aegis['removeEmojiScripts'];
				update_option( BlocksSettings::OPTION, $blocks );
			} elseif ( defined( 'AEGIS_PRO_VERSION' ) && $this->site_has_existing_aegis_data() ) {
				$blocks['perf_remove_emoji'] = true;
				update_option( BlocksSettings::OPTION, $blocks );
			}
		}

		update_option( 'aegis_emoji_perf_migrated_v1', true, false );
	}
}
