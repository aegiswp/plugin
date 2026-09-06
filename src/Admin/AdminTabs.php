<?php
/**
 * Admin tab ordering for the Aegis dashboard menu.
 *
 * @package Aegis\Plugin\Admin
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Admin;

use function apply_filters;
use function strcmp;
use function uksort;

/**
 * Canonical sort order for Aegis admin navigation tabs and submenu items.
 */
final class AdminTabs {

	/**
	 * Default tab priorities (lower = higher in the menu).
	 *
	 * @var array<string, int>
	 */
	public const DEFAULT_PRIORITIES = array(
		'dashboard'          => 0,
		'blocks'             => 10,
		'modals'             => 20,
		'hook-patterns'      => 30,
		'snippets'           => 40,
		'visibility-presets' => 45,
		'conditional-logic'  => 50,
		'integrations'      => 60,
		'connectors'        => 65,
		'performance'       => 70,
		'general-settings'  => 90,
		'license'           => 100,
	);

	/**
	 * Sort registered tabs by priority.
	 *
	 * @param array<string, array{label: string, url: string, priority?: int}> $tabs Registered tabs.
	 * @return array<string, array{label: string, url: string, priority?: int}>
	 */
	public static function sort( array $tabs ): array {
		if ( $tabs === array() ) {
			return $tabs;
		}

		$priorities = apply_filters( 'aegis_admin_tab_priorities', self::DEFAULT_PRIORITIES );

		uksort(
			$tabs,
			static function ( string $a, string $b ) use ( $tabs, $priorities ): int {
				$priority_a = isset( $tabs[ $a ]['priority'] )
					? (int) $tabs[ $a ]['priority']
					: ( $priorities[ $a ] ?? 50 );
				$priority_b = isset( $tabs[ $b ]['priority'] )
					? (int) $tabs[ $b ]['priority']
					: ( $priorities[ $b ] ?? 50 );

				if ( $priority_a === $priority_b ) {
					return strcmp( $a, $b );
				}

				return $priority_a <=> $priority_b;
			}
		);

		return $tabs;
	}
}
