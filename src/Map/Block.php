<?php
/**
 * Map block registration and REST geocoding proxy.
 *
 * @package Aegis\Plugin\Map
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Map;

use Aegis\Framework\ServiceProvider;
use Aegis\Plugin\Integrations\Settings as IntegrationsSettings;
use Aegis\Plugin\Settings\Repository;
use WP_REST_Request;
use function __;
use function add_action;
use function add_filter;
use function add_query_arg;
use function class_exists;
use function current_user_can;
use function file_exists;
use function get_current_user_id;
use function get_transient;
use function is_wp_error;
use function plugin_dir_path;
use function register_block_type;
use function register_rest_route;
use function rest_url;
use function sanitize_text_field;
use function set_transient;
use function wp_create_nonce;
use function wp_json_encode;
use function wp_localize_script;
use function wp_script_is;
use function wp_remote_get;
use function wp_remote_retrieve_body;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers aegis/map from the plugin and proxies geocoding requests.
 */
final class Block {

	/**
	 * Absolute path to the map block directory.
	 */
	private function block_path(): string {
		return plugin_dir_path( \Aegis\Plugin\FILE ) . 'src/Blocks/map';
	}

	/**
	 * Register hooks.
	 */
	public function init(): void {
		add_action( 'init', array( $this, 'register_block' ) );
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_data' ) );
	}

	/**
	 * Register the map block with WordPress.
	 */
	public function register_block(): void {
		if ( ! ServiceProvider::is_block_enabled( 'map' ) ) {
			return;
		}

		$block_path = $this->block_path();

		if ( ! file_exists( $block_path . '/block.json' ) ) {
			return;
		}

		register_block_type( $block_path );

		add_filter( 'render_block_aegis/map', array( $this, 'append_schema' ), 10, 2 );
	}

	/**
	 * Append Schema.org LocalBusiness JSON-LD to map block output.
	 *
	 * @param string               $content Block HTML.
	 * @param array<string, mixed> $block   Parsed block.
	 */
	public function append_schema( string $content, array $block ): string {
		$attrs = $block['attrs'] ?? array();

		if ( empty( $attrs['schemaEnabled'] ) ) {
			return $content;
		}

		if ( ! ServiceProvider::is_block_enabled( 'map_schema' ) ) {
			return $content;
		}

		if ( Repository::is_schema_delegated_to_seo( 'local_business' ) ) {
			return $content;
		}

		return $content . $this->render_schema( $attrs );
	}

	/**
	 * Generate Schema.org LocalBusiness JSON-LD markup.
	 *
	 * @param array<string, mixed> $attrs Block attributes.
	 */
	private function render_schema( array $attrs ): string {
		$schema = array(
			'@context' => 'https://schema.org',
			'@type'    => 'LocalBusiness',
		);

		if ( ! empty( $attrs['schemaBusinessName'] ) ) {
			$schema['name'] = sanitize_text_field( (string) $attrs['schemaBusinessName'] );
		}

		if ( ! empty( $attrs['schemaPhone'] ) ) {
			$schema['telephone'] = sanitize_text_field( (string) $attrs['schemaPhone'] );
		}

		if ( ! empty( $attrs['schemaAddress'] ) ) {
			$schema['address'] = array(
				'@type'         => 'PostalAddress',
				'streetAddress' => sanitize_text_field( (string) $attrs['schemaAddress'] ),
			);
		}

		$lat = (float) ( $attrs['lat'] ?? 0 );
		$lng = (float) ( $attrs['lng'] ?? 0 );

		if ( $lat && $lng ) {
			$schema['geo'] = array(
				'@type'     => 'GeoCoordinates',
				'latitude'  => $lat,
				'longitude' => $lng,
			);
		}

		return '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES ) . '</script>';
	}

	/**
	 * Register REST API geocoding proxy endpoint.
	 */
	public function register_rest_routes(): void {
		if ( ! ServiceProvider::is_block_enabled( 'map' ) ) {
			return;
		}

		if ( ! IntegrationsSettings::is_integration_enabled( 'google_maps' ) ) {
			return;
		}

		register_rest_route(
			'aegis/v1',
			'/map/geocode',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'proxy_geocode' ),
				'permission_callback' => static function (): bool {
					return current_user_can( 'edit_posts' );
				},
				'args'                => array(
					'address' => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);
	}

	/**
	 * Proxy geocoding request to Google Maps API.
	 *
	 * @param WP_REST_Request $request REST request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function proxy_geocode( WP_REST_Request $request ) {
		if ( ! IntegrationsSettings::is_integration_enabled( 'google_maps' ) ) {
			return new \WP_Error(
				'integration_disabled',
				'Google Maps integration is disabled.',
				array( 'status' => 403 )
			);
		}

		$api_key = Settings::get_server_api_key();

		if ( $api_key === '' ) {
			return new \WP_Error(
				'no_api_key',
				'Google Maps server API key is not configured.',
				array( 'status' => 400 )
			);
		}

		$user_id = get_current_user_id();
		$limit_key = 'aegis_map_geocode_' . $user_id;
		$count     = (int) get_transient( $limit_key );

		if ( $count >= 60 ) {
			return new \WP_Error(
				'rate_limited',
				'Geocoding rate limit exceeded. Please try again later.',
				array( 'status' => 429 )
			);
		}

		set_transient( $limit_key, $count + 1, 15 * MINUTE_IN_SECONDS );

		$address  = $request->get_param( 'address' );
		$response = wp_remote_get(
			add_query_arg(
				array(
					'address' => $address,
					'key'     => $api_key,
				),
				'https://maps.googleapis.com/maps/api/geocode/json'
			),
			array( 'timeout' => 10 )
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! isset( $body['status'] ) || $body['status'] !== 'OK' ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- debug only.
				error_log(
					sprintf(
						'Aegis map geocode failed: %s',
						$body['error_message'] ?? $body['status'] ?? 'unknown'
					)
				);
			}

			return new \WP_Error(
				'geocode_failed',
				__( 'Geocoding failed. Check the address and API key configuration.', 'aegis' ),
				array( 'status' => 400 )
			);
		}

		$location = $body['results'][0]['geometry']['location'] ?? null;

		if ( ! $location ) {
			return new \WP_Error(
				'no_results',
				'No results found for the given address.',
				array( 'status' => 404 )
			);
		}

		return new \WP_REST_Response(
			array(
				'lat'               => $location['lat'],
				'lng'               => $location['lng'],
				'formatted_address' => $body['results'][0]['formatted_address'] ?? '',
			)
		);
	}

	/**
	 * Enqueue editor data for the map block.
	 */
	public function enqueue_editor_data(): void {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		$handle = 'aegis-map-editor-script';

		if ( ! wp_script_is( $handle, 'registered' ) ) {
			return;
		}

		$google_enabled = IntegrationsSettings::is_integration_enabled( 'google_maps' );
		$browser_key    = $google_enabled ? Settings::get_browser_api_key() : '';
		$server_key     = $google_enabled ? Settings::get_server_api_key() : '';

		wp_localize_script(
			$handle,
			'aegisMapEditor',
			array(
				'restUrl'           => rest_url( 'aegis/v1/map/geocode' ),
				'restNonce'         => wp_create_nonce( 'wp_rest' ),
				'isPro'             => class_exists( 'Aegis\\Pro\\Blocks\\Map' ),
				'hasBrowserKey'     => $browser_key !== '',
				'browserKey'        => $browser_key,
				'hasServerGeocode'  => $server_key !== '',
				'googleMapsEnabled' => $google_enabled,
				'features'          => array(
					'markers'         => ServiceProvider::is_block_enabled( 'map_markers' ),
					'styles'          => ServiceProvider::is_block_enabled( 'map_styles' ),
					'controls'        => ServiceProvider::is_block_enabled( 'map_controls' ),
					'osmFallback'     => ServiceProvider::is_block_enabled( 'map_osm_fallback' ),
					'schema'          => ServiceProvider::is_block_enabled( 'map_schema' ),
					'directions'      => ServiceProvider::is_block_enabled( 'map_directions' ),
					'storeLocator'    => ServiceProvider::is_block_enabled( 'map_store_locator' ),
					'geolocation'     => ServiceProvider::is_block_enabled( 'map_geolocation' ),
					'heatmap'         => ServiceProvider::is_block_enabled( 'map_heatmap' ),
					'drawing'         => ServiceProvider::is_block_enabled( 'map_drawing' ),
					'kmlGeojson'      => ServiceProvider::is_block_enabled( 'map_kml_geojson' ),
					'dynamicMarkers'  => ServiceProvider::is_block_enabled( 'map_dynamic_markers' ),
					'customStyles'    => ServiceProvider::is_block_enabled( 'map_custom_styles' ),
					'customIcons'     => ServiceProvider::is_block_enabled( 'map_custom_icons' ),
					'clustering'      => ServiceProvider::is_block_enabled( 'map_clustering' ),
				),
			)
		);
	}
}
