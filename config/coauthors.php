<?php
/**
 * Co-Authors Plus — Feature Configuration
 *
 * Initializes the fixed Co-Authors Plus integration and registers block
 * patterns. Gated by: CAP plugin active + integration enabled in Aegis
 * Dashboard.
 *
 * The framework CoAuthorsPlus class skips itself when this plugin class
 * exists (`condition()` returns false). The plugin owns block replacement,
 * guest-author URLs/avatars, CSS, and the optional Author Schema extra.
 *
 * @package Aegis\Plugin\Config
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Config;

use Aegis\Plugin\CoAuthors\CoAuthorsPlus;
use Aegis\Plugin\Patterns\FileRegistrar;
use Aegis\Plugin\Settings\Repository;
use function add_action;
use function function_exists;
use function is_dir;
use function register_block_pattern_category;
use function __;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'init',
	static function (): void {
		if ( ! function_exists( 'get_coauthors' ) ) {
			return;
		}

		Repository::boot_if_enabled(
			'co_authors_plus',
			static function (): void {
				$cap = new CoAuthorsPlus();
				$cap->init();

				$pattern_dir = \Aegis\Plugin\DIR . 'patterns/author/';

				if ( ! is_dir( $pattern_dir ) ) {
					return;
				}

				if ( ! \WP_Block_Pattern_Categories_Registry::get_instance()->is_registered( 'author' ) ) {
					register_block_pattern_category(
						'author',
						array(
							'label' => __( 'Author', 'aegis' ),
						)
					);
				}

				$patterns = array(
					'aegis/co-authors-box'    => $pattern_dir . 'co-authors-box.php',
					'aegis/co-authors-inline' => $pattern_dir . 'co-authors-inline.php',
				);

				foreach ( $patterns as $name => $file ) {
					FileRegistrar::register( $name, $file );
				}
			}
		);
	}
);
