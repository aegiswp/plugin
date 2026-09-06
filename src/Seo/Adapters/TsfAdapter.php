<?php
/**
 * The SEO Framework adapter (minimal).
 *
 * @package Aegis\Plugin\Seo\Adapters
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Seo\Adapters;

use Aegis\Plugin\Seo\SeoAdapterInterface;
use function defined;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Minimal TSF adapter — detection only; limited public schema API.
 */
final class TsfAdapter implements SeoAdapterInterface {

	public static function is_active(): bool {
		return defined( 'THE_SEO_FRAMEWORK_VERSION' );
	}

	public static function get_slug(): string {
		return 'tsf';
	}

	public static function get_label(): string {
		return 'The SEO Framework';
	}

	public function register_hooks(): void {
		// TSF exposes limited schema hooks; defer deep integration.
	}
}
