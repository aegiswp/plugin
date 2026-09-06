<?php
/**
 * Co-Authors Plus Integration
 *
 * Fixed integration migrated from the Aegis Framework. Replaces single-author
 * block output with multi-author display when Co-Authors Plus is active.
 *
 * Key fixes over the framework version:
 * - Properly handles guest authors (CPT posts) vs real WP users for avatars and URLs
 * - Uses explicit hook registration instead of annotation-based hooks
 * - Outputs JSON-LD Person schema for multi-author singular posts
 *
 * @package Aegis\Plugin\CoAuthors
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\CoAuthors;

use WP_Block;
use function add_filter;
use function add_action;
use function get_the_ID;
use function get_avatar;
use function get_avatar_url;
use function esc_html;
use function esc_url;
use function get_author_posts_url;
use function is_singular;
use function wp_json_encode;
use function wp_kses_post;
use function wp_enqueue_style;
use function plugins_url;
use function property_exists;
use function home_url;
use function get_user_by;
use function function_exists;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CoAuthorsPlus {

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		add_filter( 'render_block_core/post-author', [ $this, 'render_post_author' ], 10, 3 );
		add_filter( 'render_block_core/post-author-name', [ $this, 'render_post_author_name' ], 10, 3 );
		add_filter( 'render_block_core/post-author-biography', [ $this, 'render_post_author_biography' ], 10, 3 );
		add_filter( 'body_class', [ $this, 'add_body_class' ] );
		add_action( 'wp_head', [ $this, 'output_author_schema' ], 5 );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );
	}

	/**
	 * Get the author URL, handling both real users and guest authors.
	 *
	 * Guest authors in Co-Authors Plus are CPT posts, not real WP users.
	 * Their ->ID is a post ID, not a user ID. We must check for this
	 * to avoid passing a post ID to get_author_posts_url().
	 *
	 * @param object $coauthor Co-author object.
	 *
	 * @return string Author archive URL.
	 */
	private function get_coauthor_url( object $coauthor ): string {
		// Guest authors have a 'type' property set to 'guest-author'.
		if ( isset( $coauthor->type ) && 'guest-author' === $coauthor->type ) {
			// Guest authors use user_nicename for the slug but don't have a real user ID.
			// get_author_posts_url with the linked_account's ID or fallback to the guest slug.
			if ( ! empty( $coauthor->linked_account ) ) {
				$linked_user = get_user_by( 'login', $coauthor->linked_account );
				if ( $linked_user ) {
					return get_author_posts_url( (int) $linked_user->ID, $linked_user->user_nicename );
				}
			}
			// Fallback: build author URL from the guest author slug.
			return home_url( '/author/' . $coauthor->user_nicename . '/' );
		}

		// Real WP user — safe to use ID directly.
		return get_author_posts_url( (int) $coauthor->ID, $coauthor->user_nicename );
	}

	/**
	 * Get avatar HTML for a co-author, handling guest authors.
	 *
	 * @param object $coauthor Co-author object.
	 * @param int    $size     Avatar pixel size.
	 *
	 * @return string Avatar HTML.
	 */
	private function get_coauthor_avatar( object $coauthor, int $size ): string {
		// Use CAP's own function if available (handles guest authors natively).
		if ( function_exists( 'coauthors_get_avatar' ) ) {
			return coauthors_get_avatar( $coauthor, $size );
		}

		// Fallback for real users.
		if ( ! isset( $coauthor->type ) || 'guest-author' !== $coauthor->type ) {
			return get_avatar( $coauthor->ID, $size );
		}

		return '';
	}

	/**
	 * Get avatar URL for a co-author, handling guest authors.
	 *
	 * @param object $coauthor Co-author object.
	 * @param int    $size     Avatar pixel size.
	 *
	 * @return string Avatar URL or empty string.
	 */
	private function get_coauthor_avatar_url( object $coauthor, int $size ): string {
		// Guest authors may have a featured image as avatar.
		if ( isset( $coauthor->type ) && 'guest-author' === $coauthor->type ) {
			if ( function_exists( 'coauthors_get_avatar' ) ) {
				// Extract URL from avatar HTML.
				$avatar_html = coauthors_get_avatar( $coauthor, $size );
				if ( preg_match( '/src=["\']([^"\']+)/', $avatar_html, $matches ) ) {
					return $matches[1];
				}
			}
			return '';
		}

		return (string) get_avatar_url( $coauthor->ID, [ 'size' => $size ] );
	}

	/**
	 * Replace core/post-author block with co-authors list.
	 *
	 * @param string   $block_content Original block HTML.
	 * @param array    $block         Parsed block array.
	 * @param WP_Block $instance      Block instance.
	 *
	 * @return string Multi-author HTML or original for single authors.
	 */
	public function render_post_author( string $block_content, array $block, WP_Block $instance ): string {
		$post_id = get_the_ID();

		if ( ! $post_id ) {
			return $block_content;
		}

		$coauthors = get_coauthors( $post_id );

		if ( empty( $coauthors ) || count( $coauthors ) <= 1 ) {
			return $block_content;
		}

		$show_avatar = $block['attrs']['showAvatar'] ?? true;
		$avatar_size = $block['attrs']['avatarSize'] ?? 48;
		$is_link     = $block['attrs']['isLink'] ?? true;
		$output      = '<div class="aegis-coauthors-list wp-block-post-author">';

		foreach ( $coauthors as $i => $coauthor ) {
			$name       = esc_html( $coauthor->display_name );
			$author_url = $this->get_coauthor_url( $coauthor );

			$output .= '<div class="aegis-coauthor">';

			if ( $show_avatar ) {
				$avatar  = $this->get_coauthor_avatar( $coauthor, (int) $avatar_size );
				$output .= '<div class="aegis-coauthor-avatar wp-block-post-author__avatar">' . $avatar . '</div>';
			}

			$output .= '<div class="aegis-coauthor-content wp-block-post-author__content">';

			if ( $is_link ) {
				$output .= '<span class="wp-block-post-author__name"><a href="' . esc_url( $author_url ) . '">' . $name . '</a></span>';
			} else {
				$output .= '<span class="wp-block-post-author__name">' . $name . '</span>';
			}

			$output .= '</div>';
			$output .= '</div>';

			if ( $i < count( $coauthors ) - 1 ) {
				$output .= '<span class="aegis-coauthor-separator">,</span>';
			}
		}

		$output .= '</div>';

		return $output;
	}

	/**
	 * Replace core/post-author-name block with formatted co-authors.
	 *
	 * @param string   $block_content Original block HTML.
	 * @param array    $block         Parsed block array.
	 * @param WP_Block $instance      Block instance.
	 *
	 * @return string Formatted names or original for single authors.
	 */
	public function render_post_author_name( string $block_content, array $block, WP_Block $instance ): string {
		$post_id = get_the_ID();

		if ( ! $post_id ) {
			return $block_content;
		}

		$coauthors = get_coauthors( $post_id );

		if ( empty( $coauthors ) || count( $coauthors ) <= 1 ) {
			return $block_content;
		}

		$is_link = $block['attrs']['isLink'] ?? true;
		$names   = [];

		foreach ( $coauthors as $coauthor ) {
			$name       = esc_html( $coauthor->display_name );
			$author_url = $this->get_coauthor_url( $coauthor );

			if ( $is_link ) {
				$names[] = '<a href="' . esc_url( $author_url ) . '">' . $name . '</a>';
			} else {
				$names[] = $name;
			}
		}

		$last = array_pop( $names );
		$list = empty( $names ) ? $last : implode( ', ', $names ) . ' &amp; ' . $last;

		return '<p class="wp-block-post-author-name aegis-coauthors-names">' . $list . '</p>';
	}

	/**
	 * Replace core/post-author-biography with stacked co-author biographies.
	 *
	 * @param string   $block_content Original block HTML.
	 * @param array    $block         Parsed block array.
	 * @param WP_Block $instance      Block instance.
	 *
	 * @return string Stacked biographies or original for single authors.
	 */
	public function render_post_author_biography( string $block_content, array $block, WP_Block $instance ): string {
		$post_id = get_the_ID();

		if ( ! $post_id ) {
			return $block_content;
		}

		$coauthors = get_coauthors( $post_id );

		if ( empty( $coauthors ) || count( $coauthors ) <= 1 ) {
			return $block_content;
		}

		$output = '<div class="aegis-coauthors-biographies">';

		foreach ( $coauthors as $coauthor ) {
			$bio = $coauthor->description ?? '';

			if ( empty( $bio ) ) {
				continue;
			}

			$name    = esc_html( $coauthor->display_name );
			$output .= '<div class="aegis-coauthor-bio">';
			$output .= '<p class="aegis-coauthor-bio-name">' . $name . '</p>';
			$output .= '<p class="wp-block-post-author-biography aegis-coauthor-bio-text">' . wp_kses_post( $bio ) . '</p>';
			$output .= '</div>';
		}

		$output .= '</div>';

		return $output;
	}

	/**
	 * Output JSON-LD Person schema for co-authors on singular posts.
	 *
	 * @return void
	 */
	public function output_author_schema(): void {
		if ( ! is_singular( 'post' ) || ! function_exists( 'get_coauthors' ) ) {
			return;
		}

		$post_id   = get_the_ID();
		$coauthors = get_coauthors( $post_id );

		if ( empty( $coauthors ) || count( $coauthors ) <= 1 ) {
			return;
		}

		$persons = [];

		foreach ( $coauthors as $coauthor ) {
			$person = [
				'@type' => 'Person',
				'name'  => $coauthor->display_name,
				'url'   => $this->get_coauthor_url( $coauthor ),
			];

			$avatar_url = $this->get_coauthor_avatar_url( $coauthor, 96 );
			if ( $avatar_url ) {
				$person['image'] = $avatar_url;
			}

			$persons[] = $person;
		}

		$schema = [
			'@context'        => 'https://schema.org',
			'@type'           => 'ItemList',
			'name'            => 'Authors',
			'itemListElement' => $persons,
		];

		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
	}

	/**
	 * Add body class for CSS scoping.
	 *
	 * @param string[] $classes Existing body classes.
	 *
	 * @return string[] Modified classes.
	 */
	public function add_body_class( array $classes ): array {
		$classes[] = 'aegis-coauthors-plus';

		return $classes;
	}

	/**
	 * Enqueue frontend CSS only when co-authors may be displayed.
	 *
	 * @return void
	 */
	public function enqueue_styles(): void {
		$should_load = false;

		if ( is_singular( 'post' ) ) {
			$post_id   = get_the_ID();
			$coauthors = $post_id ? get_coauthors( $post_id ) : [];
			$should_load = count( $coauthors ) > 1;
		} else {
			// Archive pages may render co-authors in query loops.
			$should_load = true;
		}

		if ( $should_load ) {
			wp_enqueue_style(
				'aegis-coauthors',
				plugins_url( 'public/css/co-authors-plus.css', \Aegis\Plugin\FILE ),
				[],
				\Aegis\Plugin\VERSION
			);
		}
	}
}
