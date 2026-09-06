<?php
/**
 * Conditional logic capability mapping.
 *
 * @package Aegis\Plugin\Conditionals
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Conditionals;

use function add_filter;
use function current_user_can;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Maps the custom aegis_manage_conditionals capability to edit_posts.
 */
final class Capabilities {

	public const MANAGE = 'aegis_manage_conditionals';

	public function init(): void {
		add_filter( 'map_meta_cap', array( $this, 'map_meta_cap' ), 10, 4 );
	}

	/**
	 * @param array<int, string> $caps    Required caps.
	 * @param string             $cap     Capability.
	 * @param int                $user_id User ID.
	 * @param array<int, mixed>  $args    Extra args.
	 * @return array<int, string>
	 */
	public function map_meta_cap( array $caps, string $cap, int $user_id, array $args ): array {
		unset( $user_id, $args );

		if ( self::MANAGE !== $cap ) {
			return $caps;
		}

		return array( 'edit_posts' );
	}

	public static function current_user_can_manage(): bool {
		return current_user_can( self::MANAGE );
	}
}
