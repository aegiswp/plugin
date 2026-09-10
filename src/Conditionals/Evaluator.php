<?php
/**
 * Conditions Evaluator
 *
 * Evaluates conditional logic stored in `_aegis_conditions` post meta.
 * Used by:
 * - The theme's post-conditions filter (hide/show post content)
 * - The aegis-pro plugin's HookPatternsRenderer (render hook patterns)
 *
 * @package    Aegis
 * @since      1.0.0
 * @author     Atmostfear Entertainment
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Conditionals;

use Aegis\Plugin\Integrations\ACF;
use Aegis\Plugin\Integrations\MetaBox;
use Aegis\Plugin\Utilities\UserAgent;

use function get_post_meta;
use function is_user_logged_in;
use function wp_get_current_user;
use function current_user_can;
use function current_datetime;
use function preg_match;
use function preg_replace;
use function sprintf;
use function str_replace;
use function wp_timezone;
use function is_front_page;
use function is_home;
use function is_singular;
use function is_archive;
use function is_search;
use function is_404;
use function json_decode;
use function get_post_type;
use function get_the_ID;
use function get_queried_object_id;
use function has_term;
use function is_post_type_archive;
use function is_category;
use function is_tag;
use function is_date;
use function is_author;
use function is_tax;
use function get_current_user_id;
use function get_user_meta;
use function str_contains;
use function str_starts_with;
use function function_exists;
use function wp_parse_url;
use function sanitize_text_field;
use function wp_unslash;
use function is_array;
use function is_bool;
use function is_scalar;
use function is_string;
use function implode;
use function sanitize_key;
use function strtolower;
use function trim;

/**
 * Conditions Evaluator Class
 *
 * Evaluates document-level conditional logic for any post type.
 */
class Evaluator {

	/**
	 * Check document-level conditional logic for a pattern.
	 *
	 * Reads `_aegis_conditions` (JSON string or array) and evaluates every
	 * active condition, including Smart Logic when it is enabled.
	 *
	 * @since 1.0.0
	 *
	 * @param int $pattern_id Pattern post ID.
	 *
	 * @return bool True if the pattern should render.
	 */
	public function should_render_pattern( int $pattern_id ): bool {
		$raw = get_post_meta( $pattern_id, '_aegis_conditions', true );
		if ( empty( $raw ) ) {
			return true;
		}

		if ( is_array( $raw ) ) {
			$c = $raw;
		} elseif ( is_string( $raw ) ) {
			$c = json_decode( $raw, true );
		} else {
			return true;
		}

		if ( ! is_array( $c ) || $c === array() ) {
			return true;
		}

		return $this->should_render_conditions( $c );
	}

	/**
	 * Evaluate conditional logic from a manifest-style array.
	 *
	 * @param array<string, mixed> $conditions Conditions data.
	 */
	public function should_render_conditions( array $conditions ): bool {
		if ( empty( $conditions ) ) {
			return true;
		}

		if ( ! empty( $conditions['smartLogic']['enabled'] ) ) {
			$smart = ( new SmartConditionsEvaluator( $this ) )->should_render( $conditions );
			if ( ! $smart ) {
				return false;
			}
		}

		return $this->evaluate_conditions( $conditions );
	}

	/**
	 * Evaluate block-level visibility attribute.
	 *
	 * @param array<string, mixed> $visibility Visibility attribute.
	 */
	public function should_render_visibility( array $visibility ): bool {
		if ( empty( $visibility ) ) {
			return true;
		}

		return $this->should_render_conditions( $visibility );
	}

	/**
	 * @param array<string, mixed> $c Conditions data.
	 */
	private function evaluate_conditions( array $c ): bool {
		if ( ! empty( $c['lockdown'] ) && $this->extra_enabled( 'visibility', 'lockdown' ) ) {
			return false;
		}

		// User status.
		if ( ! empty( $c['userStatus'] ) && $this->extra_enabled( 'user', 'user_status' ) ) {
			$status    = $c['userStatus'];
			$logged_in = is_user_logged_in();
			if ( $status === 'logged-in' && ! $logged_in ) {
				return false;
			}
			if ( $status === 'logged-out' && $logged_in ) {
				return false;
			}
		}

		// User role rules.
		if ( ! empty( $c['userRoleRules'] ) && is_array( $c['userRoleRules'] ) && $this->extra_enabled( 'user', 'user_role' ) ) {
			$hide = $this->evaluate_rules(
				$c['userRoleRules'],
				$c['userRoleLogic'] ?? 'show',
				$c['userRoleRelation'] ?? 'all',
				function ( array $rule ): bool {
					$user = wp_get_current_user();
					$has  = in_array( $rule['role'] ?? '', $user->roles, true );
					return ( $rule['operator'] ?? 'is' ) === 'is' ? $has : ! $has;
				}
			);
			if ( $hide ) {
				return false;
			}
		}

		// User capability rules.
		if ( ! empty( $c['userCapabilityRules'] ) && is_array( $c['userCapabilityRules'] ) && $this->extra_enabled( 'user', 'user_capability' ) ) {
			$hide = $this->evaluate_rules(
				$c['userCapabilityRules'],
				$c['userCapabilityLogic'] ?? 'show',
				$c['userCapabilityRelation'] ?? 'all',
				function ( array $rule ): bool {
					$cap = self::normalize_capability( (string) ( $rule['capability'] ?? '' ) );
					$has = $cap !== '' && current_user_can( $cap );
					return ( $rule['operator'] ?? 'is' ) === 'is' ? $has : ! $has;
				}
			);
			if ( $hide ) {
				return false;
			}
		}

		// Schedule.
		if ( ! $this->evaluate_schedule( $c ) ) {
			return false;
		}

		if ( ! empty( $c['location'] ) && $this->extra_enabled( 'visibility', 'page_type' ) ) {
			$match = $this->check_location( $c['location'] );
			$logic = $c['locationLogic'] ?? 'show';
			if ( $logic === 'show' && ! $match ) {
				return false;
			}
			if ( $logic === 'hide' && $match ) {
				return false;
			}
		}

		// Specific users.
		if ( ! empty( $c['specificUserIds'] ) && $this->extra_enabled( 'visibility', 'specific_users' ) ) {
			$ids   = array_map( 'intval', array_filter( explode( ',', $c['specificUserIds'] ) ) );
			$match = in_array( get_current_user_id(), $ids, true );
			$logic = $c['specificUsersLogic'] ?? 'show';
			if ( $logic === 'show' && ! $match ) {
				return false;
			}
			if ( $logic === 'hide' && $match ) {
				return false;
			}
		}

		// URL query string rules.
		if ( ! empty( $c['queryStringRules'] ) && is_array( $c['queryStringRules'] ) && $this->extra_enabled( 'visibility', 'query_string' ) ) {
			$hide = $this->evaluate_rules(
				$c['queryStringRules'],
				$c['queryStringLogic'] ?? 'show',
				$c['queryStringRelation'] ?? 'all',
				function ( array $rule ): bool {
					$param = $rule['param'] ?? '';
					if ( $param === '' ) {
						return false;
					}
					$actual = isset( $_GET[ $param ] ) ? sanitize_text_field( wp_unslash( $_GET[ $param ] ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification
					return $this->compare( $actual, $rule['operator'] ?? 'is', $rule['value'] ?? '' );
				}
			);
			if ( $hide ) {
				return false;
			}
		}

		// Browser & device rules (server-side UA sniffing — best effort).
		if ( ! empty( $c['deviceRules'] ) && is_array( $c['deviceRules'] ) && $this->extra_enabled( 'visibility', 'browser_device' ) ) {
			$hide = $this->evaluate_rules(
				$c['deviceRules'],
				$c['deviceLogic'] ?? 'show',
				$c['deviceRelation'] ?? 'all',
				function ( array $rule ): bool {
					$ua    = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
					$key   = $rule['device'] ?? '';
					$match = UserAgent::matches_browser( $ua, $key );
					return ( $rule['operator'] ?? 'is' ) === 'is' ? $match : ! $match;
				}
			);
			if ( $hide ) {
				return false;
			}
		}

		// Cookie rules (pro).
		if ( ! empty( $c['cookieRules'] ) && is_array( $c['cookieRules'] ) && $this->extra_enabled( 'pro_conditions', 'cookie' ) ) {
			$hide = $this->evaluate_rules(
				$c['cookieRules'],
				$c['cookieLogic'] ?? 'show',
				$c['cookieRelation'] ?? 'all',
				function ( array $rule ): bool {
					$name = $rule['name'] ?? '';
					if ( $name === '' ) {
						return false;
					}
						$actual = isset( $_COOKIE[ $name ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ $name ] ) ) : null; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
					return $this->compare( $actual, $rule['operator'] ?? 'is', $rule['value'] ?? '' );
				}
			);
			if ( $hide ) {
				return false;
			}
		}

		// Referral source rules (pro).
		if ( ! empty( $c['referralRules'] ) && is_array( $c['referralRules'] ) && $this->extra_enabled( 'pro_conditions', 'referral' ) ) {
			$hide = $this->evaluate_rules(
				$c['referralRules'],
				$c['referralLogic'] ?? 'show',
				$c['referralRelation'] ?? 'all',
				function ( array $rule ): bool {
					$referer = isset( $_SERVER['HTTP_REFERER'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
					$domain  = $rule['domain'] ?? '';
					if ( $domain === '' ) {
						return false;
					}
					$op = $rule['operator'] ?? 'is';
					if ( $op === 'contains' ) {
						return str_contains( $referer, $domain );
					}
					$ref_host = wp_parse_url( $referer, PHP_URL_HOST );
					$ref_host = is_string( $ref_host ) ? $ref_host : '';
					return $op === 'is' ? $ref_host === $domain : $ref_host !== $domain;
				}
			);
			if ( $hide ) {
				return false;
			}
		}

		// ACF field rules (pro).
		if ( ! empty( $c['acfRules'] ) && is_array( $c['acfRules'] ) && ACF::is_enabled() && function_exists( 'get_field' ) && $this->extra_enabled( 'pro_conditions', 'acf_field' ) ) {
			$hide = $this->evaluate_rules(
				$c['acfRules'],
				$c['acfLogic'] ?? 'show',
				$c['acfRelation'] ?? 'all',
				function ( array $rule ): bool {
					$field = $rule['field'] ?? '';
					if ( $field === '' ) {
						return false;
					}
					$post_id = $this->condition_post_id();
					$actual  = $this->normalize_field_value( get_field( $field, $post_id ) );
					return $this->compare( $actual, $rule['operator'] ?? 'is', $rule['value'] ?? '' );
				}
			);
			if ( $hide ) {
				return false;
			}
		}

		// Meta Box field rules (pro).
		if ( ! empty( $c['metaboxRules'] ) && is_array( $c['metaboxRules'] ) && MetaBox::is_enabled() && function_exists( 'rwmb_meta' ) && $this->extra_enabled( 'pro_conditions', 'metabox_field' ) ) {
			$hide = $this->evaluate_rules(
				$c['metaboxRules'],
				$c['metaboxLogic'] ?? 'show',
				$c['metaboxRelation'] ?? 'all',
				function ( array $rule ): bool {
					$field = $rule['field'] ?? '';
					if ( $field === '' ) {
						return false;
					}
					$post_id = $this->condition_post_id();
					$actual  = $this->normalize_field_value( rwmb_meta( $field, array(), $post_id ) );
					return $this->compare( $actual, $rule['operator'] ?? 'is', $rule['value'] ?? '' );
				}
			);
			if ( $hide ) {
				return false;
			}
		}

		// Post meta rules (pro).
		if ( ! empty( $c['postMetaRules'] ) && is_array( $c['postMetaRules'] ) && $this->extra_enabled( 'pro_conditions', 'post_meta' ) ) {
			$hide = $this->evaluate_rules(
				$c['postMetaRules'],
				$c['postMetaLogic'] ?? 'show',
				$c['postMetaRelation'] ?? 'all',
				function ( array $rule ): bool {
					$key = $rule['key'] ?? '';
					if ( $key === '' ) {
						return false;
					}
					$post_id = get_the_ID();
					if ( false === $post_id || 0 === $post_id ) {
						$post_id = get_queried_object_id();
					}
					if ( ! $post_id ) {
						return false;
					}
					$actual = (string) get_post_meta( $post_id, $key, true );
					return $this->compare( $actual, $rule['operator'] ?? 'is', $rule['value'] ?? '' );
				}
			);
			if ( $hide ) {
				return false;
			}
		}

		// User meta rules (pro). Logged-out visitors have no meta: treat as empty.
		if ( ! empty( $c['userMetaRules'] ) && is_array( $c['userMetaRules'] ) && $this->extra_enabled( 'pro_conditions', 'user_meta' ) ) {
			$uid  = get_current_user_id();
			$hide = $this->evaluate_rules(
				$c['userMetaRules'],
				$c['userMetaLogic'] ?? 'show',
				$c['userMetaRelation'] ?? 'all',
				function ( array $rule ) use ( $uid ): bool {
					$key = $rule['key'] ?? '';
					if ( $key === '' ) {
						return false;
					}
					$actual = $uid ? (string) get_user_meta( $uid, $key, true ) : null;
					return $this->compare( $actual, $rule['operator'] ?? 'is', $rule['value'] ?? '' );
				}
			);
			if ( $hide ) {
				return false;
			}
		}

		// Advanced location rules (pro).
		if ( ! empty( $c['advancedLocationRules'] ) && is_array( $c['advancedLocationRules'] ) && $this->extra_enabled( 'pro_conditions', 'advanced_location' ) ) {
			$hide = $this->evaluate_rules(
				$c['advancedLocationRules'],
				$c['advancedLocationLogic'] ?? 'show',
				$c['advancedLocationRelation'] ?? 'all',
				array( $this, 'check_advanced_location_rule' )
			);
			if ( $hide ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Post ID for field conditions: loop post first, then the queried object.
	 */
	private function condition_post_id(): int {
		$post_id = get_the_ID();

		if ( $post_id ) {
			return (int) $post_id;
		}

		return (int) get_queried_object_id();
	}

	/**
	 * @param mixed $actual ACF get_field() or Meta Box rwmb_meta() return value.
	 */
	private function normalize_field_value( $actual ): string {
		if ( is_bool( $actual ) ) {
			return $actual ? '1' : '0';
		}

		if ( is_array( $actual ) ) {
			$flat = array();

			foreach ( $actual as $item ) {
				if ( is_scalar( $item ) ) {
					$flat[] = (string) $item;
				}
			}

			return $flat === array() ? '' : implode( ', ', $flat );
		}

		if ( $actual === null ) {
			return '';
		}

		return (string) $actual;
	}

	/**
	 * Whether an admin extra is on.
	 *
	 * When Settings is unavailable (theme without plugin), extras are treated as on
	 * so saved rules still evaluate.
	 */
	private function extra_enabled( string $group, string $key ): bool {
		if ( ! class_exists( Settings::class ) ) {
			return true;
		}

		return Settings::is_enabled( $group, $key );
	}

	/**
	 * Evaluate schedule conditions (datetime range, weekdays, daily time window).
	 *
	 * @param array<string, mixed> $c Conditions data.
	 */
	private function evaluate_schedule( array $c ): bool {
		$has_range = ( ! empty( $c['scheduleStart'] ) || ! empty( $c['scheduleEnd'] ) ) && $this->extra_enabled( 'schedule', 'date_time' );
		$has_days  = ! empty( $c['scheduleDays'] ) && is_array( $c['scheduleDays'] ) && $this->extra_enabled( 'schedule', 'days_of_week' );
		$has_time  = ( ! empty( $c['scheduleTimeStart'] ) || ! empty( $c['scheduleTimeEnd'] ) ) && $this->extra_enabled( 'schedule', 'time_range' );

		if ( ! $has_range && ! $has_days && ! $has_time ) {
			return true;
		}

		$timezone = $this->resolve_schedule_timezone( $c );
		$now      = current_datetime()->setTimezone( $timezone );

		if ( $has_range ) {
			$timestamp = $now->getTimestamp();
			if ( ! empty( $c['scheduleStart'] ) ) {
				$start_ts = $this->parse_schedule_datetime( (string) $c['scheduleStart'], $timezone );
				if ( $start_ts && $timestamp < $start_ts ) {
					return false;
				}
			}
			if ( ! empty( $c['scheduleEnd'] ) ) {
				$end_ts = $this->parse_schedule_datetime( (string) $c['scheduleEnd'], $timezone );
				if ( $end_ts && $timestamp > $end_ts ) {
					return false;
				}
			}
		}

		if ( $has_days ) {
			$day     = (int) $now->format( 'w' );
			$allowed = array_map( 'intval', $c['scheduleDays'] );
			if ( ! in_array( $day, $allowed, true ) ) {
				return false;
			}
		}

		if ( $has_time ) {
			$current = $now->format( 'H:i' );
			$start   = $this->normalize_schedule_time( (string) ( $c['scheduleTimeStart'] ?? '' ), '00:00' );
			$end     = $this->normalize_schedule_time( (string) ( $c['scheduleTimeEnd'] ?? '' ), '23:59' );

			if ( $start <= $end ) {
				if ( $current < $start || $current > $end ) {
					return false;
				}
			} elseif ( $current < $start && $current > $end ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Site timezone, or the custom schedule timezone when that extra is on.
	 */
	private function resolve_schedule_timezone( array $c ): \DateTimeZone {
		$timezone = wp_timezone();

		if ( empty( $c['scheduleTimezone'] ) || ! is_string( $c['scheduleTimezone'] ) || ! $this->extra_enabled( 'schedule', 'timezone' ) ) {
			return $timezone;
		}

		if ( ! Settings::is_valid_timezone( $c['scheduleTimezone'] ) ) {
			return $timezone;
		}

		try {
			return new \DateTimeZone( $c['scheduleTimezone'] );
		} catch ( \Exception $e ) {
			unset( $e );
			return $timezone;
		}
	}

	/**
	 * Parse a datetime-local value in the schedule timezone.
	 */
	private function parse_schedule_datetime( string $value, \DateTimeZone $timezone ): ?int {
		$value = trim( str_replace( 'T', ' ', $value ) );
		if ( $value === '' ) {
			return null;
		}

		try {
			return ( new \DateTimeImmutable( $value, $timezone ) )->getTimestamp();
		} catch ( \Exception $e ) {
			unset( $e );
			return null;
		}
	}

	/**
	 * Normalize an HTML time value to H:i.
	 */
	private function normalize_schedule_time( string $value, string $fallback ): string {
		$value = trim( $value );
		if ( $value === '' ) {
			return $fallback;
		}

		if ( preg_match( '/^(\d{1,2}):(\d{2})/', $value, $m ) ) {
			$hour = (int) $m[1];
			$min  = (int) $m[2];
			if ( $hour > 23 || $min > 59 ) {
				return $fallback;
			}

			return sprintf( '%02d:%02d', $hour, $min );
		}

		return $fallback;
	}

	// -------------------------------------------------------------------------
	// Condition helpers
	// -------------------------------------------------------------------------

	/**
	 * Normalize a typed capability to a WordPress capability slug.
	 *
	 * Spaces become underscores, then sanitize_key(). Empty input stays empty
	 * so an empty rule matches the same way an empty User Role does: `is`
	 * fails, `is not` matches everyone.
	 */
	public static function normalize_capability( string $raw ): string {
		$cap = strtolower( trim( $raw ) );
		if ( $cap === '' ) {
			return '';
		}

		$replaced = preg_replace( '/\s+/', '_', $cap );

		return sanitize_key( is_string( $replaced ) ? $replaced : $cap );
	}

	/**
	 * Evaluate a set of rules with show/hide logic and all/any relation.
	 *
	 * @since 1.0.0
	 *
	 * @param array    $rules    Array of rule arrays.
	 * @param string   $logic    'show' or 'hide'.
	 * @param string   $relation 'all' or 'any'.
	 * @param callable $matcher  Receives a single rule, returns bool.
	 *
	 * @return bool True if the block should be hidden.
	 */
	private function evaluate_rules( array $rules, string $logic, string $relation, callable $matcher ): bool {
		$passes = 0;
		$total  = 0;

		foreach ( $rules as $rule ) {
			if ( ! is_array( $rule ) ) {
				continue;
			}
			++$total;
			if ( $matcher( $rule ) ) {
				++$passes;
			}
		}

		if ( $total === 0 ) {
			return false;
		}

		$rules_met = ( $relation === 'any' ) ? ( $passes > 0 ) : ( $passes === $total );

		// 'show' logic: hide when rules are NOT met.
		// 'hide' logic: hide when rules ARE met.
		return $logic === 'show' ? ! $rules_met : $rules_met;
	}

	/**
	 * Basic string comparison with operator.
	 *
	 * @since 1.0.0
	 *
	 * @param ?string $actual   Actual value (null = not set).
	 * @param string  $operator Comparison operator.
	 * @param string  $value    Expected value.
	 *
	 * @return bool
	 */
	private function compare( ?string $actual, string $operator, string $value ): bool {
		switch ( $operator ) {
			case 'is':
				return $actual !== null && $actual === $value;
			case 'isNot':
				return $actual === null || $actual !== $value;
			case 'exists':
				return $actual !== null && $actual !== '';
			case 'notExists':
				return $actual === null || $actual === '';
			case 'contains':
				return $actual !== null && str_contains( $actual, $value );
			case 'greaterThan':
				return is_numeric( $actual ) && is_numeric( $value ) && (float) $actual > (float) $value;
			case 'lessThan':
				return is_numeric( $actual ) && is_numeric( $value ) && (float) $actual < (float) $value;
			case 'greaterThanOrEqual':
				return is_numeric( $actual ) && is_numeric( $value ) && (float) $actual >= (float) $value;
			case 'lessThanOrEqual':
				return is_numeric( $actual ) && is_numeric( $value ) && (float) $actual <= (float) $value;
			default:
				return false;
		}
	}

	/**
	 * Check basic location condition.
	 *
	 * @since 1.0.0
	 *
	 * @param string $location Location key.
	 *
	 * @return bool True if current page matches.
	 */
	private function check_location( string $location ): bool {
		switch ( $location ) {
			case 'front-page':
				return is_front_page();
			case 'blog':
				return is_home();
			case 'singular':
				return is_singular();
			case 'archive':
				return is_archive();
			case 'search':
				return is_search();
			case '404':
				return is_404();
			default:
				return false;
		}
	}

	/**
	 * Check a single advanced location rule.
	 *
	 * @since 1.0.0
	 *
	 * @param array $rule Rule with type, operator, value.
	 *
	 * @return bool True if the rule matches.
	 */
	public function check_advanced_location_rule( array $rule ): bool {
		$type  = $rule['type'] ?? '';
		$op    = $rule['operator'] ?? 'is';
		$value = $rule['value'] ?? '';

		switch ( $type ) {
			case 'postType':
				$current = get_post_type();
				$current = false === $current ? '' : $current;
				return $op === 'is' ? $current === $value : $current !== $value;

			case 'postIds':
				$ids   = array_map( 'intval', array_filter( explode( ',', $value ) ) );
				$match = in_array( (int) get_queried_object_id(), $ids, true );
				return $op === 'is' ? $match : ! $match;

			case 'taxonomyTerm':
				$parts = explode( ':', $value, 2 );
				if ( count( $parts ) !== 2 ) {
					return false;
				}
				$match = has_term( $parts[1], $parts[0] );
				return $op === 'is' ? $match : ! $match;

			case 'urlPath':
				$path = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
				switch ( $op ) {
					case 'is':
						return $path === $value;
					case 'isNot':
						return $path !== $value;
					case 'contains':
						return str_contains( $path, $value );
					case 'startsWith':
						return str_starts_with( $path, $value );
					default:
						return false;
				}

			case 'archiveType':
				$match = false;
				switch ( $value ) {
					case 'category':
						$match = is_category();
						break;
					case 'tag':
						$match = is_tag();
						break;
					case 'date':
						$match = is_date();
						break;
					case 'author':
						$match = is_author();
						break;
					case 'taxonomy':
						$match = is_tax();
						break;
					case 'postTypeArchive':
						$match = is_post_type_archive();
						break;
				}
				return $op === 'is' ? $match : ! $match;

			default:
				return false;
		}
	}
}
