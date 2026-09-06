<?php
/**
 * Conditionals admin dashboard page.
 *
 * @package Aegis\Plugin\Conditionals
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Conditionals;

use function __;
use function add_action;
use function add_filter;
use function add_submenu_page;
use function admin_url;
use function do_action;
use function esc_html_e;
use function register_setting;
use function settings_fields;
use function submit_button;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and renders the Conditionals admin page.
 */
final class AdminPage {

	/**
	 * Theme admin renderer for shared UI helpers (lazy-loaded after theme bootstrap).
	 *
	 * @var \Aegis\Plugin\Admin\Renderer|null
	 */
	private $renderer = null;

	/**
	 * Settings AJAX controller.
	 */
	private SettingsController $controller;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->controller = new SettingsController();
	}

	/**
	 * Theme admin renderer for toggles, toolbar, and section headers.
	 *
	 * @return \Aegis\Plugin\Admin\Renderer
	 */
	private function renderer(): \Aegis\Plugin\Admin\Renderer {
		if ( null === $this->renderer ) {
			$this->renderer = new \Aegis\Plugin\Admin\Renderer();
		}

		return $this->renderer;
	}

	/**
	 * Register hooks.
	 */
	public function init(): void {
		add_action( 'admin_menu', [ $this, 'register_menu' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'wp_ajax_aegis_save_settings', [ $this->controller, 'ajax_save_settings' ] );
		add_filter( 'aegis_admin_tabs', [ $this, 'register_admin_tab' ] );
	}

	/**
	 * Register the Conditionals admin tab.
	 *
	 * @param array<string, array{label: string, url: string}> $tabs Registered tabs.
	 * @return array<string, array{label: string, url: string}>
	 */
	public function register_admin_tab( array $tabs ): array {
		$tabs['conditional-logic'] = array(
			'label' => __( 'Conditionals', 'aegis' ),
			'url'   => admin_url( 'admin.php?page=aegis-settings' ),
		);

		return $tabs;
	}

	/**
	 * Register hidden Conditionals submenu.
	 */
	public function register_menu(): void {
		add_submenu_page(
			'aegis-dashboard',
			__( 'Conditionals', 'aegis' ),
			__( 'Conditionals', 'aegis' ),
			'manage_options',
			'aegis-settings',
			[ $this, 'render' ]
		);
	}

	/**
	 * Register conditional logic settings with WordPress.
	 */
	public function register_settings(): void {
		register_setting(
			'aegis_conditional_logic_group',
			Settings::OPTION,
			[
				'type'              => 'array',
				'sanitize_callback' => [ Settings::class, 'sanitize' ],
				'default'           => Settings::DEFAULTS,
			]
		);
	}

	/**
	 * Render the Conditionals admin page.
	 */
	public function render(): void {
		$options = Settings::get_settings();
		?>
		<div class="wrap aegis-admin-page">
			<?php do_action( 'aegis_admin_before_conditionals_page' ); ?>

			<div class="aegis-settings-wrap">
				<h1 class="screen-reader-text"><?php esc_html_e( 'Conditionals', 'aegis' ); ?></h1>

				<?php $this->renderer()->render_toolbar( 'conditionals' ); ?>

				<form method="post" action="#" class="aegis-settings-form">
					<?php settings_fields( 'aegis_conditional_logic_group' ); ?>

					<div class="aegis-settings-layout">
						<nav class="aegis-settings-nav">
							<a href="#visibility" class="aegis-nav-item active">
								<span class="dashicons dashicons-visibility"></span>
								<?php esc_html_e( 'Visibility', 'aegis' ); ?>
							</a>
							<a href="#accessibility" class="aegis-nav-item">
								<span class="dashicons dashicons-universal-access"></span>
								<?php esc_html_e( 'Accessibility', 'aegis' ); ?>
							</a>
							<a href="#user" class="aegis-nav-item">
								<span class="dashicons dashicons-admin-users"></span>
								<?php esc_html_e( 'User', 'aegis' ); ?>
							</a>
							<a href="#schedule" class="aegis-nav-item">
								<span class="dashicons dashicons-calendar-alt"></span>
								<?php esc_html_e( 'Schedule', 'aegis' ); ?>
							</a>
							<a href="#image-source" class="aegis-nav-item">
								<span class="dashicons dashicons-format-image"></span>
								<?php esc_html_e( 'Image Source', 'aegis' ); ?>
							</a>
						</nav>

					<div class="aegis-settings-content">
						<section id="visibility" class="aegis-settings-section active">
							<?php $this->renderer()->render_section_header( __( 'Visibility Controls', 'aegis' ), __( 'Control block visibility based on screen size, request context, location, and post meta.', 'aegis' ) ); ?>
							<div class="aegis-settings-grid">
								<?php $this->renderer()->render_toggle( 'visibility', 'screen_size', __( 'Screen Size', 'aegis' ), __( 'Hide blocks on mobile, tablet, or desktop.', 'aegis' ), $options, 'smartphone', Settings::OPTION ); ?>
								<?php $this->renderer()->render_toggle( 'visibility', 'custom_breakpoints', __( 'Custom Breakpoints', 'aegis' ), __( 'Define custom min/max width breakpoints.', 'aegis' ), $options, 'editor-expand', Settings::OPTION ); ?>
								<?php $this->renderer()->render_toggle( 'visibility', 'browser_device', __( 'Browser & Device', 'aegis' ), __( 'Target specific browsers and devices.', 'aegis' ), $options, 'desktop', Settings::OPTION ); ?>
								<?php $this->renderer()->render_toggle( 'visibility', 'lockdown', __( 'Lockdown', 'aegis' ), __( 'Hide blocks from all users on the frontend (draft mode).', 'aegis' ), $options, 'lock', Settings::OPTION ); ?>
								<?php $this->renderer()->render_toggle( 'visibility', 'query_string', __( 'URL Query String', 'aegis' ), __( 'Show or hide blocks based on URL query parameters.', 'aegis' ), $options, 'admin-links', Settings::OPTION ); ?>
								<?php $this->renderer()->render_toggle( 'visibility', 'specific_users', __( 'Specific Users', 'aegis' ), __( 'Show or hide blocks for specific user IDs.', 'aegis' ), $options, 'admin-users', Settings::OPTION ); ?>
								<?php $this->renderer()->render_toggle( 'pro_conditions', 'cookie', __( 'Cookie', 'aegis' ), __( 'Show or hide blocks based on browser cookie values.', 'aegis' ), $options, 'info-outline', Settings::OPTION ); ?>
								<?php $this->renderer()->render_toggle( 'pro_conditions', 'referral', __( 'Referral Source', 'aegis' ), __( 'Show or hide blocks based on the referring domain.', 'aegis' ), $options, 'share', Settings::OPTION ); ?>
								<?php $this->renderer()->render_toggle( 'pro_conditions', 'advanced_location', __( 'Advanced Location', 'aegis' ), __( 'Show or hide blocks by post type, post IDs, taxonomy terms, URL path, or archive type.', 'aegis' ), $options, 'location-alt', Settings::OPTION ); ?>
								<?php $this->renderer()->render_toggle( 'pro_conditions', 'post_meta', __( 'Post Meta', 'aegis' ), __( 'Show or hide blocks based on raw post meta key/value.', 'aegis' ), $options, 'admin-post', Settings::OPTION ); ?>
							</div>
						</section>

						<section id="accessibility" class="aegis-settings-section">
							<?php $this->renderer()->render_section_header( __( 'Accessibility Controls', 'aegis' ), __( 'Respect user accessibility preferences.', 'aegis' ) ); ?>
							<div class="aegis-settings-grid">
								<?php $this->renderer()->render_toggle( 'accessibility', 'reduced_motion', __( 'Reduced Motion', 'aegis' ), __( 'Hide content for users who prefer reduced motion.', 'aegis' ), $options, 'controls-pause', Settings::OPTION ); ?>
								<?php $this->renderer()->render_toggle( 'accessibility', 'screen_reader_only', __( 'Screen Reader Only', 'aegis' ), __( 'Make content visible only to screen readers.', 'aegis' ), $options, 'megaphone', Settings::OPTION ); ?>
								<?php $this->renderer()->render_toggle( 'accessibility', 'color_scheme', __( 'Color Scheme', 'aegis' ), __( 'Show content only in light or dark mode.', 'aegis' ), $options, 'art', Settings::OPTION ); ?>
								<?php $this->renderer()->render_toggle( 'accessibility', 'high_contrast', __( 'High Contrast', 'aegis' ), __( 'Hide content in high contrast mode.', 'aegis' ), $options, 'admin-appearance', Settings::OPTION ); ?>
								<?php $this->renderer()->render_toggle( 'accessibility', 'forced_colors', __( 'Forced Colors', 'aegis' ), __( 'Hide content in Windows High Contrast mode.', 'aegis' ), $options, 'color-picker', Settings::OPTION ); ?>
							</div>
						</section>

						<section id="user" class="aegis-settings-section">
							<?php $this->renderer()->render_section_header( __( 'User Controls', 'aegis' ), __( 'Control visibility based on user status, role, and user meta.', 'aegis' ) ); ?>
							<div class="aegis-settings-grid">
								<?php $this->renderer()->render_toggle( 'user', 'user_status', __( 'User Status', 'aegis' ), __( 'Show content to logged-in or logged-out users.', 'aegis' ), $options, 'admin-users', Settings::OPTION ); ?>
								<?php $this->renderer()->render_toggle( 'user', 'user_role', __( 'User Role', 'aegis' ), __( 'Show content to specific user roles.', 'aegis' ), $options, 'groups', Settings::OPTION ); ?>
								<?php $this->renderer()->render_toggle( 'pro_conditions', 'user_meta', __( 'User Meta', 'aegis' ), __( 'Show or hide blocks based on current user meta key/value.', 'aegis' ), $options, 'id-alt', Settings::OPTION ); ?>
							</div>
						</section>

						<section id="schedule" class="aegis-settings-section">
							<?php $this->renderer()->render_section_header( __( 'Schedule Controls', 'aegis' ), __( 'Control visibility based on date and time.', 'aegis' ) ); ?>
							<div class="aegis-settings-grid">
								<?php $this->renderer()->render_toggle( 'schedule', 'date_time', __( 'Date & Time', 'aegis' ), __( 'Show content during specific dates and times.', 'aegis' ), $options, 'clock', Settings::OPTION ); ?>
								<?php $this->renderer()->render_toggle( 'schedule', 'days_of_week', __( 'Days of Week', 'aegis' ), __( 'Limit visibility to selected weekdays.', 'aegis' ), $options, 'calendar-alt', Settings::OPTION ); ?>
								<?php $this->renderer()->render_toggle( 'schedule', 'time_range', __( 'Daily Time Range', 'aegis' ), __( 'Limit visibility to a daily start/end time.', 'aegis' ), $options, 'clock', Settings::OPTION ); ?>
								<?php $this->renderer()->render_toggle( 'schedule', 'timezone', __( 'Timezone', 'aegis' ), __( 'Use a custom timezone for schedule rules.', 'aegis' ), $options, 'admin-site-alt3', Settings::OPTION ); ?>
							</div>
						</section>

						<section id="image-source" class="aegis-settings-section">
							<?php $this->renderer()->render_section_header( __( 'Image Source Order', 'aegis' ), __( 'Choose which image to display in Post Featured Image blocks instead of the default featured image.', 'aegis' ) ); ?>
							<div class="aegis-settings-grid">
								<?php $this->renderer()->render_toggle( 'image_source', 'image_source_order', __( 'Content Image', 'aegis' ), __( 'Use an image from post content by position, with fallback chain support.', 'aegis' ), $options, 'format-image', Settings::OPTION ); ?>
								<?php $this->renderer()->render_toggle( 'image_source', 'acf', __( 'ACF Image Field', 'aegis' ), __( 'Use an Advanced Custom Fields image field as the featured image source.', 'aegis' ), $options, 'database', Settings::OPTION ); ?>
								<?php $this->renderer()->render_toggle( 'image_source', 'metabox', __( 'Meta Box Image Field', 'aegis' ), __( 'Use a Meta Box image field as the featured image source.', 'aegis' ), $options, 'archive', Settings::OPTION ); ?>
							</div>
						</section>

						<?php do_action( 'aegis_admin_conditionals_sections' ); ?>

						<div class="aegis-settings-footer">
							<?php submit_button( __( 'Save Settings', 'aegis' ), 'primary', 'submit', false ); ?>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
		<?php
	}
}
