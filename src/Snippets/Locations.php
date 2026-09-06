<?php
/**
 * Type-aware snippet run locations.
 *
 * @package Aegis\Plugin\Snippets
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Snippets;

use function __;
use function array_keys;
use function array_merge;
use function in_array;
use function preg_replace;
use function sanitize_key;
use function sanitize_title;
use function str_replace;
use function substr;
use function trim;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Maps snippet types to run locations and migrates legacy values.
 */
final class Locations {

	public const TYPE_PHP     = 'php';
	public const TYPE_CONTENT = 'content';
	public const TYPE_CSS     = 'css';
	public const TYPE_JS      = 'js';

	public const SHORTCODE_TAG = 'aegis_snippet';

	/**
	 * @return array<string, string>
	 */
	public static function types(): array {
		return array(
			self::TYPE_PHP     => __( 'Functions (PHP)', 'aegis' ),
			self::TYPE_CONTENT => __( 'Content', 'aegis' ),
			self::TYPE_CSS     => __( 'CSS', 'aegis' ),
			self::TYPE_JS      => __( 'JavaScript', 'aegis' ),
		);
	}

	public static function normalize_type( string $type ): string {
		if ( $type === 'html' ) {
			return self::TYPE_CONTENT;
		}

		if ( in_array( $type, array( self::TYPE_PHP, self::TYPE_CONTENT, self::TYPE_CSS, self::TYPE_JS ), true ) ) {
			return $type;
		}

		return self::TYPE_CONTENT;
	}

	public static function type_label( string $type ): string {
		$type   = self::normalize_type( $type );
		$types  = self::types();

		return $types[ $type ] ?? $type;
	}

	/**
	 * @return array<string, string>
	 */
	public static function php_locations(): array {
		return array(
			'php_everywhere' => __( 'Everywhere', 'aegis' ),
			'php_frontend'   => __( 'Frontend only', 'aegis' ),
			'php_admin'      => __( 'Admin only', 'aegis' ),
			'php_login'      => __( 'Login screen', 'aegis' ),
		);
	}

	/**
	 * @return array<string, string>
	 */
	public static function content_locations(): array {
		return array(
			'shortcode'      => __( 'Shortcode', 'aegis' ),
			'wp_head'        => __( 'Site-wide header', 'aegis' ),
			'wp_body_open'   => __( 'Site-wide body open', 'aegis' ),
			'wp_footer'      => __( 'Site-wide footer', 'aegis' ),
			'before_content' => __( 'Before content', 'aegis' ),
			'after_content'  => __( 'After content', 'aegis' ),
		);
	}

	/**
	 * @return array<string, string>
	 */
	public static function css_locations(): array {
		return array(
			'css_frontend' => __( 'Frontend', 'aegis' ),
			'css_admin'    => __( 'Admin', 'aegis' ),
			'css_login'    => __( 'Login screen', 'aegis' ),
			'css_editor'   => __( 'Block editor', 'aegis' ),
		);
	}

	/**
	 * @return array<string, string>
	 */
	public static function js_locations(): array {
		return array(
			'js_frontend_header' => __( 'Frontend header', 'aegis' ),
			'js_frontend_footer' => __( 'Frontend footer', 'aegis' ),
			'js_admin_header'    => __( 'Admin header', 'aegis' ),
			'js_admin_footer'    => __( 'Admin footer', 'aegis' ),
			'js_login'           => __( 'Login screen', 'aegis' ),
			'js_editor'          => __( 'Block editor', 'aegis' ),
		);
	}

	/**
	 * @return array<string, string>
	 */
	public static function location_help(): array {
		return array(
			'php_everywhere'     => __( 'Runs on every request, like code in functions.php.', 'aegis' ),
			'php_frontend'       => __( 'Runs on the public site only.', 'aegis' ),
			'php_admin'          => __( 'Runs in wp-admin only.', 'aegis' ),
			'php_login'          => __( 'Runs on the login screen.', 'aegis' ),
			'wp_head'            => __( 'Insert snippet between the head tags of your website (frontend).', 'aegis' ),
			'wp_body_open'       => __( 'Insert snippet after the opening body tag of your website (frontend).', 'aegis' ),
			'wp_footer'          => __( 'Insert snippet before the closing body tag of your website on all pages (frontend).', 'aegis' ),
			'before_content'     => __( 'Insert snippet at the beginning of single post/page content.', 'aegis' ),
			'after_content'      => __( 'Insert snippet at the end of single post/page content.', 'aegis' ),
			'shortcode'          => __( 'Only display when inserted into a post or page using shortcode.', 'aegis' ),
			'css_frontend'       => __( 'Load this CSS on the public site.', 'aegis' ),
			'css_admin'          => __( 'Load this CSS in wp-admin.', 'aegis' ),
			'css_login'          => __( 'Load this CSS on the login screen.', 'aegis' ),
			'css_editor'         => __( 'Load this CSS in the block editor.', 'aegis' ),
			'js_frontend_header' => __( 'Print this script in the frontend head.', 'aegis' ),
			'js_frontend_footer' => __( 'Print this script in the frontend footer.', 'aegis' ),
			'js_admin_header'    => __( 'Print this script in the admin head.', 'aegis' ),
			'js_admin_footer'    => __( 'Print this script in the admin footer.', 'aegis' ),
			'js_login'           => __( 'Print this script on the login screen.', 'aegis' ),
			'js_editor'          => __( 'Print this script in the block editor.', 'aegis' ),
		);
	}

	public static function help_for( string $location ): string {
		$help = self::location_help();

		return $help[ $location ] ?? __( 'Runs at this WordPress hook.', 'aegis' );
	}

	/**
	 * Primary chooser cards for a snippet type (custom hooks stay in the select).
	 *
	 * @return array<int, array{value: string, label: string, help: string, wide: bool}>
	 */
	public static function cards_for_type( string $type ): array {
		$type   = self::normalize_type( $type );
		$labels = match ( $type ) {
			self::TYPE_PHP => self::php_locations(),
			self::TYPE_CSS => self::css_locations(),
			self::TYPE_JS  => self::js_locations(),
			default        => self::content_locations(),
		};
		$wide = match ( $type ) {
			self::TYPE_CONTENT => array( 'shortcode', 'wp_head', 'wp_body_open', 'wp_footer' ),
			self::TYPE_PHP     => array_keys( $labels ),
			default            => array(),
		};

		$cards = array();

		foreach ( $labels as $value => $label ) {
			$cards[] = array(
				'value' => (string) $value,
				'label' => (string) $label,
				'help'  => self::help_for( (string) $value ),
				'wide'  => in_array( (string) $value, $wide, true ),
			);
		}

		return $cards;
	}

	/**
	 * @return array<string, string>
	 */
	public static function type_badges(): array {
		return array(
			self::TYPE_PHP     => 'PHP',
			self::TYPE_CONTENT => 'PHP + HTML',
			self::TYPE_CSS     => 'CSS',
			self::TYPE_JS      => 'JavaScript',
		);
	}

	public static function starter( string $type ): string {
		return match ( self::normalize_type( $type ) ) {
			self::TYPE_PHP => "<?php\n\n",
			self::TYPE_CSS => "/* Custom CSS */\n\n",
			self::TYPE_JS  => "(function () {\n\t'use strict';\n\n})();\n",
			default        => "<!-- begin content (HTML / PHP / Mixed) -->\n\n<!-- end content -->\n",
		};
	}

	/**
	 * @param array<string, string> $custom_hooks Hook => description.
	 * @return array<string, array<int, array{label: string, options: array<string, string>}>>
	 */
	public static function for_editor( array $custom_hooks ): array {
		$custom_group = array(
			'label'   => __( 'Custom hook', 'aegis' ),
			'options' => $custom_hooks,
		);

		return array(
			self::TYPE_PHP     => array(
				array(
					'label'   => '',
					'options' => self::php_locations(),
				),
				$custom_group,
			),
			self::TYPE_CONTENT => array(
				array(
					'label'   => '',
					'options' => self::content_locations(),
				),
				$custom_group,
			),
			self::TYPE_CSS     => array(
				array(
					'label'   => '',
					'options' => self::css_locations(),
				),
			),
			self::TYPE_JS      => array(
				array(
					'label'   => '',
					'options' => self::js_locations(),
				),
			),
		);
	}

	/**
	 * Flatten grouped injection locations into hook => description.
	 *
	 * @param array<string, array<string, string>> $grouped Grouped locations.
	 * @return array<string, string>
	 */
	public static function flatten_hooks( array $grouped ): array {
		$flat = array();

		foreach ( $grouped as $hooks ) {
			foreach ( $hooks as $hook => $label ) {
				$flat[ (string) $hook ] = (string) $label;
			}
		}

		return $flat;
	}

	/**
	 * Injection catalog hooks that are not first-class type locations.
	 *
	 * @param array<string, array<string, string>> $grouped Grouped locations.
	 * @return array<string, string>
	 */
	public static function custom_hooks_for_editor( array $grouped ): array {
		$flat     = self::flatten_hooks( $grouped );
		$reserved = array_merge(
			array_keys( self::php_locations() ),
			array_keys( self::content_locations() ),
			array_keys( self::css_locations() ),
			array_keys( self::js_locations() )
		);

		foreach ( $reserved as $key ) {
			unset( $flat[ $key ] );
		}

		return $flat;
	}

	public static function default_location( string $type ): string {
		return match ( self::normalize_type( $type ) ) {
			self::TYPE_PHP => 'php_everywhere',
			self::TYPE_CSS => 'css_frontend',
			self::TYPE_JS  => 'js_frontend_footer',
			default        => 'wp_head',
		};
	}

	/**
	 * Stable filename-style id from a snippet title (hyphens kept).
	 */
	public static function id_from_title( string $title ): string {
		$slug = sanitize_title( $title );
		$slug = (string) preg_replace( '/[^a-z0-9\-]/', '', $slug );
		$slug = trim( $slug, '-' );

		return $slug !== '' ? $slug : 'snippet';
	}

	public static function shortcode_markup( string $id ): string {
		$id = trim( $id );

		if ( $id === '' ) {
			$id = 'snippet';
		}

		return '[' . self::SHORTCODE_TAG . ' id="' . $id . '"]';
	}

	public static function default_shortcode( string $id ): string {
		$slug = sanitize_key( str_replace( '-', '', $id ) );

		if ( $slug === '' ) {
			return self::SHORTCODE_TAG;
		}

		return 'aegis_' . substr( $slug, 0, 8 );
	}

	/**
	 * @param array<string, mixed> $snippet Snippet row.
	 */
	public static function migrate_location( array $snippet ): string {
		$type     = self::normalize_type( (string) ( $snippet['type'] ?? self::TYPE_CONTENT ) );
		$location = (string) ( $snippet['location'] ?? '' );
		$scope    = sanitize_key( (string) ( $snippet['scope'] ?? 'frontend' ) );

		if ( $type === self::TYPE_CSS ) {
			if ( isset( self::css_locations()[ $location ] ) ) {
				return $location;
			}

			return match ( $scope ) {
				'admin'  => 'css_admin',
				'login'  => 'css_login',
				'editor' => 'css_editor',
				default  => 'css_frontend',
			};
		}

		if ( $type === self::TYPE_JS ) {
			if ( isset( self::js_locations()[ $location ] ) ) {
				return $location;
			}

			return match ( $scope ) {
				'admin'  => 'js_admin_footer',
				'login'  => 'js_login',
				'editor' => 'js_editor',
				default  => ( $location === 'wp_head' ) ? 'js_frontend_header' : 'js_frontend_footer',
			};
		}

		if ( $type === self::TYPE_PHP ) {
			if ( isset( self::php_locations()[ $location ] ) ) {
				return $location;
			}

			return $location !== '' ? $location : 'php_everywhere';
		}

		if ( isset( self::content_locations()[ $location ] ) ) {
			return $location;
		}

		return $location !== '' ? $location : 'wp_head';
	}

	public static function scope_from_location( string $type, string $location ): string {
		$type = self::normalize_type( $type );

		if ( $type === self::TYPE_CSS ) {
			return match ( $location ) {
				'css_admin'  => 'admin',
				'css_login'  => 'login',
				'css_editor' => 'editor',
				default      => 'frontend',
			};
		}

		if ( $type === self::TYPE_JS ) {
			return match ( $location ) {
				'js_admin_header', 'js_admin_footer' => 'admin',
				'js_login'  => 'login',
				'js_editor' => 'editor',
				default     => 'frontend',
			};
		}

		if ( $type === self::TYPE_PHP ) {
			return match ( $location ) {
				'php_admin' => 'admin',
				'php_login' => 'login',
				default     => 'frontend',
			};
		}

		return 'frontend';
	}

	public static function label( string $location, string $type = '' ): string {
		$maps = array_merge(
			self::php_locations(),
			self::content_locations(),
			self::css_locations(),
			self::js_locations()
		);

		if ( isset( $maps[ $location ] ) ) {
			return $maps[ $location ];
		}

		unset( $type );

		return $location;
	}

	public static function is_virtual( string $location ): bool {
		$maps = array_merge(
			self::php_locations(),
			self::content_locations(),
			self::css_locations(),
			self::js_locations()
		);

		return isset( $maps[ $location ] );
	}

	/**
	 * Sanitize a shortcode tag.
	 */
	public static function sanitize_shortcode( string $shortcode, string $fallback_id ): string {
		$shortcode = sanitize_key( $shortcode );
		$shortcode = (string) preg_replace( '/[^a-z0-9_]/', '', $shortcode );

		if ( $shortcode === '' ) {
			return $fallback_id === '' ? '' : self::default_shortcode( $fallback_id );
		}

		return $shortcode;
	}
}
