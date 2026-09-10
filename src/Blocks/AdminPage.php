<?php
/**
 * Blocks admin dashboard page.
 *
 * @package Aegis\Plugin\Blocks
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Blocks;

use function __;
use function add_action;
use function add_filter;
use function add_submenu_page;
use function admin_url;
use function do_action;
use function esc_html__;
use function esc_html_e;
use function get_option;
use function register_setting;
use function settings_fields;
use function sprintf;
use function submit_button;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and renders the Blocks admin page.
 */
final class AdminPage {

	/** @var \Aegis\Plugin\Admin\Renderer|null */
	private $renderer = null;

	private SettingsController $controller;

	public function __construct() {
		$this->controller = new SettingsController();
	}

	private function renderer(): \Aegis\Plugin\Admin\Renderer {
		if ( null === $this->renderer ) {
			$this->renderer = new \Aegis\Plugin\Admin\Renderer();
		}

		return $this->renderer;
	}

	public function init(): void {
		add_action( 'admin_menu', [ $this, 'register_menu' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'wp_ajax_aegis_save_blocks', [ $this->controller, 'ajax_save_blocks' ] );
		add_filter( 'aegis_admin_tabs', [ $this, 'register_admin_tab' ] );
	}

	/**
	 * @param array<string, array{label: string, url: string}> $tabs Registered tabs.
	 * @return array<string, array{label: string, url: string}>
	 */
	public function register_admin_tab( array $tabs ): array {
		$tabs['blocks'] = array(
			'label' => __( 'Blocks', 'aegis' ),
			'url'   => admin_url( 'admin.php?page=aegis-blocks' ),
		);

		return $tabs;
	}

	public function register_menu(): void {
		add_submenu_page(
			'aegis-dashboard',
			__( 'Blocks', 'aegis' ),
			__( 'Blocks', 'aegis' ),
			'manage_options',
			'aegis-blocks',
			[ $this, 'render' ]
		);
	}

	public function register_settings(): void {
		register_setting(
			'aegis_blocks_group',
			Settings::OPTION,
			[
				'type'              => 'array',
				'sanitize_callback' => [ Settings::class, 'sanitize' ],
				'default'           => Settings::DEFAULTS,
			]
		);
	}

	public function render(): void {
		$options = Settings::get_settings();
		?>
				<div class="wrap aegis-admin-page">
					<?php do_action( 'aegis_admin_before_blocks_page' ); ?>
		
					<div class="aegis-settings-wrap">
						<h1 class="screen-reader-text"><?php esc_html_e( 'Blocks', 'aegis' ); ?></h1>
		
						<?php $this->renderer()->render_toolbar('blocks'); ?>
		
						<form method="post" action="#" class="aegis-settings-form aegis-blocks-form">
							<?php settings_fields('aegis_blocks_group'); ?>
		
							<div class="aegis-settings-layout">
								<nav class="aegis-settings-nav">
									<a href="#accordion" class="aegis-nav-item active">
										<span class="dashicons dashicons-list-view"></span>
										<?php esc_html_e('Accordion', 'aegis'); ?>
									</a>
									<a href="#countdown" class="aegis-nav-item">
										<span class="dashicons dashicons-clock"></span>
										<?php esc_html_e('Countdown', 'aegis'); ?>
									</a>
									<a href="#counter" class="aegis-nav-item">
										<span class="dashicons dashicons-editor-ol"></span>
										<?php esc_html_e('Counter', 'aegis'); ?>
									</a>
									<a href="#icon" class="aegis-nav-item">
										<span class="dashicons dashicons-star-filled"></span>
										<?php esc_html_e('Icon', 'aegis'); ?>
									</a>
									<a href="#image-compare" class="aegis-nav-item">
										<span class="dashicons dashicons-image-flip-horizontal"></span>
										<?php esc_html_e('Image Compare', 'aegis'); ?>
									</a>
									<a href="#image-lightbox" class="aegis-nav-item">
										<span class="dashicons dashicons-format-image"></span>
										<?php esc_html_e('Image Lightbox', 'aegis'); ?>
									</a>
									<a href="#map" class="aegis-nav-item">
										<span class="dashicons dashicons-location"></span>
										<?php esc_html_e('Map', 'aegis'); ?>
									</a>
									<a href="#marquee" class="aegis-nav-item">
										<span class="dashicons dashicons-minus"></span>
										<?php esc_html_e('Marquee', 'aegis'); ?>
									</a>
									<a href="#modal" class="aegis-nav-item">
										<span class="dashicons dashicons-editor-expand"></span>
										<?php esc_html_e('Modal', 'aegis'); ?>
									</a>
									<a href="#newsletter" class="aegis-nav-item">
										<span class="dashicons dashicons-email-alt"></span>
										<?php esc_html_e('Newsletter', 'aegis'); ?>
									</a>
									<a href="#query-loop" class="aegis-nav-item">
										<span class="dashicons dashicons-database"></span>
										<?php esc_html_e('Query Loop', 'aegis'); ?>
									</a>
									<a href="#related-posts" class="aegis-nav-item">
										<span class="dashicons dashicons-admin-links"></span>
										<?php esc_html_e('Related Posts', 'aegis'); ?>
									</a>
									<a href="#slider" class="aegis-nav-item">
										<span class="dashicons dashicons-slides"></span>
										<?php esc_html_e('Slider', 'aegis'); ?>
									</a>
									<a href="#svg" class="aegis-nav-item">
										<span class="dashicons dashicons-superhero-alt"></span>
										<?php esc_html_e('SVG', 'aegis'); ?>
									</a>
									<a href="#toggle" class="aegis-nav-item">
										<span class="dashicons dashicons-arrow-down-alt2"></span>
										<?php esc_html_e('Toggle', 'aegis'); ?>
									</a>
									<?php /* @todo Uncomment for v1.0.0 release.
									<a href="#global-styles" class="aegis-nav-item">
										<span class="dashicons dashicons-screenoptions"></span>
										<?php esc_html_e('Utilities', 'aegis'); ?>
									</a>
									*/ ?>
									<a href="#video-block" class="aegis-nav-item">
										<span class="dashicons dashicons-video-alt3"></span>
										<?php esc_html_e('Video', 'aegis'); ?>
									</a>
								</nav>
		
								<div class="aegis-settings-content">
									<!-- Accordion Section -->
									<section id="accordion" class="aegis-settings-section active">
										<?php $this->renderer()->render_section_header(__('Accordion Block', 'aegis'), __('List block variation that transforms lists into semantic accordions.', 'aegis'), true, 'list-view'); ?>
										<div class="aegis-settings-section__content">
											<div class="aegis-settings-grid aegis-settings-grid--features">
												<?php $this->renderer()->render_block_feature_toggle('accordion_open_first', __('Open First Item', 'aegis'), __('Automatically expand the first accordion item on load.', 'aegis'), $options, 'arrow-down-alt2'); ?>
												<?php $this->renderer()->render_block_feature_toggle('accordion_open_all', __('Open All Items', 'aegis'), __('Expand all accordion items by default.', 'aegis'), $options, 'editor-expand'); ?>
												<?php $this->renderer()->render_block_feature_toggle('accordion_icon', __('Toggle Icon', 'aegis'), __('Show expand/collapse icon indicator.', 'aegis'), $options, 'plus-alt'); ?>
												<?php $this->renderer()->render_block_feature_toggle('accordion_border', __('Border Separator', 'aegis'), __('Show border separator between title and content.', 'aegis'), $options, 'minus'); ?>
												<?php
												$seo_faq = \Aegis\Plugin\Settings\Repository::is_schema_delegated_to_seo( 'faq' );
												$faq_desc = $seo_faq
													? $this->seo_delegation_description( 'faq' )
													: __( 'Add FAQPage structured data markup for SEO.', 'aegis' );
												$this->renderer()->render_block_feature_toggle( 'accordion_faq_schema', __( 'FAQ Schema', 'aegis' ), $faq_desc, $options, 'editor-help', false, $seo_faq );
												?>
												<?php $this->renderer()->render_block_feature_toggle('accordion_single_open', __('Single Open Mode', 'aegis'), __('Close other items when one is opened.', 'aegis'), $options, 'controls-repeat', true); ?>
											</div>
										</div>
									</section>
		
									<!-- Countdown Section -->
									<section id="countdown" class="aegis-settings-section">
										<?php $this->renderer()->render_section_header(__('Countdown Block', 'aegis'), __('Countdown timer block that counts down to a specific date and time.', 'aegis'), true, 'clock'); ?>
										<div class="aegis-settings-section__content">
											<div class="aegis-settings-grid aegis-settings-grid--features">
												<?php $this->renderer()->render_block_feature_toggle('countdown_segments', __('Segment Toggles', 'aegis'), __('Show or hide individual segments (days, hours, minutes, seconds).', 'aegis'), $options, 'visibility'); ?>
												<?php $this->renderer()->render_block_feature_toggle('countdown_labels', __('Custom Labels', 'aegis'), __('Customize the text labels below each countdown segment.', 'aegis'), $options, 'editor-textcolor'); ?>
												<?php $this->renderer()->render_block_feature_toggle('countdown_separator', __('Separator Style', 'aegis'), __('Choose separator between segments: colon, dot, dash, or none.', 'aegis'), $options, 'minus'); ?>
												<?php $this->renderer()->render_block_feature_toggle('countdown_layout', __('Layout Options', 'aegis'), __('Switch between inline and stacked layout modes.', 'aegis'), $options, 'layout'); ?>
												<?php $this->renderer()->render_block_feature_toggle('countdown_expiry_message', __('Expiry Message', 'aegis'), __('Display a custom message when the countdown reaches zero.', 'aegis'), $options, 'warning'); ?>
												<?php $this->renderer()->render_block_feature_toggle('countdown_timezone', __('Timezone Control', 'aegis'), __('Choose between UTC and visitor local timezone.', 'aegis'), $options, 'admin-site-alt3'); ?>
												<?php
												$seo_event = \Aegis\Plugin\Settings\Repository::is_schema_delegated_to_seo( 'event' );
												$event_desc = $seo_event
													? $this->seo_delegation_description( 'event' )
													: __( 'Add Schema.org Event structured data for search engines.', 'aegis' );
												$this->renderer()->render_block_feature_toggle( 'countdown_schema', __( 'Schema.org Event', 'aegis' ), $event_desc, $options, 'editor-help', false, $seo_event );
												?>
												<?php $this->renderer()->render_block_feature_toggle('countdown_evergreen', __('Evergreen Countdown', 'aegis'), __('Each visitor gets their own countdown starting from their first visit.', 'aegis'), $options, 'backup', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('countdown_animation', __('Animation Styles', 'aegis'), __('Animate digit transitions when values change.', 'aegis'), $options, 'image-rotate', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('countdown_auto_restart', __('Auto-Restart', 'aegis'), __('Automatically restart the countdown after it expires.', 'aegis'), $options, 'controls-repeat', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('countdown_expiry_actions', __('Expiry Actions', 'aegis'), __('Perform actions when the countdown reaches zero (redirect, hide).', 'aegis'), $options, 'warning', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('countdown_urgency', __('Urgency Styling', 'aegis'), __('Change appearance when time is running low.', 'aegis'), $options, 'bell', true); ?>
											</div>
										</div>
									</section>
		
									<!-- Counter Section -->
									<section id="counter" class="aegis-settings-section">
										<?php $this->renderer()->render_section_header(__('Counter Block', 'aegis'), __('Paragraph block variation that animates numbers with counting effects.', 'aegis'), true, 'editor-ol'); ?>
										<div class="aegis-settings-section__content">
											<div class="aegis-settings-grid aegis-settings-grid--features">
												<?php $this->renderer()->render_block_feature_toggle('counter_prefix', __('Prefix Text', 'aegis'), __('Display text before the counter number (e.g. currency symbol).', 'aegis'), $options, 'editor-textcolor'); ?>
												<?php $this->renderer()->render_block_feature_toggle('counter_suffix', __('Suffix Text', 'aegis'), __('Display text after the counter number (e.g. percentage sign).', 'aegis'), $options, 'editor-textcolor'); ?>
												<?php $this->renderer()->render_block_feature_toggle('counter_delay', __('Start Delay', 'aegis'), __('Delay before the counting animation begins.', 'aegis'), $options, 'clock'); ?>
												<?php $this->renderer()->render_block_feature_toggle('counter_duration', __('Custom Duration', 'aegis'), __('Control the speed of the counting animation.', 'aegis'), $options, 'dashboard'); ?>
												<?php $this->renderer()->render_block_feature_toggle('counter_intersection', __('Scroll Trigger', 'aegis'), __('Start animation when the counter scrolls into view.', 'aegis'), $options, 'visibility', true); ?>
											</div>
										</div>
									</section>
		
									<!-- Icon Section -->
									<section id="icon" class="aegis-settings-section">
										<?php $this->renderer()->render_section_header(__('Icon Block', 'aegis'), __('Enhancements for the WordPress Icon block: Core Icon library collections, gradients, animation, custom SVG, gallery, and responsive sizing.', 'aegis'), true, 'star-filled'); ?>
										<div class="aegis-settings-section__content">
											<div class="aegis-settings-grid aegis-settings-grid--features">
												<?php $this->renderer()->render_block_feature_toggle('icon_gradient', __('Gradient Colors', 'aegis'), __('Apply gradient colors to icons using CSS mask technique.', 'aegis'), $options, 'art'); ?>
												<?php $this->renderer()->render_block_feature_toggle('icon_animation', __('Animations', 'aegis'), __('Add animation effects to icon elements.', 'aegis'), $options, 'image-rotate'); ?>
												<?php $this->renderer()->render_block_feature_toggle('icon_custom_svg', __('Custom SVG', 'aegis'), __('Paste custom SVG markup on the Icon block.', 'aegis'), $options, 'editor-code'); ?>
												<?php $this->renderer()->render_block_feature_toggle('icon_gallery', __('Icon Gallery', 'aegis'), __('Display every icon from the selected library set in a grid.', 'aegis'), $options, 'format-gallery'); ?>
												<?php $this->renderer()->render_block_feature_toggle('icon_responsive', __('Responsive Sizing', 'aegis'), __('Different icon sizes per breakpoint.', 'aegis'), $options, 'smartphone'); ?>
												<?php $this->renderer()->render_block_feature_toggle('icon_rest_api', __('REST API Endpoint', 'aegis'), __('Expose Aegis icon sets via REST for the button/tab picker. On WordPress 7.0 this also merges sets into the editor; WordPress 7.1+ registers collections on the Core Icon library.', 'aegis'), $options, 'rest-api', true); ?>
											</div>
										</div>
									</section>
		
									<!-- Image Compare Section -->
									<section id="image-compare" class="aegis-settings-section">
										<?php $this->renderer()->render_section_header(__('Image Compare Block', 'aegis'), __('Pro before/after image slider. Enabling any extra implies the block in the inserter. Saved blocks still render when extras are off.', 'aegis'), true, 'image-flip-horizontal'); ?>
										<div class="aegis-settings-section__content">
											<div class="aegis-settings-grid aegis-settings-grid--features">
												<?php $this->renderer()->render_block_feature_toggle('image_compare_orientation', __('Orientation', 'aegis'), __('Horizontal or vertical slider direction in the inspector.', 'aegis'), $options, 'image-flip-vertical', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('image_compare_labels', __('Before/After Labels', 'aegis'), __('Show and customize labels on the comparison slider, including show-on-hover.', 'aegis'), $options, 'editor-textcolor', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('image_compare_starting_point', __('Starting Position', 'aegis'), __('Set where the comparison handle starts (0–100%).', 'aegis'), $options, 'leftright', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('image_compare_hover_start', __('Slide on Hover', 'aegis'), __('Move the handle when the pointer enters the image.', 'aegis'), $options, 'universal-access', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('image_compare_handle', __('Circle Handle', 'aegis'), __('Use a circular handle instead of the default line.', 'aegis'), $options, 'image-filter', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('image_compare_smoothing', __('Smooth Motion', 'aegis'), __('Ease the handle as it moves. Off: library default (smooth, 100ms).', 'aegis'), $options, 'image-rotate', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('image_compare_fluid', __('Fluid Clip', 'aegis'), __('Clip the after image with CSS clip-path instead of a width crop.', 'aegis'), $options, 'leftright', true); ?>
											</div>
										</div>
									</section>

									<!-- Image Lightbox Section -->
									<section id="image-lightbox" class="aegis-settings-section">
										<?php $this->renderer()->render_section_header(__('Image Lightbox', 'aegis'), __('Enhances the native WordPress Image lightbox (Expand on click). Enable extras for grouped navigation, pinch/wheel zoom, thumbnail strips, and swipe.', 'aegis'), true, 'format-image'); ?>
										<div class="aegis-settings-section__content">
											<div class="aegis-settings-grid aegis-settings-grid--features">
												<?php $this->renderer()->render_block_feature_toggle('image_lightbox_gallery_nav', __('Gallery Navigation', 'aegis'), __('Navigate between images in the same group with prev/next arrows and keyboard.', 'aegis'), $options, 'arrow-left-alt2'); ?>
												<?php $this->renderer()->render_block_feature_toggle('image_lightbox_zoom', __('Zoom', 'aegis'), __('Double-click, scroll wheel, and pinch-to-zoom with pan support.', 'aegis'), $options, 'search'); ?>
												<?php $this->renderer()->render_block_feature_toggle('image_lightbox_thumbnails', __('Thumbnail Strip', 'aegis'), __('Show a thumbnail navigation strip for galleries with 4+ images.', 'aegis'), $options, 'format-gallery'); ?>
												<?php $this->renderer()->render_block_feature_toggle('image_lightbox_swipe', __('Swipe Gestures', 'aegis'), __('Navigate between images with touch swipe on mobile devices.', 'aegis'), $options, 'smartphone'); ?>
											</div>
										</div>
									</section>
		
									<!-- Map Section -->
									<section id="map" class="aegis-settings-section">
										<?php $this->renderer()->render_section_header(__('Map Block', 'aegis'), __('Google Maps and OpenStreetMap block with markers, styles, and interactive controls.', 'aegis'), true, 'location'); ?>
										<div class="aegis-settings-section__content">
											<div class="aegis-settings-grid aegis-settings-grid--features">
												<?php $this->renderer()->render_block_feature_toggle('map_markers', __('Multiple Markers', 'aegis'), __('Add multiple markers with title and description info windows.', 'aegis'), $options, 'location-alt'); ?>
												<?php $this->renderer()->render_block_feature_toggle('map_styles', __('Map Style Presets', 'aegis'), __('Silver, Dark, Retro, Night, and Aubergine styles, including the click-to-load preview.', 'aegis'), $options, 'art'); ?>
												<?php $this->renderer()->render_block_feature_toggle('map_controls', __('Map Controls', 'aegis'), __('Zoom, map type, street view, fullscreen, scroll-wheel, and drag controls.', 'aegis'), $options, 'admin-settings'); ?>
												<?php $this->renderer()->render_block_feature_toggle('map_osm_fallback', __('OpenStreetMap Fallback', 'aegis'), __('Use OpenStreetMap when no Google Maps API key is configured.', 'aegis'), $options, 'admin-site-alt3'); ?>
												<?php $this->renderer()->render_block_feature_toggle('map_directions', __('Directions', 'aegis'), __('Route directions with travel mode selector.', 'aegis'), $options, 'randomize', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('map_store_locator', __('Store Locator', 'aegis'), __('Search box and radius circle for nearby locations.', 'aegis'), $options, 'store', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('map_geolocation', __('Geolocation', 'aegis'), __('Show user current position on the map.', 'aegis'), $options, 'location', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('map_heatmap', __('Heatmap Layer', 'aegis'), __('Visualize density data on the map.', 'aegis'), $options, 'chart-area', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('map_drawing', __('Drawing Tools', 'aegis'), __('Polygon, circle, rectangle, and polyline overlays.', 'aegis'), $options, 'edit', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('map_kml_geojson', __('KML/GeoJSON Import', 'aegis'), __('Load external geographic data layers.', 'aegis'), $options, 'upload', true); ?>
												<?php
												$seo_local = \Aegis\Plugin\Settings\Repository::is_schema_delegated_to_seo( 'local_business' );
												$local_desc = $seo_local
													? $this->seo_delegation_description( 'local_business' )
													: __( 'Auto-generate LocalBusiness structured data.', 'aegis' );
												$this->renderer()->render_block_feature_toggle( 'map_schema', __( 'Schema.org Markup', 'aegis' ), $local_desc, $options, 'admin-site-alt3', false, $seo_local );
												?>
												<?php $this->renderer()->render_block_feature_toggle('map_dynamic_markers', __('Dynamic CPT Markers', 'aegis'), __('Pull markers from custom post types with lat/lng fields.', 'aegis'), $options, 'database', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('map_custom_styles', __('Custom Style JSON', 'aegis'), __('Paste a Google Maps style JSON array. Overrides presets, including the click-to-load preview.', 'aegis'), $options, 'editor-code', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('map_custom_icons', __('Custom Marker Icons', 'aegis'), __('Upload custom marker icons from the media library.', 'aegis'), $options, 'format-image', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('map_clustering', __('Marker Clustering', 'aegis'), __('Group nearby markers at lower zoom levels.', 'aegis'), $options, 'networking', true); ?>
											</div>
											<div class="aegis-settings-notice">
												<p>
													<strong><?php esc_html_e('Google Maps API', 'aegis'); ?></strong><br>
													<?php 
													printf(
														/* translators: %s: link to Connectors page */
														esc_html__('Configure your Google Maps API key in %s.', 'aegis'),
														'<a href="' . esc_url(admin_url('admin.php?page=aegis-connectors#maps')) . '">' . esc_html__('Connectors → Google Maps', 'aegis') . '</a>'
													);
													?>
												</p>
											</div>
										</div>
									</section>
		
									<!-- Marquee Section -->
									<section id="marquee" class="aegis-settings-section">
										<?php $this->renderer()->render_section_header(__('Marquee Block', 'aegis'), __('Group block variation that creates CSS-powered infinite scrolling banners.', 'aegis'), true, 'minus'); ?>
										<div class="aegis-settings-section__content">
											<div class="aegis-settings-grid aegis-settings-grid--features">
												<?php $this->renderer()->render_block_feature_toggle('marquee_pause_hover', __('Pause on Hover', 'aegis'), __('Pause the scrolling animation on hover and focus. Off: animation keeps running.', 'aegis'), $options, 'controls-pause'); ?>
												<?php $this->renderer()->render_block_feature_toggle('marquee_direction', __('Direction Control', 'aegis'), __('Set scroll direction (left-to-right or right-to-left). Off: always forwards.', 'aegis'), $options, 'arrow-right-alt'); ?>
												<?php $this->renderer()->render_block_feature_toggle('marquee_speed', __('Speed Control', 'aegis'), __('Set loop duration in seconds (lower is faster). Off: 60s mobile / 90s desktop.', 'aegis'), $options, 'dashboard'); ?>
												<?php $this->renderer()->render_block_feature_toggle('marquee_repeat', __('Repeat Items', 'aegis'), __('Control how many times items are cloned for seamless looping. Off: clones twice.', 'aegis'), $options, 'controls-repeat'); ?>
												<?php $this->renderer()->render_block_feature_toggle('marquee_responsive_speed', __('Responsive Speed', 'aegis'), __('Separate desktop duration from the mobile speed control. Desktop is used from 782px wide.', 'aegis'), $options, 'smartphone', true); ?>
											</div>
										</div>
									</section>
		
									<!-- Modal Section -->
									<section id="modal" class="aegis-settings-section">
										<?php $this->renderer()->render_section_header(__('Modal Block', 'aegis'), __('Popup/modal block with various trigger types.', 'aegis'), true, 'editor-expand'); ?>
										<div class="aegis-settings-section__content">
											<div class="aegis-settings-grid aegis-settings-grid--features">
												<?php $this->renderer()->render_block_feature_toggle('modal_click', __('Button Trigger', 'aegis'), __('Open modal on button click.', 'aegis'), $options, 'button'); ?>
												<?php $this->renderer()->render_block_feature_toggle('modal_icon', __('Icon Trigger', 'aegis'), __('Open modal on icon click.', 'aegis'), $options, 'admin-customizer'); ?>
												<?php $this->renderer()->render_block_feature_toggle('modal_text', __('Text Trigger', 'aegis'), __('Open modal on text link click.', 'aegis'), $options, 'editor-textcolor'); ?>
												<?php $this->renderer()->render_block_feature_toggle('modal_image', __('Image Trigger', 'aegis'), __('Open modal on image click.', 'aegis'), $options, 'format-image'); ?>
												<?php $this->renderer()->render_block_feature_toggle('modal_offcanvas', __('Off-Canvas Modes', 'aegis'), __('Left, right off-canvas and bottom sheet styles.', 'aegis'), $options, 'align-pull-left'); ?>
												<?php $this->renderer()->render_block_feature_toggle('modal_fullscreen', __('Fullscreen Mode', 'aegis'), __('Full viewport modal style.', 'aegis'), $options, 'fullscreen-alt'); ?>
												<?php $this->renderer()->render_block_feature_toggle('modal_animations', __('Animations', 'aegis'), __('Fade, slide, and zoom. Off: no enter animation.', 'aegis'), $options, 'image-rotate'); ?>
												<?php $this->renderer()->render_block_feature_toggle('modal_exit_intent', __('Exit Intent', 'aegis'), __('Open modal when user moves to leave the page.', 'aegis'), $options, 'migrate', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('modal_scroll_depth', __('Scroll Depth', 'aegis'), __('Open modal after scrolling a percentage of the page.', 'aegis'), $options, 'sort', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('modal_time_delay', __('Time Delay', 'aegis'), __('Open modal after a specified time delay.', 'aegis'), $options, 'clock', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('modal_auto_close', __('Auto Close', 'aegis'), __('Automatically close modal after delay.', 'aegis'), $options, 'dismiss', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('modal_show_once', __('Show Once', 'aegis'), __('Remember if user has seen this modal.', 'aegis'), $options, 'hidden', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('modal_device_visibility', __('Device Visibility', 'aegis'), __('Show/hide on specific devices.', 'aegis'), $options, 'smartphone', true); ?>
											</div>
										</div>
									</section>
		
									<!-- Newsletter Section -->
									<section id="newsletter" class="aegis-settings-section">
										<?php $this->renderer()->render_section_header(__('Newsletter Block', 'aegis'), __('Search block variation that transforms the search form into a newsletter signup.', 'aegis'), true, 'email-alt'); ?>
										<div class="aegis-settings-section__content">
											<div class="aegis-settings-grid aegis-settings-grid--features">
												<?php $this->renderer()->render_block_feature_toggle('newsletter_email_validation', __('Email Validation', 'aegis'), __('Validate email format on the input field before submission.', 'aegis'), $options, 'shield'); ?>
												<?php $this->renderer()->render_block_feature_toggle('newsletter_success_message', __('Success Message', 'aegis'), __('Display a confirmation message after successful signup.', 'aegis'), $options, 'yes-alt'); ?>
												<?php $this->renderer()->render_block_feature_toggle('newsletter_placeholder', __('Custom Placeholder', 'aegis'), __('Set custom placeholder text for the email input field.', 'aegis'), $options, 'editor-textcolor'); ?>
											</div>
										</div>
									</section>
		
									<!-- Query Loop Section -->
									<section id="query-loop" class="aegis-settings-section">
										<?php $this->renderer()->render_section_header(__('Query Loop', 'aegis'), __('Advanced query parameters and layout controls for the Query Loop block.', 'aegis'), true, 'database'); ?>
										<div class="aegis-settings-section__content">
											<div class="aegis-settings-grid aegis-settings-grid--features">
												<?php $this->renderer()->render_block_feature_toggle('query_loop_post_types', __('Multiple Post Types', 'aegis'), __('Query multiple post types in a single loop.', 'aegis'), $options, 'admin-post'); ?>
												<?php $this->renderer()->render_block_feature_toggle('query_loop_taxonomy', __('Taxonomy Filtering', 'aegis'), __('Filter by categories, tags, and custom taxonomies.', 'aegis'), $options, 'tag'); ?>
												<?php $this->renderer()->render_block_feature_toggle('query_loop_include_exclude', __('Include/Exclude Posts', 'aegis'), __('Manually select posts to include or exclude.', 'aegis'), $options, 'list-view'); ?>
												<?php $this->renderer()->render_block_feature_toggle('query_loop_meta_query', __('Custom Field Query', 'aegis'), __('Filter posts by custom field values.', 'aegis'), $options, 'admin-generic'); ?>
												<?php $this->renderer()->render_block_feature_toggle('query_loop_order_meta', __('Order by Custom Field', 'aegis'), __('Sort posts by custom field values.', 'aegis'), $options, 'sort'); ?>
												<?php $this->renderer()->render_block_feature_toggle('query_loop_responsive_columns', __('Responsive Columns', 'aegis'), __('Different column counts per breakpoint.', 'aegis'), $options, 'columns'); ?>
												<?php $this->renderer()->render_block_feature_toggle('query_loop_gap_controls', __('Gap Controls', 'aegis'), __('Custom row and column gap settings.', 'aegis'), $options, 'editor-expand'); ?>
												<?php $this->renderer()->render_block_feature_toggle('query_loop_featured_first', __('Featured First Post', 'aegis'), __('Make the first post span multiple columns.', 'aegis'), $options, 'star-filled'); ?>
												<?php $this->renderer()->render_block_feature_toggle('query_loop_equal_height', __('Equal Height Cards', 'aegis'), __('Force all cards to have equal height.', 'aegis'), $options, 'align-full-width'); ?>
												<?php $this->renderer()->render_block_feature_toggle('query_loop_no_results', __('No Results Template', 'aegis'), __('Custom message when no posts match query.', 'aegis'), $options, 'warning'); ?>
												<?php $this->renderer()->render_block_feature_toggle('query_loop_extended_order', __('Extended Ordering', 'aegis'), __('Random, comment count, modified date, etc.', 'aegis'), $options, 'sort'); ?>
												<?php $this->renderer()->render_block_feature_toggle('query_loop_advanced_meta', __('Advanced Meta Query', 'aegis'), __('Multiple meta queries with AND/OR relation.', 'aegis'), $options, 'filter', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('query_loop_date_query', __('Date Query', 'aegis'), __('Filter by date ranges and relative dates.', 'aegis'), $options, 'calendar-alt', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('query_loop_parent_child', __('Parent/Child Posts', 'aegis'), __('Query child pages of current or specific post.', 'aegis'), $options, 'networking', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('query_loop_acf_integration', __('ACF/MetaBox Integration', 'aegis'), __('Field picker for ACF and Meta Box plugins.', 'aegis'), $options, 'admin-plugins', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('query_loop_ajax_pagination', __('AJAX Pagination', 'aegis'), __('Load more, infinite scroll, AJAX page nav.', 'aegis'), $options, 'update', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('query_loop_frontend_filters', __('Frontend Filters', 'aegis'), __('Taxonomy, search, and sort filters.', 'aegis'), $options, 'filter', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('query_loop_masonry_layout', __('Masonry Layout', 'aegis'), __('CSS and JS masonry grid with animations.', 'aegis'), $options, 'grid-view', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('query_loop_carousel_layout', __('Carousel Layout', 'aegis'), __('Slider with navigation, pagination, autoplay.', 'aegis'), $options, 'slides', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('query_loop_woocommerce', __('WooCommerce Integration', 'aegis'), __('Product filtering, sorting, and display options.', 'aegis'), $options, 'cart', true); ?>
											</div>
										</div>
									</section>

									<!-- Related Posts Section -->
									<section id="related-posts" class="aegis-settings-section">
										<?php $this->renderer()->render_section_header(__('Related Posts Block', 'aegis'), __('Display posts related to the current content by shared taxonomy terms.', 'aegis'), true, 'admin-links'); ?>
										<div class="aegis-settings-section__content">
											<div class="aegis-settings-grid aegis-settings-grid--features">
												<?php $this->renderer()->render_block_feature_toggle('related_posts_taxonomy_source', __('Related By', 'aegis'), __('Choose category, tag, author, or auto-detect from the current post.', 'aegis'), $options, 'tag'); ?>
												<?php $this->renderer()->render_block_feature_toggle('related_posts_orderby', __('Order By', 'aegis'), __('Order related posts by date, title, or randomly.', 'aegis'), $options, 'sort'); ?>
												<?php $this->renderer()->render_block_feature_toggle('related_posts_fallback', __('Fallback Behavior', 'aegis'), __('Show latest posts or hide block when no related matches found.', 'aegis'), $options, 'backup'); ?>
												<?php $this->renderer()->render_block_feature_toggle('related_posts_style_variants', __('Style Variants', 'aegis'), __('Grid, list, cards, and minimal layout options.', 'aegis'), $options, 'layout'); ?>
												<?php $this->renderer()->render_block_feature_toggle('related_posts_excerpt_length', __('Excerpt Length', 'aegis'), __('Custom excerpt word count for related post summaries.', 'aegis'), $options, 'editor-textcolor'); ?>
												<?php $this->renderer()->render_block_feature_toggle('related_posts_image_ratio', __('Image Aspect Ratio', 'aegis'), __('Custom featured image aspect ratio for related posts.', 'aegis'), $options, 'image-crop'); ?>
											</div>
										</div>
									</section>
		
									<!-- Slider Section -->
									<section id="slider" class="aegis-settings-section">
										<?php $this->renderer()->render_section_header(__('Slider Block', 'aegis'), __('Slider/carousel block with multiple navigation options.', 'aegis'), true, 'slides'); ?>
										<div class="aegis-settings-section__content">
											<div class="aegis-settings-grid aegis-settings-grid--features">
												<?php $this->renderer()->render_block_feature_toggle('slider_slide', __('Slide Effect', 'aegis'), __('Basic slide transition between slides.', 'aegis'), $options, 'slides'); ?>
												<?php $this->renderer()->render_block_feature_toggle('slider_fade', __('Fade Effect', 'aegis'), __('Fade transition between slides.', 'aegis'), $options, 'visibility'); ?>
												<?php $this->renderer()->render_block_feature_toggle('slider_navigation', __('Navigation Arrows', 'aegis'), __('Show previous/next navigation arrows.', 'aegis'), $options, 'arrow-left-alt2'); ?>
												<?php $this->renderer()->render_block_feature_toggle('slider_pagination', __('Pagination Dots', 'aegis'), __('Show pagination dots below the slider.', 'aegis'), $options, 'marker'); ?>
												<?php $this->renderer()->render_block_feature_toggle('slider_loop', __('Infinite Loop', 'aegis'), __('Loop slides infinitely.', 'aegis'), $options, 'controls-repeat'); ?>
												<?php $this->renderer()->render_block_feature_toggle('slider_keyboard', __('Keyboard Navigation', 'aegis'), __('Navigate slides with arrow keys.', 'aegis'), $options, 'laptop'); ?>
												<?php $this->renderer()->render_block_feature_toggle('slider_responsive', __('Responsive Slides', 'aegis'), __('Different slides per view on devices.', 'aegis'), $options, 'smartphone'); ?>
												<?php $this->renderer()->render_block_feature_toggle('slider_autoplay', __('Autoplay', 'aegis'), __('Auto-advance slides with configurable delay.', 'aegis'), $options, 'controls-play', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('slider_effects', __('Advanced Effects', 'aegis'), __('Cube, coverflow, flip, cards, creative effects.', 'aegis'), $options, 'image-rotate', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('slider_thumbnails', __('Thumbnail Navigation', 'aegis'), __('Thumbnail strip for slide navigation.', 'aegis'), $options, 'format-gallery', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('slider_lightbox', __('Lightbox Mode', 'aegis'), __('Open slides in fullscreen lightbox.', 'aegis'), $options, 'fullscreen-alt', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('slider_mousewheel', __('Mousewheel Control', 'aegis'), __('Navigate slides with mouse scroll.', 'aegis'), $options, 'arrow-down-alt', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('slider_aspect_ratio', __('Aspect Ratio Lock', 'aegis'), __('Lock to specific aspect ratios.', 'aegis'), $options, 'image-crop', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('slider_lazy_load', __('Lazy Loading', 'aegis'), __('Performance optimization for images.', 'aegis'), $options, 'performance', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('slider_arrow_styles', __('Custom Arrow Styles', 'aegis'), __('Minimal, rounded, square arrow styles.', 'aegis'), $options, 'admin-customizer', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('slider_dot_styles', __('Custom Dot Styles', 'aegis'), __('Line, dash, fraction pagination styles.', 'aegis'), $options, 'ellipsis', true); ?>
											</div>
										</div>
									</section>
		
									<!-- SVG Section -->
									<section id="svg" class="aegis-settings-section">
										<?php $this->renderer()->render_section_header(__('SVG', 'aegis'), __('Image block variation for inline SVG logos and illustrations. Not the Icon block. Media Library SVG uploads are at Aegis → Settings.', 'aegis'), true, 'superhero-alt'); ?>
										<div class="aegis-settings-section__content">
											<div class="aegis-settings-grid aegis-settings-grid--features">
												<?php $this->renderer()->render_block_feature_toggle('svg_markup', __('Paste Markup', 'aegis'), __('Insert the SVG Image variation and paste inline SVG for logos and illustrations. Not the Icon block.', 'aegis'), $options, 'editor-code'); ?>
												<?php $this->renderer()->render_block_feature_toggle('svg_mask', __('Mask Mode', 'aegis'), __('Render the pasted SVG as a CSS mask so it follows the text color.', 'aegis'), $options, 'art'); ?>
												<?php $this->renderer()->render_block_feature_toggle('svg_onclick', __('Onclick Action', 'aegis'), __('Keep the Image onclick handler on the inlined SVG after the img tag is removed.', 'aegis'), $options, 'admin-links'); ?>
												<?php $this->renderer()->render_block_feature_toggle('svg_inline', __('Inline SVG in Text', 'aegis'), __('Rich-text format: insert inline SVG into paragraphs. Converts CSS-masked images to real SVG on the front end.', 'aegis'), $options, 'editor-italic'); ?>
												<?php $this->renderer()->render_block_feature_toggle('svg_inline_file', __('Inline SVG Files', 'aegis'), __('Replace Image, Button, Site Logo, and Featured Image tags that point at .svg files with inline SVG markup.', 'aegis'), $options, 'media-default'); ?>
											</div>
										</div>
									</section>
		
									<!-- Toggle Section -->
									<section id="toggle" class="aegis-settings-section">
										<?php $this->renderer()->render_section_header(__('Toggle Block', 'aegis'), __('Content switcher with two labeled views (not an accordion). Use Accordion List for FAQ sections.', 'aegis'), true, 'image-flip-horizontal'); ?>
										<div class="aegis-settings-section__content">
											<div class="aegis-settings-grid aegis-settings-grid--features">
												<?php $this->renderer()->render_block_feature_toggle('toggle_pill', __('Pill Style', 'aegis'), __('Rounded pill switcher between the two views.', 'aegis'), $options, 'button'); ?>
												<?php $this->renderer()->render_block_feature_toggle('toggle_switch', __('Switch Style', 'aegis'), __('On/off switch between the two views.', 'aegis'), $options, 'controls-repeat'); ?>
												<?php $this->renderer()->render_block_feature_toggle('toggle_buttons', __('Button Style', 'aegis'), __('Separate buttons for each view.', 'aegis'), $options, 'button'); ?>
												<?php $this->renderer()->render_block_feature_toggle('toggle_position', __('Position Control', 'aegis'), __('Align the switcher left, center, or right.', 'aegis'), $options, 'align-center'); ?>
												<?php $this->renderer()->render_block_feature_toggle('toggle_labels', __('Custom Labels', 'aegis'), __('Edit the primary and secondary switcher labels.', 'aegis'), $options, 'editor-textcolor'); ?>
												<?php $this->renderer()->render_block_feature_toggle('toggle_url_sync', __('URL Sync', 'aegis'), __('Sync the active view with a URL parameter.', 'aegis'), $options, 'admin-links', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('toggle_persist', __('State Persistence', 'aegis'), __('Remember the visitor’s last view.', 'aegis'), $options, 'database', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('toggle_animations', __('Animations', 'aegis'), __('Fade, slide, scale, or flip when switching views.', 'aegis'), $options, 'image-rotate', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('toggle_nested', __('Nested Toggles', 'aegis'), __('Allow a Toggle switcher inside Toggle Content.', 'aegis'), $options, 'networking', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('toggle_conditional', __('Conditional Visibility', 'aegis'), __('Show or hide other elements based on the active view.', 'aegis'), $options, 'hidden', true); ?>
											</div>
										</div>
									</section>
		
									<?php /* @todo Uncomment for v1.0.0 release.
									<!-- Utilities Section -->
									<section id="global-styles" class="aegis-settings-section">
										<?php $this->renderer()->render_section_header(__('Utilities', 'aegis'), __('Utility CSS classes for consistent styling across blocks.', 'aegis'), true, 'screenoptions'); ?>
										<div class="aegis-settings-section__content">
											<div class="aegis-settings-grid aegis-settings-grid--features">
												<?php $this->renderer()->render_block_feature_toggle('global_styles_spacing', __('Spacing Classes', 'aegis'), __('Padding, margin, and gap utilities.', 'aegis'), $options, 'editor-expand'); ?>
												<?php $this->renderer()->render_block_feature_toggle('global_styles_typography', __('Typography Classes', 'aegis'), __('Font size, weight, and text utilities.', 'aegis'), $options, 'editor-textcolor'); ?>
												<?php $this->renderer()->render_block_feature_toggle('global_styles_layout', __('Layout Classes', 'aegis'), __('Flexbox, grid, and display utilities.', 'aegis'), $options, 'layout'); ?>
												<?php $this->renderer()->render_block_feature_toggle('global_styles_effects', __('Effects Classes', 'aegis'), __('Shadows, borders, and opacity utilities.', 'aegis'), $options, 'admin-appearance'); ?>
												<?php $this->renderer()->render_block_feature_toggle('global_styles_create', __('Create Custom Classes', 'aegis'), __('Create reusable CSS classes with visual editor.', 'aegis'), $options, 'plus-alt', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('global_styles_library', __('Class Library', 'aegis'), __('Manage and organize custom global classes.', 'aegis'), $options, 'portfolio', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('global_styles_transfer', __('Transfer Local to Global', 'aegis'), __('Convert block styles to reusable classes.', 'aegis'), $options, 'migrate', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('global_styles_export', __('Import/Export', 'aegis'), __('Export and import class libraries.', 'aegis'), $options, 'download', true); ?>
											</div>
										</div>
									</section>
									*/ ?>
		
									<!-- Video Section -->
									<section class="aegis-settings-section" id="video-block">
										<?php $this->renderer()->render_section_header(__('Video Block', 'aegis'), __('Enhanced video player with modern controls and features.', 'aegis'), true, 'video-alt3'); ?>
										<div class="aegis-settings-section__content">
											<div class="aegis-settings-grid aegis-settings-grid--features">
												<?php $this->renderer()->render_block_feature_toggle('video_custom_player', __('Custom Player', 'aegis'), __('Modern player with custom controls.', 'aegis'), $options, 'controls-play'); ?>
												<?php $this->renderer()->render_block_feature_toggle('video_theater_mode', __('Theater Mode', 'aegis'), __('Requires Custom Player. Expand video to viewport width.', 'aegis'), $options, 'fullscreen-alt'); ?>
												<?php $this->renderer()->render_block_feature_toggle('video_keyboard_shortcuts', __('Keyboard Shortcuts', 'aegis'), __('Requires Custom Player. Space, arrows, M, F keyboard controls.', 'aegis'), $options, 'editor-textcolor'); ?>
												<?php
												$seo_video = \Aegis\Plugin\Settings\Repository::is_schema_delegated_to_seo( 'video' );
												$video_schema_desc = $seo_video
													? $this->seo_delegation_description( 'video' )
													: __( 'VideoObject SEO schema for search engines.', 'aegis' );
												$this->renderer()->render_block_feature_toggle( 'video_schema_markup', __( 'Schema Markup', 'aegis' ), $video_schema_desc, $options, 'admin-site-alt3', false, $seo_video );
												?>
												<?php $this->renderer()->render_block_feature_toggle('video_ambient_light', __('Ambient Light', 'aegis'), __('Glow effect behind video based on colors.', 'aegis'), $options, 'lightbulb', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('video_thumbnail_preview', __('Thumbnail Preview', 'aegis'), __('Show preview when hovering progress bar.', 'aegis'), $options, 'format-image', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('video_touch_gestures', __('Touch Gestures', 'aegis'), __('Double-tap seek, swipe volume controls.', 'aegis'), $options, 'smartphone', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('video_playlists', __('Playlists', 'aegis'), __('Multi-video playlists with autoplay.', 'aegis'), $options, 'playlist-video', true); ?>
												<?php
												$seo_handles_sitemap = \Aegis\Plugin\Settings\Repository::is_video_sitemap_delegated_to_seo();
												$active_seo = \Aegis\Plugin\Seo\Manager::get_active_slug();
												if ( $seo_handles_sitemap ) {
													$sitemap_desc = $this->seo_delegation_description( 'video_sitemap' );
												} elseif ( $active_seo === null ) {
													$sitemap_desc = __( 'Adds videos to Rank Math, Yoast, AIOSEO, or SEOPress sitemaps. Enable an SEO plugin at Aegis → Integrations first.', 'aegis' );
												} else {
													$sitemap_desc = __( 'Include core/video blocks in the active SEO plugin sitemap.', 'aegis' );
												}
												$this->renderer()->render_block_feature_toggle( 'video_sitemap', __( 'Video Sitemap', 'aegis' ), $sitemap_desc, $options, 'networking', true, $seo_handles_sitemap );
												?>
												<?php $this->renderer()->render_block_feature_toggle('video_sticky_player', __('Sticky Player', 'aegis'), __('Floating video when scrolling past.', 'aegis'), $options, 'sticky', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('video_focus_mode', __('Focus Mode', 'aegis'), __('Dim background when video plays.', 'aegis'), $options, 'visibility', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('video_save_progress', __('Save Progress', 'aegis'), __('Remember playback position.', 'aegis'), $options, 'backup', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('video_chapters', __('Chapters', 'aegis'), __('Video chapters with navigation.', 'aegis'), $options, 'editor-ol', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('video_email_capture', __('Email Capture', 'aegis'), __('Collect emails during playback.', 'aegis'), $options, 'email', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('video_analytics', __('Analytics', 'aegis'), __('Track video engagement metrics.', 'aegis'), $options, 'chart-bar', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('video_multi_audio', __('Multi-Audio', 'aegis'), __('Multiple audio track support.', 'aegis'), $options, 'format-audio', true); ?>
												<?php $this->renderer()->render_block_feature_toggle('video_privacy', __('Privacy Controls', 'aegis'), __('Private videos, watermarks, expiring URLs.', 'aegis'), $options, 'lock', true); ?>
											</div>
											<div class="aegis-settings-notice">
												<p>
													<strong><?php esc_html_e('BunnyCDN Integration', 'aegis'); ?></strong><br>
													<?php 
													printf(
														/* translators: %s: link to Connectors page */
														esc_html__('Configure BunnyCDN features for cloud video hosting in %s.', 'aegis'),
														'<a href="' . esc_url(admin_url('admin.php?page=aegis-connectors#bunnycdn')) . '">' . esc_html__('Connectors → BunnyCDN', 'aegis') . '</a>'
													);
													?>
												</p>
											</div>
										</div>
									</section>
		
									<div class="aegis-settings-footer">
										<?php submit_button(__('Save Settings', 'aegis'), 'primary', 'submit', false); ?>
									</div>
								</div>
							</div>
						</form>
					</div>
		</div>
		<?php
	}

	/**
	 * Description for block toggles when schema or sitemap is delegated to the active SEO plugin.
	 *
	 * @param string $type faq|event|local_business|video|video_sitemap
	 */
	private function seo_delegation_description( string $type ): string {
		$slug        = \Aegis\Plugin\Seo\Manager::get_active_slug();
		$integration = $slug !== null ? \Aegis\Plugin\Integrations\Registry::get( $slug ) : null;
		$plugin_name = $integration['label'] ?? esc_html__( 'your SEO plugin', 'aegis' );

		$messages = [
			/* translators: %s: SEO plugin name */
			'faq'            => __( '%s handles FAQ Schema. Disable in Integrations to use built-in schema.', 'aegis' ),
			/* translators: %s: SEO plugin name */
			'event'          => __( '%s handles Event Schema. Disable in Integrations to use built-in schema.', 'aegis' ),
			/* translators: %s: SEO plugin name */
			'local_business' => __( '%s handles Local Business Schema. Disable in Integrations to use built-in schema.', 'aegis' ),
			/* translators: %s: SEO plugin name */
			'video'          => __( '%s handles Video Schema. Disable in Integrations to use built-in schema.', 'aegis' ),
			/* translators: %s: SEO plugin name */
			'video_sitemap'  => __( 'Video sitemap is handled by %s. Disable Video Sitemap in Integrations to control it from this extra.', 'aegis' ),
		];

		return sprintf( $messages[ $type ] ?? $messages['faq'], $plugin_name );
	}
}
