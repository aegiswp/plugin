<?php
/**
 * Central settings repository — option names, defaults, and cached getters.
 *
 * @package Aegis\Plugin\Settings
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Settings;

use Aegis\Plugin\Analytics\Settings as AnalyticsSettings;
use Aegis\Plugin\Blocks\Settings as BlocksSettings;
use Aegis\Plugin\Conditionals\Settings as ConditionalsSettings;
use Aegis\Plugin\General\Settings as GeneralSettings;
use Aegis\Plugin\Integrations\Settings as IntegrationsSettings;
use Aegis\Plugin\Map\Settings as MapSettings;
use Aegis\Plugin\Seo\Manager as SeoManager;
use function class_exists;
use function get_option;
use function in_array;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Aggregates Aegis admin settings across feature modules.
 */
final class Repository {

	public const OPTION_NAME         = ConditionalsSettings::OPTION;
	public const INTEGRATIONS_OPTION = IntegrationsSettings::OPTION;
	public const BLOCKS_OPTION       = BlocksSettings::OPTION;
	public const BUNNYCDN_OPTION     = IntegrationsSettings::BUNNYCDN_OPTION;
	public const GOOGLE_MAPS_OPTION  = MapSettings::OPTION_KEY;
	public const SETTINGS_OPTION     = GeneralSettings::OPTION;
	public const ANALYTICS_OPTION    = AnalyticsSettings::OPTION;

	/** @var array<string, array<string, bool>> */
	public const DEFAULTS = ConditionalsSettings::DEFAULTS;

	/** @var array<string, bool> */
	public const INTEGRATION_DEFAULTS = IntegrationsSettings::INTEGRATION_DEFAULTS;

	/** @var array<string, string> */
	public const BUNNYCDN_DEFAULTS = IntegrationsSettings::BUNNYCDN_DEFAULTS;

	/** @var array<string, string> */
	public const GOOGLE_MAPS_DEFAULTS = MapSettings::DEFAULTS;

	/** @var array<string, bool> */
	public const SETTINGS_DEFAULTS = GeneralSettings::DEFAULTS;

	/** @var array<string, mixed> */
	public const ANALYTICS_DEFAULTS = AnalyticsSettings::DEFAULTS;

	/** @var array<string, bool> */
	public const BLOCKS_DEFAULTS = BlocksSettings::DEFAULTS;

	/** @var array<int, string> */
	public const PARENT_BLOCK_KEYS = BlocksSettings::PARENT_BLOCK_KEYS;

	/**
	 * @return array<string, array<string, bool>>
	 */
	public static function get_settings(): array {
		return ConditionalsSettings::get_settings();
	}

	/**
	 * @return array<string, bool>
	 */
	public static function get_integration_settings(): array {
		return IntegrationsSettings::get_settings();
	}

	public static function is_integration_enabled( string $integration ): bool {
		return IntegrationsSettings::is_integration_enabled( $integration );
	}

	/**
	 * Boot a callback when an integration toggle is enabled.
	 *
	 * @param string   $integration Integration setting key.
	 * @param callable $callback    Callback to invoke when enabled.
	 */
	public static function boot_if_enabled( string $integration, callable $callback ): void {
		if ( self::is_integration_enabled( $integration ) ) {
			$callback();
		}
	}

	/**
	 * Whether built-in schema output is delegated to the active SEO plugin.
	 *
	 * @param string $schema_type faq|event|local_business|video
	 */
	public static function is_schema_delegated_to_seo( string $schema_type ): bool {
		$schema_keys = [
			'faq'            => 'seo_faq_schema',
			'event'          => 'seo_event_schema',
			'local_business' => 'seo_local_schema',
			'video'          => 'seo_video_schema',
		];

		if ( ! isset( $schema_keys[ $schema_type ] ) ) {
			return false;
		}

		$active_slug = SeoManager::get_active_slug();

		if ( $active_slug === null ) {
			return false;
		}

		$seo_plugins = [ 'rank_math', 'yoast_seo', 'aioseo', 'seopress' ];

		if ( ! in_array( $active_slug, $seo_plugins, true ) ) {
			return false;
		}

		$integrations = self::get_integration_settings();

		return ! empty( $integrations[ $active_slug ] )
			&& ! empty( $integrations[ $schema_keys[ $schema_type ] ] );
	}

	/**
	 * Whether video sitemap output is delegated to the active SEO plugin.
	 */
	public static function is_video_sitemap_delegated_to_seo(): bool {
		$active_slug = SeoManager::get_active_slug();

		if ( $active_slug === null ) {
			return false;
		}

		$integrations = self::get_integration_settings();

		return ! empty( $integrations[ $active_slug ] )
			&& ! empty( $integrations['seo_video_sitemap'] );
	}

	/**
	 * @deprecated Use is_schema_delegated_to_seo() instead.
	 *
	 * @param string $schema_key Legacy rank_math_* or seo_* schema option key.
	 */
	public static function is_schema_handled_by_rank_math( string $schema_key ): bool {
		$legacy_map = [
			'rank_math_faq_schema'    => 'faq',
			'rank_math_event_schema'  => 'event',
			'rank_math_local_schema'  => 'local_business',
			'rank_math_video_schema'  => 'video',
			'seo_faq_schema'          => 'faq',
			'seo_event_schema'        => 'event',
			'seo_local_schema'        => 'local_business',
			'seo_video_schema'        => 'video',
		];

		if ( isset( $legacy_map[ $schema_key ] ) ) {
			return self::is_schema_delegated_to_seo( $legacy_map[ $schema_key ] );
		}

		return false;
	}

	/**
	 * @return array<string, bool>
	 */
	public static function get_block_settings(): array {
		return BlocksSettings::get_settings();
	}

	public static function is_block_enabled( string $block ): bool {
		return BlocksSettings::is_enabled( $block );
	}

	/**
	 * @return array<string, bool>
	 */
	public static function get_general_settings(): array {
		return GeneralSettings::get_settings();
	}

	public static function is_svg_upload_enabled(): bool {
		return GeneralSettings::is_svg_upload_enabled();
	}

	public static function flush_cache(): void {
		ConditionalsSettings::flush_cache();
		BlocksSettings::flush_cache();
		IntegrationsSettings::flush_cache();
		GeneralSettings::flush_cache();
	}
}
