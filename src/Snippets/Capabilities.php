<?php
/**
 * Snippets capability mapping.
 *
 * @package Aegis\Plugin\Snippets
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Snippets;

use function add_filter;
use function current_user_can;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers custom capabilities for snippet management.
 */
final class Capabilities {

	public const MANAGE = 'aegis_manage_snippets';

	/**
	 * Register capability filters.
	 */
	public function init(): void {
		add_filter( 'map_meta_cap', array( $this, 'map_meta_cap' ), 10, 4 );
	}

	/**
	 * Map snippet management to manage_options.
	 *
	 * @param array<int, string> $caps    Required caps.
	 * @param string             $cap     Capability.
	 * @param int                $user_id User ID.
	 * @param array<int, mixed>  $args    Extra args.
	 * @return array<int, string>
	 */
	public function map_meta_cap( array $caps, string $cap, int $user_id, array $args ): array {
		unset( $user_id, $args );

		if ( self::MANAGE === $cap ) {
			return array( 'manage_options' );
		}

		return $caps;
	}

	/**
	 * Whether the current user can manage snippets.
	 */
	public static function current_user_can_manage(): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Whether the current user can run PHP snippets.
	 */
	public static function current_user_can_run_php(): bool {
		return self::current_user_can_manage() && Settings::is_php_enabled();
	}
}
