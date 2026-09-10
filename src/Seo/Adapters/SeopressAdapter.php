<?php
/**
 * SEOPress SEO adapter.
 *
 * @package Aegis\Plugin\Seo\Adapters
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Seo\Adapters;

use Aegis\Plugin\Integrations\SEOPress;
use Aegis\Plugin\Seo\SeoAdapterInterface;
use function add_filter;
use function apply_filters;
use function class_exists;
use function defined;
use function function_exists;
use function is_array;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers SEOPress schema integration hooks.
 *
 * SEOPress exposes per-type automatic schema filters (not a single graph hook):
 * FAQ, Event, Local Business, and Video.
 */
final class SeopressAdapter implements SeoAdapterInterface {

	public static function is_active(): bool {
		if ( class_exists( SEOPress::class ) ) {
			return SEOPress::is_plugin_active();
		}

		return defined( 'SEOPRESS_VERSION' )
			|| defined( 'SEOPRESS_PLUGIN_DIR_PATH' )
			|| defined( 'SEOPRESS_PRO_VERSION' )
			|| defined( 'SEOPRESS_PRO_PLUGIN_DIR_PATH' )
			|| function_exists( 'seopress_get_service' );
	}

	public static function get_slug(): string {
		return 'seopress';
	}

	public static function get_label(): string {
		return 'SEOPress';
	}

	public function register_hooks(): void {
		add_filter( 'seopress_schemas_auto_faq_json', [ $this, 'filter_faq_schema' ], 20 );
		add_filter( 'seopress_schemas_auto_event_json', [ $this, 'filter_event_schema' ], 20 );
		add_filter( 'seopress_schemas_auto_lb_json', [ $this, 'filter_local_business_schema' ], 20 );
		add_filter( 'seopress_schemas_auto_video_json', [ $this, 'filter_video_schema' ], 20 );
	}

	/**
	 * @param mixed $schema Schema property map from SEOPress.
	 * @return array<string, mixed>
	 */
	public function filter_faq_schema( $schema ): array {
		return $this->filter_schema( is_array( $schema ) ? $schema : [], 'faq' );
	}

	/**
	 * @param mixed $schema Schema property map from SEOPress.
	 * @return array<string, mixed>
	 */
	public function filter_event_schema( $schema ): array {
		return $this->filter_schema( is_array( $schema ) ? $schema : [], 'event' );
	}

	/**
	 * @param mixed $schema Schema property map from SEOPress.
	 * @return array<string, mixed>
	 */
	public function filter_local_business_schema( $schema ): array {
		return $this->filter_schema( is_array( $schema ) ? $schema : [], 'local_business' );
	}

	/**
	 * @param mixed $schema Schema property map from SEOPress.
	 * @return array<string, mixed>
	 */
	public function filter_video_schema( $schema ): array {
		return $this->filter_schema( is_array( $schema ) ? $schema : [], 'video' );
	}

	/**
	 * @param array<string, mixed> $schema Schema property map from SEOPress.
	 * @param string               $type   faq|event|local_business|video.
	 * @return array<string, mixed>
	 */
	public function filter_schema( array $schema, string $type = '' ): array {
		/**
		 * Filters SEOPress automatic schema JSON before output.
		 *
		 * @param array<string, mixed> $schema Schema data.
		 * @param string               $type   Schema type key.
		 */
		return apply_filters( 'aegis_seopress_schemas_auto_schema', $schema, $type );
	}
}
