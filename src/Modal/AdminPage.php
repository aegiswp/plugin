<?php
/**
 * Modals admin dashboard page.
 *
 * @package Aegis\Plugin\Modal
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Modal;

use Aegis\Plugin\Blocks\Settings as BlocksSettings;
use WP_Block_Patterns_Registry;
use function __;
use function absint;
use function add_action;
use function add_filter;
use function add_submenu_page;
use function admin_url;
use function array_merge;
use function check_admin_referer;
use function check_ajax_referer;
use function class_exists;
use function current_user_can;
use function defined;
use function dirname;
use function do_action;
use function esc_html__;
use function file_exists;
use function filemtime;
use function get_current_user_id;
use function get_edit_post_link;
use function get_option;
use function is_array;
use function is_string;
use function is_wp_error;
use function ob_get_clean;
use function ob_start;
use function parse_blocks;
use function plugins_url;
use function sanitize_key;
use function sanitize_text_field;
use function serialize_blocks;
use function str_starts_with;
use function substr;
use function trim;
use function update_option;
use function wp_create_nonce;
use function wp_die;
use function wp_enqueue_script;
use function wp_enqueue_style;
use function wp_generate_uuid4;
use function wp_insert_post;
use function wp_json_encode;
use function wp_localize_script;
use function wp_register_script;
use function wp_register_style;
use function wp_safe_redirect;
use function wp_send_json_error;
use function wp_send_json_success;
use function wp_style_is;
use function wp_unslash;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and renders the Modals admin page.
 */
final class AdminPage {

	/**
	 * Register hooks.
	 */
	public function init(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_post_aegis_create_modal', array( $this, 'handle_create' ) );
		add_action( 'wp_ajax_aegis_toggle_modal', array( $this, 'ajax_toggle' ) );
		add_action( 'wp_ajax_aegis_delete_modal', array( $this, 'ajax_delete' ) );
		add_filter( 'aegis_admin_tabs', array( $this, 'register_admin_tab' ) );
	}

	/**
	 * @param array<string, array{label: string, url: string}> $tabs Registered tabs.
	 * @return array<string, array{label: string, url: string}>
	 */
	public function register_admin_tab( array $tabs ): array {
		$tabs['modals'] = array(
			'label' => __( 'Modals', 'aegis' ),
			'url'   => admin_url( 'admin.php?page=aegis-modals' ),
		);

		return $tabs;
	}

	public function register_menu(): void {
		add_submenu_page(
			'aegis-dashboard',
			__( 'Modals', 'aegis' ),
			__( 'Modals', 'aegis' ),
			'manage_options',
			'aegis-modals',
			array( $this, 'render' )
		);
	}

	/**
	 * @param string $hook_suffix Current admin page hook.
	 */
	public function enqueue_assets( string $hook_suffix ): void {
		unset( $hook_suffix );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['page'] ) ) : '';

		if ( ! str_starts_with( $page, 'aegis-' ) ) {
			return;
		}

		$this->ensure_admin_settings_assets();

		$plugin_dir = \Aegis\Plugin\DIR;
		$hook_css   = $plugin_dir . 'assets/admin/hook-patterns.css';
		$modal_css  = $plugin_dir . 'assets/admin/modals.css';
		$modal_js   = $plugin_dir . 'assets/admin/modals-admin.js';

		wp_enqueue_style(
			'aegis-hook-patterns-admin',
			plugins_url( 'assets/admin/hook-patterns.css', \Aegis\Plugin\FILE ),
			array( 'aegis-admin-settings' ),
			file_exists( $hook_css ) ? (string) filemtime( $hook_css ) : \Aegis\Plugin\VERSION
		);

		wp_enqueue_style(
			'aegis-modals-admin',
			plugins_url( 'assets/admin/modals.css', \Aegis\Plugin\FILE ),
			array( 'aegis-hook-patterns-admin' ),
			file_exists( $modal_css ) ? (string) filemtime( $modal_css ) : \Aegis\Plugin\VERSION
		);

		wp_enqueue_script(
			'aegis-modals-admin',
			plugins_url( 'assets/admin/modals-admin.js', \Aegis\Plugin\FILE ),
			array( 'jquery' ),
			file_exists( $modal_js ) ? (string) filemtime( $modal_js ) : \Aegis\Plugin\VERSION,
			true
		);

		wp_localize_script(
			'aegis-modals-admin',
			'aegisModalsList',
			array(
				'nonce'           => wp_create_nonce( 'aegis_manage_modal' ),
				'confirmDelete'   => __( 'Delete this modal from the page? If the page only contains this modal, the draft will also be moved to Trash.', 'aegis' ),
				'copied'          => __( 'Copied', 'aegis' ),
				'copyId'          => __( 'Copy modal ID', 'aegis' ),
				'disabledLabel'   => __( 'Disabled', 'aegis' ),
				'triggerOffLabel' => __( 'Trigger off', 'aegis' ),
			)
		);
	}

	private function ensure_admin_settings_assets(): void {
		if ( wp_style_is( 'aegis-admin-settings', 'registered' ) ) {
			wp_enqueue_style( 'aegis-admin-settings' );
			wp_enqueue_script( 'aegis-admin-settings' );
			return;
		}

		$plugin_dir = \Aegis\Plugin\DIR;
		$plugin_url = plugins_url( '', \Aegis\Plugin\FILE );
		$asset_file = $plugin_dir . 'assets/admin/build/index.asset.php';

		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset = require $asset_file;

		wp_register_style(
			'aegis-admin-settings',
			$plugin_url . '/assets/admin/build/index.css',
			array( 'dashicons', 'wp-components', 'wp-theme' ),
			$asset['version']
		);

		wp_register_script(
			'aegis-admin-settings',
			$plugin_url . '/assets/admin/build/index.js',
			array_merge( $asset['dependencies'], array( 'jquery' ) ),
			$asset['version'],
			true
		);

		wp_enqueue_style( 'aegis-admin-settings' );
		wp_enqueue_script( 'aegis-admin-settings' );
	}

	public function render(): void {
		$instances     = InstanceRepository::get_instances();
		$modal_enabled = BlocksSettings::is_enabled( 'modal' );
		$starters        = $this->starters();
		$pro_active      = defined( 'AEGIS_PRO_VERSION' );
		$error           = isset( $_GET['aegis_modal_error'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$template        = dirname( __DIR__, 2 ) . '/templates/modals-admin-page.php';

		?>
		<div class="wrap aegis-admin-page">
			<?php do_action( 'aegis_admin_before_modals_page' ); ?>
			<?php
			if ( file_exists( $template ) ) {
				include $template;
			}
			?>
		</div>
		<?php
	}

	/**
	 * Create a draft page from a blank modal or a registered pattern.
	 */
	public function handle_create(): void {
		check_admin_referer( 'aegis_create_modal' );

		if ( ! current_user_can( 'edit_pages' ) ) {
			wp_die( esc_html__( 'You do not have permission to create pages.', 'aegis' ) );
		}

		$starter = isset( $_GET['starter'] ) ? sanitize_key( wp_unslash( (string) $_GET['starter'] ) ) : 'blank';
		$starters = $this->starters();

		if ( ! isset( $starters[ $starter ] ) ) {
			$this->redirect_error();
		}

		$config  = $starters[ $starter ];
		$content = $this->starter_content( $starter, $config );
		$title   = (string) $config['page_title'];

		if ( $content === '' ) {
			$this->redirect_error();
		}

		$feature = (string) $config['feature'];
		$is_pro  = ! empty( $config['pro'] );
		$enable  = isset( $config['enable'] ) && is_array( $config['enable'] ) ? $config['enable'] : array();

		foreach ( $enable as $key ) {
			if ( is_string( $key ) && $key !== '' ) {
				$this->enable_feature( $key );
			}
		}

		if ( $feature !== '' && ( ! $is_pro || defined( 'AEGIS_PRO_VERSION' ) ) ) {
			$this->enable_feature( $feature );

			$enable_pro = isset( $config['enable_pro'] ) && is_array( $config['enable_pro'] ) ? $config['enable_pro'] : array();

			foreach ( $enable_pro as $key ) {
				if ( is_string( $key ) && $key !== '' ) {
					$this->enable_feature( $key );
				}
			}
		}

		$post_id = wp_insert_post(
			array(
				'post_title'   => $title,
				'post_content' => $content,
				'post_status'  => 'draft',
				'post_type'    => 'page',
				'post_author'  => get_current_user_id(),
			),
			true
		);

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			$this->redirect_error();
		}

		InstanceRepository::flush();

		$edit = get_edit_post_link( (int) $post_id, 'raw' );

		if ( ! is_string( $edit ) || $edit === '' ) {
			$this->redirect_error();
		}

		wp_safe_redirect( $edit );
		exit;
	}

	public function ajax_toggle(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized', 'aegis' ) ) );
		}

		check_ajax_referer( 'aegis_manage_modal', 'nonce' );

		$post_id  = absint( $_POST['post_id'] ?? 0 );
		$modal_id = sanitize_text_field( wp_unslash( (string) ( $_POST['modal_id'] ?? '' ) ) );
		$index    = absint( $_POST['block_index'] ?? 0 );
		$enabled  = ! empty( $_POST['enabled'] );

		if ( $post_id < 1 || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to edit this modal.', 'aegis' ) ) );
		}

		if ( ! InstanceRepository::set_enabled( $post_id, $modal_id, $index, $enabled ) ) {
			wp_send_json_error( array( 'message' => __( 'Could not update the modal.', 'aegis' ) ) );
		}

		wp_send_json_success( array( 'enabled' => $enabled ) );
	}

	public function ajax_delete(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized', 'aegis' ) ) );
		}

		check_ajax_referer( 'aegis_manage_modal', 'nonce' );

		$post_id  = absint( $_POST['post_id'] ?? 0 );
		$modal_id = sanitize_text_field( wp_unslash( (string) ( $_POST['modal_id'] ?? '' ) ) );
		$index    = absint( $_POST['block_index'] ?? 0 );

		if ( $post_id < 1 || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to delete this modal.', 'aegis' ) ) );
		}

		if ( ! InstanceRepository::delete_instance( $post_id, $modal_id, $index ) ) {
			wp_send_json_error( array( 'message' => __( 'Could not delete the modal.', 'aegis' ) ) );
		}

		wp_send_json_success();
	}

	/**
	 * @return array<string, array{label: string, description: string, icon: string, pattern: ?string, feature: string, pro: bool, page_title: string}>
	 */
	private function starters(): array {
		return array(
			'blank'      => array(
				'label'       => __( 'Blank modal', 'aegis' ),
				'description' => __( 'Empty modal with a button trigger.', 'aegis' ),
				'icon'        => 'plus-alt2',
				'pattern'     => null,
				'feature'     => 'modal_click',
				'pro'         => false,
				'page_title'  => __( 'Modal', 'aegis' ),
			),
			'contact'    => array(
				'label'       => __( 'Contact', 'aegis' ),
				'description' => __( 'Form popup opened from a button.', 'aegis' ),
				'icon'        => 'email',
				'pattern'     => 'aegis/modal-contact',
				'feature'     => 'modal_click',
				'enable'      => array( 'modal_animations' ),
				'pro'         => false,
				'page_title'  => __( 'Contact modal', 'aegis' ),
			),
			'newsletter' => array(
				'label'       => __( 'Newsletter', 'aegis' ),
				'description' => __( 'Subscribe popup on exit intent.', 'aegis' ),
				'icon'        => 'megaphone',
				'pattern'     => 'aegis/modal-newsletter',
				'feature'     => 'modal_exit_intent',
				'enable'      => array( 'modal_animations' ),
				'enable_pro'  => array( 'modal_show_once' ),
				'pro'         => true,
				'page_title'  => __( 'Newsletter modal', 'aegis' ),
			),
			'video'      => array(
				'label'       => __( 'Video', 'aegis' ),
				'description' => __( 'Lightbox for an embedded video.', 'aegis' ),
				'icon'        => 'video-alt3',
				'pattern'     => 'aegis/modal-video',
				'feature'     => 'modal_click',
				'enable'      => array( 'modal_animations' ),
				'pro'         => false,
				'page_title'  => __( 'Video modal', 'aegis' ),
			),
			'cookie'     => array(
				'label'       => __( 'Cookie consent', 'aegis' ),
				'description' => __( 'Bottom sheet after a short delay.', 'aegis' ),
				'icon'        => 'privacy',
				'pattern'     => 'aegis/modal-cookie-consent',
				'feature'     => 'modal_time_delay',
				'enable'      => array( 'modal_offcanvas', 'modal_animations' ),
				'enable_pro'  => array( 'modal_show_once' ),
				'pro'         => true,
				'page_title'  => __( 'Cookie consent', 'aegis' ),
			),
		);
	}

	/**
	 * @param array{pattern: ?string, page_title: string, feature: string, enable?: array<int, string>, enable_pro?: array<int, string>, pro?: bool} $config Starter config.
	 */
	private function starter_content( string $starter, array $config ): string {
		if ( $starter === 'blank' ) {
			$attrs = wp_json_encode(
				array(
					'modalId'     => 'modal-' . wp_generate_uuid4(),
					'triggerType' => 'button',
					'modalTitle'  => __( 'New Modal', 'aegis' ),
				)
			);

			if ( ! is_string( $attrs ) ) {
				return '';
			}

			return '<!-- wp:aegis/modal ' . $attrs . " -->\n<!-- wp:paragraph -->\n<p></p>\n<!-- /wp:paragraph -->\n<!-- /wp:aegis/modal -->";
		}

		$pattern = (string) ( $config['pattern'] ?? '' );
		$content = $this->pattern_content( $pattern );

		if ( $content === '' ) {
			return '';
		}

		return $this->uniquify_modal_ids( $content );
	}

	private function pattern_content( string $name ): string {
		if ( $name === '' || ! class_exists( WP_Block_Patterns_Registry::class ) ) {
			return '';
		}

		$registry = WP_Block_Patterns_Registry::get_instance();

		if ( ! $registry->is_registered( $name ) ) {
			return '';
		}

		$registered = $registry->get_registered( $name );
		$content    = trim( (string) ( $registered['content'] ?? '' ) );

		if ( $content !== '' ) {
			return $content;
		}

		$file = (string) ( $registered['filePath'] ?? '' );

		if ( $file === '' || ! file_exists( $file ) ) {
			return '';
		}

		ob_start();
		include $file;

		return trim( (string) ob_get_clean() );
	}

	private function uniquify_modal_ids( string $content ): string {
		$blocks = parse_blocks( $content );
		$this->replace_modal_ids( $blocks );

		return serialize_blocks( $blocks );
	}

	/**
	 * @param array<int, array<string, mixed>> $blocks Blocks.
	 */
	private function replace_modal_ids( array &$blocks ): void {
		foreach ( $blocks as &$block ) {
			if ( ( $block['blockName'] ?? '' ) === 'aegis/modal' ) {
				$attrs   = is_array( $block['attrs'] ?? null ) ? $block['attrs'] : array();
				$base    = sanitize_key( (string) ( $attrs['modalId'] ?? 'modal' ) );
				$base    = $base !== '' ? $base : 'modal';
				$attrs['modalId'] = $base . '-' . substr( wp_generate_uuid4(), 0, 8 );
				$block['attrs']   = $attrs;
			}

			if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
				$this->replace_modal_ids( $block['innerBlocks'] );
			}
		}
	}

	private function enable_feature( string $key ): void {
		$stored = get_option( BlocksSettings::OPTION, array() );
		$stored = is_array( $stored ) ? $stored : array();
		$stored[ $key ] = true;
		update_option( BlocksSettings::OPTION, $stored );
		BlocksSettings::flush_cache();
	}

	private function redirect_error(): void {
		wp_safe_redirect( admin_url( 'admin.php?page=aegis-modals&aegis_modal_error=1' ) );
		exit;
	}
}
