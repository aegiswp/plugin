<?php
/**
 * Code Snippets admin dashboard page.
 *
 * @package Aegis\Plugin\Snippets
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Snippets;

use Aegis\Plugin\Injection\LocationRegistry;
use Aegis\Plugin\Injection\Preview;
use function __;
use function add_action;
use function add_filter;
use function add_query_arg;
use function add_submenu_page;
use function admin_url;
use function check_admin_referer;
use function array_values;
use function get_post_types;
use function nocache_headers;
use function sanitize_file_name;
use function current_user_can;
use function defined;
use function do_action;
use function esc_attr;
use function esc_html;
use function esc_html_e;
use function esc_html__;
use function esc_textarea;
use function esc_url;
use function escapeshellarg;
use function exec;
use function file_exists;
use function filemtime;
use function file_put_contents;
use function implode;
use function in_array;
use function max;
use function plugins_url;
use function preg_match;
use function printf;
use function sanitize_key;
use function sanitize_text_field;
use function sanitize_textarea_field;
use function ksort;
use function strtolower;
use function strtotime;
use function str_contains;
use function str_starts_with;
use function strcasecmp;
use function trim;
use function unlink;
use function usort;
use function selected;
use function checked;
use function disabled;
use function submit_button;
use function ucfirst;
use function wp_die;
use function wp_enqueue_script;
use function wp_enqueue_style;
use function wp_generate_uuid4;
use function wp_nonce_field;
use function wp_nonce_url;
use function wp_register_script;
use function wp_register_style;
use function wp_safe_redirect;
use function wp_enqueue_code_editor;
use function wp_get_current_user;
use function wp_json_encode;
use function wp_send_json_error;
use function wp_send_json_success;
use function wp_create_nonce;
use function wp_date;
use function wp_localize_script;
use function wp_unslash;
use function wp_strip_all_tags;
use function check_ajax_referer;
use function header;
use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;
use function sprintf;
use function _n;
use function json_decode;
use function array_map;
use function array_filter;
use function array_keys;
use function explode;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and renders the Code Snippets admin page.
 */
final class AdminPage {

	/** @var \Aegis\Plugin\Admin\Renderer|null */
	private $renderer = null;

	private SettingsController $settings_controller;

	/**
	 * Register hooks.
	 */
	public function init(): void {
		$this->settings_controller = new SettingsController();

		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_filter( 'aegis_admin_tabs', array( $this, 'register_admin_tab' ) );
		add_action( 'admin_post_aegis_save_snippet', array( $this, 'handle_save' ) );
		add_action( 'admin_post_aegis_delete_snippet', array( $this, 'handle_delete' ) );
		add_action( 'admin_post_aegis_download_snippet', array( $this, 'handle_download' ) );
		add_action( 'admin_post_aegis_export_snippets', array( $this, 'handle_export' ) );
		add_action( 'admin_post_aegis_save_snippets_settings', array( $this, 'handle_settings_save' ) );
		add_action( 'admin_post_aegis_regenerate_safe_mode_token', array( $this, 'handle_regenerate_safe_mode_token' ) );
		add_action( 'admin_post_aegis_disable_safe_mode', array( $this, 'handle_disable_safe_mode' ) );
		add_action( 'wp_ajax_aegis_save_snippets_settings', array( $this->settings_controller, 'ajax_save_settings' ) );
		add_action( 'wp_ajax_aegis_toggle_snippet', array( $this, 'ajax_toggle_snippet' ) );
		add_action( 'wp_ajax_aegis_import_snippets', array( $this, 'ajax_import_snippets' ) );
		add_action( 'wp_ajax_aegis_lint_php', array( $this, 'ajax_lint_php' ) );
		add_action( 'admin_notices', array( $this, 'render_safe_mode_banner' ) );
	}

	/**
	 * @param array<string, array{label: string, url: string}> $tabs Registered tabs.
	 * @return array<string, array{label: string, url: string}>
	 */
	public function register_admin_tab( array $tabs ): array {
		$tabs['snippets'] = array(
			'label' => __( 'Code Snippets', 'aegis' ),
			'url'   => admin_url( 'admin.php?page=aegis-snippets' ),
		);

		return $tabs;
	}

	public function register_menu(): void {
		add_submenu_page(
			'aegis-dashboard',
			__( 'Code Snippets', 'aegis' ),
			__( 'Code Snippets', 'aegis' ),
			'manage_options',
			'aegis-snippets',
			array( $this, 'render' )
		);
	}

	private function renderer(): \Aegis\Plugin\Admin\Renderer {
		if ( null === $this->renderer ) {
			$this->renderer = new \Aegis\Plugin\Admin\Renderer();
		}

		return $this->renderer;
	}

	/**
	 * @param array<string, array<string, string>> $locations Grouped locations.
	 * @return array<string, array<string, string>>
	 */
	private function filter_enabled_locations( array $locations ): array {
		foreach ( $locations as $group => $hooks ) {
			$locations[ $group ] = array_filter(
				$hooks,
				static function ( string $description, string $hook ): bool {
					unset( $description );
					return Settings::is_location_enabled( $hook );
				},
				ARRAY_FILTER_USE_BOTH
			);
		}

		return array_filter(
			$locations,
			static function ( array $hooks ): bool {
				return $hooks !== array();
			}
		);
	}

	public function enqueue_assets( string $hook_suffix ): void {
		unset( $hook_suffix );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['page'] ) ) : '';

		if ( ! str_starts_with( $page, 'aegis-' ) ) {
			return;
		}

		$this->ensure_admin_settings_assets();

		$plugin_dir  = \Aegis\Plugin\DIR;
		$hook_css    = $plugin_dir . 'assets/admin/hook-patterns.css';
		$snippets_js = $plugin_dir . 'assets/admin/snippets-admin.js';

		wp_enqueue_style(
			'aegis-hook-patterns-admin',
			plugins_url( 'assets/admin/hook-patterns.css', \Aegis\Plugin\FILE ),
			array( 'aegis-admin-settings' ),
			file_exists( $hook_css ) ? (string) filemtime( $hook_css ) : \Aegis\Plugin\VERSION
		);

		wp_enqueue_script(
			'aegis-snippets-admin',
			plugins_url( 'assets/admin/snippets-admin.js', \Aegis\Plugin\FILE ),
			array( 'jquery' ),
			file_exists( $snippets_js ) ? (string) filemtime( $snippets_js ) : \Aegis\Plugin\VERSION,
			true
		);

		wp_localize_script(
			'aegis-snippets-admin',
			'aegisSnippetsList',
			array(
				'nonce'       => wp_create_nonce( 'aegis_toggle_snippet' ),
				'importNonce' => wp_create_nonce( 'aegis_import_snippets' ),
				'shortcodeTag' => Locations::SHORTCODE_TAG,
				'i18n'        => array(
					'copy'           => __( 'Copy shortcode', 'aegis' ),
					'copied'         => __( 'Copied', 'aegis' ),
					'copyUrl'           => __( 'Copy', 'aegis' ),
					'confirmDelete'     => __( 'Delete this snippet? There is no undo.', 'aegis' ),
					'confirmRegenerate' => __( 'Regenerate the Safe Mode URL? The old URL will stop working.', 'aegis' ),
					'hiddenInactive'    => __( '%d inactive snippets hidden.', 'aegis' ),
					'importFailed'      => __( 'Could not import snippets.', 'aegis' ),
					'publishedLabel'    => __( 'Published', 'aegis' ),
					'disabledLabel'     => __( 'Disabled', 'aegis' ),
				),
			)
		);

		if ( $page === 'aegis-snippets' && isset( $_GET['edit'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			foreach ( array( 'text/html', 'text/css', 'application/javascript', 'application/x-httpd-php' ) as $mime ) {
				wp_enqueue_code_editor(
					array(
						'type'       => $mime,
						'codemirror' => array(
							'lint'              => true,
							'lineNumbers'       => true,
							'matchBrackets'     => true,
							'autoCloseBrackets' => true,
							'gutters'           => array( 'CodeMirror-lint-markers', 'CodeMirror-linenumbers' ),
						),
					)
				);
			}

			wp_enqueue_script( 'code-editor' );
			wp_enqueue_style( 'code-editor' );
			wp_enqueue_style( 'wp-codemirror' );

			wp_enqueue_script(
				'aegis-code-editor',
				plugins_url( 'assets/admin/code-editor.js', \Aegis\Plugin\FILE ),
				array( 'code-editor', 'jquery' ),
				\Aegis\Plugin\VERSION,
				true
			);

			$custom_hooks = Locations::custom_hooks_for_editor( $this->filter_enabled_locations( LocationRegistry::get_grouped_for_select() ) );

			wp_localize_script(
				'aegis-code-editor',
				'aegisSnippetEditor',
				array(
					'locations'     => Locations::for_editor( $custom_hooks ),
					'locationHelp'  => Locations::location_help(),
					'locationCards' => array(
						'php'     => Locations::cards_for_type( Locations::TYPE_PHP ),
						'content' => Locations::cards_for_type( Locations::TYPE_CONTENT ),
						'css'     => Locations::cards_for_type( Locations::TYPE_CSS ),
						'js'      => Locations::cards_for_type( Locations::TYPE_JS ),
					),
					'starters'      => array(
						'php'     => Locations::starter( Locations::TYPE_PHP ),
						'content' => Locations::starter( Locations::TYPE_CONTENT ),
						'css'     => Locations::starter( Locations::TYPE_CSS ),
						'js'      => Locations::starter( Locations::TYPE_JS ),
					),
					'typeBadges'    => Locations::type_badges(),
					'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
					'lintNonce'     => wp_create_nonce( 'aegis_lint_php' ),
					'phpEnabled'    => Settings::is_php_enabled(),
					'i18n'       => array(
						'suggestPhp'     => __( 'This looks like PHP. Switch to Functions (PHP)?', 'aegis' ),
						'suggestCss'     => __( 'This looks like CSS. Switch to CSS?', 'aegis' ),
						'suggestContent' => __( 'This looks like HTML. Switch to Content?', 'aegis' ),
						'switchType'     => __( 'Switch type', 'aegis' ),
						'dismiss'        => __( 'Dismiss', 'aegis' ),
					),
				)
			);

			wp_enqueue_script(
				'aegis-smart-conditions',
				plugins_url( 'assets/admin/smart-conditions.js', \Aegis\Plugin\FILE ),
				array(),
				\Aegis\Plugin\VERSION,
				true
			);
		}
	}

	public function render_safe_mode_banner(): void {
		if ( ! Settings::is_safe_mode() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$forced = Settings::is_forced_by_constant();
		?>
		<div class="aegis-safe-mode-banner">
			<div>
				<strong><?php esc_html_e( 'Safe Mode Active', 'aegis' ); ?></strong>
				<span>
					<?php
					if ( $forced ) {
						esc_html_e( 'AEGIS_DISABLE_SNIPPETS is defined in wp-config.php. Remove that line to turn Safe Mode off.', 'aegis' );
					} else {
						esc_html_e( 'All code snippets and hook pattern output is disabled. Fix the offending snippet before turning Safe Mode off.', 'aegis' );
					}
					?>
				</span>
			</div>
			<?php if ( $forced ) : ?>
				<a class="button" href="<?php echo esc_url( add_query_arg( 'tab', 'settings', admin_url( 'admin.php?page=aegis-snippets' ) ) ); ?>"><?php esc_html_e( 'Open Snippet Settings', 'aegis' ); ?></a>
			<?php else : ?>
				<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=aegis_disable_safe_mode' ), 'aegis_disable_safe_mode' ) ); ?>"><?php esc_html_e( 'Turn off Safe Mode', 'aegis' ); ?></a>
			<?php endif; ?>
		</div>
		<?php
	}

	public function ajax_toggle_snippet(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized', 'aegis' ) ) );
		}

		check_ajax_referer( 'aegis_toggle_snippet', 'nonce' );

		$id      = sanitize_text_field( wp_unslash( (string) ( $_POST['id'] ?? '' ) ) );
		$enabled = ! empty( $_POST['enabled'] );

		if ( $id === '' || ! Storage::toggle_snippet( $id, $enabled ) ) {
			wp_send_json_error( array( 'message' => __( 'Could not update snippet.', 'aegis' ) ) );
		}

		wp_send_json_success( array( 'enabled' => $enabled ) );
	}

	public function ajax_import_snippets(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized', 'aegis' ) ) );
		}

		check_ajax_referer( 'aegis_import_snippets', 'nonce' );

		$json = isset( $_POST['payload'] ) ? (string) wp_unslash( $_POST['payload'] ) : '';
		$data = json_decode( $json, true );

		if ( ! is_array( $data ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid snippet export.', 'aegis' ) ) );
		}

		$count = Storage::import_payload( $data );

		wp_send_json_success(
			array(
				'message' => sprintf(
					/* translators: %d: number of imported snippets */
					_n( '%d snippet imported.', '%d snippets imported.', $count, 'aegis' ),
					$count
				),
			)
		);
	}

	public function handle_download(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'aegis' ) );
		}

		check_admin_referer( 'aegis_download_snippet' );

		$id      = sanitize_text_field( wp_unslash( (string) ( $_GET['id'] ?? '' ) ) );
		$payload = Storage::export_payload( $id );

		if ( $id === '' || empty( $payload['snippets'] ) ) {
			wp_die( esc_html__( 'Snippet not found.', 'aegis' ) );
		}

		$this->send_snippet_download( $payload, 'aegis-snippet-' . sanitize_file_name( $id ) . '.json' );
	}

	public function handle_export(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'aegis' ) );
		}

		check_admin_referer( 'aegis_export_snippets' );

		$this->send_snippet_download( Storage::export_payload(), 'aegis-snippets.json' );
	}

	/**
	 * @param array<string, mixed> $payload Export payload.
	 */
	private function send_snippet_download( array $payload, string $filename ): void {
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		echo wp_json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
		exit;
	}

	public function ajax_lint_php(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized', 'aegis' ) ) );
		}

		check_ajax_referer( 'aegis_lint_php' );

		$code     = isset( $_POST['code'] ) ? (string) wp_unslash( $_POST['code'] ) : '';
		$wrapped  = false;

		if ( $code === '' ) {
			wp_send_json_success( array( 'errors' => array() ) );
		}

		if ( ! str_contains( $code, '<?php' ) && ! str_contains( $code, '<?=' ) ) {
			$code    = "<?php\n" . $code;
			$wrapped = true;
		}

		if ( ! Storage::ensure_directory() ) {
			wp_send_json_error( array( 'message' => __( 'Could not write a temporary file.', 'aegis' ) ) );
		}

		$file = '.lint-' . wp_generate_uuid4() . '.php';
		$path = Storage::resolve_snippet_path( $file );

		if ( $path === null ) {
			wp_send_json_error( array( 'message' => __( 'Could not write a temporary file.', 'aegis' ) ) );
		}

		file_put_contents( $path, $code );

		$output   = array();
		$status   = 0;
		$php      = ( defined( 'PHP_BINARY' ) && PHP_BINARY !== '' ) ? PHP_BINARY : 'php';
		$errors   = array();

		if ( function_exists( 'exec' ) ) {
			exec( escapeshellarg( $php ) . ' -l ' . escapeshellarg( $path ) . ' 2>&1', $output, $status );
		}

		if ( file_exists( $path ) ) {
			unlink( $path );
		}

		if ( $status !== 0 && $output !== array() ) {
			$raw  = implode( "\n", $output );
			$line = 1;

			if ( preg_match( '/on line (\d+)/i', $raw, $match ) ) {
				$line = max( 1, (int) $match[1] );

				if ( $wrapped ) {
					$line = max( 1, $line - 1 );
				}
			}

			$errors[] = array(
				'line'     => $line,
				'message'  => wp_strip_all_tags( $raw ),
				'severity' => 'error',
			);
		}

		wp_send_json_success( array( 'errors' => $errors ) );
	}

	private function ensure_admin_settings_assets(): void {
		if ( wp_style_is( 'aegis-admin-settings', 'registered' ) ) {
			wp_enqueue_style( 'aegis-admin-settings' );
			wp_enqueue_script( 'aegis-admin-settings' );
			return;
		}

		$asset_file = \Aegis\Plugin\DIR . 'assets/admin/build/index.asset.php';

		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset      = require $asset_file;
		$plugin_url = plugins_url( '', \Aegis\Plugin\FILE );

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

	public function handle_save(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'aegis' ) );
		}

		check_admin_referer( 'aegis_save_snippet' );

		$posted_id = sanitize_text_field( wp_unslash( (string) ( $_POST['snippet_id'] ?? '' ) ) );
		$title     = sanitize_text_field( wp_unslash( (string) ( $_POST['snippet_title'] ?? '' ) ) );
		$type      = Locations::normalize_type( sanitize_key( wp_unslash( (string) ( $_POST['snippet_type'] ?? 'content' ) ) ) );
		$content   = isset( $_POST['snippet_content'] ) ? (string) wp_unslash( $_POST['snippet_content'] ) : '';

		if ( ! isset( Locations::types()[ $type ] ) ) {
			$type = Locations::TYPE_CONTENT;
		}

		if ( $type === Locations::TYPE_PHP && ! Settings::is_php_enabled() ) {
			wp_die( esc_html__( 'PHP snippets are disabled in settings.', 'aegis' ) );
		}

		$existing = $posted_id !== '' ? Storage::get_snippet( $posted_id ) : null;
		$id       = $existing !== null ? $posted_id : Storage::unique_id_from_title( $title );
		$conditions   = array();
		$raw_cond     = wp_unslash( (string) ( $_POST['snippet_conditions'] ?? '' ) );
		$decoded_cond = json_decode( $raw_cond, true );
		if ( is_array( $decoded_cond ) ) {
			$conditions = $decoded_cond;
		}

		$tags_raw = sanitize_text_field( wp_unslash( (string) ( $_POST['snippet_tags'] ?? '' ) ) );
		$tags     = array_values(
			array_filter(
				array_map( 'trim', explode( ',', $tags_raw ) )
			)
		);

		$location = sanitize_text_field( wp_unslash( (string) ( $_POST['snippet_location'] ?? '' ) ) );

		if ( $location === '' ) {
			$location = Locations::default_location( $type );
		}

		$user    = wp_get_current_user();
		$snippet = array(
			'id'         => $id,
			'title'      => $title,
			'type'       => $type,
			'location'   => $location,
			'priority'   => (int) ( $_POST['snippet_priority'] ?? 10 ),
			'scope'      => Locations::scope_from_location( $type, $location ),
			'enabled'    => ! empty( $_POST['snippet_enabled'] ),
			'conditions' => $conditions,
			'tags'       => $tags,
			'group'      => sanitize_text_field( wp_unslash( (string) ( $_POST['snippet_group'] ?? '' ) ) ),
			'shortcode'  => (string) ( is_array( $existing ) ? ( $existing['shortcode'] ?? '' ) : '' ),
			'note'       => sanitize_textarea_field( wp_unslash( (string) ( $_POST['snippet_note'] ?? '' ) ) ),
			'author'     => is_array( $existing ) ? ( $existing['author'] ?? ( $user->exists() ? $user->display_name : '' ) ) : ( $user->exists() ? $user->display_name : '' ),
			'created_at' => is_array( $existing ) ? ( $existing['created_at'] ?? gmdate( 'c' ) ) : gmdate( 'c' ),
			'updated_at' => gmdate( 'c' ),
			'last_error' => is_array( $existing ) ? ( $existing['last_error'] ?? null ) : null,
		);

		Storage::save_snippet( $snippet );
		Storage::write_snippet_file( $id, $type, $content );

		wp_safe_redirect( admin_url( 'admin.php?page=aegis-snippets&edit=' . rawurlencode( $id ) . '&updated=1' ) );
		exit;
	}

	public function handle_delete(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'aegis' ) );
		}

		check_admin_referer( 'aegis_delete_snippet' );

		$id = sanitize_text_field( wp_unslash( (string) ( $_GET['id'] ?? '' ) ) );

		if ( $id !== '' ) {
			Storage::delete_snippet( $id );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=aegis-snippets&deleted=1' ) );
		exit;
	}

	public function handle_settings_save(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'aegis' ) );
		}

		check_admin_referer( 'aegis_save_snippets_settings' );

		$this->settings_controller->persist_settings( wp_unslash( $_POST ) );

		wp_safe_redirect( add_query_arg( array( 'tab' => 'settings', 'settings' => '1' ), admin_url( 'admin.php?page=aegis-snippets' ) ) );
		exit;
	}

	public function handle_regenerate_safe_mode_token(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'aegis' ) );
		}

		check_admin_referer( 'aegis_regenerate_safe_mode_token' );
		Settings::regenerate_safe_mode_token();
		wp_safe_redirect( add_query_arg( array( 'tab' => 'settings', 'token' => '1' ), admin_url( 'admin.php?page=aegis-snippets' ) ) );
		exit;
	}

	public function handle_disable_safe_mode(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'aegis' ) );
		}

		check_admin_referer( 'aegis_disable_safe_mode' );
		Settings::disable_safe_mode();
		wp_safe_redirect( add_query_arg( 'tab', 'settings', admin_url( 'admin.php?page=aegis-snippets' ) ) );
		exit;
	}

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$snippets         = Storage::get_snippets();
		$locations        = LocationRegistry::get_grouped_for_select();
		$select_locations = $this->filter_enabled_locations( $locations );
		$edit_id          = sanitize_text_field( wp_unslash( (string) ( $_GET['edit'] ?? '' ) ) );
		$editing          = $edit_id !== '' ? Storage::get_snippet( $edit_id ) : null;
		$settings         = Settings::get_settings();
		$is_editing       = isset( $_GET['edit'] );
		$tab              = sanitize_key( wp_unslash( (string) ( $_GET['tab'] ?? 'snippets' ) ) );
		$is_settings_tab  = $tab === 'settings';

		?>
		<div class="wrap aegis-admin-page">
			<?php do_action( 'aegis_admin_before_snippets_page' ); ?>

			<div class="aegis-settings-wrap aegis-snippets-page">
				<h1 class="screen-reader-text"><?php echo $is_editing ? esc_html__( 'Edit Snippet', 'aegis' ) : esc_html__( 'Code Snippets', 'aegis' ); ?></h1>

				<?php if ( isset( $_GET['updated'] ) ) : ?>
					<div class="aegis-notice aegis-notice-success"><?php esc_html_e( 'Snippet saved.', 'aegis' ); ?></div>
				<?php elseif ( isset( $_GET['deleted'] ) ) : ?>
					<div class="aegis-notice aegis-notice-success"><?php esc_html_e( 'Snippet deleted.', 'aegis' ); ?></div>
				<?php elseif ( isset( $_GET['imported'] ) ) : ?>
					<div class="aegis-notice aegis-notice-success"><?php esc_html_e( 'Snippets imported.', 'aegis' ); ?></div>
				<?php elseif ( isset( $_GET['settings'] ) ) : ?>
					<div class="aegis-notice aegis-notice-success"><?php esc_html_e( 'Snippet settings saved.', 'aegis' ); ?></div>
				<?php elseif ( isset( $_GET['token'] ) ) : ?>
					<div class="aegis-notice aegis-notice-success"><?php esc_html_e( 'Safe Mode URL regenerated. Update any copies you stored.', 'aegis' ); ?></div>
				<?php endif; ?>

				<?php if ( $is_editing ) : ?>
					<?php $this->render_editor( $editing, $select_locations ); ?>
				<?php elseif ( $is_settings_tab ) : ?>
					<?php $this->render_settings_screen( $settings, $locations ); ?>
				<?php else : ?>
					<?php $this->render_list_toolbar(); ?>
					<?php if ( $snippets === array() ) : ?>
						<?php $this->render_empty(); ?>
					<?php else : ?>
						<?php $this->render_list( $snippets ); ?>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	private function render_list_toolbar(): void {
		$create_url = admin_url( 'admin.php?page=aegis-snippets&edit=new' );
		$export_url = wp_nonce_url( admin_url( 'admin-post.php?action=aegis_export_snippets' ), 'aegis_export_snippets' );
		?>
		<div class="wp-filter aegis-toolbar">
			<div class="aegis-toolbar-left filter-items">
				<a href="<?php echo esc_url( $create_url ); ?>" class="button button-primary">
					<span class="dashicons dashicons-plus-alt2"></span>
					<?php esc_html_e( 'Add New', 'aegis' ); ?>
				</a>
				<a class="button" href="<?php echo esc_url( $export_url ); ?>"><?php esc_html_e( 'Export', 'aegis' ); ?></a>
				<button type="button" class="button" id="aegis-snippet-import">
					<?php esc_html_e( 'Import', 'aegis' ); ?>
				</button>
				<input type="file" id="aegis-snippet-import-file" accept="application/json,.json" hidden>
			</div>
			<div class="aegis-toolbar-right search-form">
				<p class="search-box">
					<label for="aegis-snippet-search"><?php esc_html_e( 'Search', 'aegis' ); ?></label>
					<input type="search" id="aegis-snippet-search" class="aegis-search-input">
				</p>
			</div>
		</div>
		<?php
	}

	private function render_empty(): void {
		$create_url = admin_url( 'admin.php?page=aegis-snippets&edit=new' );
		?>
		<div class="aegis-snippets-empty">
			<h2><?php esc_html_e( 'No snippets yet', 'aegis' ); ?></h2>
			<p><?php esc_html_e( 'Click Add New to create one.', 'aegis' ); ?></p>
			<p class="aegis-snippets-empty-action">
				<a href="<?php echo esc_url( $create_url ); ?>" class="button button-primary">
					<span class="dashicons dashicons-plus-alt2"></span>
					<?php esc_html_e( 'Add New', 'aegis' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * @param array<string, mixed>                 $settings  Snippet settings.
	 * @param array<string, array<string, string>> $locations Grouped locations.
	 */
	private function render_settings_screen( array $settings, array $locations ): void {
		?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="aegis-snippets-settings-form">
			<input type="hidden" name="action" value="aegis_save_snippets_settings" />
			<?php wp_nonce_field( 'aegis_save_snippets_settings' ); ?>
			<div class="aegis-snippets-settings-screen">
				<?php $this->render_settings_panel( $settings ); ?>
				<?php $this->render_safe_mode_panel( $settings ); ?>
				<?php $this->render_location_reference( $locations ); ?>
				<div class="aegis-snippets-settings-actions aegis-snippets-settings-actions--page">
					<?php submit_button( __( 'Save Settings', 'aegis' ), 'primary', 'submit', false ); ?>
				</div>
			</div>
		</form>
		<?php
	}

	/**
	 * @param array<string, bool> $settings Snippet settings.
	 */
	private function render_settings_panel( array $settings ): void {
		$hooks_preview     = Preview::is_hooks_preview_enabled();
		$snippets_preview  = Preview::is_snippets_preview_enabled();
		$admin_bar_preview = Preview::is_admin_bar_shortcuts_enabled();
		?>
		<div class="aegis-snippets-settings aegis-settings-section active">
			<?php $this->renderer()->render_section_header( __( 'Snippet Settings', 'aegis' ), __( 'Control PHP snippets, frontend previews, and admin bar shortcuts.', 'aegis' ), false, 'admin-settings' ); ?>

			<div class="aegis-snippets-settings-list">
				<div class="aegis-toggle-card">
					<div class="aegis-toggle-info">
						<div class="aegis-toggle-icon">
							<span class="dashicons dashicons-editor-code"></span>
						</div>
						<div class="aegis-toggle-text">
							<h3><?php esc_html_e( 'PHP snippets', 'aegis' ); ?></h3>
							<p><?php esc_html_e( 'Allow trusted administrators to run PHP files from the protected snippets directory.', 'aegis' ); ?></p>
						</div>
					</div>
					<label class="aegis-toggle">
						<input type="checkbox" name="php_enabled" value="1" <?php checked( ! empty( $settings['php_enabled'] ) ); ?> />
						<span class="aegis-toggle-slider"></span>
					</label>
				</div>

				<div class="aegis-toggle-card">
					<div class="aegis-toggle-info">
						<div class="aegis-toggle-icon">
							<span class="dashicons dashicons-warning"></span>
						</div>
						<div class="aegis-toggle-text">
							<h3><?php esc_html_e( 'Auto Safe Mode on Fatal Error', 'aegis' ); ?></h3>
							<p><?php esc_html_e( 'Enable site-wide safe mode when a PHP snippet triggers a fatal error.', 'aegis' ); ?></p>
						</div>
					</div>
					<label class="aegis-toggle">
						<input type="checkbox" name="auto_safe_mode_on_fatal" value="1" <?php checked( ! empty( $settings['auto_safe_mode_on_fatal'] ) ); ?> />
						<span class="aegis-toggle-slider"></span>
					</label>
				</div>

				<div class="aegis-toggle-card">
					<div class="aegis-toggle-info">
						<div class="aegis-toggle-icon">
							<span class="dashicons dashicons-layout"></span>
						</div>
						<div class="aegis-toggle-text">
							<h3><?php esc_html_e( 'Show hook positions', 'aegis' ); ?></h3>
							<p><?php esc_html_e( 'Highlight available hook locations on the frontend, including wp_head and template parts.', 'aegis' ); ?></p>
						</div>
					</div>
					<label class="aegis-toggle">
						<input type="checkbox" name="preview_hooks" value="1" <?php checked( $hooks_preview ); ?> />
						<span class="aegis-toggle-slider"></span>
					</label>
				</div>

				<div class="aegis-toggle-card">
					<div class="aegis-toggle-info">
						<div class="aegis-toggle-icon">
							<span class="dashicons dashicons-media-code"></span>
						</div>
						<div class="aegis-toggle-text">
							<h3><?php esc_html_e( 'Show snippet positions', 'aegis' ); ?></h3>
							<p><?php esc_html_e( 'Highlight snippet injection points and show how many snippets use each location.', 'aegis' ); ?></p>
						</div>
					</div>
					<label class="aegis-toggle">
						<input type="checkbox" name="preview_snippets" value="1" <?php checked( $snippets_preview ); ?> />
						<span class="aegis-toggle-slider"></span>
					</label>
				</div>

				<div class="aegis-toggle-card">
					<div class="aegis-toggle-info">
						<div class="aegis-toggle-icon">
							<span class="dashicons dashicons-admin-site"></span>
						</div>
						<div class="aegis-toggle-text">
							<h3><?php esc_html_e( 'Admin bar shortcuts', 'aegis' ); ?></h3>
							<p><?php esc_html_e( 'Show the Aegis Positions menu in the frontend admin bar with the brand icon.', 'aegis' ); ?></p>
						</div>
					</div>
					<label class="aegis-toggle">
						<input type="checkbox" name="preview_admin_bar" value="1" <?php checked( $admin_bar_preview ); ?> />
						<span class="aegis-toggle-slider"></span>
					</label>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * @param array<string, mixed> $settings Snippet settings.
	 */
	private function render_safe_mode_panel( array $settings ): void {
		$forced         = Settings::is_forced_by_constant();
		$regenerate_url = wp_nonce_url( admin_url( 'admin-post.php?action=aegis_regenerate_safe_mode_token' ), 'aegis_regenerate_safe_mode_token' );
		?>
		<div class="aegis-snippets-settings aegis-safe-mode-panel aegis-settings-section active">
			<?php $this->renderer()->render_section_header( __( 'Safe Mode', 'aegis' ), __( 'Turn every snippet and hook pattern off at once. The URL works without logging in.', 'aegis' ), false, 'shield' ); ?>

			<div class="aegis-snippets-settings-list">
				<div class="aegis-toggle-card">
					<div class="aegis-toggle-info">
						<div class="aegis-toggle-icon">
							<span class="dashicons dashicons-shield"></span>
						</div>
						<div class="aegis-toggle-text">
							<h3><?php esc_html_e( 'Enable Safe Mode', 'aegis' ); ?></h3>
							<p><?php esc_html_e( 'Temporarily disable all snippet and hook pattern output. Admin screens still work so you can fix the offending snippet first.', 'aegis' ); ?></p>
							<?php if ( $forced ) : ?>
								<p class="description"><?php esc_html_e( 'AEGIS_DISABLE_SNIPPETS is defined in wp-config.php, so Safe Mode stays on until you remove that line.', 'aegis' ); ?></p>
							<?php endif; ?>
						</div>
					</div>
					<label class="aegis-toggle">
						<input type="checkbox" name="safe_mode" value="1" <?php checked( $forced || ! empty( $settings['safe_mode'] ) ); ?> <?php disabled( $forced ); ?> />
						<span class="aegis-toggle-slider"></span>
					</label>
				</div>
			</div>

			<div class="aegis-safe-mode-panel-body">
				<div class="aegis-safe-mode-url-field">
					<label for="aegis-safe-mode-url"><?php esc_html_e( 'Safe Mode URL', 'aegis' ); ?></label>
					<p class="description"><?php esc_html_e( 'Copy this somewhere you can reach without your site. Anyone with the URL can disable custom code, so treat it like a password.', 'aegis' ); ?></p>
					<div class="aegis-safe-mode-url-row">
						<code id="aegis-safe-mode-url"><?php echo esc_html( Settings::get_safe_mode_url() ); ?></code>
						<button type="button" class="button aegis-safe-mode-url-copy"><?php esc_html_e( 'Copy', 'aegis' ); ?></button>
						<a class="button aegis-safe-mode-url-regenerate" href="<?php echo esc_url( $regenerate_url ); ?>"><?php esc_html_e( 'Regenerate', 'aegis' ); ?></a>
					</div>
				</div>

				<div class="aegis-safe-mode-constant">
					<p><?php esc_html_e( 'If you cannot reach the site over HTTP, add this to wp-config.php:', 'aegis' ); ?></p>
					<pre><code><?php echo esc_html( "define( 'AEGIS_DISABLE_SNIPPETS', true );" ); ?></code></pre>
					<p class="description"><?php esc_html_e( 'Remove the line to turn Safe Mode off. That cannot be undone from the admin.', 'aegis' ); ?></p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * @param array<int, array<string, mixed>> $snippets Snippet list.
	 */
	private function render_list( array $snippets ): void {
		$groups = array();
		$tags   = array();

		foreach ( $snippets as $snippet ) {
			$group = trim( (string) ( $snippet['group'] ?? '' ) );

			if ( $group !== '' && ! in_array( $group, $groups, true ) ) {
				$groups[] = $group;
			}

			foreach ( (array) ( $snippet['tags'] ?? array() ) as $tag ) {
				$tag = trim( (string) $tag );
				if ( $tag !== '' && ! in_array( $tag, $tags, true ) ) {
					$tags[] = $tag;
				}
			}
		}

		sort( $groups );
		sort( $tags );

		$type_counts = array( '' => count( $snippets ) );
		foreach ( array_keys( Locations::types() ) as $type_key ) {
			$type_counts[ $type_key ] = 0;
		}
		foreach ( $snippets as $snippet ) {
			$type_key = Locations::normalize_type( (string) ( $snippet['type'] ?? Locations::TYPE_CONTENT ) );
			if ( isset( $type_counts[ $type_key ] ) ) {
				++$type_counts[ $type_key ];
			}
		}
		?>
		<div class="aegis-hooks-reference aegis-snippets-list" data-view="table">
			<div class="aegis-hooks-reference-header">
				<span class="dashicons dashicons-editor-code"></span>
				<span><?php esc_html_e( 'Your Snippets', 'aegis' ); ?></span>
			</div>
			<div class="aegis-snippets-filters">
				<div class="aegis-snippets-filters-primary">
					<label class="aegis-snippets-hide-inactive">
						<span><?php esc_html_e( 'Hide inactives', 'aegis' ); ?></span>
						<span class="aegis-toggle">
							<input type="checkbox" id="aegis-snippet-hide-inactive" />
							<span class="aegis-toggle-slider"></span>
						</span>
					</label>
					<ul class="subsubsub aegis-snippets-type-filters">
						<li>
							<button type="button" class="aegis-snippet-type-filter current is-active" data-type="" aria-pressed="true">
								<?php esc_html_e( 'All snippets', 'aegis' ); ?>
								<span class="count">(<?php echo esc_html( (string) number_format_i18n( (int) $type_counts[''] ) ); ?>)</span>
							</button>
						</li>
						<?php foreach ( Locations::types() as $type_key => $type_label ) : ?>
							<li>
								<button type="button" class="aegis-snippet-type-filter" data-type="<?php echo esc_attr( $type_key ); ?>" aria-pressed="false">
									<?php echo esc_html( $type_label ); ?>
									<span class="count">(<?php echo esc_html( (string) number_format_i18n( (int) ( $type_counts[ $type_key ] ?? 0 ) ) ); ?>)</span>
								</button>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
				<div class="aegis-snippets-filters-secondary">
					<div class="aegis-snippets-view-toggle" role="group" aria-label="<?php esc_attr_e( 'View', 'aegis' ); ?>">
						<button type="button" class="button aegis-snippet-view" data-view="grouped" aria-pressed="false"><?php esc_html_e( 'Grouped', 'aegis' ); ?></button>
						<button type="button" class="button aegis-snippet-view is-active" data-view="table" aria-pressed="true"><?php esc_html_e( 'Table', 'aegis' ); ?></button>
					</div>
					<label class="screen-reader-text" for="aegis-snippet-tag-filter"><?php esc_html_e( 'Filter by tag', 'aegis' ); ?></label>
					<select id="aegis-snippet-tag-filter">
						<option value=""><?php esc_html_e( 'All tags', 'aegis' ); ?></option>
						<?php foreach ( $tags as $tag ) : ?>
							<option value="<?php echo esc_attr( $tag ); ?>"><?php echo esc_html( $tag ); ?></option>
						<?php endforeach; ?>
					</select>
					<label class="screen-reader-text" for="aegis-snippet-sort"><?php esc_html_e( 'Sort snippets', 'aegis' ); ?></label>
					<select id="aegis-snippet-sort">
						<option value="name-asc"><?php esc_html_e( 'Name', 'aegis' ); ?></option>
						<option value="created-desc"><?php esc_html_e( 'Created (newest)', 'aegis' ); ?></option>
						<option value="created-asc"><?php esc_html_e( 'Created (oldest)', 'aegis' ); ?></option>
						<option value="updated-desc"><?php esc_html_e( 'Updated (newest)', 'aegis' ); ?></option>
						<option value="updated-asc"><?php esc_html_e( 'Updated (oldest)', 'aegis' ); ?></option>
						<option value="priority-asc"><?php esc_html_e( 'Priority (low first)', 'aegis' ); ?></option>
						<option value="priority-desc"><?php esc_html_e( 'Priority (high first)', 'aegis' ); ?></option>
					</select>
				</div>
			</div>
			<div class="aegis-snippets-table-view">
					<div class="aegis-admin-table-wrap">
						<table class="aegis-admin-table aegis-snippets-table">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Title', 'aegis' ); ?></th>
									<th><?php esc_html_e( 'Description', 'aegis' ); ?></th>
									<th><?php esc_html_e( 'Type', 'aegis' ); ?></th>
									<th><?php esc_html_e( 'Runs at', 'aegis' ); ?></th>
									<th><?php esc_html_e( 'Tags', 'aegis' ); ?></th>
									<th><?php esc_html_e( 'Date', 'aegis' ); ?></th>
									<th><?php esc_html_e( 'Status', 'aegis' ); ?></th>
									<th><?php esc_html_e( 'Actions', 'aegis' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $snippets as $snippet ) : ?>
									<?php $this->render_snippet_table_row( $snippet ); ?>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>
				<div class="aegis-snippets-grouped-view" hidden>
					<?php
					$grouped   = array();
					$ungrouped = array();
					foreach ( $snippets as $snippet ) {
						$group = trim( (string) ( $snippet['group'] ?? '' ) );
						if ( $group === '' ) {
							$ungrouped[] = $snippet;
						} else {
							$grouped[ $group ][] = $snippet;
						}
					}
					ksort( $grouped, SORT_NATURAL | SORT_FLAG_CASE );
					foreach ( $grouped as $group_name => $group_snippets ) :
						?>
						<div class="aegis-snippets-folder" data-snippet-group="<?php echo esc_attr( (string) $group_name ); ?>">
							<button type="button" class="aegis-snippets-folder-header">
								<span class="dashicons dashicons-category"></span>
								<strong><?php echo esc_html( (string) $group_name ); ?></strong>
								<span class="aegis-snippets-folder-count"><?php echo esc_html( (string) count( $group_snippets ) ); ?></span>
							</button>
							<div class="aegis-admin-table-wrap">
								<table class="aegis-admin-table aegis-snippets-table">
									<tbody>
										<?php foreach ( $group_snippets as $snippet ) : ?>
											<?php $this->render_snippet_table_row( $snippet ); ?>
										<?php endforeach; ?>
									</tbody>
								</table>
							</div>
						</div>
					<?php endforeach; ?>
					<?php if ( $ungrouped !== array() ) : ?>
						<div class="aegis-snippets-folder" data-snippet-group="">
							<button type="button" class="aegis-snippets-folder-header">
								<span class="dashicons dashicons-category"></span>
								<strong><?php esc_html_e( 'Ungrouped', 'aegis' ); ?></strong>
								<span class="aegis-snippets-folder-count"><?php echo esc_html( (string) count( $ungrouped ) ); ?></span>
							</button>
							<div class="aegis-admin-table-wrap">
								<table class="aegis-admin-table aegis-snippets-table">
									<tbody>
										<?php foreach ( $ungrouped as $snippet ) : ?>
											<?php $this->render_snippet_table_row( $snippet ); ?>
										<?php endforeach; ?>
									</tbody>
								</table>
							</div>
						</div>
					<?php endif; ?>
				</div>
				<p class="aegis-snippets-hidden-count" hidden></p>
		</div>
		<?php
	}

	/**
	 * @param array<string, mixed> $snippet Snippet row.
	 */
	private function render_snippet_table_row( array $snippet ): void {
		$id           = (string) ( $snippet['id'] ?? '' );
		$type         = Locations::normalize_type( (string) ( $snippet['type'] ?? 'content' ) );
		$group        = trim( (string) ( $snippet['group'] ?? '' ) );
		$note         = (string) ( $snippet['note'] ?? '' );
		$error        = (string) ( $snippet['last_error'] ?? '' );
		$enabled      = ! empty( $snippet['enabled'] );
		$location_key = (string) ( $snippet['location'] ?? '' );
		$priority     = (int) ( $snippet['priority'] ?? 10 );
		$tag_list     = array_values( array_filter( array_map( 'strval', (array) ( $snippet['tags'] ?? array() ) ) ) );
		$search       = strtolower( implode( ' ', array_filter( array( (string) ( $snippet['title'] ?? '' ), $note, $group, implode( ' ', $tag_list ) ) ) ) );
		$created      = strtotime( (string) ( $snippet['created_at'] ?? '' ) ) ?: 0;
		$updated      = strtotime( (string) ( $snippet['updated_at'] ?? $snippet['created_at'] ?? '' ) ) ?: 0;
		$paused       = ! $enabled && $error !== '';
		$runs_at      = $location_key === 'shortcode'
			? Locations::shortcode_markup( $id )
			: $this->get_location_label( $location_key, $type );
		?>
		<tr
			class="aegis-snippet-item<?php echo $paused ? ' has-error' : ''; ?>"
			data-snippet-id="<?php echo esc_attr( $id ); ?>"
			data-snippet-type="<?php echo esc_attr( $type ); ?>"
			data-snippet-group="<?php echo esc_attr( $group ); ?>"
			data-snippet-tags="<?php echo esc_attr( implode( ',', $tag_list ) ); ?>"
			data-snippet-enabled="<?php echo $enabled ? '1' : '0'; ?>"
			data-search="<?php echo esc_attr( $search ); ?>"
			data-sort-name="<?php echo esc_attr( strtolower( (string) ( $snippet['title'] ?? '' ) ) ); ?>"
			data-sort-created="<?php echo esc_attr( (string) $created ); ?>"
			data-sort-updated="<?php echo esc_attr( (string) $updated ); ?>"
			data-sort-priority="<?php echo esc_attr( (string) $priority ); ?>"
		>
			<td>
				<div class="aegis-snippet-title-cell">
					<strong><?php echo esc_html( (string) ( $snippet['title'] ?? '' ) ); ?></strong>
				</div>
				<?php if ( $group !== '' ) : ?>
					<span class="aegis-hooks-meta aegis-snippet-group-label"><span class="dashicons dashicons-category"></span><?php echo esc_html( $group ); ?></span>
				<?php endif; ?>
			</td>
			<td>
				<?php if ( $error !== '' ) : ?>
					<span class="aegis-snippets-error"><?php echo esc_html( $error ); ?></span>
				<?php else : ?>
					<?php echo $note !== '' ? esc_html( $note ) : '—'; ?>
				<?php endif; ?>
			</td>
			<td><span class="aegis-badge aegis-badge-type aegis-badge-type-<?php echo esc_attr( $type ); ?>"><?php echo esc_html( Locations::type_label( $type ) ); ?></span></td>
			<td>
				<span class="aegis-snippet-runs-at">
					<span class="dashicons dashicons-superhero"></span>
					<?php echo esc_html( $runs_at ); ?>
					<code>= <?php echo esc_html( (string) $priority ); ?></code>
				</span>
			</td>
			<td>
				<?php foreach ( $tag_list as $tag ) : ?>
					<span class="aegis-tag-chip"><?php echo esc_html( $tag ); ?></span>
				<?php endforeach; ?>
				<?php if ( $tag_list === array() ) : ?>
					—
				<?php endif; ?>
			</td>
			<td>
				<?php esc_html_e( 'Last Modified', 'aegis' ); ?>
				<span class="aegis-hooks-meta"><?php echo esc_html( $this->format_list_date( (string) ( $snippet['updated_at'] ?? $snippet['created_at'] ?? '' ) ) ); ?></span>
			</td>
			<td class="aegis-snippets-status">
				<?php if ( $enabled ) : ?>
					<span class="aegis-status-enabled"><?php esc_html_e( 'Published', 'aegis' ); ?></span>
				<?php else : ?>
					<span class="aegis-status-disabled"><?php esc_html_e( 'Disabled', 'aegis' ); ?></span>
				<?php endif; ?>
			</td>
			<td>
				<div class="aegis-hooks-row-actions">
					<label class="aegis-toggle" title="<?php esc_attr_e( 'Enable or disable snippet', 'aegis' ); ?>">
						<input type="checkbox" class="aegis-snippet-status-toggle" value="1" <?php checked( $enabled ); ?> />
						<span class="aegis-toggle-slider"></span>
					</label>
					<a class="button button-compact" href="<?php echo esc_url( admin_url( 'admin.php?page=aegis-snippets&edit=' . rawurlencode( $id ) ) ); ?>"><?php esc_html_e( 'Edit', 'aegis' ); ?></a>
					<a class="button button-compact" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=aegis_download_snippet&id=' . rawurlencode( $id ) ), 'aegis_download_snippet' ) ); ?>"><?php esc_html_e( 'Download', 'aegis' ); ?></a>
					<a class="button button-compact aegis-snippet-delete" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=aegis_delete_snippet&id=' . rawurlencode( $id ) ), 'aegis_delete_snippet' ) ); ?>"><?php esc_html_e( 'Delete', 'aegis' ); ?></a>
				</div>
			</td>
		</tr>
		<?php
	}

	private function get_location_label( string $hook, string $type = '' ): string {
		if ( $hook === '' ) {
			return '—';
		}

		$virtual = Locations::label( $hook, $type );

		if ( $virtual !== $hook ) {
			return $virtual;
		}

		foreach ( LocationRegistry::get_grouped_for_select() as $hooks ) {
			if ( isset( $hooks[ $hook ] ) ) {
				return (string) $hooks[ $hook ];
			}
		}

		return $hook;
	}

	private function format_list_date( string $iso ): string {
		if ( $iso === '' ) {
			return '—';
		}

		$time = strtotime( $iso );

		if ( ! $time ) {
			return '—';
		}

		return sprintf(
			/* translators: 1: Date, 2: Time. */
			__( '%1$s at %2$s', 'aegis' ),
			(string) wp_date( __( 'Y/m/d', 'aegis' ), $time ),
			(string) wp_date( __( 'g:i a', 'aegis' ), $time )
		);
	}

	/**
	 * @param array<string, array<string, string>> $locations Grouped locations.
	 */
	private function render_location_reference( array $locations ): void {
		$icons = array(
			'global'         => 'dashicons-admin-site-alt3',
			'template-parts' => 'dashicons-layout',
			'content'        => 'dashicons-text-page',
			'integrations'   => 'dashicons-admin-plugins',
			'admin'          => 'dashicons-admin-tools',
			'login'          => 'dashicons-lock',
			'editor'         => 'dashicons-edit',
		);

		?>
		<div class="aegis-hooks-reference aegis-snippet-locations aegis-settings-section active">
			<?php $this->renderer()->render_section_header( __( 'Available Locations', 'aegis' ), __( 'These hooks appear as custom run locations for Content and PHP snippets. Header, shortcode, CSS, and JavaScript locations are always available.', 'aegis' ), true, 'location' ); ?>
			<input type="hidden" name="enabled_locations[]" value="" />
			<?php foreach ( $locations as $group => $hooks ) : ?>
				<?php if ( empty( $hooks ) ) : continue; endif; ?>
				<div class="aegis-hooks-group is-collapsed" data-location-group="<?php echo esc_attr( $group ); ?>">
					<div class="aegis-hooks-group-header">
						<span class="dashicons <?php echo esc_attr( $icons[ $group ] ?? 'dashicons-admin-generic' ); ?>"></span>
						<span class="aegis-hooks-group-title"><?php echo esc_html( ucfirst( str_replace( '-', ' ', $group ) ) ); ?></span>
						<span class="aegis-hooks-group-count"><?php echo esc_html( (string) count( $hooks ) ); ?></span>
					</div>
					<?php foreach ( $hooks as $hook => $description ) : ?>
						<div class="aegis-hooks-row aegis-hooks-row--toggle">
							<div class="aegis-hooks-row-main">
								<code class="aegis-hooks-row-name"><?php echo esc_html( $hook ); ?></code>
								<span class="aegis-hooks-row-desc"><?php echo esc_html( $description ); ?></span>
							</div>
							<label class="aegis-toggle" title="<?php esc_attr_e( 'Enable this location for snippets', 'aegis' ); ?>">
								<input type="checkbox" name="enabled_locations[]" value="<?php echo esc_attr( $hook ); ?>" <?php checked( Settings::is_location_enabled( $hook ) ); ?> />
								<span class="aegis-toggle-slider"></span>
							</label>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * @param array<string, mixed>|null               $snippet   Snippet being edited.
	 * @param array<string, array<string, string>> $locations Grouped locations.
	 */
	private function render_editor( ?array $snippet, array $locations ): void {
		$is_new      = $snippet === null;
		$snippet_id  = $is_new ? '' : (string) ( $snippet['id'] ?? '' );
		$type        = Locations::normalize_type( (string) ( $snippet['type'] ?? Locations::TYPE_CONTENT ) );
		$content     = $is_new ? Locations::starter( $type ) : Storage::read_snippet_file( $snippet );
		$conditions  = ( is_array( $snippet ) && is_array( $snippet['conditions'] ?? null ) ) ? $snippet['conditions'] : array();
		$tags        = ( is_array( $snippet ) && is_array( $snippet['tags'] ?? null ) ) ? $snippet['tags'] : array();
		$location    = (string) ( $snippet['location'] ?? Locations::default_location( $type ) );
		$has_pro     = defined( 'AEGIS_PRO_VERSION' );
		$cl_settings = class_exists( '\Aegis\Plugin\Conditionals\Settings' )
			? \Aegis\Plugin\Conditionals\Settings::get_settings()
			: array();
		$groups      = Locations::for_editor( Locations::custom_hooks_for_editor( $locations ) );
		$type_badges = Locations::type_badges();
		?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="aegis-snippet-editor">
			<input type="hidden" name="action" value="aegis_save_snippet" />
			<input type="hidden" name="snippet_id" value="<?php echo esc_attr( $snippet_id ); ?>" />
			<input type="hidden" id="snippet_conditions" name="snippet_conditions" value="<?php echo esc_attr( wp_json_encode( $conditions ) ); ?>" />
			<input type="hidden" id="snippet_tags" name="snippet_tags" value="<?php echo esc_attr( implode( ',', $tags ) ); ?>" />
			<?php wp_nonce_field( 'aegis_save_snippet' ); ?>

			<div class="aegis-snippet-editor-topbar">
				<div class="aegis-snippet-editor-title">
					<label for="snippet_title" class="screen-reader-text"><?php esc_html_e( 'Title', 'aegis' ); ?></label>
					<input type="text" class="regular-text" id="snippet_title" name="snippet_title" value="<?php echo esc_attr( (string) ( $snippet['title'] ?? '' ) ); ?>" placeholder="<?php esc_attr_e( 'Snippet title', 'aegis' ); ?>" required />
				</div>
				<div class="aegis-snippet-editor-actions">
					<label class="aegis-snippet-enabled" for="snippet_enabled">
						<input type="checkbox" id="snippet_enabled" name="snippet_enabled" value="1" <?php checked( $is_new || ! empty( $snippet['enabled'] ) ); ?> />
						<?php esc_html_e( 'Enabled', 'aegis' ); ?>
					</label>
					<?php submit_button( $is_new ? __( 'Create Snippet', 'aegis' ) : __( 'Update Snippet', 'aegis' ), 'primary', 'submit', false ); ?>
					<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=aegis-snippets' ) ); ?>"><?php esc_html_e( 'Cancel', 'aegis' ); ?></a>
				</div>
			</div>

			<fieldset class="nav-tab-wrapper aegis-snippet-type-tabs">
				<legend class="screen-reader-text"><?php esc_html_e( 'Snippet type', 'aegis' ); ?></legend>
				<?php foreach ( Locations::types() as $type_key => $type_label ) : ?>
					<label class="nav-tab<?php echo $type === $type_key ? ' nav-tab-active' : ''; ?>">
						<input type="radio" name="snippet_type" value="<?php echo esc_attr( $type_key ); ?>" <?php checked( $type, $type_key ); ?> />
						<?php echo esc_html( $type_label ); ?>
					</label>
				<?php endforeach; ?>
			</fieldset>

			<div class="aegis-snippet-editor-layout">
				<div class="aegis-snippet-editor-main">
					<div class="aegis-snippet-code-panel">
						<div class="aegis-snippet-code-toolbar">
							<span class="aegis-snippet-code-label">
								<?php esc_html_e( 'Code', 'aegis' ); ?>
								<span class="aegis-snippet-type-badge" id="aegis-snippet-type-badge"><?php echo esc_html( $type_badges[ $type ] ?? $type ); ?></span>
							</span>
							<button type="button" class="button" id="aegis-snippet-format"><?php esc_html_e( 'Format', 'aegis' ); ?></button>
						</div>
						<div id="aegis-snippet-type-hint" class="notice notice-info inline aegis-snippet-type-hint" hidden></div>
						<label class="screen-reader-text" for="snippet_content"><?php esc_html_e( 'Code', 'aegis' ); ?></label>
						<textarea id="snippet_content" name="snippet_content" rows="20" class="large-text code" spellcheck="false"><?php echo esc_textarea( $content ); ?></textarea>
					</div>

					<div class="aegis-snippet-section aegis-snippet-run">
						<h2><?php esc_html_e( 'Where to run?', 'aegis' ); ?></h2>
						<label class="screen-reader-text" for="snippet_location"><?php esc_html_e( 'Select snippet run location', 'aegis' ); ?></label>
						<select id="snippet_location" name="snippet_location" class="widefat">
							<?php $this->render_location_options( $groups[ $type ] ?? array(), $location ); ?>
						</select>
						<div id="aegis-snippet-location-cards" class="aegis-snippet-location-cards">
							<?php $this->render_location_cards( $type, $location ); ?>
						</div>
						<div class="aegis-snippet-shortcode-row" <?php echo $location === 'shortcode' ? '' : 'hidden'; ?>>
							<p><?php esc_html_e( 'Use Shortcode to display the return or print content of this snippet:', 'aegis' ); ?></p>
							<div class="aegis-snippet-shortcode-copy">
								<code
									id="aegis-snippet-shortcode-markup"
									data-locked="<?php echo $is_new ? '0' : '1'; ?>"
									data-id="<?php echo esc_attr( $snippet_id ); ?>"
								><?php echo esc_html( Locations::shortcode_markup( $snippet_id !== '' ? $snippet_id : Locations::id_from_title( (string) ( $snippet['title'] ?? '' ) ) ) ); ?></code>
								<button type="button" class="aegis-snippet-shortcode-copy-button" aria-label="<?php esc_attr_e( 'Copy shortcode', 'aegis' ); ?>">
									<span class="dashicons dashicons-admin-page" aria-hidden="true"></span>
								</button>
							</div>
						</div>
					</div>

					<div class="aegis-snippet-section aegis-snippet-conditions-box">
						<h2><?php esc_html_e( 'Advanced Conditional Logic', 'aegis' ); ?></h2>
						<div class="aegis-snippet-section-body">
							<div id="aegis-smart-conditions-ui"></div>
						</div>
					</div>
				</div>

				<aside class="aegis-snippet-editor-sidebar">
					<h2><?php esc_html_e( 'Snippet', 'aegis' ); ?></h2>
					<div class="aegis-snippet-sidebar-fields">
						<p class="aegis-snippet-metabox-field">
							<label for="snippet_group"><?php esc_html_e( 'Group', 'aegis' ); ?></label>
							<input type="text" class="widefat" id="snippet_group" name="snippet_group" list="aegis-snippet-group-suggestions" value="<?php echo esc_attr( (string) ( $snippet['group'] ?? '' ) ); ?>" />
							<datalist id="aegis-snippet-group-suggestions">
								<?php
								$group_names = array();
								foreach ( Storage::get_snippets() as $row ) {
									$group_name = trim( (string) ( $row['group'] ?? '' ) );
									if ( $group_name !== '' && ! in_array( $group_name, $group_names, true ) ) {
										$group_names[] = $group_name;
									}
								}
								sort( $group_names );
								foreach ( $group_names as $group_name ) :
									?>
									<option value="<?php echo esc_attr( $group_name ); ?>"></option>
								<?php endforeach; ?>
							</datalist>
							<span class="description"><?php esc_html_e( 'Optional folder-style label for the snippets list.', 'aegis' ); ?></span>
						</p>
						<p class="aegis-snippet-metabox-field">
							<label for="snippet_priority"><?php esc_html_e( 'Priority', 'aegis' ); ?></label>
							<input type="number" class="small-text" id="snippet_priority" name="snippet_priority" value="<?php echo esc_attr( (string) ( $snippet['priority'] ?? 10 ) ); ?>" min="1" max="999" />
						</p>
						<div class="aegis-snippet-metabox-field">
							<label for="aegis-snippet-tags"><?php esc_html_e( 'Tags', 'aegis' ); ?></label>
							<div id="aegis-snippet-tags"></div>
						</div>
						<p class="aegis-snippet-metabox-field">
							<label for="snippet_note"><?php esc_html_e( 'Description', 'aegis' ); ?></label>
							<textarea id="snippet_note" name="snippet_note" rows="4" class="widefat"><?php echo esc_textarea( (string) ( $snippet['note'] ?? '' ) ); ?></textarea>
						</p>
					</div>
				</aside>
			</div>
		</form>
		<script>
			window.aegisCodeEditorConfig = {
				textareaId: 'snippet_content',
				typeInputName: 'snippet_type'
			};
			window.aegisSnippetConditionsConfig = {
				conditions: <?php echo wp_json_encode( $conditions ); ?>,
				tags: <?php echo wp_json_encode( $tags ); ?>,
				hasPro: <?php echo $has_pro ? 'true' : 'false'; ?>,
				settings: <?php echo wp_json_encode( $cl_settings ); ?>,
				postTypes: <?php echo wp_json_encode( array_values( get_post_types( array( 'public' => true ), 'names' ) ) ); ?>
			};
		</script>
		<?php
	}

	/**
	 * @param array<int, array{label: string, options: array<string, string>}> $groups Location groups.
	 */
	private function render_location_options( array $groups, string $selected ): void {
		foreach ( $groups as $group ) {
			$label   = (string) ( $group['label'] ?? '' );
			$options = is_array( $group['options'] ?? null ) ? $group['options'] : array();

			if ( $options === array() ) {
				continue;
			}

			if ( $label !== '' ) {
				echo '<optgroup label="' . esc_attr( $label ) . '">';
			}

			foreach ( $options as $value => $text ) {
				printf(
					'<option value="%s"%s>%s</option>',
					esc_attr( (string) $value ),
					selected( $selected, (string) $value, false ),
					esc_html( (string) $text )
				);
			}

			if ( $label !== '' ) {
				echo '</optgroup>';
			}
		}
	}

	private function render_location_cards( string $type, string $selected ): void {
		foreach ( Locations::cards_for_type( $type ) as $card ) {
			$classes = 'aegis-snippet-location-card';

			if ( ! empty( $card['wide'] ) ) {
				$classes .= ' is-wide';
			}

			if ( (string) $card['value'] === $selected ) {
				$classes .= ' is-selected';
			}

			printf(
				'<button type="button" class="%s" data-location="%s" aria-pressed="%s"><strong>%s</strong><span>%s</span></button>',
				esc_attr( $classes ),
				esc_attr( (string) $card['value'] ),
				(string) $card['value'] === $selected ? 'true' : 'false',
				esc_html( (string) $card['label'] ),
				esc_html( (string) $card['help'] )
			);
		}
	}
}
