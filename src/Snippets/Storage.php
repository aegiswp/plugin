<?php
/**
 * File-based snippet storage.
 *
 * @package Aegis\Plugin\Snippets
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Snippets;

use function basename;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_array;
use function is_string;
use function is_writable;
use function mkdir;
use function realpath;
use function rtrim;
use function sanitize_file_name;
use function sanitize_text_field;
use function str_contains;
use function str_starts_with;
use function unlink;
use function wp_generate_uuid4;
use function wp_json_encode;
use function wp_mkdir_p;
use function wp_normalize_path;
use function wp_upload_dir;
use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reads and writes snippets under uploads/aegis-snippets/.
 */
final class Storage {

	public const MANIFEST = 'manifest.json';

	/** @var array<string, mixed>|null */
	private static ?array $manifest_cache = null;

	/**
	 * Absolute path to the snippets directory.
	 */
	public static function get_dir(): string {
		$upload = wp_upload_dir();

		return rtrim( (string) $upload['basedir'], '/\\' ) . '/aegis-snippets';
	}

	/**
	 * Public URL base (not directly web-accessible; for admin reference only).
	 */
	public static function get_url(): string {
		$upload = wp_upload_dir();

		return rtrim( (string) $upload['baseurl'], '/\\' ) . '/aegis-snippets';
	}

	/**
	 * Ensure storage directory exists with protection files.
	 */
	public static function ensure_directory(): bool {
		$dir = self::get_dir();

		if ( ! wp_mkdir_p( $dir ) ) {
			return false;
		}

		self::write_protection_files( $dir );

		return is_dir( $dir ) && is_writable( $dir );
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_snippets(): array {
		$manifest = self::read_manifest();
		$snippets = is_array( $manifest['snippets'] ?? null ) ? $manifest['snippets'] : array();

		return array_map( array( self::class, 'normalize_snippet' ), $snippets );
	}

	/**
	 * @param array<string, mixed> $snippet Snippet entry.
	 * @return array<string, mixed>
	 */
	public static function normalize_snippet( array $snippet ): array {
		if ( empty( $snippet['created_at'] ) ) {
			$snippet['created_at'] = gmdate( 'c' );
		}

		if ( empty( $snippet['author'] ) ) {
			$snippet['author'] = '';
		}

		if ( ! isset( $snippet['tags'] ) || ! is_array( $snippet['tags'] ) ) {
			$snippet['tags'] = array();
		}

		if ( ! isset( $snippet['note'] ) ) {
			$snippet['note'] = '';
		}

		$snippet['type']     = Locations::normalize_type( (string) ( $snippet['type'] ?? Locations::TYPE_CONTENT ) );
		$snippet['location'] = Locations::migrate_location( $snippet );
		$snippet['scope']    = Locations::scope_from_location( $snippet['type'], (string) $snippet['location'] );

		if ( ! isset( $snippet['group'] ) || ! is_string( $snippet['group'] ) ) {
			$snippet['group'] = '';
		} else {
			$snippet['group'] = sanitize_text_field( $snippet['group'] );
		}

		if ( empty( $snippet['shortcode'] ) ) {
			$snippet['shortcode'] = '';
		} else {
			$snippet['shortcode'] = Locations::sanitize_shortcode(
				(string) $snippet['shortcode'],
				''
			);
		}

		return $snippet;
	}

	/**
	 * Filename-style id from a title, unique among existing snippets.
	 */
	public static function unique_id_from_title( string $title ): string {
		$base = Locations::id_from_title( $title );
		$id   = $base;
		$n    = 2;

		while ( self::get_snippet( $id ) !== null ) {
			$id = $base . '-' . $n;
			++$n;
		}

		return $id;
	}

	/**
	 * Toggle snippet enabled state.
	 */
	public static function toggle_snippet( string $id, bool $enabled ): bool {
		$manifest = self::read_manifest();
		$snippets = is_array( $manifest['snippets'] ?? null ) ? $manifest['snippets'] : array();

		foreach ( $snippets as $index => $snippet ) {
			if ( ( $snippet['id'] ?? '' ) === $id ) {
				$snippets[ $index ]['enabled'] = $enabled;
				$manifest['snippets']          = $snippets;
				return self::write_manifest( $manifest );
			}
		}

		return false;
	}

	/**
	 * @return array<string, mixed>|null
	 */
	public static function get_snippet( string $id ): ?array {
		foreach ( self::get_snippets() as $snippet ) {
			if ( ( $snippet['id'] ?? '' ) === $id ) {
				return $snippet;
			}
		}

		return null;
	}

	/**
	 * @param array<string, mixed> $snippet Snippet manifest entry.
	 */
	public static function save_snippet( array $snippet ): bool {
		if ( ! self::ensure_directory() ) {
			return false;
		}

		$id   = (string) ( $snippet['id'] ?? wp_generate_uuid4() );
		$type = Locations::normalize_type( (string) ( $snippet['type'] ?? Locations::TYPE_CONTENT ) );

		$manifest  = self::read_manifest();
		$snippets  = is_array( $manifest['snippets'] ?? null ) ? $manifest['snippets'] : array();
		$found     = false;
		$file_name = sanitize_file_name( $id ) . '.' . self::extension_for_type( $type );

		foreach ( $snippets as $index => $existing ) {
			if ( ( $existing['id'] ?? '' ) === $id ) {
				$snippet['id']       = $id;
				$snippet['file']     = $file_name;
				$snippets[ $index ]  = $snippet;
				$found               = true;
				break;
			}
		}

		if ( ! $found ) {
			$snippet['id']   = $id;
			$snippet['file'] = $file_name;
			$snippets[]      = $snippet;
		}

		$manifest['snippets'] = array_values( $snippets );

		return self::write_manifest( $manifest );
	}

	/**
	 * Write snippet body to its typed file.
	 */
	public static function write_snippet_file( string $id, string $type, string $content ): bool {
		if ( ! self::ensure_directory() ) {
			return false;
		}

		$path = self::resolve_snippet_path( sanitize_file_name( $id ) . '.' . self::extension_for_type( $type ) );

		if ( $path === null ) {
			return false;
		}

		return false !== file_put_contents( $path, $content );
	}

	/**
	 * Read snippet file contents.
	 */
	public static function read_snippet_file( array $snippet ): string {
		$file = (string) ( $snippet['file'] ?? '' );

		if ( $file === '' ) {
			return '';
		}

		$path = self::resolve_snippet_path( $file );

		if ( $path === null || ! file_exists( $path ) ) {
			return '';
		}

		return (string) file_get_contents( $path );
	}

	/**
	 * @return array{version: int, plugin: string, snippets: array<int, array<string, mixed>>}
	 */
	public static function export_payload( ?string $id = null ): array {
		$items = array();

		foreach ( self::get_snippets() as $snippet ) {
			$snippet_id = (string) ( $snippet['id'] ?? '' );

			if ( $id !== null && $snippet_id !== $id ) {
				continue;
			}

			$snippet['content'] = self::read_snippet_file( $snippet );
			$items[]            = $snippet;
		}

		return array(
			'version'  => 1,
			'plugin'   => 'aegis-snippets',
			'snippets' => $items,
		);
	}

	/**
	 * @param array<string, mixed> $payload Export payload.
	 */
	public static function import_payload( array $payload ): int {
		$imported = is_array( $payload['snippets'] ?? null ) ? $payload['snippets'] : array();
		$count    = 0;

		if ( $imported === array() && isset( $payload['id'] ) ) {
			$imported = array( $payload );
		}

		foreach ( $imported as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$content = (string) ( $row['content'] ?? '' );
			unset( $row['content'] );

			$id = sanitize_file_name( (string) ( $row['id'] ?? '' ) );

			if ( $id === '' ) {
				$id         = self::unique_id_from_title( (string) ( $row['title'] ?? 'snippet' ) );
				$row['id']  = $id;
			}

			$type = Locations::normalize_type( (string) ( $row['type'] ?? Locations::TYPE_CONTENT ) );
			$row['type'] = $type;
			$row['id']   = $id;

			if ( self::save_snippet( $row ) && self::write_snippet_file( $id, $type, $content ) ) {
				++$count;
			}
		}

		return $count;
	}

	public static function delete_snippet( string $id ): bool {
		$manifest = self::read_manifest();
		$snippets = is_array( $manifest['snippets'] ?? null ) ? $manifest['snippets'] : array();
		$kept     = array();

		foreach ( $snippets as $snippet ) {
			if ( ( $snippet['id'] ?? '' ) === $id ) {
				$file = (string) ( $snippet['file'] ?? '' );
				$path = $file !== '' ? self::resolve_snippet_path( $file ) : null;

				if ( $path !== null && file_exists( $path ) ) {
					unlink( $path );
				}
				continue;
			}

			$kept[] = $snippet;
		}

		$manifest['snippets'] = array_values( $kept );

		return self::write_manifest( $manifest );
	}

	/**
	 * Mark a snippet disabled and store last error message.
	 */
	public static function disable_snippet( string $id, string $error ): void {
		$manifest = self::read_manifest();
		$snippets = is_array( $manifest['snippets'] ?? null ) ? $manifest['snippets'] : array();

		foreach ( $snippets as $index => $snippet ) {
			if ( ( $snippet['id'] ?? '' ) === $id ) {
				$snippets[ $index ]['enabled']    = false;
				$snippets[ $index ]['last_error'] = $error;
				break;
			}
		}

		$manifest['snippets'] = $snippets;
		self::write_manifest( $manifest );
	}

	/**
	 * Validate that a file path stays inside the snippets directory.
	 */
	public static function resolve_snippet_path( string $file_name ): ?string {
		$file_name = basename( $file_name );

		if ( $file_name === '' || str_contains( $file_name, '..' ) ) {
			return null;
		}

		$dir  = self::get_dir();
		$full = $dir . '/' . $file_name;

		self::ensure_directory();

		$real_dir = realpath( $dir );

		if ( $real_dir === false ) {
			return null;
		}

		if ( file_exists( $full ) ) {
			$real_file = realpath( $full );

			if ( $real_file === false ) {
				return null;
			}

			if ( ! str_starts_with( wp_normalize_path( $real_file ), wp_normalize_path( $real_dir ) ) ) {
				return null;
			}

			return $real_file;
		}

		$candidate = $real_dir . '/' . $file_name;

		if ( ! str_starts_with( wp_normalize_path( $candidate ), wp_normalize_path( $real_dir ) ) ) {
			return null;
		}

		return $candidate;
	}

	/**
	 * @return array<string, mixed>
	 */
	private static function read_manifest(): array {
		if ( self::$manifest_cache !== null ) {
			return self::$manifest_cache;
		}

		self::ensure_directory();

		$path = self::get_dir() . '/' . self::MANIFEST;

		if ( ! file_exists( $path ) ) {
			self::$manifest_cache = array( 'version' => 1, 'snippets' => array() );
			return self::$manifest_cache;
		}

		$decoded = json_decode( (string) file_get_contents( $path ), true );

		self::$manifest_cache = is_array( $decoded )
			? $decoded
			: array( 'version' => 1, 'snippets' => array() );

		return self::$manifest_cache;
	}

	/**
	 * @param array<string, mixed> $manifest Manifest data.
	 */
	private static function write_manifest( array $manifest ): bool {
		self::$manifest_cache = $manifest;

		$path = self::get_dir() . '/' . self::MANIFEST;

		return false !== file_put_contents(
			$path,
			wp_json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES )
		);
	}

	private static function extension_for_type( string $type ): string {
		return match ( Locations::normalize_type( $type ) ) {
			'css' => 'css',
			'js'  => 'js',
			'php' => 'php',
			default => 'html',
		};
	}

	private static function write_protection_files( string $dir ): void {
		$htaccess = $dir . '/.htaccess';

		if ( ! file_exists( $htaccess ) ) {
			file_put_contents(
				$htaccess,
				"# Aegis Snippets — deny direct access\n<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n"
			);
		}

		$index = $dir . '/index.php';

		if ( ! file_exists( $index ) ) {
			file_put_contents( $index, "<?php\n// Silence is golden.\n" );
		}
	}
}
