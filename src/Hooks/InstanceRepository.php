<?php
/**
 * Lists and mutates hook pattern posts for the Hooks admin dashboard.
 *
 * @package Aegis\Plugin\Hooks
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Hooks;

use Aegis\Plugin\Injection\LocationRegistry;
use function __;
use function class_exists;
use function current_user_can;
use function get_edit_post_link;
use function get_post;
use function get_post_meta;
use function get_post_status_object;
use function get_post_type;
use function get_posts;
use function get_the_author_meta;
use function is_array;
use function json_decode;
use function post_type_exists;
use function trim;
use function update_post_meta;
use function wp_trash_post;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hook pattern instances (aegis_hook_pattern CPT, registered by Pro).
 */
final class InstanceRepository {

	public const POST_TYPE = PatternsManager::POST_TYPE;

	public static function post_type_ready(): bool {
		return post_type_exists( self::POST_TYPE );
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_instances(): array {
		if ( ! self::post_type_ready() ) {
			return array();
		}

		$posts = get_posts(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
				'posts_per_page' => 200,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		if ( ! is_array( $posts ) ) {
			return array();
		}

		$labels    = self::hook_labels();
		$instances = array();

		foreach ( $posts as $post ) {
			$hook      = (string) get_post_meta( $post->ID, '_aegis_hook_name', true );
			$priority  = (int) get_post_meta( $post->ID, '_aegis_priority', true );
			$enabled   = (string) get_post_meta( $post->ID, '_aegis_enabled', true ) === '1';
			$status_obj = get_post_status_object( (string) $post->post_status );
			$author_id = (int) $post->post_author;

			$conditions = self::summarize_conditions( (string) get_post_meta( $post->ID, '_aegis_conditions', true ) );

			$instances[] = array(
				'post_id'           => (int) $post->ID,
				'title'             => (string) $post->post_title,
				'hook'              => $hook,
				'hook_label'        => $labels[ $hook ] ?? ( $hook !== '' ? $hook : __( 'Not set', 'aegis' ) ),
				'priority'          => $priority > 0 ? $priority : 10,
				'enabled'           => $enabled,
				'conditional'       => $conditions['conditional'],
				'rule_count'        => $conditions['rule_count'],
				'post_status'       => (string) $post->post_status,
				'post_status_label' => $status_obj ? (string) $status_obj->label : (string) $post->post_status,
				'author'            => (string) ( get_the_author_meta( 'display_name', $author_id ) ?: '—' ),
				'edit_url'          => (string) get_edit_post_link( (int) $post->ID, 'raw' ),
			);
		}

		return $instances;
	}

	public static function enabled_count( array $instances ): int {
		$count = 0;

		foreach ( $instances as $instance ) {
			if ( ! empty( $instance['enabled'] ) ) {
				++$count;
			}
		}

		return $count;
	}

	public static function set_enabled( int $post_id, bool $enabled ): bool {
		if ( ! self::is_hook_pattern( $post_id ) ) {
			return false;
		}

		update_post_meta( $post_id, '_aegis_enabled', $enabled ? '1' : '0' );
		self::flush_renderer();

		return true;
	}

	public static function delete_instance( int $post_id ): bool {
		if ( ! self::is_hook_pattern( $post_id ) || ! current_user_can( 'delete_post', $post_id ) ) {
			return false;
		}

		$trashed = wp_trash_post( $post_id );
		self::flush_renderer();

		return (bool) $trashed;
	}

	/**
	 * @return array<string, string>
	 */
	public static function hook_labels(): array {
		$labels = array();

		foreach ( LocationRegistry::get_grouped_for_select() as $hooks ) {
			foreach ( $hooks as $hook => $label ) {
				$labels[ (string) $hook ] = (string) $label;
			}
		}

		return $labels;
	}

	/**
	 * @return array{conditional: bool, rule_count: int}
	 */
	private static function summarize_conditions( string $raw ): array {
		if ( $raw === '' ) {
			return array(
				'conditional' => false,
				'rule_count'  => 0,
			);
		}

		$decoded = json_decode( $raw, true );

		if ( ! is_array( $decoded ) || $decoded === array() ) {
			return array(
				'conditional' => false,
				'rule_count'  => 0,
			);
		}

		$count = 0;
		$smart = is_array( $decoded['smartLogic'] ?? null ) ? $decoded['smartLogic'] : array();

		if ( ! empty( $smart['enabled'] ) ) {
			foreach ( (array) ( $smart['groups'] ?? array() ) as $group ) {
				if ( ! is_array( $group ) ) {
					continue;
				}

				foreach ( (array) ( $group['rules'] ?? array() ) as $rule ) {
					if ( ! is_array( $rule ) ) {
						continue;
					}

					++$count;
				}
			}

			if ( $count === 0 ) {
				$count = 1;
			}
		}

		unset( $decoded['smartLogic'] );

		$classic = self::has_nonempty( $decoded );

		return array(
			'conditional' => $count > 0 || $classic,
			'rule_count'  => $count,
		);
	}

	/**
	 * @param array<string, mixed> $data Nested conditions payload.
	 */
	private static function has_nonempty( array $data ): bool {
		foreach ( $data as $value ) {
			if ( is_array( $value ) ) {
				if ( self::has_nonempty( $value ) ) {
					return true;
				}

				continue;
			}

			if ( $value === true || $value === 1 || $value === '1' ) {
				return true;
			}

			if ( is_string( $value ) && trim( $value ) !== '' ) {
				return true;
			}
		}

		return false;
	}

	private static function is_hook_pattern( int $post_id ): bool {
		if ( $post_id < 1 || ! self::post_type_ready() ) {
			return false;
		}

		$post = get_post( $post_id );

		return $post && get_post_type( $post ) === self::POST_TYPE;
	}

	private static function flush_renderer(): void {
		if ( class_exists( \Aegis\Pro\HookPatternsRenderer::class ) ) {
			( new \Aegis\Pro\HookPatternsRenderer() )->clear_all_caches();
		}
	}
}
