<?php
/**
 * Analytics Script Proxy
 *
 * Caches external analytics libraries (GA4 gtag.js, GTM gtm.js) locally
 * in wp-content/uploads/aegis-analytics/ to eliminate DNS lookups, enable
 * HTTP/2 multiplexing, and bypass ad blockers.
 *
 * @package Aegis\Plugin\Analytics
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Analytics;

use WP_Filesystem_Base;
use function add_action;
use function filemtime;
use function get_option;
use function is_wp_error;
use function rawurlencode;
use function time;
use function trailingslashit;
use function wp_clear_scheduled_hook;
use function wp_delete_file;
use function wp_mkdir_p;
use function wp_next_scheduled;
use function wp_remote_get;
use function wp_remote_retrieve_body;
use function wp_remote_retrieve_response_code;
use function wp_schedule_event;
use function wp_upload_dir;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ScriptProxy {

	/**
	 * Subdirectory inside wp-content/uploads/.
	 *
	 * @var string
	 */
	private const CACHE_DIR = 'aegis-analytics';

	/**
	 * WP-Cron hook name.
	 *
	 * @var string
	 */
	private const CRON_HOOK = 'aegis_analytics_refresh_scripts';

	/**
	 * Refresh interval in seconds (24 hours).
	 *
	 * @var int
	 */
	private const REFRESH_INTERVAL = DAY_IN_SECONDS;

	/**
	 * Stale threshold — fall back to CDN if file is older than this (48 hours).
	 *
	 * @var int
	 */
	private const STALE_THRESHOLD = 2 * DAY_IN_SECONDS;

	/**
	 * CDN URLs for each provider.
	 *
	 * @var array<string, string>
	 */
	private const CDN_URLS = [
		'gtag' => 'https://www.googletagmanager.com/gtag/js',
		'gtm'  => 'https://www.googletagmanager.com/gtm.js',
	];

	/**
	 * Local filenames for each provider.
	 *
	 * @var array<string, string>
	 */
	private const LOCAL_FILES = [
		'gtag' => 'gtag.js',
		'gtm'  => 'gtm.js',
	];

	/**
	 * Cached upload directory info.
	 *
	 * @var array|null
	 */
	private ?array $upload_dir = null;

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( self::CRON_HOOK, [ $this, 'refresh_scripts' ] );
	}

	/**
	 * Schedule the cron event if not already scheduled.
	 *
	 * @return void
	 */
	public function schedule(): void {
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time(), 'daily', self::CRON_HOOK );
		}
	}

	/**
	 * Unschedule the cron event.
	 *
	 * @return void
	 */
	public function unschedule(): void {
		wp_clear_scheduled_hook( self::CRON_HOOK );
	}

	/**
	 * Get the script URL for a provider.
	 *
	 * Returns local URL if the cached file exists and is fresh, otherwise CDN.
	 *
	 * @param string $provider 'gtag' or 'gtm'.
	 * @param string $id       Measurement ID (GA4) or Container ID (GTM).
	 *
	 * @return string URL to the script.
	 */
	public function get_script_url( string $provider, string $id = '' ): string {
		$dir  = $this->get_cache_dir();
		$file = $dir . ( self::LOCAL_FILES[ $provider ] ?? '' );

		if ( ! $file || ! $this->path_exists( $file ) ) {
			return $this->get_cdn_url( $provider, $id );
		}

		$age = time() - filemtime( $file );

		if ( $age > self::STALE_THRESHOLD ) {
			return $this->get_cdn_url( $provider, $id );
		}

		$upload = $this->get_upload_info();
		$url    = trailingslashit( $upload['baseurl'] ) . self::CACHE_DIR . '/' . self::LOCAL_FILES[ $provider ];

		// Cache-busting.
		$url .= '?v=' . filemtime( $file );

		return $url;
	}

	/**
	 * Get the CDN URL for a provider.
	 *
	 * @param string $provider 'gtag' or 'gtm'.
	 * @param string $id       Measurement ID or Container ID.
	 *
	 * @return string
	 */
	public function get_cdn_url( string $provider, string $id = '' ): string {
		$url = self::CDN_URLS[ $provider ] ?? '';

		if ( ! $url ) {
			return '';
		}

		if ( $id ) {
			$url .= '?id=' . rawurlencode( $id );
		}

		return $url;
	}

	/**
	 * Refresh cached scripts via WP-Cron callback.
	 *
	 * @return void
	 */
	public function refresh_scripts(): void {
		$settings = get_option( Settings::OPTION, [] );

		if ( empty( $settings['local_scripts'] ) ) {
			return;
		}

		$dir = $this->get_cache_dir();

		if ( ! $this->ensure_directory( $dir ) ) {
			return;
		}

		// GA4 gtag.js.
		if ( ! empty( $settings['ga4_enabled'] ) && ! empty( $settings['ga4_measurement_id'] ) ) {
			$this->fetch_and_cache(
				self::CDN_URLS['gtag'] . '?id=' . rawurlencode( $settings['ga4_measurement_id'] ),
				$dir . self::LOCAL_FILES['gtag']
			);
		}

		// GTM gtm.js.
		if ( ! empty( $settings['gtm_enabled'] ) && ! empty( $settings['gtm_container_id'] ) ) {
			$this->fetch_and_cache(
				self::CDN_URLS['gtm'] . '?id=' . rawurlencode( $settings['gtm_container_id'] ),
				$dir . self::LOCAL_FILES['gtm']
			);
		}
	}

	/**
	 * Fetch a remote script and save it locally.
	 *
	 * @param string $url  Remote URL.
	 * @param string $path Local file path.
	 *
	 * @return bool True on success.
	 */
	private function fetch_and_cache( string $url, string $path ): bool {
		$response = wp_remote_get(
			$url,
			[
				'timeout'   => 15,
				'sslverify' => true,
			]
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$code = wp_remote_retrieve_response_code( $response );

		if ( $code !== 200 ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $response );

		if ( empty( $body ) ) {
			return false;
		}

		return $this->write_file( $path, $body );
	}

	/**
	 * Ensure the cache directory exists with protective files.
	 *
	 * @param string $dir Directory path.
	 *
	 * @return bool True if directory is writable.
	 */
	private function ensure_directory( string $dir ): bool {
		$filesystem = $this->get_filesystem();

		if ( $filesystem === null ) {
			return false;
		}

		if ( ! $this->path_exists( $dir ) ) {
			wp_mkdir_p( $dir );
		}

		if ( ! $filesystem->is_writable( $dir ) ) {
			return false;
		}

		// Allow direct access to JS files.
		$htaccess = $dir . '.htaccess';
		if ( ! $this->path_exists( $htaccess ) ) {
			$this->write_file( $htaccess, "<IfModule mod_authz_core.c>\n\tRequire all granted\n</IfModule>\n<IfModule !mod_authz_core.c>\n\tOrder Allow,Deny\n\tAllow from all\n</IfModule>\n" );
		}

		// Index file.
		$index = $dir . 'index.php';
		if ( ! $this->path_exists( $index ) ) {
			$this->write_file( $index, "<?php\n// Silence is golden.\n" );
		}

		return true;
	}

	/**
	 * Write content to a file using WP_Filesystem when available.
	 *
	 * @param string $path    File path.
	 * @param string $content File content.
	 *
	 * @return bool True on success.
	 */
	private function write_file( string $path, string $content ): bool {
		$filesystem = $this->get_filesystem();

		if ( $filesystem === null ) {
			return false;
		}

		return $filesystem->put_contents( $path, $content, FS_CHMOD_FILE );
	}

	/**
	 * Get the cache directory path (with trailing slash).
	 *
	 * @return string
	 */
	private function get_cache_dir(): string {
		$upload = $this->get_upload_info();

		return trailingslashit( $upload['basedir'] ) . self::CACHE_DIR . '/';
	}

	/**
	 * Get upload directory info (cached).
	 *
	 * @return array
	 */
	private function get_upload_info(): array {
		if ( $this->upload_dir === null ) {
			$this->upload_dir = wp_upload_dir();
		}

		return $this->upload_dir;
	}

	/**
	 * Check if the cache directory is writable.
	 *
	 * @return bool
	 */
	public function is_writable(): bool {
		$filesystem = $this->get_filesystem();

		if ( $filesystem === null ) {
			return false;
		}

		$dir = $this->get_cache_dir();

		if ( ! $this->path_exists( $dir ) ) {
			return $filesystem->is_writable( dirname( $dir ) );
		}

		return $filesystem->is_writable( $dir );
	}

	/**
	 * Public URL for a locally cached script file (with cache-bust query).
	 *
	 * Does not include provider measurement/container IDs — callers append those.
	 *
	 * @param string $provider 'gtag' or 'gtm'.
	 */
	public function get_local_file_url( string $provider ): string {
		$file = self::LOCAL_FILES[ $provider ] ?? '';

		if ( $file === '' || ! $this->has_local( $provider ) ) {
			return '';
		}

		$upload = $this->get_upload_info();
		$path   = $this->get_cache_dir() . $file;
		$url    = trailingslashit( $upload['baseurl'] ) . self::CACHE_DIR . '/' . $file;

		return $url . '?v=' . filemtime( $path );
	}

	/**
	 * Check if local scripts are available for a provider.
	 *
	 * @param string $provider 'gtag' or 'gtm'.
	 *
	 * @return bool
	 */
	public function has_local( string $provider ): bool {
		$file = $this->get_cache_dir() . ( self::LOCAL_FILES[ $provider ] ?? '' );

		if ( ! $file || ! $this->path_exists( $file ) ) {
			return false;
		}

		$age = time() - filemtime( $file );

		return $age <= self::STALE_THRESHOLD;
	}

	/**
	 * Cleanup cached files and cron event.
	 *
	 * @return void
	 */
	public function cleanup(): void {
		$this->unschedule();

		$filesystem = $this->get_filesystem();
		$dir        = $this->get_cache_dir();

		foreach ( self::LOCAL_FILES as $file ) {
			$path = $dir . $file;
			if ( $this->path_exists( $path ) ) {
				wp_delete_file( $path );
			}
		}

		$htaccess = $dir . '.htaccess';
		if ( $this->path_exists( $htaccess ) ) {
			wp_delete_file( $htaccess );
		}

		$index = $dir . 'index.php';
		if ( $this->path_exists( $index ) ) {
			wp_delete_file( $index );
		}

		if ( $filesystem !== null && $this->path_exists( $dir ) && $filesystem->is_dir( $dir ) ) {
			$filesystem->rmdir( $dir );
		}
	}

	/**
	 * Initialize and return the WordPress filesystem API.
	 */
	private function get_filesystem(): ?WP_Filesystem_Base {
		global $wp_filesystem;

		if ( ! $wp_filesystem instanceof WP_Filesystem_Base ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		return $wp_filesystem instanceof WP_Filesystem_Base ? $wp_filesystem : null;
	}

	/**
	 * Check whether a path exists via WP_Filesystem.
	 *
	 * @param string $path Absolute file or directory path.
	 */
	private function path_exists( string $path ): bool {
		$filesystem = $this->get_filesystem();

		if ( $filesystem === null ) {
			return false;
		}

		return $filesystem->exists( $path );
	}
}
