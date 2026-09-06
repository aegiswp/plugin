<?php
/**
 * Fires integration-specific injection hooks when integrations are active.
 *
 * @package Aegis\Plugin\Injection
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Injection;

use Aegis\Plugin\Settings\Repository;
use function add_action;
use function add_filter;
use function do_action;
use function is_singular;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bridges third-party plugin hooks to Aegis injection points.
 */
final class IntegrationInjector {

	/**
	 * Register integration bridge hooks.
	 */
	public function init(): void {
		$this->register_woocommerce();
		$this->register_edd();
		$this->register_affiliate_wp();
		$this->register_lifter_lms();
		$this->register_sensei_lms();
		$this->register_fluent_forms();
		$this->register_gravity_forms();
		$this->register_ninja_forms();
		$this->register_bbpress();
		$this->register_coauthors();
		$this->register_map_block();
	}

	private function register_woocommerce(): void {
		Repository::boot_if_enabled(
			'woocommerce',
			static function (): void {
				add_action(
					'woocommerce_before_checkout_form',
					static function (): void {
						do_action( 'aegis_before_woocommerce_checkout' );
					},
					5
				);

				add_action(
					'woocommerce_after_checkout_form',
					static function (): void {
						do_action( 'aegis_after_woocommerce_checkout' );
					},
					99
				);

				add_action(
					'woocommerce_before_cart',
					static function (): void {
						do_action( 'aegis_before_woocommerce_cart' );
					},
					5
				);

				add_action(
					'woocommerce_after_cart',
					static function (): void {
						do_action( 'aegis_after_woocommerce_cart' );
					},
					99
				);

				add_filter(
					'render_block_woocommerce/checkout',
					static function ( string $content ): string {
						ob_start();
						do_action( 'aegis_before_woocommerce_checkout' );
						$before = (string) ob_get_clean();

						ob_start();
						do_action( 'aegis_after_woocommerce_checkout' );
						$after = (string) ob_get_clean();

						return $before . $content . $after;
					},
					5
				);

				add_filter(
					'render_block_woocommerce/cart',
					static function ( string $content ): string {
						ob_start();
						do_action( 'aegis_before_woocommerce_cart' );
						$before = (string) ob_get_clean();

						ob_start();
						do_action( 'aegis_after_woocommerce_cart' );
						$after = (string) ob_get_clean();

						return $before . $content . $after;
					},
					5
				);
			}
		);
	}

	private function register_edd(): void {
		Repository::boot_if_enabled(
			'easy_digital_downloads',
			static function (): void {
				add_action(
					'edd_before_download_content',
					static function (): void {
						do_action( 'aegis_before_edd_download' );
					},
					5
				);

				add_action(
					'edd_after_download_content',
					static function (): void {
						do_action( 'aegis_after_edd_download' );
					},
					99
				);
			}
		);
	}

	private function register_affiliate_wp(): void {
		Repository::boot_if_enabled(
			'affiliate_wp',
			static function (): void {
				add_action(
					'affwp_affiliate_dashboard_before_content',
					static function (): void {
						do_action( 'aegis_before_affwp_dashboard' );
					},
					5
				);

				add_action(
					'affwp_affiliate_dashboard_after_content',
					static function (): void {
						do_action( 'aegis_after_affwp_dashboard' );
					},
					99
				);
			}
		);
	}

	private function register_lifter_lms(): void {
		Repository::boot_if_enabled(
			'lifter_lms',
			static function (): void {
				add_action(
					'lifterlms_before_main_content',
					static function (): void {
						if ( is_singular( 'course' ) ) {
							do_action( 'aegis_before_llms_course' );
						}
					},
					5
				);

				add_action(
					'lifterlms_after_main_content',
					static function (): void {
						if ( is_singular( 'course' ) ) {
							do_action( 'aegis_after_llms_course' );
						}
					},
					99
				);
			}
		);
	}

	private function register_sensei_lms(): void {
		Repository::boot_if_enabled(
			'sensei_lms',
			static function (): void {
				add_action(
					'sensei_before_main_content',
					static function (): void {
						if ( is_singular( 'lesson' ) ) {
							do_action( 'aegis_before_sensei_lesson' );
						}
					},
					5
				);

				add_action(
					'sensei_after_main_content',
					static function (): void {
						if ( is_singular( 'lesson' ) ) {
							do_action( 'aegis_after_sensei_lesson' );
						}
					},
					99
				);
			}
		);
	}

	private function register_fluent_forms(): void {
		Repository::boot_if_enabled(
			'fluent_forms',
			static function (): void {
				add_action(
					'fluentform/before_form_render',
					static function (): void {
						do_action( 'aegis_before_fluentform' );
					},
					5
				);

				add_action(
					'fluentform/after_form_render',
					static function (): void {
						do_action( 'aegis_after_fluentform' );
					},
					99
				);
			}
		);
	}

	private function register_gravity_forms(): void {
		Repository::boot_if_enabled(
			'gravity_forms',
			static function (): void {
				add_filter(
					'gform_pre_render',
					static function ( $form ) {
						do_action( 'aegis_before_gform' );
						return $form;
					},
					5
				);

				add_filter(
					'gform_get_form_filter',
					static function ( string $markup ): string {
						ob_start();
						do_action( 'aegis_after_gform' );
						$after = (string) ob_get_clean();

						return $markup . $after;
					},
					99
				);
			}
		);
	}

	private function register_ninja_forms(): void {
		Repository::boot_if_enabled(
			'ninja_forms',
			static function (): void {
				add_action(
					'ninja_forms_before_form_display',
					static function (): void {
						do_action( 'aegis_before_nf_form' );
					},
					5
				);

				add_action(
					'ninja_forms_after_form_display',
					static function (): void {
						do_action( 'aegis_after_nf_form' );
					},
					99
				);
			}
		);
	}

	private function register_bbpress(): void {
		Repository::boot_if_enabled(
			'bbpress',
			static function (): void {
				add_action(
					'bbp_template_before_forums_loop',
					static function (): void {
						do_action( 'aegis_before_bbpress_forum' );
					},
					5
				);

				add_action(
					'bbp_template_after_single_topic',
					static function (): void {
						do_action( 'aegis_after_bbpress_topic' );
					},
					99
				);
			}
		);
	}

	private function register_coauthors(): void {
		Repository::boot_if_enabled(
			'co_authors_plus',
			static function (): void {
				add_filter(
					'render_block_core/post-author',
					static function ( string $content ): string {
						ob_start();
						do_action( 'aegis_before_post_author' );
						$before = (string) ob_get_clean();

						ob_start();
						do_action( 'aegis_after_post_author' );
						$after = (string) ob_get_clean();

						return $before . $content . $after;
					},
					4,
					1
				);
			}
		);
	}

	private function register_map_block(): void {
		Repository::boot_if_enabled(
			'google_maps',
			static function (): void {
				add_filter(
					'render_block_aegis/map',
					static function ( string $content ): string {
						ob_start();
						do_action( 'aegis_before_map_block' );
						$before = (string) ob_get_clean();

						ob_start();
						do_action( 'aegis_after_map_block' );
						$after = (string) ob_get_clean();

						return $before . $content . $after;
					},
					4
				);
			}
		);
	}
}
