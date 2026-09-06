<?php
/**
 * Video block Schema.org output for core/video.
 *
 * @package Aegis\Plugin\Blocks
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Blocks;

use Aegis\Plugin\Settings\Repository;
use function absint;
use function add_filter;
use function esc_url;
use function get_post_thumbnail_id;
use function sanitize_text_field;
use function wp_get_attachment_image_url;
use function wp_get_attachment_url;
use function wp_json_encode;
use function wp_strip_all_tags;
use function wp_trim_words;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Appends VideoObject JSON-LD to core/video block output.
 */
final class VideoSchema {

	/**
	 * Register render filter.
	 */
	public function init(): void {
		add_filter( 'render_block_core/video', [ $this, 'append_schema' ], 30, 2 );
	}

	/**
	 * Append VideoObject JSON-LD when enabled and not delegated to an SEO plugin.
	 *
	 * @param string               $content Block HTML.
	 * @param array<string, mixed> $block   Parsed block.
	 */
	public function append_schema( string $content, array $block ): string {
		if ( ! Repository::is_block_enabled( 'video_schema_markup' ) ) {
			return $content;
		}

		if ( Repository::is_schema_delegated_to_seo( 'video' ) ) {
			return $content;
		}

		$attrs = $block['attrs'] ?? [];

		$video_url = '';
		if ( ! empty( $attrs['src'] ) ) {
			$video_url = (string) $attrs['src'];
		} elseif ( ! empty( $attrs['id'] ) ) {
			$video_url = (string) wp_get_attachment_url( absint( $attrs['id'] ) );
		}

		if ( $video_url === '' && ! empty( $block['innerHTML'] ) && preg_match( '/src="([^"]+)"/', (string) $block['innerHTML'], $matches ) ) {
			$video_url = $matches[1];
		}

		if ( $video_url === '' ) {
			return $content;
		}

		$thumbnail_url = '';
		if ( ! empty( $attrs['poster'] ) ) {
			$thumbnail_url = (string) $attrs['poster'];
		} else {
			$post_id = get_the_ID();
			if ( $post_id ) {
				$thumb_id = get_post_thumbnail_id( $post_id );
				if ( $thumb_id ) {
					$thumbnail_url = (string) wp_get_attachment_image_url( $thumb_id, 'large' );
				}
			}
		}

		$title = '';
		if ( ! empty( $attrs['caption'] ) ) {
			$title = wp_strip_all_tags( (string) $attrs['caption'] );
		}
		if ( $title === '' ) {
			$title = get_the_title() ?: '';
		}

		$description = '';
		if ( ! empty( $attrs['aegisProDescription'] ) ) {
			$description = wp_strip_all_tags( (string) $attrs['aegisProDescription'] );
		} else {
			$post = get_post();
			if ( $post ) {
				$description = wp_trim_words( $post->post_excerpt ?: $post->post_content, 30 );
			}
		}

		$schema = [
			'@context'     => 'https://schema.org',
			'@type'        => 'VideoObject',
			'name'         => sanitize_text_field( $title ),
			'description'  => sanitize_text_field( $description ),
			'contentUrl'   => esc_url( $video_url ),
			'thumbnailUrl' => esc_url( $thumbnail_url ),
			'uploadDate'   => get_the_date( 'c' ) ?: '',
		];

		if ( ! empty( $attrs['aegisProDuration'] ) ) {
			$schema['duration'] = 'PT' . absint( $attrs['aegisProDuration'] ) . 'S';
		}

		return $content . '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES ) . '</script>';
	}
}
