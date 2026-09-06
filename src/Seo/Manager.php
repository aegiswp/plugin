<?php
/**
 * Detects the active SEO plugin and boots the matching adapter.
 *
 * @package Aegis\Plugin\Seo
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Seo;

use Aegis\Plugin\Integrations\Settings as IntegrationsSettings;
use Aegis\Plugin\Seo\Adapters\AioseoAdapter;
use Aegis\Plugin\Seo\Adapters\RankMathAdapter;
use Aegis\Plugin\Seo\Adapters\SeopressAdapter;
use Aegis\Plugin\Seo\Adapters\TsfAdapter;
use Aegis\Plugin\Seo\Adapters\YoastAdapter;
use function add_action;
use function count;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolves one primary SEO adapter when multiple plugins are present.
 */
final class Manager {

	/** @var array<string, class-string<SeoAdapterInterface>> */
	private const ADAPTERS = [
		'rank_math'  => RankMathAdapter::class,
		'yoast_seo'  => YoastAdapter::class,
		'aioseo'     => AioseoAdapter::class,
		'seopress'   => SeopressAdapter::class,
		'tsf'        => TsfAdapter::class,
	];

	/** @var array<int, string> */
	private const PRIORITY = [
		'rank_math',
		'yoast_seo',
		'aioseo',
		'seopress',
		'tsf',
	];

	private static ?string $active_slug = null;

	private static bool $resolved = false;

	/**
	 * Register bootstrap hooks.
	 */
	public function init(): void {
		add_action( 'init', [ $this, 'boot_adapter' ], 5 );
	}

	/**
	 * Boot the primary SEO adapter when its integration toggle is enabled.
	 */
	public function boot_adapter(): void {
		$adapter = self::get_active_adapter();

		if ( $adapter === null ) {
			return;
		}

		if ( ! IntegrationsSettings::is_integration_enabled( $adapter::get_slug() ) ) {
			return;
		}

		$adapter->register_hooks();
	}

	/**
	 * Slug of the primary detected SEO plugin, or null when none are active.
	 */
	public static function get_active_slug(): ?string {
		self::resolve_active_slug();

		return self::$active_slug;
	}

	/**
	 * Instantiated adapter for the primary detected SEO plugin.
	 */
	public static function get_active_adapter(): ?SeoAdapterInterface {
		$slug = self::get_active_slug();

		if ( $slug === null ) {
			return null;
		}

		$class = self::ADAPTERS[ $slug ] ?? null;

		if ( $class === null || ! $class::is_active() ) {
			return null;
		}

		return new $class();
	}

	/**
	 * All detected SEO plugins in priority order.
	 *
	 * @return array<int, string>
	 */
	public static function get_detected_slugs(): array {
		$detected = [];

		foreach ( self::PRIORITY as $slug ) {
			$class = self::ADAPTERS[ $slug ] ?? null;

			if ( $class !== null && $class::is_active() ) {
				$detected[] = $slug;
			}
		}

		return $detected;
	}

	/**
	 * Resolve and cache the primary SEO plugin slug.
	 */
	private static function resolve_active_slug(): void {
		if ( self::$resolved ) {
			return;
		}

		self::$resolved = true;

		foreach ( self::PRIORITY as $slug ) {
			$class = self::ADAPTERS[ $slug ] ?? null;

			if ( $class !== null && $class::is_active() ) {
				self::$active_slug = $slug;
				break;
			}
		}
	}

	/**
	 * Whether multiple SEO plugins are active simultaneously.
	 */
	public static function has_multiple_seo_plugins(): bool {
		return count( self::get_detected_slugs() ) > 1;
	}
}
