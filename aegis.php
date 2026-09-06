<?php
/**
 * Plugin Name:  Aegis
 * Plugin URI:   https://www.atmostfear-entertaiment.com/aegis/plugin/
 * Description:  Companion plugin for the Aegis theme — Map and Modal blocks, settings dashboard, conditionals, hooks, analytics, and integrations. Requires the Aegis theme.
 * Author:       Atmostfear Entertainment
 * Author URI:   https://www.atmostfear-entertainment.com/
 * Version:      1.0.0
 * License:      GPL-2.0-or-later
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Requires WP:  6.9
 * Requires PHP: 7.4
 * Text Domain:  aegis
 * Domain Path:  /languages
 */

declare( strict_types=1 );

namespace Aegis\Plugin;

use function add_action;
use function dirname;
use function esc_html;
use function esc_html__;
use function get_template;
use function load_plugin_textdomain;
use function plugin_basename;
use function printf;
use function version_compare;
use function wp_get_theme;
use const DIRECTORY_SEPARATOR;

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

const DIR               = __DIR__ . DIRECTORY_SEPARATOR;
const FILE              = __FILE__;
const VERSION           = '1.0.0';
const MIN_THEME_VERSION = '1.0.0';

add_action(
	'init',
	static function (): void {
		load_plugin_textdomain( 'aegis', false, dirname( plugin_basename( FILE ) ) . '/languages' );
	}
);

( static function (): void {
	$theme         = wp_get_theme( get_template() );
	$theme_version = $theme->get( 'Version' );
	$min_version   = MIN_THEME_VERSION;

	if ( $theme->get_template() !== 'aegis' ) {
		add_action(
			'admin_notices',
			static function (): void {
				printf(
					'<div class="notice notice-warning is-dismissible"><p>%s</p></div>',
					esc_html__( 'The Aegis plugin requires the Aegis theme. Install and activate the Aegis theme to use this plugin.', 'aegis' )
				);
			}
		);

		return;
	}

	if ( version_compare( $theme_version, $min_version, '<' ) ) {
		add_action(
			'admin_notices',
			static function () use ( $min_version ): void {
				printf(
					'<div class="notice notice-warning is-dismissible"><p>%s <strong>%s</strong> %s</p></div>',
					esc_html__( 'Aegis plugin recommends Aegis theme version', 'aegis' ),
					esc_html( $min_version ),
					esc_html__( 'or higher for the best experience.', 'aegis' )
				);
			}
		);
	}

	// PSR-4 autoloader for Aegis\Plugin namespace.
	require_once DIR . 'src/autoload.php';

	// Feature configs.
	require_once DIR . 'config/coauthors.php';
	require_once DIR . 'config/map.php';
	require_once DIR . 'config/analytics.php';
	require_once DIR . 'config/blocks.php';
	require_once DIR . 'config/blocks-registrar.php';
	require_once DIR . 'config/modal.php';
	require_once DIR . 'config/injection.php';
	require_once DIR . 'config/hooks.php';
	require_once DIR . 'config/snippets.php';
	require_once DIR . 'config/visibility-presets.php';
	require_once DIR . 'config/conditionals.php';
	require_once DIR . 'config/conditionals-renderer.php';
	require_once DIR . 'config/integrations.php';
	require_once DIR . 'config/connectors.php';
	require_once DIR . 'config/seo.php';
	require_once DIR . 'config/admin.php';
	require_once DIR . 'config/admin-shell.php';
	require_once DIR . 'config/editor.php';
	require_once DIR . 'config/performance.php';
	require_once DIR . 'config/woocommerce.php';
	require_once DIR . 'config/patterns.php';
	require_once DIR . 'config/woocommerce-patterns.php';
	require_once DIR . 'config/activation-redirect.php';
} )();
