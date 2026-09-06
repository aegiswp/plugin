<?php
/**
 * Snippets module bootstrap.
 *
 * @package Aegis\Plugin\Snippets
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Snippets;

use function add_action;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Boots snippet storage, loader, capabilities, and admin UI.
 */
final class Manager {

	/**
	 * Initialize the snippets module.
	 */
	public function init(): void {
		Settings::maybe_activate_from_request();

		( new Capabilities() )->init();
		( new Loader() )->init();

		add_action(
			'after_setup_theme',
			static function (): void {
				if ( ! class_exists( \Aegis\Plugin\Admin\Renderer::class ) ) {
					return;
				}

				( new AdminPage() )->init();
			},
			20
		);
	}
}
