<?php
/**
 * Registers WooCommerce and TI Wishlist block patterns when dependencies are active.
 *
 * @package Aegis\Plugin\Blocks
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Patterns;

use Aegis\Plugin\Integrations\WooCommerce as WooCommerceHelper;
use Aegis\Utilities\Pattern;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use function add_action;
use function class_exists;
use function defined;
use function is_readable;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers commerce integration patterns from the plugin patterns directory.
 */
final class CommercePatternRegistrar {

	/**
	 * Hooks `init` at priority 12 (after theme unregistration of `woocommerce/` slugs).
	 */
	public static function init(): void {
		add_action( 'init', array( self::class, 'register' ), 12 );
	}

	/**
	 * Whether WooCommerce is active.
	 */
	public static function is_woocommerce_active(): bool {
		return WooCommerceHelper::is_plugin_active();
	}

	/**
	 * Whether TI WooCommerce Wishlist is active.
	 */
	public static function is_ti_wishlist_active(): bool {
		return defined( 'TINVWL_VERSION' ) || function_exists( 'tinvwl_get_wishlist' );
	}

	/**
	 * Register gated commerce patterns.
	 */
	public static function register(): void {
		if ( ! class_exists( Pattern::class ) ) {
			return;
		}

		if ( self::is_woocommerce_active() ) {
			self::register_directory( \Aegis\Plugin\DIR . 'patterns/woocommerce/' );
		}

		if ( self::is_woocommerce_active() && self::is_ti_wishlist_active() ) {
			self::register_directory( \Aegis\Plugin\DIR . 'patterns/wishlist/' );
		}
	}

	/**
	 * Register all pattern PHP files under a directory tree.
	 *
	 * @param string $base_dir Absolute path ending with slash.
	 */
	private static function register_directory( string $base_dir ): void {
		if ( ! is_readable( $base_dir ) ) {
			return;
		}

		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $base_dir, \FilesystemIterator::SKIP_DOTS )
		);

		foreach ( $iterator as $file ) {
			if ( ! $file->isFile() || 'php' !== $file->getExtension() ) {
				continue;
			}

			$basename = $file->getBasename();
			if ( str_starts_with( $basename, '.' ) ) {
				continue;
			}

			$path = $file->getPathname();

			if ( is_readable( $path ) ) {
				Pattern::register_from_file( $path );
			}
		}
	}
}
