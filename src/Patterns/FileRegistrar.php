<?php
/**
 * Registers a block pattern from a PHP file with a fixed slug.
 *
 * @package Aegis\Plugin\Patterns
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Patterns;

use function explode;
use function file_exists;
use function get_file_data;
use function register_block_pattern;
use function trim;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers plugin patterns that use explicit slugs (e.g. aegis/slider-hero).
 */
final class FileRegistrar {

	/**
	 * Register a pattern when the file exists and has a Title header.
	 *
	 * @param string $name Pattern slug.
	 * @param string $file Absolute path to the pattern PHP file.
	 */
	public static function register( string $name, string $file ): void {
		if ( ! file_exists( $file ) ) {
			return;
		}

		$headers = get_file_data(
			$file,
			array(
				'title'       => 'Title',
				'categories'  => 'Categories',
				'description' => 'Description',
			)
		);

		$title = trim( (string) ( $headers['title'] ?? '' ) );
		if ( '' === $title ) {
			return;
		}

		$properties = array(
			'title'    => $title,
			'filePath' => $file,
		);

		$categories = trim( (string) ( $headers['categories'] ?? '' ) );
		if ( '' !== $categories ) {
			$properties['categories'] = array_map( 'trim', explode( ',', $categories ) );
		}

		$description = trim( (string) ( $headers['description'] ?? '' ) );
		if ( '' !== $description ) {
			$properties['description'] = $description;
		}

		register_block_pattern( $name, $properties );
	}
}
