<?php
/**
 * WP Fusion helpers for editor pickers and list membership.
 *
 * @package Aegis\Plugin\Integrations
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Integrations;

use function array_key_exists;
use function class_exists;
use function defined;
use function function_exists;
use function is_array;
use function is_object;
use function is_scalar;
use function is_user_logged_in;
use function is_wp_error;
use function method_exists;
use function trim;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Catalog tags/lists for inspectors and Smart Logic.
 *
 * `wpf_get_tags()` returns the current user's tags, not the CRM catalog.
 * Lists are not tags: Mailchimp audiences and FluentCRM/ActiveCampaign lists
 * live in CRM list APIs, not `wpf_has_tag()`.
 */
final class WPFusion {

	/**
	 * Per-request list membership cache.
	 *
	 * @var array<string, bool>
	 */
	private static array $list_cache = array();

	/**
	 * Whether WP Fusion or WP Fusion Lite is loaded.
	 *
	 * Used by Registry, Pro query boot, and list/login helpers.
	 */
	public static function is_plugin_active(): bool {
		return defined( 'WP_FUSION_VERSION' ) || function_exists( 'wpf_has_tag' );
	}

	/**
	 * Whether the current visitor is logged in via WP Fusion (including auto-login).
	 */
	public static function user_is_logged_in(): bool {
		if ( ! self::is_plugin_active() ) {
			return false;
		}

		if ( function_exists( 'wpf_is_user_logged_in' ) ) {
			return (bool) \wpf_is_user_logged_in();
		}

		return is_user_logged_in();
	}

	/**
	 * Tag choices for the block inspector and Smart Logic.
	 *
	 * @return list<array{label: string, value: string}>
	 */
	public static function editor_tags(): array {
		if ( ! self::should_localize() ) {
			return array();
		}

		$raw = array();

		if ( function_exists( 'wp_fusion' ) ) {
			$wpf = \wp_fusion();

			if ( is_object( $wpf ) && isset( $wpf->settings ) && is_object( $wpf->settings ) && method_exists( $wpf->settings, 'get_available_tags_flat' ) ) {
				$flat = $wpf->settings->get_available_tags_flat( true, false );
				$raw  = is_array( $flat ) ? $flat : array();
			}
		}

		if ( $raw === array() && function_exists( 'wpf_get_option' ) ) {
			$stored = \wpf_get_option( 'available_tags' );
			$raw    = is_array( $stored ) ? $stored : array();
		}

		return self::choices_from_map( $raw );
	}

	/**
	 * List choices for the block inspector and Smart Logic.
	 *
	 * @return list<array{label: string, value: string}>
	 */
	public static function editor_lists(): array {
		if ( ! self::should_localize() || ! function_exists( 'wpf_get_option' ) ) {
			return array();
		}

		$stored = \wpf_get_option( 'available_lists' );

		if ( ! is_array( $stored ) || $stored === array() ) {
			$mailchimp = \wpf_get_option( 'mc_lists' );
			$stored    = is_array( $mailchimp ) ? $mailchimp : array();
		}

		return self::choices_from_map( is_array( $stored ) ? $stored : array() );
	}

	/**
	 * Whether the current WP Fusion contact is on a CRM list.
	 */
	public static function user_has_list( string $list_id ): bool {
		$list_id = trim( $list_id );

		if ( $list_id === '' || ! self::is_plugin_active() ) {
			return false;
		}

		$user_id = function_exists( 'wpf_get_current_user_id' ) ? (int) \wpf_get_current_user_id() : 0;
		$cache   = $user_id . ':' . $list_id;

		if ( array_key_exists( $cache, self::$list_cache ) ) {
			return self::$list_cache[ $cache ];
		}

		self::$list_cache[ $cache ] = self::resolve_list_membership( $list_id );

		return self::$list_cache[ $cache ];
	}

	private static function should_localize(): bool {
		return self::is_plugin_active()
			&& class_exists( Settings::class )
			&& Settings::is_integration_enabled( 'wp_fusion' );
	}

	private static function resolve_list_membership( string $list_id ): bool {
		$crm = ( function_exists( 'wp_fusion' ) && is_object( \wp_fusion() ) ) ? \wp_fusion()->crm : null;
		$slug = ( is_object( $crm ) && isset( $crm->slug ) ) ? (string) $crm->slug : '';

		if ( is_object( $crm ) && method_exists( $crm, 'get_contact_lists' ) && function_exists( 'wpf_get_contact_id' ) ) {
			$contact_id = \wpf_get_contact_id();

			if ( $contact_id ) {
				$lists = $crm->get_contact_lists( $contact_id );

				if ( ! is_wp_error( $lists ) && is_array( $lists ) ) {
					return self::id_in_list_values( $list_id, $lists );
				}
			}
		}

		if ( $slug === 'fluentcrm' && function_exists( 'FluentCrmApi' ) && function_exists( 'wpf_get_contact_id' ) ) {
			$contact_id = \wpf_get_contact_id();

			if ( ! $contact_id ) {
				return false;
			}

			$contact = \FluentCrmApi( 'contacts' )->getContact( $contact_id );

			if ( ! $contact || empty( $contact->lists ) ) {
				return false;
			}

			foreach ( $contact->lists as $list ) {
				if ( isset( $list->id ) && (string) $list->id === $list_id ) {
					return true;
				}
			}

			return false;
		}

		if ( $slug === 'mailchimp' && function_exists( 'wpf_get_option' ) && function_exists( 'wpf_get_contact_id' ) ) {
			$default = (string) \wpf_get_option( 'mc_default_list' );

			return $default !== '' && $default === $list_id && (bool) \wpf_get_contact_id();
		}

		if ( function_exists( 'wpf_has_tag' ) && \wpf_has_tag( $list_id ) ) {
			return true;
		}

		if ( function_exists( 'wpf_get_option' ) && function_exists( 'wpf_has_tag' ) ) {
			$lists = \wpf_get_option( 'available_lists' );
			$label = is_array( $lists ) && isset( $lists[ $list_id ] ) ? (string) $lists[ $list_id ] : '';

			if ( $label !== '' && $label !== $list_id && \wpf_has_tag( $label ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param array<int|string, mixed> $lists CRM list IDs or id => label map.
	 */
	private static function id_in_list_values( string $list_id, array $lists ): bool {
		foreach ( $lists as $id => $label ) {
			if ( (string) $id === $list_id ) {
				return true;
			}

			if ( is_scalar( $label ) && (string) $label === $list_id ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param array<int|string, mixed> $raw ID => label or nested label array.
	 * @return list<array{label: string, value: string}>
	 */
	private static function choices_from_map( array $raw ): array {
		$choices = array();

		foreach ( $raw as $id => $label ) {
			if ( is_array( $label ) ) {
				$label = $label['label'] ?? $id;
			}

			$choices[] = array(
				'label' => (string) $label,
				'value' => (string) $id,
			);
		}

		return $choices;
	}
}
