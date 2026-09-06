<?php
/**
 * PHP snippet executor.
 *
 * @package Aegis\Plugin\Snippets\Executors
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Snippets\Executors;

use Aegis\Plugin\Snippets\Settings;
use Aegis\Plugin\Snippets\Storage;
use function defined;
use function error_get_last;
use function in_array;
use function is_array;
use function is_readable;
use function register_shutdown_function;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Includes PHP snippet files from the protected snippets directory.
 */
final class PhpExecutor {

	/** @var string|null */
	private static ?string $running_id = null;

	/**
	 * @param array<string, mixed> $snippet Snippet manifest entry.
	 */
	public function render( array $snippet ): void {
		$this->execute( $snippet );
	}

	/**
	 * Include a PHP snippet and return its return value.
	 *
	 * `$atts` and `$content` are in scope for the included file.
	 *
	 * @param array<string, mixed> $snippet Snippet manifest entry.
	 * @param array<string, mixed> $context Optional `atts` and `content` for shortcodes.
	 * @return mixed
	 */
	public function execute( array $snippet, array $context = array() ): mixed {
		if ( defined( 'AEGIS_DISABLE_SNIPPETS' ) && AEGIS_DISABLE_SNIPPETS ) {
			return null;
		}

		if ( ! Settings::is_php_enabled() ) {
			return null;
		}

		$file = (string) ( $snippet['file'] ?? '' );
		$path = $file !== '' ? Storage::resolve_snippet_path( $file ) : null;
		$id   = (string) ( $snippet['id'] ?? '' );

		if ( $path === null || ! is_readable( $path ) ) {
			return null;
		}

		self::$running_id = $id;

		register_shutdown_function(
			static function () use ( $id ): void {
				if ( self::$running_id !== $id ) {
					return;
				}

				$error = error_get_last();

				if ( $error && in_array( $error['type'], array( E_ERROR, E_PARSE, E_COMPILE_ERROR ), true ) ) {
					Storage::disable_snippet(
						$id,
						(string) ( $error['message'] ?? 'Fatal error' )
					);

					if ( Settings::get_settings()['auto_safe_mode_on_fatal'] ?? false ) {
						Settings::enable_safe_mode();
					}
				}

				self::$running_id = null;
			}
		);

		$atts    = is_array( $context['atts'] ?? null ) ? $context['atts'] : array();
		$content = $context['content'] ?? null;

		// phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable -- path validated via realpath.
		$returned = include $path;

		self::$running_id = null;

		return $returned;
	}
}
