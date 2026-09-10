<?php
/**
 * Analytics Tracker
 *
 * Outputs analytics tracking scripts on the frontend. All configuration
 * is rendered as inline <script> tags — zero enqueued JS files from the
 * theme. External libraries load with async/defer attributes.
 *
 * Free: GA4, GTM, Clarity, Plausible, Fathom, Matomo, basic GDPR.
 * Pro:  Meta Pixel, Consent Mode v2, Data Layer, Debug Mode.
 *
 * @package Aegis\Plugin\Analytics
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Analytics;

use Aegis\Plugin\Integrations\WooCommerce as WooCommerceHelper;
use function absint;
use function add_action;
use function current_user_can;
use function defined;
use function esc_attr;
use function esc_url;
use function get_post;
use function get_query_var;
use function get_the_author_meta;
use function get_the_category;
use function is_admin;
use function is_array;
use function is_404;
use function is_archive;
use function is_author;
use function is_cart;
use function is_category;
use function is_checkout;
use function is_front_page;
use function is_order_received_page;
use function is_product;
use function is_search;
use function is_singular;
use function is_string;
use function is_tag;
use function is_user_logged_in;
use function function_exists;
use function rawurlencode;
use function sanitize_text_field;
use function wp_json_encode;
use function wp_parse_url;
use function wp_unslash;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Tracker {

	/**
	 * Cached settings.
	 *
	 * @var array|null
	 */
	private ?array $settings = null;

	/**
	 * Script proxy instance.
	 *
	 * @var ScriptProxy
	 */
	private ScriptProxy $proxy;

	/**
	 * Whether the GTM noscript iframe was printed on wp_body_open.
	 *
	 * @var bool
	 */
	private bool $gtm_noscript_printed = false;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->proxy = new ScriptProxy();
	}

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		if ( is_admin() ) {
			return;
		}

		$this->proxy->init();

		add_action( 'wp_head', [ $this, 'render_dns_prefetch' ], 0 );
		add_action( 'wp_head', [ $this, 'render_head_early' ], 1 );
		add_action( 'wp_head', [ $this, 'render_head_scripts' ], 5 );
		add_action( 'wp_body_open', [ $this, 'render_body_open' ] );
		add_action( 'wp_footer', [ $this, 'render_footer_scripts' ], 20 );
		add_action( 'woocommerce_add_to_cart', [ $this, 'capture_meta_pixel_add_to_cart' ], 10, 6 );
	}

	/**
	 * Get settings (cached).
	 *
	 * @return array
	 */
	public function get_settings(): array {
		if ( $this->settings === null ) {
			$this->settings = Settings::get_settings();
		}

		return $this->settings;
	}

	/**
	 * Check if any analytics are enabled.
	 *
	 * @return bool
	 */
	private function has_any_enabled(): bool {
		$s = $this->get_settings();

		return ! empty( $s['ga4_enabled'] )
			|| ! empty( $s['gtm_enabled'] )
			|| ! empty( $s['clarity_enabled'] )
			|| ! empty( $s['plausible_enabled'] )
			|| ! empty( $s['fathom_enabled'] )
			|| ! empty( $s['matomo_enabled'] )
			|| ( $this->is_pro_active() && ! empty( $s['meta_pixel_enabled'] ) );
	}

	/**
	 * Check if scripts should load on this request.
	 *
	 * @return bool
	 */
	private function should_load(): bool {
		if ( ! $this->has_any_enabled() ) {
			return false;
		}

		// Skip for admin users (unless Pro Debug Mode is on).
		$s = $this->get_settings();
		$debug_admin = $this->is_pro_active()
			&& ! empty( $s['gdpr_debug_mode'] )
			&& current_user_can( 'manage_options' );

		if ( current_user_can( 'manage_options' ) && ! $debug_admin ) {
			return false;
		}

		// Respect Do Not Track and Global Privacy Control (admins in Debug Mode still load).
		if ( ! empty( $s['gdpr_respect_dnt'] ) && ! $debug_admin ) {
			$dnt = sanitize_text_field( wp_unslash( $_SERVER['HTTP_DNT'] ?? '' ) );
			$gpc = sanitize_text_field( wp_unslash( $_SERVER['HTTP_SEC_GPC'] ?? '' ) );
			if ( $dnt === '1' || $gpc === '1' ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check if Aegis Pro plugin is active.
	 *
	 * @return bool
	 */
	private function is_pro_active(): bool {
		return defined( 'AEGIS_PRO_VERSION' );
	}

	/**
	 * Check if Complianz integration is active.
	 *
	 * @return bool
	 */
	private function is_complianz_active(): bool {
		$s = $this->get_settings();

		return ! empty( $s['gdpr_complianz'] ) && defined( 'CMPLZ_VERSION' );
	}

	/**
	 * Get the script type attribute for Complianz integration.
	 *
	 * @param string $service Complianz service name.
	 *
	 * @return string 'text/javascript' or 'text/plain' when Complianz manages consent.
	 */
	private function get_script_type( string $service = 'statistics' ): string {
		if ( $this->is_complianz_active() && ! empty( $this->get_settings()['gdpr_consent_required'] ) ) {
			return 'text/plain';
		}

		return 'text/javascript';
	}

	/**
	 * Get Complianz data attributes for a script tag.
	 *
	 * @param string $service  Complianz service name (e.g., 'google-analytics').
	 * @param string $category Complianz category (default: 'statistics').
	 *
	 * @return string HTML attributes string.
	 */
	private function get_complianz_attrs( string $service, string $category = 'statistics' ): string {
		if ( ! $this->is_complianz_active() || empty( $this->get_settings()['gdpr_consent_required'] ) ) {
			return '';
		}

		return ' data-service="' . esc_attr( $service ) . '" data-category="' . esc_attr( $category ) . '"';
	}

	// -------------------------------------------------------------------------
	// DNS Prefetch (wp_head priority 0)
	// -------------------------------------------------------------------------

	/**
	 * Output DNS prefetch hints for enabled external domains.
	 *
	 * @return void
	 */
	public function render_dns_prefetch(): void {
		if ( ! $this->should_load() ) {
			return;
		}

		$s       = $this->get_settings();
		$domains = [];

		// GA4/GTM — prefetch CDN hosts when not serving a fresh local file.
		if ( ! empty( $s['ga4_enabled'] ) && ! empty( $s['ga4_measurement_id'] )
			&& ( empty( $s['local_scripts'] ) || ! $this->proxy->has_local( 'gtag' ) )
		) {
			$domains[] = '//www.googletagmanager.com';
		}
		if ( ! empty( $s['gtm_enabled'] ) && ! empty( $s['gtm_container_id'] )
			&& ( empty( $s['local_scripts'] ) || ! $this->proxy->has_local( 'gtm' ) )
		) {
			$domains[] = '//www.googletagmanager.com';
		}

		// Clarity.
		if ( ! empty( $s['clarity_enabled'] ) ) {
			$clarity_id = Settings::normalize_clarity_project_id( (string) ( $s['clarity_project_id'] ?? '' ) );
			if ( $clarity_id !== '' ) {
				$domains[] = '//www.clarity.ms';
			}
		}

		// Plausible — prefetch cloud or custom script host.
		if ( ! empty( $s['plausible_enabled'] ) ) {
			$plausible_domain = Settings::normalize_plausible_domain( (string) ( $s['plausible_domain'] ?? '' ) );
			if ( $plausible_domain !== '' ) {
				$script_url = Settings::normalize_plausible_script_url( (string) ( $s['plausible_script_url'] ?? '' ) );
				if ( $script_url !== '' ) {
					$host = wp_parse_url( $script_url, PHP_URL_HOST );
					if ( is_string( $host ) && $host !== '' ) {
						$domains[] = '//' . $host;
					}
				} else {
					$domains[] = '//plausible.io';
				}
			}
		}

		// Fathom — prefetch cloud or custom script host.
		if ( ! empty( $s['fathom_enabled'] ) ) {
			$fathom_id = Settings::normalize_fathom_site_id( (string) ( $s['fathom_site_id'] ?? '' ) );
			if ( $fathom_id !== '' ) {
				$script_url = Settings::normalize_fathom_script_url( (string) ( $s['fathom_script_url'] ?? '' ) );
				if ( $script_url !== '' ) {
					$host = wp_parse_url( $script_url, PHP_URL_HOST );
					if ( is_string( $host ) && $host !== '' ) {
						$domains[] = '//' . $host;
					}
				} else {
					$domains[] = '//cdn.usefathom.com';
				}
			}
		}

		// Matomo (self-hosted — prefetch the configured host).
		if ( ! empty( $s['matomo_enabled'] ) ) {
			$matomo_url = Settings::normalize_matomo_url( (string) ( $s['matomo_url'] ?? '' ) );
			$matomo_id  = Settings::normalize_matomo_site_id( (string) ( $s['matomo_site_id'] ?? '' ) );
			if ( $matomo_url !== '' && $matomo_id !== '' ) {
				$host = wp_parse_url( $matomo_url, PHP_URL_HOST );
				if ( is_string( $host ) && $host !== '' ) {
					$domains[] = '//' . $host;
				}
			}
		}

		// Meta Pixel (Pro — prefetch connect.facebook.net when a valid ID is set).
		if ( $this->is_pro_active() && ! empty( $s['meta_pixel_enabled'] )
			&& Settings::normalize_meta_pixel_id( (string) ( $s['meta_pixel_id'] ?? '' ) ) !== ''
		) {
			$domains[] = '//connect.facebook.net';
		}

		$domains = array_unique( $domains );

		foreach ( $domains as $domain ) {
			echo '<link rel="dns-prefetch" href="' . esc_attr( $domain ) . '">' . "\n";
		}
	}

	// -------------------------------------------------------------------------
	// Head Early (wp_head priority 1)
	// -------------------------------------------------------------------------

	/**
	 * Output early head scripts: Consent Mode v2 defaults, Data Layer.
	 *
	 * @return void
	 */
	public function render_head_early(): void {
		if ( ! $this->should_load() ) {
			return;
		}

		$s = $this->get_settings();

		// PRO: Consent Mode v2 denied defaults — before GA4 gtag and/or GTM.
		if ( $this->should_render_consent_mode() ) {
			$this->render_consent_defaults();
		}

		// PRO: GTM Data Layer population — must be before GTM loads.
		if ( $this->is_pro_active() && ! empty( $s['gtm_data_layer'] ) && ! empty( $s['gtm_enabled'] ) && ! empty( $s['gtm_container_id'] ) ) {
			$this->render_data_layer();
		}
	}

	// -------------------------------------------------------------------------
	// Head Scripts (wp_head priority 5)
	// -------------------------------------------------------------------------

	/**
	 * Output head scripts: GA4, GTM, Clarity, Plausible, Fathom, Matomo, Meta Pixel.
	 *
	 * @return void
	 */
	public function render_head_scripts(): void {
		if ( ! $this->should_load() ) {
			return;
		}

		$s = $this->get_settings();

		// GA4.
		if ( ! empty( $s['ga4_enabled'] ) && ! empty( $s['ga4_measurement_id'] ) ) {
			$this->render_ga4();
		}

		// GTM head snippet.
		if ( ! empty( $s['gtm_enabled'] ) && ! empty( $s['gtm_container_id'] ) ) {
			$this->render_gtm_head();
		}

		// Clarity (Microsoft recommends <head>; script is async).
		if ( ! empty( $s['clarity_enabled'] ) ) {
			$clarity_id = Settings::normalize_clarity_project_id( (string) ( $s['clarity_project_id'] ?? '' ) );
			if ( $clarity_id !== '' ) {
				$this->render_clarity( $clarity_id );
			}
		}

		// Plausible (official snippet goes in <head> with defer).
		if ( ! empty( $s['plausible_enabled'] ) ) {
			$plausible_domain = Settings::normalize_plausible_domain( (string) ( $s['plausible_domain'] ?? '' ) );
			if ( $plausible_domain !== '' ) {
				$this->render_plausible( $plausible_domain );
			}
		}

		// Fathom (official snippet goes in <head> with defer).
		if ( ! empty( $s['fathom_enabled'] ) ) {
			$fathom_id = Settings::normalize_fathom_site_id( (string) ( $s['fathom_site_id'] ?? '' ) );
			if ( $fathom_id !== '' ) {
				$this->render_fathom( $fathom_id );
			}
		}

		// Matomo (tracking bootstrap in <head>).
		if ( ! empty( $s['matomo_enabled'] ) ) {
			$matomo_url = Settings::normalize_matomo_url( (string) ( $s['matomo_url'] ?? '' ) );
			$matomo_id  = Settings::normalize_matomo_site_id( (string) ( $s['matomo_site_id'] ?? '' ) );
			if ( $matomo_url !== '' && $matomo_id !== '' ) {
				$this->render_matomo( $matomo_url, $matomo_id );
			}
		}

		// PRO: Meta Pixel (Meta recommends base code in <head>).
		if ( $this->is_pro_active() && ! empty( $s['meta_pixel_enabled'] ) ) {
			$meta_pixel_id = Settings::normalize_meta_pixel_id( (string) ( $s['meta_pixel_id'] ?? '' ) );
			if ( $meta_pixel_id !== '' ) {
				$this->render_meta_pixel( $meta_pixel_id );
			}
		}
	}

	// -------------------------------------------------------------------------
	// Body Open (wp_body_open)
	// -------------------------------------------------------------------------

	/**
	 * Output body open scripts: GTM noscript iframe.
	 *
	 * @return void
	 */
	public function render_body_open(): void {
		if ( ! $this->should_load() ) {
			return;
		}

		$s = $this->get_settings();

		if ( ! empty( $s['gtm_enabled'] ) && ! empty( $s['gtm_container_id'] ) ) {
			// Noscript fallback cannot participate in Complianz script blocking.
			if ( ! ( $this->is_complianz_active() && ! empty( $s['gdpr_consent_required'] ) ) ) {
				$this->render_gtm_body();
			}
		}
	}

	// -------------------------------------------------------------------------
	// Footer Scripts (wp_footer priority 20)
	// -------------------------------------------------------------------------

	/**
	 * Output footer scripts: GTM noscript fallback, Debug.
	 *
	 * @return void
	 */
	public function render_footer_scripts(): void {
		if ( ! $this->should_load() ) {
			return;
		}

		$s = $this->get_settings();

		// GTM noscript fallback when the theme skipped wp_body_open.
		if ( ! empty( $s['gtm_enabled'] ) && ! empty( $s['gtm_container_id'] ) && ! $this->gtm_noscript_printed ) {
			if ( ! ( $this->is_complianz_active() && ! empty( $s['gdpr_consent_required'] ) ) ) {
				$this->render_gtm_body();
			}
		}

		// PRO: Debug Mode.
		if ( $this->is_pro_active() && ! empty( $s['gdpr_debug_mode'] ) && current_user_can( 'manage_options' ) ) {
			$this->render_debug();
		}
	}

	// =========================================================================
	// FREE Provider Renderers
	// =========================================================================

	/**
	 * Render GA4 tracking code.
	 *
	 * @return void
	 */
	private function render_ga4(): void {
		$s   = $this->get_settings();
		$id  = esc_attr( $s['ga4_measurement_id'] );
		$url = $this->get_ga4_script_url();

		$type   = $this->get_script_type();
		$cmplz  = $this->get_complianz_attrs( 'google-analytics' );

		// External library — async.
		echo '<script type="' . $type . '" async src="' . esc_url( $url ) . '"' . $cmplz . '></script>' . "\n";

		// Inline config. GA4 anonymizes IPs by default; this flag keeps the legacy gtag option for older setups.
		$anon   = ! empty( $s['ga4_anonymize_ip'] ) ? "'anonymize_ip':true" : '';
		$config = $anon ? ',{' . $anon . '}' : '';

		echo '<script type="' . $type . '"' . $cmplz . '>' . "\n";
		echo "window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','" . $id . "'" . $config . ");\n";
		echo '</script>' . "\n";
	}

	/**
	 * Get the GA4 script URL (local proxy or CDN).
	 *
	 * @return string
	 */
	private function get_ga4_script_url(): string {
		$s = $this->get_settings();

		if ( ! empty( $s['local_scripts'] ) && $this->proxy->has_local( 'gtag' ) ) {
			return $this->proxy->get_script_url( 'gtag', $s['ga4_measurement_id'] );
		}

		return $this->proxy->get_cdn_url( 'gtag', $s['ga4_measurement_id'] );
	}

	/**
	 * Render GTM head snippet.
	 *
	 * Uses the official loader pattern so the container ID is always appended
	 * (required for local proxied gtm.js, which has no ID in the file URL).
	 *
	 * @return void
	 */
	private function render_gtm_head(): void {
		$s   = $this->get_settings();
		$id  = (string) $s['gtm_container_id'];
		$src = $this->get_gtm_js_base_url();

		if ( $id === '' || $src === '' ) {
			return;
		}

		$type  = $this->get_script_type();
		$cmplz = $this->get_complianz_attrs( 'google-tag-manager' );

		echo '<script type="' . esc_attr( $type ) . '"' . $cmplz . '>' . "\n";
		echo '(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({\'gtm.start\':new Date().getTime(),event:\'gtm.js\'});';
		echo 'var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!=\'dataLayer\'?\'&l=\'+l:\'\',u=' . wp_json_encode( $src ) . ';';
		echo 'j.async=true;j.src=u+(u.indexOf(\'?\')>=0?\'&\':\'?\')+\'id=\'+i+dl;';
		echo 'f.parentNode.insertBefore(j,f);})(window,document,\'script\',\'dataLayer\',' . wp_json_encode( $id ) . ');' . "\n";
		echo '</script>' . "\n";
	}

	/**
	 * Base URL for gtm.js (CDN or local cache), without the container id query arg.
	 */
	private function get_gtm_js_base_url(): string {
		$s = $this->get_settings();

		if ( ! empty( $s['local_scripts'] ) && $this->proxy->has_local( 'gtm' ) ) {
			return $this->proxy->get_local_file_url( 'gtm' );
		}

		return 'https://www.googletagmanager.com/gtm.js';
	}

	/**
	 * Render GTM noscript iframe (body open / footer fallback).
	 *
	 * @return void
	 */
	private function render_gtm_body(): void {
		$s  = $this->get_settings();
		$id = (string) $s['gtm_container_id'];

		if ( $id === '' ) {
			return;
		}

		$this->gtm_noscript_printed = true;

		echo '<noscript><iframe src="' . esc_url( 'https://www.googletagmanager.com/ns.html?id=' . rawurlencode( $id ) ) . '" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>' . "\n";
	}

	/**
	 * Render Microsoft Clarity snippet (official async head loader).
	 *
	 * @param string $project_id Normalized Clarity Project ID.
	 * @return void
	 */
	private function render_clarity( string $project_id ): void {
		if ( $project_id === '' ) {
			return;
		}

		$type  = $this->get_script_type();
		$cmplz = $this->get_complianz_attrs( 'clarity' );

		echo '<script type="' . esc_attr( $type ) . '"' . $cmplz . '>' . "\n";
		echo "(function(c,l,a,r,i,t,y){c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};";
		echo "t=l.createElement(r);t.async=1;t.src='https://www.clarity.ms/tag/'+i;";
		echo "y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);";
		echo "})(window,document,'clarity','script'," . wp_json_encode( $project_id ) . ");\n";
		echo '</script>' . "\n";
	}

	/**
	 * Render Plausible Analytics script tag (official defer head snippet).
	 *
	 * @param string $domain Normalized data-domain value.
	 * @return void
	 */
	private function render_plausible( string $domain ): void {
		if ( $domain === '' ) {
			return;
		}

		$s = $this->get_settings();

		$script_url = Settings::normalize_plausible_script_url( (string) ( $s['plausible_script_url'] ?? '' ) );
		if ( $script_url === '' ) {
			$script_url = 'https://plausible.io/js/script.js';
		}

		$type  = $this->get_script_type();
		$cmplz = $this->get_complianz_attrs( 'plausible' );

		echo '<script type="' . esc_attr( $type ) . '" defer data-domain="' . esc_attr( $domain ) . '" src="' . esc_url( $script_url ) . '"' . $cmplz . '></script>' . "\n";
	}

	/**
	 * Render Fathom Analytics script tag (official defer head snippet).
	 *
	 * @param string $site_id Normalized Fathom Site ID.
	 * @return void
	 */
	private function render_fathom( string $site_id ): void {
		if ( $site_id === '' ) {
			return;
		}

		$s = $this->get_settings();

		$script_url = Settings::normalize_fathom_script_url( (string) ( $s['fathom_script_url'] ?? '' ) );
		if ( $script_url === '' ) {
			$script_url = 'https://cdn.usefathom.com/script.js';
		}

		$type  = $this->get_script_type();
		$cmplz = $this->get_complianz_attrs( 'fathom' );

		echo '<script type="' . esc_attr( $type ) . '" src="' . esc_url( $script_url ) . '" data-site="' . esc_attr( $site_id ) . '" defer' . $cmplz . '></script>' . "\n";
	}

	// =========================================================================
	// PRO Provider Renderers
	// =========================================================================

	/**
	 * Render GA4 Consent Mode v2 denied defaults (head priority 1).
	 *
	 * Complianz Integration updates grants via category events. Other CMPs can
	 * dispatch CustomEvent('aegis_analytics_consent', { detail: { analytics, marketing } }).
	 *
	 * @return void
	 */
	private function render_consent_defaults(): void {
		echo '<script>' . "\n";
		echo "window.dataLayer=window.dataLayer||[];\n";
		echo "function gtag(){dataLayer.push(arguments);}\n";
		echo "gtag('consent','default',{'analytics_storage':'denied','ad_storage':'denied','ad_user_data':'denied','ad_personalization':'denied','wait_for_update':500});\n";

		if ( $this->is_complianz_active() ) {
			echo "(function(){\n";
			echo "function aegisGrantConsent(cats){\n";
			echo "cats=Array.isArray(cats)?cats:[];\n";
			echo "var stats=cats.indexOf('statistics')!==-1||cats.indexOf('statistics-anonymous')!==-1;\n";
			echo "var marketing=cats.indexOf('marketing')!==-1;\n";
			echo "if(typeof gtag!=='function'){return;}\n";
			echo "gtag('consent','update',{\n";
			echo "analytics_storage:stats?'granted':'denied',\n";
			echo "ad_storage:marketing?'granted':'denied',\n";
			echo "ad_user_data:marketing?'granted':'denied',\n";
			echo "ad_personalization:marketing?'granted':'denied'\n";
			echo "});\n";
			echo "}\n";
			echo "function aegisCatsFromEvent(e){\n";
			echo "if(e&&Array.isArray(e.detail)){return e.detail;}\n";
			echo "return (e&&e.detail&&Array.isArray(e.detail.categories))?e.detail.categories:[];\n";
			echo "}\n";
			echo "document.addEventListener('cmplz_fire_categories',function(e){aegisGrantConsent(aegisCatsFromEvent(e));});\n";
			echo "document.addEventListener('cmplz_revoke',function(e){aegisGrantConsent(aegisCatsFromEvent(e));});\n";
			echo "document.addEventListener('aegis_analytics_consent',function(e){\n";
			echo "var d=(e&&e.detail)?e.detail:{};\n";
			echo "var cats=[];\n";
			echo "if(d.analytics||d.statistics){cats.push('statistics');}\n";
			echo "if(d.marketing||d.ads){cats.push('marketing');}\n";
			echo "if(Array.isArray(d.categories)){cats=d.categories;}\n";
			echo "aegisGrantConsent(cats);\n";
			echo "});\n";
			echo "})();\n";
		} else {
			// Non-Complianz CMPs: dispatch CustomEvent('aegis_analytics_consent', { detail: { analytics, marketing } }).
			echo "(function(){\n";
			echo "document.addEventListener('aegis_analytics_consent',function(e){\n";
			echo "var d=(e&&e.detail)?e.detail:{};\n";
			echo "if(typeof gtag!=='function'){return;}\n";
			echo "var analytics=!!(d.analytics||d.statistics);\n";
			echo "var marketing=!!(d.marketing||d.ads);\n";
			echo "if(Array.isArray(d.categories)){\n";
			echo "analytics=d.categories.indexOf('statistics')!==-1||d.categories.indexOf('statistics-anonymous')!==-1;\n";
			echo "marketing=d.categories.indexOf('marketing')!==-1;\n";
			echo "}\n";
			echo "gtag('consent','update',{\n";
			echo "analytics_storage:analytics?'granted':'denied',\n";
			echo "ad_storage:marketing?'granted':'denied',\n";
			echo "ad_user_data:marketing?'granted':'denied',\n";
			echo "ad_personalization:marketing?'granted':'denied'\n";
			echo "});\n";
			echo "});\n";
			echo "})();\n";
		}

		echo '</script>' . "\n";
	}

	/**
	 * Render GTM Data Layer population (head priority 1, before GTM).
	 *
	 * @return void
	 */
	private function render_data_layer(): void {
		$data = [
			'pageType' => $this->get_page_type(),
		];

		if ( is_singular() ) {
			$post = get_post();
			if ( $post ) {
				$data['postType']   = $post->post_type;
				$data['postId']     = $post->ID;
				$data['postAuthor'] = get_the_author_meta( 'display_name', $post->post_author );

				$categories = get_the_category( $post->ID );
				if ( ! empty( $categories ) ) {
					$data['postCategory'] = $categories[0]->name;
				}
			}
		}

		$data['userLoggedIn'] = is_user_logged_in();

		echo '<script>' . "\n";
		echo 'window.dataLayer=window.dataLayer||[];dataLayer.push(' . wp_json_encode( $data ) . ');' . "\n";
		echo '</script>' . "\n";
	}

	/**
	 * Render Matomo tracking code (head bootstrap).
	 *
	 * @param string $url     Normalized Matomo instance URL (no trailing slash).
	 * @param string $site_id Normalized numeric site ID.
	 * @return void
	 */
	private function render_matomo( string $url, string $site_id ): void {
		if ( $url === '' || $site_id === '' ) {
			return;
		}

		$s    = $this->get_settings();
		$anon = ! empty( $s['matomo_privacy_mode'] );

		$type  = $this->get_script_type();
		$cmplz = $this->get_complianz_attrs( 'matomo' );

		echo '<script type="' . esc_attr( $type ) . '"' . $cmplz . '>' . "\n";
		echo "var _paq=window._paq=window._paq||[];\n";
		if ( $anon ) {
			echo "_paq.push(['setDoNotTrack',true]);\n";
			echo "_paq.push(['disableCookies']);\n";
		}
		echo "_paq.push(['trackPageView']);\n";
		echo "_paq.push(['enableLinkTracking']);\n";
		echo '(function(){var u=' . wp_json_encode( $url . '/' ) . ";\n";
		echo "_paq.push(['setTrackerUrl',u+'matomo.php']);\n";
		echo '_paq.push([\'setSiteId\',' . wp_json_encode( (int) $site_id ) . "]);\n";
		echo "var d=document,g=d.createElement('script'),s=d.getElementsByTagName('script')[0];\n";
		echo "g.type='text/javascript';g.async=true;g.src=u+'matomo.js';s.parentNode.insertBefore(g,s);})();\n";
		echo '</script>' . "\n";
	}

	/**
	 * Stash the last AddToCart payload for Meta Pixel (fires on the next page view).
	 *
	 * @param string               $cart_item_key  Cart item key.
	 * @param int|string           $product_id     Product ID.
	 * @param int|float|string     $quantity       Quantity added.
	 * @param int|string           $variation_id   Variation ID.
	 * @param array<string, mixed> $variation      Variation attributes.
	 * @param array<string, mixed> $cart_item_data Extra cart item data.
	 * @return void
	 */
	public function capture_meta_pixel_add_to_cart(
		$cart_item_key,
		$product_id,
		$quantity,
		$variation_id = 0,
		$variation = [],
		$cart_item_data = []
	): void {
		unset( $cart_item_key, $variation, $cart_item_data );

		if ( ! $this->is_pro_active() ) {
			return;
		}

		$s = $this->get_settings();

		if ( empty( $s['meta_pixel_enabled'] )
			|| empty( $s['meta_pixel_woo_events'] )
			|| Settings::normalize_meta_pixel_id( (string) ( $s['meta_pixel_id'] ?? '' ) ) === ''
			|| ! WooCommerceHelper::is_plugin_active()
			|| ! function_exists( 'WC' )
			|| ! WC()->session
		) {
			return;
		}

		$product_id   = absint( $product_id );
		$variation_id = absint( $variation_id );
		$id           = $variation_id > 0 ? $variation_id : $product_id;
		$product      = function_exists( 'wc_get_product' ) ? wc_get_product( $id ) : false;

		if ( ! $product ) {
			return;
		}

		$qty   = max( 1, (int) $quantity );
		$price = (float) $product->get_price();

		WC()->session->set(
			'aegis_meta_pixel_add_to_cart',
			[
				'content_ids'  => [ (string) $product->get_id() ],
				'content_name' => $product->get_name(),
				'content_type' => 'product',
				'value'        => $price * $qty,
				'currency'     => function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : '',
				'num_items'    => $qty,
			]
		);
	}

	/**
	 * Render Meta Pixel (Facebook Pixel) base code (head).
	 *
	 * @param string $pixel_id Normalized Meta Pixel ID.
	 * @return void
	 */
	private function render_meta_pixel( string $pixel_id ): void {
		if ( $pixel_id === '' ) {
			return;
		}

		$s = $this->get_settings();

		$type  = $this->get_script_type();
		$cmplz = $this->get_complianz_attrs( 'facebook', 'marketing' );

		echo '<script type="' . esc_attr( $type ) . '"' . $cmplz . '>' . "\n";
		echo "!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');\n";
		echo 'fbq(\'init\',' . wp_json_encode( $pixel_id ) . ");\n";
		echo "fbq('track','PageView');\n";
		echo '</script>' . "\n";

		// Noscript pixel cannot participate in Complianz script blocking.
		if ( ! ( $this->is_complianz_active() && ! empty( $s['gdpr_consent_required'] ) ) ) {
			echo '<noscript><img height="1" width="1" style="display:none" src="' . esc_url( 'https://www.facebook.com/tr?id=' . rawurlencode( $pixel_id ) . '&ev=PageView&noscript=1' ) . '"/></noscript>' . "\n";
		}

		if ( ! empty( $s['meta_pixel_woo_events'] ) && WooCommerceHelper::is_plugin_active() ) {
			$this->render_meta_pixel_woo_events();
		}
	}

	/**
	 * Render Meta Pixel WooCommerce event tracking.
	 *
	 * @return void
	 */
	private function render_meta_pixel_woo_events(): void {
		$events = '';

		if ( function_exists( 'is_product' ) && is_product() && function_exists( 'wc_get_product' ) ) {
			$product = wc_get_product();
			if ( $product ) {
				$payload = [
					'content_name' => $product->get_name(),
					'content_ids'  => [ (string) $product->get_id() ],
					'content_type' => 'product',
					'value'        => (float) $product->get_price(),
					'currency'     => function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : '',
				];
				$events .= 'fbq(\'track\',\'ViewContent\',' . wp_json_encode( $payload ) . ");\n";
			}
		}

		// AddToCart from the last woocommerce_add_to_cart (fires once on the next view).
		if ( function_exists( 'WC' ) && WC()->session ) {
			$pending = WC()->session->get( 'aegis_meta_pixel_add_to_cart' );
			if ( is_array( $pending ) && $pending !== [] ) {
				$events .= 'fbq(\'track\',\'AddToCart\',' . wp_json_encode( $pending ) . ");\n";
				WC()->session->set( 'aegis_meta_pixel_add_to_cart', null );
			}
		}

		if ( function_exists( 'is_checkout' ) && is_checkout() && ! is_order_received_page() ) {
			$payload = [ 'content_type' => 'product' ];
			if ( function_exists( 'WC' ) && WC()->cart ) {
				$payload['value']     = (float) WC()->cart->get_total( 'edit' );
				$payload['currency']  = function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : '';
				$payload['num_items'] = (int) WC()->cart->get_cart_contents_count();
				$ids                  = [];
		// Prefer variation IDs when present (matches AddToCart / Purchase payloads).
				foreach ( WC()->cart->get_cart() as $item ) {
					$pid = isset( $item['variation_id'] ) && (int) $item['variation_id'] > 0
						? (int) $item['variation_id']
						: (int) ( $item['product_id'] ?? 0 );
					if ( $pid > 0 ) {
						$ids[] = (string) $pid;
					}
				}
				if ( $ids !== [] ) {
					$payload['content_ids'] = array_values( array_unique( $ids ) );
				}
			}
			$events .= 'fbq(\'track\',\'InitiateCheckout\',' . wp_json_encode( $payload ) . ");\n";
		}

		if ( function_exists( 'is_order_received_page' ) && is_order_received_page() && function_exists( 'wc_get_order' ) ) {
			$order_id = absint( get_query_var( 'order-received' ) );
			$order    = $order_id > 0 ? wc_get_order( $order_id ) : false;

			// Skip when the order is missing or Purchase was already emitted for this order.
			if ( $order && ! $order->get_meta( '_aegis_meta_pixel_purchase_tracked' ) ) {
				$ids = [];
				foreach ( $order->get_items() as $item ) {
					$variation_id = (int) $item->get_variation_id();
					$pid          = $variation_id > 0 ? $variation_id : (int) $item->get_product_id();
					if ( $pid > 0 ) {
						$ids[] = (string) $pid;
					}
				}
				$payload = [
					'value'        => (float) $order->get_total(),
					'currency'     => $order->get_currency(),
					'content_type' => 'product',
					'content_ids'  => array_values( array_unique( $ids ) ),
					'num_items'    => (int) $order->get_item_count(),
				];
				$events .= 'fbq(\'track\',\'Purchase\',' . wp_json_encode( $payload ) . ");\n";

				$order->update_meta_data( '_aegis_meta_pixel_purchase_tracked', (string) time() );
				$order->save();
			}
		}

		if ( $events !== '' ) {
			$type  = $this->get_script_type();
			$cmplz = $this->get_complianz_attrs( 'facebook', 'marketing' );
			echo '<script type="' . esc_attr( $type ) . '"' . $cmplz . '>' . "\n" . $events . '</script>' . "\n";
		}
	}

	/**
	 * Render admin-only debug console wrapper.
	 *
	 * @return void
	 */
	private function render_debug(): void {
		$s = $this->get_settings();

		$providers = [];
		if ( ! empty( $s['ga4_enabled'] ) && ! empty( $s['ga4_measurement_id'] ) ) {
			$providers[] = 'GA4';
		}
		if ( ! empty( $s['gtm_enabled'] ) && ! empty( $s['gtm_container_id'] ) ) {
			$providers[] = 'GTM';
		}
		if ( ! empty( $s['clarity_enabled'] )
			&& Settings::normalize_clarity_project_id( (string) ( $s['clarity_project_id'] ?? '' ) ) !== ''
		) {
			$providers[] = 'Clarity';
		}
		if ( ! empty( $s['plausible_enabled'] )
			&& Settings::normalize_plausible_domain( (string) ( $s['plausible_domain'] ?? '' ) ) !== ''
		) {
			$providers[] = 'Plausible';
		}
		if ( ! empty( $s['fathom_enabled'] )
			&& Settings::normalize_fathom_site_id( (string) ( $s['fathom_site_id'] ?? '' ) ) !== ''
		) {
			$providers[] = 'Fathom';
		}
		if ( ! empty( $s['matomo_enabled'] )
			&& Settings::normalize_matomo_url( (string) ( $s['matomo_url'] ?? '' ) ) !== ''
			&& Settings::normalize_matomo_site_id( (string) ( $s['matomo_site_id'] ?? '' ) ) !== ''
		) {
			$providers[] = 'Matomo';
		}
		if ( $this->is_pro_active()
			&& ! empty( $s['meta_pixel_enabled'] )
			&& Settings::normalize_meta_pixel_id( (string) ( $s['meta_pixel_id'] ?? '' ) ) !== ''
		) {
			$providers[] = 'Meta Pixel';
		}

		$list = $providers !== [] ? implode( ', ', $providers ) : '(none with valid credentials)';

		$dnt_header = sanitize_text_field( wp_unslash( $_SERVER['HTTP_DNT'] ?? '' ) );
		$gpc_header = sanitize_text_field( wp_unslash( $_SERVER['HTTP_SEC_GPC'] ?? '' ) );

		echo '<script>' . "\n";
		echo "console.log('%c[Aegis Analytics Debug]','color:#3b82f6;font-weight:bold','Active providers:'," . wp_json_encode( $list ) . ");\n";
		echo "console.log('%c[Aegis Analytics Debug]','color:#3b82f6;font-weight:bold',"
			. "'Consent Mode:'," . ( $this->should_render_consent_mode() ? 'true' : 'false' ) . ","
			. "'Consent required:'," . ( ! empty( $s['gdpr_consent_required'] ) ? 'true' : 'false' ) . ","
			. "'Complianz:'," . ( $this->is_complianz_active() ? 'true' : 'false' ) . ","
			. "'Respect DNT/GPC:'," . ( ! empty( $s['gdpr_respect_dnt'] ) ? 'true' : 'false' ) . ","
			. "'DNT header:'," . wp_json_encode( $dnt_header ) . ","
			. "'GPC header:'," . wp_json_encode( $gpc_header ) . ","
			. "'Local scripts:'," . ( ! empty( $s['local_scripts'] ) ? 'true' : 'false' ) . ");\n";
		echo '</script>' . "\n";
	}

	// =========================================================================
	// Helpers
	// =========================================================================

	/**
	 * Get the current page type for Data Layer.
	 *
	 * WooCommerce templates are checked before generic singular/archive labels
	 * so shop, cart, and checkout are not mislabeled (including shop-as-homepage).
	 *
	 * @return string
	 */
	private function get_page_type(): string {
		if ( function_exists( 'is_shop' ) && is_shop() ) {
			return 'shop';
		}
		if ( function_exists( 'is_product' ) && is_product() ) {
			return 'product';
		}
		if ( function_exists( 'is_product_category' ) && is_product_category() ) {
			return 'product_category';
		}
		if ( function_exists( 'is_product_tag' ) && is_product_tag() ) {
			return 'product_tag';
		}
		if ( function_exists( 'is_order_received_page' ) && is_order_received_page() ) {
			return 'order_received';
		}
		if ( function_exists( 'is_cart' ) && is_cart() ) {
			return 'cart';
		}
		if ( function_exists( 'is_checkout' ) && is_checkout() ) {
			return 'checkout';
		}
		if ( function_exists( 'is_account_page' ) && is_account_page() ) {
			return 'account';
		}

		if ( is_front_page() ) {
			return 'home';
		}
		if ( is_singular( 'post' ) ) {
			return 'post';
		}
		if ( is_singular() ) {
			$post = get_post();
			return $post ? (string) $post->post_type : 'singular';
		}
		if ( is_category() ) {
			return 'category';
		}
		if ( is_tag() ) {
			return 'tag';
		}
		if ( is_author() ) {
			return 'author';
		}
		if ( is_search() ) {
			return 'search';
		}
		if ( is_404() ) {
			return '404';
		}
		if ( is_archive() ) {
			return 'archive';
		}

		return 'other';
	}

	/**
	 * Whether Consent Mode defaults should render for this request.
	 */
	private function should_render_consent_mode(): bool {
		$s = $this->get_settings();

		if ( ! $this->is_pro_active() || empty( $s['ga4_consent_mode'] ) ) {
			return false;
		}

		$ga4 = ! empty( $s['ga4_enabled'] ) && ! empty( $s['ga4_measurement_id'] );
		$gtm = ! empty( $s['gtm_enabled'] ) && ! empty( $s['gtm_container_id'] );

		return $ga4 || $gtm;
	}
}
