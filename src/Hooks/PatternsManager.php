<?php
/**
 * Hook patterns manager — conditions meta and editor meta box.
 *
 * @package Aegis\Plugin\Hooks
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Hooks;

use WP_Post;
use function add_action;
use function add_meta_box;
use function class_exists;
use function current_user_can;
use function defined;
use function esc_textarea;
use function get_post;
use function get_post_meta;
use function get_post_type_object;
use function is_array;
use function json_decode;
use function plugins_url;
use function register_post_meta;
use function translate_user_role;
use function update_post_meta;
use function wp_enqueue_script;
use function wp_enqueue_style;
use function wp_is_post_autosave;
use function wp_is_post_revision;
use function wp_json_encode;
use function wp_nonce_field;
use function wp_roles;
use function wp_style_is;
use function wp_unslash;
use function wp_verify_nonce;
use function __;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers `_aegis_conditions` meta and the Conditionals meta box in the editor.
 */
final class PatternsManager {

	/**
	 * Post type for hook patterns (registered by aegis-pro when active).
	 */
	public const POST_TYPE = 'aegis_hook_pattern';

	/**
	 * Plugin admin asset base URL.
	 */
	private function admin_asset_url( string $file ): string {
		return plugins_url( 'assets/admin/' . $file, \Aegis\Plugin\FILE );
	}

	/**
	 * Conditional logic feature toggles for meta box gating.
	 *
	 * @return array<string, array<string, bool>>
	 */
	private function get_conditional_settings(): array {
		if ( class_exists( '\Aegis\Plugin\Conditionals\Settings' ) ) {
			return \Aegis\Plugin\Conditionals\Settings::get_settings();
		}

		if ( class_exists( '\Aegis\Plugin\Settings\Repository' ) ) {
			return \Aegis\Plugin\Settings\Repository::get_settings();
		}

		return [];
	}

	/**
	 * Initialize the hook patterns manager.
	 */
	public function init(): void {
		$this->register_conditions_meta();
		$this->register_admin_hooks();
	}

	/**
	 * Register the `_aegis_conditions` meta field for all post types.
	 */
	private function register_conditions_meta(): void {
		register_post_meta(
			'',
			'_aegis_conditions',
			[
				'type'          => 'string',
				'description'   => __( 'JSON-encoded conditional logic rules.', 'aegis' ),
				'single'        => true,
				'show_in_rest'  => true,
				'default'       => '',
				'auth_callback' => static function ( $allowed, $meta_key, $post_id ) {
					return current_user_can( 'edit_post', $post_id );
				},
			]
		);
	}

	/**
	 * Register admin hooks for conditions UI.
	 */
	private function register_admin_hooks(): void {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );
		add_action( 'save_post', [ $this, 'save_conditions_meta' ] );
		add_action( 'add_meta_boxes', [ $this, 'add_conditions_meta_box' ] );
	}

	/**
	 * Enqueue conditions admin scripts and styles on post edit screens.
	 *
	 * @param string $hook Current admin page.
	 */
	public function enqueue_admin_scripts( string $hook ): void {
		if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
			return;
		}

		$settings = $this->get_conditional_settings();

		if ( empty( $settings ) ) {
			return;
		}
		$has_any_enabled = false;

		foreach ( $settings as $features ) {
			if ( is_array( $features ) ) {
				foreach ( $features as $enabled ) {
					if ( $enabled ) {
						$has_any_enabled = true;
						break 2;
					}
				}
			}
		}

		if ( ! $has_any_enabled ) {
			return;
		}

		if ( ! wp_style_is( 'aegis-hook-patterns-admin', 'enqueued' ) ) {
			wp_enqueue_style(
				'aegis-conditions-admin',
				$this->admin_asset_url( 'hook-patterns.css' ),
				array( 'wp-theme' ),
				\Aegis\Plugin\VERSION
			);
		}

		wp_enqueue_script(
			'aegis-smart-conditions',
			$this->admin_asset_url( 'smart-conditions.js' ),
			array(),
			\Aegis\Plugin\VERSION,
			true
		);
	}

	/**
	 * Register the Conditionals meta box on REST-enabled post types.
	 *
	 * @param string $post_type Current post type.
	 */
	public function add_conditions_meta_box( string $post_type ): void {
		$settings = $this->get_conditional_settings();

		if ( empty( $settings ) ) {
			return;
		}
		$has_any  = false;

		foreach ( $settings as $features ) {
			if ( is_array( $features ) ) {
				foreach ( $features as $enabled ) {
					if ( $enabled ) {
						$has_any = true;
						break 2;
					}
				}
			}
		}

		if ( ! $has_any ) {
			return;
		}

		$post_type_object = get_post_type_object( $post_type );

		if ( ! $post_type_object || ! $post_type_object->show_in_rest ) {
			return;
		}

		if ( $post_type === self::POST_TYPE ) {
			return;
		}

		add_meta_box(
			'aegis_conditions',
			__( 'Conditionals', 'aegis' ),
			[ $this, 'render_conditions_meta_box' ],
			$post_type,
			'side',
			'default'
		);
	}

	/**
	 * Render the Conditional Logic meta box.
	 *
	 * @param WP_Post $post Post object.
	 */
	public function render_conditions_meta_box( WP_Post $post ): void {
		$raw        = get_post_meta( $post->ID, '_aegis_conditions', true );
		$conditions = ! empty( $raw ) ? json_decode( $raw, true ) : [];

		if ( ! is_array( $conditions ) ) {
			$conditions = [];
		}

		$roles    = [];
		$wp_roles = wp_roles();

		foreach ( $wp_roles->role_names as $slug => $name ) {
			$roles[] = [ 'value' => $slug, 'label' => translate_user_role( $name ) ];
		}

		$has_pro     = defined( 'AEGIS_PRO_VERSION' );
		$cl_settings = $this->get_conditional_settings();

		wp_nonce_field( 'aegis_save_conditions', 'aegis_conditions_nonce' );
		?>
		<textarea id="aegis_conditions" name="aegis_conditions" class="hidden"><?php
			echo esc_textarea( wp_json_encode( $conditions ) );
		?></textarea>
		<div id="aegis-conditions-ui" class="aegis-conditions-ui"></div>
		<script>
			window.aegisConditionsConfig = {
				conditions: <?php echo wp_json_encode( $conditions ); ?>,
				roles: <?php echo wp_json_encode( $roles ); ?>,
				hasPro: <?php echo $has_pro ? 'true' : 'false'; ?>,
				settings: <?php echo wp_json_encode( $cl_settings ); ?>,
				postType: <?php echo wp_json_encode( $post->post_type ); ?>,
			};
		</script>
		<?php
	}

	/**
	 * Save conditional logic meta for any post type except hook patterns.
	 *
	 * @param int $post_id Post ID.
	 */
	public function save_conditions_meta( int $post_id ): void {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}

		$post = get_post( $post_id );

		if ( $post && $post->post_type === self::POST_TYPE ) {
			return;
		}

		if ( ! isset( $_POST['aegis_conditions_nonce'] )
			|| ! wp_verify_nonce( wp_unslash( $_POST['aegis_conditions_nonce'] ), 'aegis_save_conditions' )
		) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['aegis_conditions'] ) ) {
			$raw     = wp_unslash( $_POST['aegis_conditions'] );
			$decoded = json_decode( $raw, true );

			if ( is_array( $decoded ) ) {
				update_post_meta( $post_id, '_aegis_conditions', wp_json_encode( $decoded ) );
			} else {
				update_post_meta( $post_id, '_aegis_conditions', '' );
			}
		}
	}
}
