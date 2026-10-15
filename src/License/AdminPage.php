<?php
/**
 * Free-plugin Get Pro / License admin page.
 *
 * Registered only when Aegis Pro is not active. Pro replaces this with its
 * license activation screen on the same slug (`aegis-license`).
 *
 * @package Aegis\Plugin\License
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\License;

use function __;
use function add_action;
use function add_filter;
use function add_submenu_page;
use function admin_url;
use function defined;
use function dirname;
use function do_action;
use function file_exists;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Upsell screen for Pro features (Presets, Hook Patterns, license CTA).
 */
final class AdminPage {

	private string $hook_suffix = '';

	public function init(): void {
		if ( defined( 'AEGIS_PRO_VERSION' ) ) {
			return;
		}

		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_filter( 'aegis_admin_tabs', array( $this, 'register_admin_tab' ) );
	}

	/**
	 * @param array<string, array{label: string, url: string}> $tabs Registered tabs.
	 * @return array<string, array{label: string, url: string}>
	 */
	public function register_admin_tab( array $tabs ): array {
		$tabs['license'] = array(
			'label' => __( 'License', 'aegis' ),
			'url'   => admin_url( 'admin.php?page=aegis-license' ),
		);

		return $tabs;
	}

	public function register_menu(): void {
		$this->hook_suffix = (string) add_submenu_page(
			'aegis-dashboard',
			__( 'License', 'aegis' ),
			__( 'License', 'aegis' ),
			'manage_options',
			'aegis-license',
			array( $this, 'render' )
		);

		if ( $this->hook_suffix !== '' ) {
			add_action(
				"load-{$this->hook_suffix}",
				static function (): void {
					global $title;
					$title = __( 'License', 'aegis' );
				}
			);
		}
	}

	public function render(): void {
		$template = dirname( __DIR__, 2 ) . '/templates/license-admin-page.php';

		?>
		<div class="wrap aegis-admin-page">
			<?php do_action( 'aegis_admin_before_license_page' ); ?>
			<?php
			if ( file_exists( $template ) ) {
				include $template;
			}
			?>
		</div>
		<?php
	}
}
