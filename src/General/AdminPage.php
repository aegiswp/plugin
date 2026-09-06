<?php
/**
 * General settings admin dashboard page.
 *
 * @package Aegis\Plugin\General
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\General;

use Aegis\Plugin\Settings\Controller;
use function __;
use function add_action;
use function add_filter;
use function add_submenu_page;
use function admin_url;
use function do_action;
use function file_exists;
use function register_setting;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and renders the General Settings admin page.
 */
final class AdminPage {

	/** @var \Aegis\Plugin\Admin\Renderer|null */
	private $renderer = null;

	private string $hook_suffix = '';

	private Controller $controller;

	public function __construct( Controller $controller ) {
		$this->controller = $controller;
	}

	public function init(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_filter( 'aegis_admin_tabs', array( $this, 'register_admin_tab' ) );
	}

	/**
	 * @param array<string, array{label: string, url: string}> $tabs Registered tabs.
	 * @return array<string, array{label: string, url: string}>
	 */
	public function register_admin_tab( array $tabs ): array {
		$tabs['general-settings'] = array(
			'label' => __( 'Settings', 'aegis' ),
			'url'   => admin_url( 'admin.php?page=aegis-general-settings' ),
		);

		return $tabs;
	}

	public function register_menu(): void {
		$this->hook_suffix = (string) add_submenu_page(
			'aegis-dashboard',
			__( 'Settings', 'aegis' ),
			__( 'Settings', 'aegis' ),
			'manage_options',
			'aegis-general-settings',
			array( $this, 'render' )
		);

		if ( $this->hook_suffix !== '' ) {
			add_action(
				"load-{$this->hook_suffix}",
				static function (): void {
					global $title;
					$title = __( 'Settings', 'aegis' );
				}
			);
		}
	}

	public function register_settings(): void {
		register_setting(
			'aegis_general_settings_group',
			Settings::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this->controller, 'sanitize_general_settings' ),
				'default'           => Settings::DEFAULTS,
			)
		);
	}

	public function render(): void {
		$options  = Settings::get_settings();
		$renderer = $this->renderer();
		$template = dirname( __DIR__, 2 ) . '/templates/general-settings-admin-page.php';

		?>
		<div class="wrap aegis-admin-page">
			<?php do_action( 'aegis_admin_before_general_settings_page' ); ?>
			<?php
			if ( file_exists( $template ) ) {
				include $template;
			}
			?>
		</div>
		<?php
	}

	private function renderer(): \Aegis\Plugin\Admin\Renderer {
		if ( null === $this->renderer ) {
			$this->renderer = new \Aegis\Plugin\Admin\Renderer();
		}

		return $this->renderer;
	}
}
