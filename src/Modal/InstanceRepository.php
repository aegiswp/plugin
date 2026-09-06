<?php
/**
 * Discovers aegis/modal blocks in site content.
 *
 * @package Aegis\Plugin\Modal
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Modal;

use Aegis\Plugin\Blocks\Settings as BlocksSettings;
use function __;
use function add_action;
use function array_fill;
use function array_filter;
use function array_merge;
use function array_unique;
use function array_values;
use function count;
use function current_user_can;
use function delete_transient;
use function get_edit_post_link;
use function get_option;
use function get_permalink;
use function get_post;
use function get_post_status_object;
use function get_post_timestamp;
use function get_post_type_object;
use function get_post_types;
use function get_preview_post_link;
use function get_the_author_meta;
use function get_the_time;
use function get_transient;
use function implode;
use function is_array;
use function is_string;
use function is_wp_error;
use function parse_blocks;
use function serialize_blocks;
use function set_transient;
use function wp_date;
use function wp_is_post_revision;
use function wp_slash;
use function wp_strip_all_tags;
use function wp_trash_post;
use function wp_update_post;
use const HOUR_IN_SECONDS;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cached list of Modal block instances across posts, templates, and patterns.
 */
final class InstanceRepository {

	public const CACHE_KEY       = 'aegis_modal_instances_v4';
	public const COUNT_CACHE_KEY = 'aegis_modal_block_count_v4';

	/**
	 * triggerType attribute => Blocks settings key.
	 *
	 * @var array<string, string>
	 */
	public const TRIGGER_FEATURES = array(
		'button'      => 'modal_click',
		'icon'        => 'modal_icon',
		'text'        => 'modal_text',
		'image'       => 'modal_image',
		'scroll'      => 'modal_scroll_depth',
		'exit-intent' => 'modal_exit_intent',
		'timed'       => 'modal_time_delay',
	);

	/**
	 * Modal sub-feature keys shown on Blocks → Modal.
	 *
	 * @var array<int, string>
	 */
	public const FEATURE_KEYS = array(
		'modal_click',
		'modal_icon',
		'modal_text',
		'modal_image',
		'modal_offcanvas',
		'modal_fullscreen',
		'modal_animations',
		'modal_exit_intent',
		'modal_scroll_depth',
		'modal_time_delay',
		'modal_auto_close',
		'modal_show_once',
		'modal_device_visibility',
	);

	public static function init(): void {
		add_action( 'save_post', array( self::class, 'flush_on_save' ), 20, 1 );
		add_action( 'deleted_post', array( self::class, 'flush' ) );
		add_action( 'trashed_post', array( self::class, 'flush' ) );
		add_action( 'untrashed_post', array( self::class, 'flush' ) );
	}

	/**
	 * @param int $post_id Post ID.
	 */
	public static function flush_on_save( $post_id ): void {
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		self::flush();
	}

	public static function flush(): void {
		delete_transient( self::CACHE_KEY );
		delete_transient( self::COUNT_CACHE_KEY );
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_instances(): array {
		$cached = get_transient( self::CACHE_KEY );

		if ( is_array( $cached ) ) {
			return $cached;
		}

		$instances = self::scan();
		set_transient( self::CACHE_KEY, $instances, HOUR_IN_SECONDS );
		set_transient( self::COUNT_CACHE_KEY, count( $instances ), HOUR_IN_SECONDS );

		return $instances;
	}

	public static function count(): int {
		return count( self::get_instances() );
	}

	public static function active_feature_count(): int {
		$settings = BlocksSettings::get_settings();

		return count(
			array_filter(
				self::FEATURE_KEYS,
				static function ( string $key ) use ( $settings ): bool {
					return ! empty( $settings[ $key ] );
				}
			)
		);
	}

	/**
	 * Enable or disable a modal instance without changing the host post status.
	 */
	public static function set_enabled( int $post_id, string $modal_id, int $index, bool $enabled ): bool {
		return self::mutate_modal(
			$post_id,
			$modal_id,
			$index,
			static function ( array $block ) use ( $enabled ): ?array {
				$attrs              = is_array( $block['attrs'] ?? null ) ? $block['attrs'] : array();
				$attrs['isEnabled'] = $enabled;
				$block['attrs']     = $attrs;

				return $block;
			}
		);
	}

	/**
	 * Remove a modal block from its host. Trashes an otherwise empty draft page.
	 */
	public static function delete_instance( int $post_id, string $modal_id, int $index ): bool {
		return self::mutate_modal(
			$post_id,
			$modal_id,
			$index,
			static function (): ?array {
				return null;
			},
			true
		);
	}

	public static function trigger_label( string $trigger ): string {
		$labels = array(
			'button'      => __( 'Button', 'aegis' ),
			'icon'        => __( 'Icon', 'aegis' ),
			'text'        => __( 'Text', 'aegis' ),
			'image'       => __( 'Image', 'aegis' ),
			'scroll'      => __( 'Scroll depth', 'aegis' ),
			'exit-intent' => __( 'Exit intent', 'aegis' ),
			'timed'       => __( 'Time delay', 'aegis' ),
		);

		return $labels[ $trigger ] ?? $trigger;
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	private static function scan(): array {
		global $wpdb;

		$types = self::content_post_types();

		if ( $types === array() ) {
			return array();
		}

		$placeholders = implode( ',', array_fill( 0, count( $types ), '%s' ) );
		$like         = '%' . $wpdb->esc_like( '<!-- wp:aegis/modal' ) . '%';

		$sql = $wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE post_status NOT IN ('trash', 'auto-draft', 'inherit') AND post_type IN ({$placeholders}) AND post_content LIKE %s LIMIT 500",
			...array_merge( $types, array( $like ) )
		); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- placeholders built from count().

		if ( ! is_string( $sql ) || $sql === '' ) {
			return array();
		}

		$ids = $wpdb->get_col( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( ! is_array( $ids ) || $ids === array() ) {
			return array();
		}

		$settings  = BlocksSettings::get_settings();
		$instances = array();

		foreach ( $ids as $id ) {
			$post = get_post( (int) $id );

			if ( ! $post ) {
				continue;
			}

			$index = 0;
			self::collect(
				parse_blocks( (string) $post->post_content ),
				$post,
				$settings,
				$instances,
				$index
			);
		}

		return $instances;
	}

	/**
	 * @return array<int, string>
	 */
	private static function content_post_types(): array {
		$public = get_post_types(
			array(
				'public' => true,
			),
			'names'
		);

		unset( $public['attachment'] );

		return array_values(
			array_unique(
				array_merge(
					array_values( $public ),
					array( 'wp_template', 'wp_template_part', 'wp_block' )
				)
			)
		);
	}

	/**
	 * @param array<int, array<string, mixed>> $blocks    Parsed blocks.
	 * @param \WP_Post                         $post      Host post.
	 * @param array<string, bool>              $settings  Block feature flags.
	 * @param array<int, array<string, mixed>> $instances Collector.
	 * @param int                              $index     Nth modal in this post (DFS).
	 */
	private static function collect( array $blocks, $post, array $settings, array &$instances, int &$index ): void {
		foreach ( $blocks as $block ) {
			$name = (string) ( $block['blockName'] ?? '' );

			if ( $name === 'aegis/modal' ) {
				$attrs      = is_array( $block['attrs'] ?? null ) ? $block['attrs'] : array();
				$trigger    = (string) ( $attrs['triggerType'] ?? 'button' );
				$feature    = self::TRIGGER_FEATURES[ $trigger ] ?? 'modal_click';
				$modal_id   = (string) ( $attrs['modalId'] ?? '' );
				$title      = (string) ( $attrs['modalTitle'] ?? '' );
				$type_obj   = get_post_type_object( (string) $post->post_type );
				$status_obj = get_post_status_object( (string) $post->post_status );

				if ( $title === '' ) {
					$title = $modal_id !== '' ? $modal_id : (string) $post->post_title;
				}

				$modified     = get_post_timestamp( $post, 'modified' );
				$published    = get_post_timestamp( $post );
				$author_id    = (int) $post->post_author;
				$author       = (string) ( get_the_author_meta( 'display_name', $author_id ) ?: '—' );
				$post_status  = (string) $post->post_status;
				$view_url     = $post_status === 'publish' ? (string) get_permalink( $post ) : (string) get_preview_post_link( $post );

				if ( '0000-00-00 00:00:00' === (string) $post->post_date ) {
					$date_display = __( 'Unpublished', 'aegis' );
					$date_status  = '';
				} else {
					$date_display = sprintf(
						/* translators: 1: Date, 2: Time. */
						__( '%1$s at %2$s', 'aegis' ),
						(string) get_the_time( __( 'Y/m/d', 'aegis' ), $post ),
						(string) get_the_time( __( 'g:i a', 'aegis' ), $post )
					);

					if ( 'publish' === $post_status ) {
						$date_status = __( 'Published', 'aegis' );
					} elseif ( 'future' === $post_status ) {
						$date_status = ( $published && ( time() - $published ) > 0 )
							? __( 'Missed schedule', 'aegis' )
							: __( 'Scheduled', 'aegis' );
					} else {
						$date_status = __( 'Last Modified', 'aegis' );
					}
				}

				$instances[] = array(
					'post_id'           => (int) $post->ID,
					'post_title'        => (string) $post->post_title,
					'post_type'         => (string) $post->post_type,
					'post_type_label'   => $type_obj ? (string) $type_obj->labels->singular_name : (string) $post->post_type,
					'post_status'       => $post_status,
					'post_status_label' => $status_obj ? (string) $status_obj->label : $post_status,
					'author'            => $author !== '' ? $author : '—',
					'author_id'         => $author_id,
					'view_url'          => $view_url,
					'date_status'       => $date_status,
					'date_display'      => $date_display,
					'date_ts'           => $modified ? (int) $modified : 0,
					'date'              => $modified ? (string) wp_date( (string) get_option( 'date_format' ), $modified ) : '—',
					'edit_url'          => (string) get_edit_post_link( (int) $post->ID, 'raw' ),
					'modal_id'          => $modal_id,
					'block_index'       => $index,
					'title'             => $title,
					'trigger'           => $trigger,
					'trigger_label'     => self::trigger_label( $trigger ),
					'feature_key'       => $feature,
					'trigger_enabled'   => ! empty( $settings[ $feature ] ),
					'enabled'           => (bool) ( $attrs['isEnabled'] ?? true ),
				);

				++$index;
			}

			$inner = $block['innerBlocks'] ?? array();

			if ( is_array( $inner ) && $inner !== array() ) {
				self::collect( $inner, $post, $settings, $instances, $index );
			}
		}
	}

	/**
	 * @param callable(array<string, mixed>): ?array<string, mixed> $transform Return null to delete the block.
	 */
	private static function mutate_modal( int $post_id, string $modal_id, int $index, callable $transform, bool $trash_empty_draft = false ): bool {
		$post = get_post( $post_id );

		if ( ! $post || wp_is_post_revision( $post_id ) ) {
			return false;
		}

		$found  = false;
		$seen   = 0;
		$blocks = self::walk_modals(
			parse_blocks( (string) $post->post_content ),
			$modal_id,
			$index,
			$transform,
			$found,
			$seen
		);

		if ( ! $found ) {
			return false;
		}

		if (
			$trash_empty_draft
			&& self::blocks_are_empty( $blocks )
			&& $post->post_type === 'page'
			&& $post->post_status === 'draft'
			&& current_user_can( 'delete_post', $post_id )
		) {
			return (bool) wp_trash_post( $post_id );
		}

		$updated = wp_update_post(
			array(
				'ID'           => $post_id,
				'post_content' => wp_slash( serialize_blocks( $blocks ) ),
			),
			true
		);

		if ( is_wp_error( $updated ) || ! $updated ) {
			return false;
		}

		self::flush();

		return true;
	}

	/**
	 * @param array<int, array<string, mixed>>                      $blocks    Parsed blocks.
	 * @param callable(array<string, mixed>): ?array<string, mixed> $transform Return null to delete.
	 * @return array<int, array<string, mixed>>
	 */
	private static function walk_modals( array $blocks, string $modal_id, int $index, callable $transform, bool &$found, int &$seen ): array {
		$out = array();

		foreach ( $blocks as $block ) {
			$name = (string) ( $block['blockName'] ?? '' );

			if ( $name === 'aegis/modal' ) {
				$attrs   = is_array( $block['attrs'] ?? null ) ? $block['attrs'] : array();
				$id      = (string) ( $attrs['modalId'] ?? '' );
				$matches = $modal_id !== '' ? $id === $modal_id : $seen === $index;
				++$seen;

				if ( $matches && ! $found ) {
					$found  = true;
					$result = $transform( $block );

					if ( $result === null ) {
						continue;
					}

					$block = $result;
				}
			}

			if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
				$block['innerBlocks'] = self::walk_modals( $block['innerBlocks'], $modal_id, $index, $transform, $found, $seen );
			}

			$out[] = $block;
		}

		return $out;
	}

	/**
	 * @param array<int, array<string, mixed>> $blocks Parsed blocks.
	 */
	private static function blocks_are_empty( array $blocks ): bool {
		foreach ( $blocks as $block ) {
			$name = $block['blockName'] ?? null;
			$text = trim( wp_strip_all_tags( (string) ( $block['innerHTML'] ?? '' ) ) );

			if ( $name === null || $name === '' ) {
				if ( $text !== '' ) {
					return false;
				}

				continue;
			}

			if ( $name === 'core/paragraph' && $text === '' ) {
				continue;
			}

			return false;
		}

		return true;
	}
}
