<?php
/**
 * Shared injection location registry for hooks and snippets.
 *
 * @package Aegis\Plugin\Injection
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Injection;

use Aegis\Plugin\Integrations\Registry;
use Aegis\Plugin\Settings\Repository;
use function __;
use function apply_filters;
use function basename;
use function delete_transient;
use function get_post;
use function get_posts;
use function get_template_directory;
use function glob;
use function is_array;
use function is_dir;
use function sprintf;
use function ucfirst;
use function str_replace;
use function array_unique;
use function sort;
use function str_contains;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Central catalog of injection hook locations grouped by context.
 */
final class LocationRegistry {

	/** @var array<string, array<string, array{label: string, scope: string, requires: string|null}>>|null */
	private static ?array $locations = null;

	/**
	 * Flush cached location data.
	 */
	public static function flush_cache(): void {
		delete_transient( 'aegis_injection_locations' );
		self::$locations = null;
	}

	/**
	 * Get all locations grouped by category.
	 *
	 * @return array<string, array<string, array{label: string, scope: string, requires: string|null}>>
	 */
	public static function get_all(): array {
		if ( self::$locations !== null ) {
			return self::$locations;
		}

		$locations = array(
			'global'   => self::get_global_locations(),
			'admin'    => self::get_admin_locations(),
			'login'    => self::get_login_locations(),
			'editor'   => self::get_editor_locations(),
			'theme'    => self::get_theme_locations(),
			'integrations' => IntegrationLocations::get_available(),
		);

		$locations = apply_filters( 'aegis_injection_locations', $locations );

		if ( ! is_array( $locations ) ) {
			$locations = array();
		}

		self::$locations = $locations;

		return self::$locations;
	}

	/**
	 * Get locations for a specific integration key.
	 *
	 * @param string $key Integration registry key.
	 * @return array<string, array{label: string, scope: string, requires: string|null}>
	 */
	public static function get_for_integration( string $key ): array {
		$all = self::get_all();
		$integration_hooks = $all['integrations'] ?? array();

		return array_filter(
			$integration_hooks,
			static function ( array $location ) use ( $key ): bool {
				return ( $location['requires'] ?? null ) === $key;
			}
		);
	}

	/**
	 * Whether a hook is registered and available in the current environment.
	 *
	 * @param string $hook_name Hook name.
	 */
	public static function is_available( string $hook_name ): bool {
		foreach ( self::get_all() as $group => $hooks ) {
			if ( isset( $hooks[ $hook_name ] ) ) {
				if ( $group !== 'integrations' ) {
					return true;
				}

				$requires = $hooks[ $hook_name ]['requires'] ?? null;

				if ( $requires === null ) {
					return true;
				}

				return Registry::is_plugin_active( $requires )
					&& Repository::is_integration_enabled( $requires );
			}
		}

		return false;
	}

	/**
	 * Theme template-part and content hooks (legacy group keys for UI).
	 *
	 * @return array<string, array<string, string>>
	 */
	public static function get_grouped_for_select(): array {
		$all = self::get_all();

		$grouped = array(
			'global'         => self::flatten_group( $all['global'] ?? array() ),
			'admin'          => self::flatten_group( $all['admin'] ?? array() ),
			'login'          => self::flatten_group( $all['login'] ?? array() ),
			'editor'         => self::flatten_group( $all['editor'] ?? array() ),
			'template-parts' => array(),
			'content'        => array(),
			'integrations'   => self::flatten_group( $all['integrations'] ?? array() ),
		);

		foreach ( $all['theme'] ?? array() as $hook => $meta ) {
			if ( str_contains( $hook, '_content' ) ) {
				$grouped['content'][ $hook ] = $meta['label'];
			} else {
				$grouped['template-parts'][ $hook ] = $meta['label'];
			}
		}

		return array_filter(
			$grouped,
			static function ( array $hooks ): bool {
				return $hooks !== array();
			}
		);
	}

	/**
	 * Theme hooks only (backward compatibility for Pro).
	 *
	 * @return array<string, array<string, string>>
	 */
	public static function get_theme_hooks(): array {
		$grouped = self::get_grouped_for_select();

		return array(
			'template-parts' => $grouped['template-parts'] ?? array(),
			'content'        => $grouped['content'] ?? array(),
		);
	}

	/**
	 * @return array<string, array{label: string, scope: string, requires: string|null}>
	 */
	private static function get_global_locations(): array {
		return array(
			'wp_head'             => array(
				'label'    => __( 'Document head', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => null,
			),
			'wp_body_open'        => array(
				'label'    => __( 'After body open', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => null,
			),
			'wp_footer'           => array(
				'label'    => __( 'Document footer', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => null,
			),
			'wp_enqueue_scripts'  => array(
				'label'    => __( 'Enqueue scripts (frontend)', 'aegis' ),
				'scope'    => 'frontend',
				'requires' => null,
			),
		);
	}

	/**
	 * @return array<string, array{label: string, scope: string, requires: string|null}>
	 */
	private static function get_admin_locations(): array {
		return array(
			'admin_head'             => array(
				'label'    => __( 'Admin head', 'aegis' ),
				'scope'    => 'admin',
				'requires' => null,
			),
			'admin_footer'           => array(
				'label'    => __( 'Admin footer', 'aegis' ),
				'scope'    => 'admin',
				'requires' => null,
			),
			'admin_enqueue_scripts'  => array(
				'label'    => __( 'Enqueue scripts (admin)', 'aegis' ),
				'scope'    => 'admin',
				'requires' => null,
			),
		);
	}

	/**
	 * @return array<string, array{label: string, scope: string, requires: string|null}>
	 */
	private static function get_login_locations(): array {
		return array(
			'login_head'   => array(
				'label'    => __( 'Login head', 'aegis' ),
				'scope'    => 'login',
				'requires' => null,
			),
			'login_footer' => array(
				'label'    => __( 'Login footer', 'aegis' ),
				'scope'    => 'login',
				'requires' => null,
			),
		);
	}

	/**
	 * @return array<string, array{label: string, scope: string, requires: string|null}>
	 */
	private static function get_editor_locations(): array {
		return array(
			'enqueue_block_editor_assets' => array(
				'label'    => __( 'Block editor assets', 'aegis' ),
				'scope'    => 'editor',
				'requires' => null,
			),
		);
	}

	/**
	 * @return array<string, array{label: string, scope: string, requires: string|null}>
	 */
	private static function get_theme_locations(): array {
		$locations = array();
		$slugs     = self::collect_template_part_slugs();

		foreach ( $slugs as $slug ) {
			$label = ucfirst( str_replace( array( '-', '_' ), ' ', $slug ) );

			$locations[ "aegis_before_{$slug}" ] = array(
				'label'    => sprintf(
					/* translators: %s: template part slug */
					__( 'Before %s template part', 'aegis' ),
					$label
				),
				'scope'    => 'frontend',
				'requires' => null,
			);

			$locations[ "aegis_after_{$slug}" ] = array(
				'label'    => sprintf(
					/* translators: %s: template part slug */
					__( 'After %s template part', 'aegis' ),
					$label
				),
				'scope'    => 'frontend',
				'requires' => null,
			);
		}

		$locations['aegis_before_content'] = array(
			'label'    => __( 'Before post content', 'aegis' ),
			'scope'    => 'frontend',
			'requires' => null,
		);

		$locations['aegis_after_content'] = array(
			'label'    => __( 'After post content', 'aegis' ),
			'scope'    => 'frontend',
			'requires' => null,
		);

		return $locations;
	}

	/**
	 * @return list<string>
	 */
	private static function collect_template_part_slugs(): array {
		$slugs = array();

		$parts_dir = get_template_directory() . '/parts';
		if ( is_dir( $parts_dir ) ) {
			$parts = glob( $parts_dir . '/*.html' );
			if ( is_array( $parts ) ) {
				foreach ( $parts as $part ) {
					$slugs[] = basename( $part, '.html' );
				}
			}
		}

		$db_parts = get_posts(
			array(
				'post_type'      => 'wp_template_part',
				'post_status'    => 'publish',
				'posts_per_page' => 100,
				'fields'         => 'ids',
			)
		);

		foreach ( $db_parts as $part_id ) {
			$post = get_post( $part_id );
			if ( $post && $post->post_name ) {
				$slugs[] = $post->post_name;
			}
		}

		$slugs = array_unique( $slugs );
		sort( $slugs );

		return array_values( $slugs );
	}

	/**
	 * @param array<string, array{label: string, scope: string, requires: string|null}> $group Group data.
	 * @return array<string, string>
	 */
	private static function flatten_group( array $group ): array {
		$flat = array();

		foreach ( $group as $hook => $meta ) {
			if ( isset( $meta['requires'] ) && $meta['requires'] !== null ) {
				if ( ! Registry::is_plugin_active( $meta['requires'] )
					|| ! Repository::is_integration_enabled( $meta['requires'] )
				) {
					continue;
				}
			}

			$flat[ $hook ] = $meta['label'];
		}

		return $flat;
	}
}
