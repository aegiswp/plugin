<?php
/**
 * Smart conditional logic evaluator (group-based OR/AND rules).
 *
 * @package Aegis\Plugin\Conditionals
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Conditionals;

use function array_filter;
use function array_map;
use function explode;
use function function_exists;
use function get_post_type;
use function in_array;
use function is_user_logged_in;
use function current_user_can;
use function sanitize_text_field;
use function strtolower;
use function trim;
use function wp_get_current_user;
use function wp_unslash;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Evaluates smartLogic group conditions from snippets and posts.
 */
final class SmartConditionsEvaluator {

	private Evaluator $evaluator;

	public function __construct( ?Evaluator $evaluator = null ) {
		$this->evaluator = $evaluator ?? new Evaluator();
	}

	/**
	 * @param array<string, mixed> $conditions Raw conditions array.
	 */
	public function should_render( array $conditions ): bool {
		if ( empty( $conditions ) ) {
			return true;
		}

		$smart = $conditions['smartLogic'] ?? null;

		if ( ! is_array( $smart ) || empty( $smart['enabled'] ) ) {
			return true;
		}

		$action = (string) ( $smart['action'] ?? 'show' );
		$groups = $smart['groups'] ?? array();

		if ( ! is_array( $groups ) || $groups === array() ) {
			return true;
		}

		$any_group_matches  = false;
		$had_enabled_rules  = false;

		foreach ( $groups as $group ) {
			if ( ! is_array( $group ) ) {
				continue;
			}

			$rules    = $group['rules'] ?? array();
			$relation = (string) ( $group['relation'] ?? 'all' );

			if ( ! is_array( $rules ) || $rules === array() ) {
				continue;
			}

			$passes = 0;
			$total  = 0;

			foreach ( $rules as $rule ) {
				if ( ! is_array( $rule ) ) {
					continue;
				}

				$field = (string) ( $rule['field'] ?? '' );

				if ( $field === '' || ! $this->field_extra_enabled( $field ) ) {
					continue;
				}

				++$total;

				if ( $this->match_rule( $rule ) ) {
					++$passes;
				}
			}

			if ( $total === 0 ) {
				continue;
			}

			$had_enabled_rules = true;

			$group_met = ( $relation === 'any' ) ? ( $passes > 0 ) : ( $passes === $total );

			if ( $group_met ) {
				$any_group_matches = true;
				break;
			}
		}

		if ( ! $had_enabled_rules ) {
			return true;
		}

		if ( $action === 'hide' ) {
			return ! $any_group_matches;
		}

		return $any_group_matches;
	}

	/**
	 * @param array<string, mixed> $rule Rule definition.
	 */
	private function match_rule( array $rule ): bool {
		$field    = (string) ( $rule['field'] ?? '' );
		$operator = (string) ( $rule['operator'] ?? 'is' );
		$value    = (string) ( $rule['value'] ?? '' );

		$operator = match ( $operator ) {
			'equal', 'equals' => 'is',
			'includes'        => 'contains',
			default           => $operator,
		};

		switch ( $field ) {
			case 'user_status':
				$logged_in = is_user_logged_in();
				$want_in   = in_array( $value, array( 'logged-in', 'true', '1', 'yes' ), true );
				$want_out  = in_array( $value, array( 'logged-out', 'false', '0', 'no' ), true );
				if ( $want_in ) {
					return $this->compare_bool( $logged_in, $operator );
				}
				if ( $want_out ) {
					return $this->compare_bool( ! $logged_in, $operator );
				}
				return false;

			case 'user_role':
				$user = wp_get_current_user();
				$has  = in_array( $value, $user->roles, true );
				return $this->compare_bool( $has, $operator );

			case 'user_capability':
				$cap = Evaluator::normalize_capability( $value );
				$has = $cap !== '' && current_user_can( $cap );
				return $this->compare_bool( $has, $operator );

			case 'post_type':
				$current = (string) ( get_post_type() ?: '' );
				$types   = array_filter( array_map( 'trim', explode( ',', strtolower( $value ) ) ) );
				$has     = in_array( strtolower( $current ), $types, true );
				if ( $operator === 'contains' ) {
					return $has;
				}
				if ( $operator === 'notContains' ) {
					return ! $has;
				}
				return $this->compare_bool( $has, $operator );

			case 'page_url':
				$path = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
				return $this->compare_string( $path, $operator, $value );

			case 'query_string':
				$param  = $value;
				$actual = isset( $_GET[ $param ] ) ? sanitize_text_field( wp_unslash( $_GET[ $param ] ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification
				return $this->compare_string( $actual ?? '', $operator, (string) ( $rule['compareValue'] ?? '' ) );

			case 'wp_fusion_tag':
				if ( ! function_exists( 'wpf_has_tag' ) ) {
					return false;
				}
				$has = wpf_has_tag( $value );
				return $this->compare_bool( $has, $operator );

			case 'wp_fusion_list':
				if ( ! function_exists( 'wpf_has_tag' ) ) {
					return false;
				}
				$has = wpf_has_tag( $value );
				return $this->compare_bool( $has, $operator );

			default:
				return false;
		}
	}

	/**
	 * Whether the Conditionals extra for a smart-logic field is on.
	 *
	 * post_type is the core builder field and is always available.
	 */
	private function field_extra_enabled( string $field ): bool {
		$extra = match ( $field ) {
			'user_status'     => array( 'user', 'user_status' ),
			'user_role'       => array( 'user', 'user_role' ),
			'user_capability' => array( 'user', 'user_capability' ),
			'page_url'       => array( 'pro_conditions', 'advanced_location' ),
			'query_string'   => array( 'visibility', 'query_string' ),
			'wp_fusion_tag'  => array( 'wp_fusion', 'tags' ),
			'wp_fusion_list' => array( 'wp_fusion', 'lists' ),
			default          => array(),
		};

		if ( $extra === array() ) {
			return true;
		}

		if ( ! class_exists( Settings::class ) ) {
			return true;
		}

		return Settings::is_enabled( $extra[0], $extra[1] );
	}

	private function compare_bool( bool $actual, string $operator ): bool {
		return match ( $operator ) {
			'is'    => $actual,
			'isNot' => ! $actual,
			default => false,
		};
	}

	private function compare_string( string $actual, string $operator, string $expected ): bool {
		return match ( $operator ) {
			'is'        => $actual === $expected,
			'isNot'     => $actual !== $expected,
			'contains'  => str_contains( $actual, $expected ),
			'notContains' => ! str_contains( $actual, $expected ),
			'startsWith' => str_starts_with( $actual, $expected ),
			default     => false,
		};
	}
}
