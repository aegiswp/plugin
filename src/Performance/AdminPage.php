<?php
/**
 * Performance admin page.
 *
 * @package Aegis\Plugin\Performance
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Performance;

use Aegis\Plugin\Blocks\Settings as BlocksSettings;
use function __;
use function add_action;
use function add_filter;
use function add_submenu_page;
use function admin_url;
use function dirname;
use function do_action;
use function file_exists;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers Aegis → Performance.
 */
final class AdminPage {

	/** @var \Aegis\Plugin\Admin\Renderer|null */
	private $renderer = null;

	private string $hook_suffix = '';

	public function init(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_filter( 'aegis_admin_tabs', array( $this, 'register_admin_tab' ) );
	}

	/**
	 * @param array<string, array{label: string, url: string}> $tabs Registered tabs.
	 * @return array<string, array{label: string, url: string}>
	 */
	public function register_admin_tab( array $tabs ): array {
		$tabs['performance'] = array(
			'label' => __( 'Performance', 'aegis' ),
			'url'   => admin_url( 'admin.php?page=aegis-performance' ),
		);

		return $tabs;
	}

	public function register_menu(): void {
		$this->hook_suffix = (string) add_submenu_page(
			'aegis-dashboard',
			__( 'Performance', 'aegis' ),
			__( 'Performance', 'aegis' ),
			'manage_options',
			'aegis-performance',
			array( $this, 'render' )
		);

		if ( $this->hook_suffix !== '' ) {
			add_action(
				"load-{$this->hook_suffix}",
				static function (): void {
					global $title;
					$title = __( 'Performance', 'aegis' );
				}
			);
		}
	}

	public function render(): void {
		$options  = BlocksSettings::get_settings();
		$renderer = $this->renderer();
		$template = dirname( __DIR__, 2 ) . '/templates/performance-admin-page.php';

		?>
		<div class="wrap aegis-admin-page">
			<?php do_action( 'aegis_admin_before_performance_page' ); ?>
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
