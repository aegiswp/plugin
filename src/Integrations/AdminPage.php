<?php
/**
 * Integrations admin dashboard page.
 *
 * @package Aegis\Plugin\Integrations
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Integrations;

use Aegis\Plugin\Map\Settings as MapSettings;
use function __;
use function add_action;
use function add_filter;
use function add_submenu_page;
use function admin_url;
use function current_user_can;
use function do_action;
use function esc_html__;
use function file_exists;
use function register_setting;
use function wp_die;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and renders the Integrations admin page.
 */
final class AdminPage {

	/** @var \Aegis\Plugin\Admin\Renderer|null */
	private $renderer = null;

	private SettingsController $controller;

	public function __construct() {
		$this->controller = new SettingsController();
	}

	private function renderer(): \Aegis\Plugin\Admin\Renderer {
		if ( null === $this->renderer ) {
			$this->renderer = new \Aegis\Plugin\Admin\Renderer();
		}

		return $this->renderer;
	}

	public function init(): void {
		add_action( 'admin_menu', [ $this, 'register_menu' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'wp_ajax_aegis_save_integrations', [ $this->controller, 'ajax_save_integrations' ] );
		add_action( 'wp_ajax_aegis_save_bunnycdn', [ $this->controller, 'ajax_save_bunnycdn' ] );
		add_action( 'wp_ajax_aegis_test_bunnycdn', [ $this->controller, 'ajax_test_bunnycdn' ] );
		add_filter( 'aegis_admin_tabs', [ $this, 'register_admin_tab' ] );
	}

	/**
	 * @param array<string, array{label: string, url: string}> $tabs Registered tabs.
	 * @return array<string, array{label: string, url: string}>
	 */
	public function register_admin_tab( array $tabs ): array {
		$tabs['integrations'] = array(
			'label' => __( 'Integrations', 'aegis' ),
			'url'   => admin_url( 'admin.php?page=aegis-integrations' ),
		);

		return $tabs;
	}

	public function register_menu(): void {
		$hook = add_submenu_page(
			'aegis-dashboard',
			__( 'Integrations', 'aegis' ),
			__( 'Integrations', 'aegis' ),
			'manage_options',
			'aegis-integrations',
			[ $this, 'render' ]
		);

		if ( is_string( $hook ) && $hook !== '' ) {
			add_action(
				"load-{$hook}",
				static function (): void {
					global $title;
					$title = __( 'Integrations', 'aegis' );
				}
			);
		}
	}

	public function register_settings(): void {
		register_setting(
			'aegis_integrations_group',
			Settings::OPTION,
			[
				'type'              => 'array',
				'sanitize_callback' => [ Settings::class, 'sanitize' ],
				'default'           => Settings::INTEGRATION_DEFAULTS,
			]
		);

		register_setting(
			'aegis_integrations_group',
			Settings::PATTERN_CONTROL_OPTION,
			[
				'type'              => 'array',
				'sanitize_callback' => [ Settings::class, 'sanitize_pattern_control' ],
				'default'           => Settings::PATTERN_CONTROL_DEFAULTS,
			]
		);

		register_setting(
			'aegis_integrations_group',
			Settings::BUNNYCDN_OPTION,
			[
				'type'              => 'array',
				'sanitize_callback' => [ Settings::class, 'sanitize_bunnycdn' ],
				'default'           => Settings::BUNNYCDN_DEFAULTS,
			]
		);

		register_setting(
			'aegis_integrations_group',
			MapSettings::OPTION_KEY,
			[
				'type'              => 'array',
				'sanitize_callback' => [ MapSettings::class, 'sanitize' ],
				'default'           => MapSettings::DEFAULTS,
			]
		);

		Settings::migrate_legacy_bunnycdn_option();
	}

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'aegis' ) );
		}

		$options  = Settings::get_settings();
		$renderer = $this->renderer();
		$template = dirname( __DIR__, 2 ) . '/templates/integrations-admin-page.php';

		?>
		<div class="wrap aegis-admin-page">
			<?php do_action( 'aegis_admin_before_integrations_page' ); ?>
			<?php
			if ( file_exists( $template ) ) {
				include $template;
			}
			?>
		</div>
		<?php
	}
}
