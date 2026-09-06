<?php
/**
 * Visibility Presets admin dashboard page.
 *
 * @package Aegis\Plugin\VisibilityPresets
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\VisibilityPresets;

use function __;
use function add_action;
use function add_filter;
use function add_submenu_page;
use function admin_url;
use function class_exists;
use function defined;
use function dirname;
use function do_action;
use function file_exists;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and renders the Visibility Presets admin page.
 */
final class AdminPage {

	/**
	 * Register hooks.
	 */
	public function init(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_filter( 'aegis_admin_tabs', array( $this, 'register_admin_tab' ) );
	}

	/**
	 * @param array<string, array{label: string, url: string}> $tabs Registered tabs.
	 * @return array<string, array{label: string, url: string}>
	 */
	public function register_admin_tab( array $tabs ): array {
		$tabs['visibility-presets'] = array(
			'label' => __( 'Presets', 'aegis' ),
			'url'   => admin_url( 'admin.php?page=aegis-visibility-presets' ),
		);

		return $tabs;
	}

	public function register_menu(): void {
		add_submenu_page(
			'aegis-dashboard',
			__( 'Visibility Presets', 'aegis' ),
			__( 'Presets', 'aegis' ),
			'manage_options',
			'aegis-visibility-presets',
			array( $this, 'render' )
		);
	}

	public function render(): void {
		$pro_active = defined( 'AEGIS_PRO_VERSION' );
		$presets    = array();
		$create_url = admin_url( 'admin.php?page=aegis-license' );

		if ( $pro_active && class_exists( \AegisPro\Conditionals\Presets::class ) ) {
			$presets = \AegisPro\Conditionals\Presets::get_presets_for_editor();
		}

		$template = dirname( __DIR__, 2 ) . '/templates/visibility-presets-admin-page.php';

		?>
		<div class="wrap aegis-admin-page">
			<?php do_action( 'aegis_admin_before_visibility_presets_page' ); ?>
			<?php
			if ( file_exists( $template ) ) {
				include $template;
			}
			?>
		</div>
		<?php
	}
}
