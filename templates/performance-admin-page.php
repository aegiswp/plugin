<?php
/**
 * Performance admin page markup.
 *
 * @package Aegis\Plugin\Performance
 * @var array<string, bool>          $options
 * @var \Aegis\Plugin\Admin\Renderer $renderer
 */

declare( strict_types=1 );

use Aegis\Plugin\Blocks\Settings as BlocksSettings;
use Aegis\Plugin\Integrations\WooCommerce as WooCommerceHelper;
use function __;
use function admin_url;
use function esc_attr_e;
use function esc_html__;
use function esc_html_e;
use function esc_url;
use function submit_button;
use function wp_parse_args;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$options         = wp_parse_args( $options, BlocksSettings::DEFAULTS );
$woo_unavailable = ! WooCommerceHelper::is_plugin_active();

?>
			<div class="aegis-settings-wrap">
				<h1 class="screen-reader-text"><?php esc_html_e( 'Performance', 'aegis' ); ?></h1>

				<?php $renderer->render_toolbar( 'performance' ); ?>

				<form method="post" action="#" class="aegis-settings-form aegis-performance-form">
					<div class="aegis-settings-layout">
						<nav class="aegis-settings-nav" aria-label="<?php esc_attr_e( 'Performance sections', 'aegis' ); ?>">
							<a href="#wordpress" class="aegis-nav-item active">
								<span class="dashicons dashicons-wordpress"></span>
								<?php esc_html_e( 'WordPress', 'aegis' ); ?>
							</a>
							<a href="#core-blocks" class="aegis-nav-item">
								<span class="dashicons dashicons-block-default"></span>
								<?php esc_html_e( 'Core Blocks', 'aegis' ); ?>
							</a>
							<a href="#woocommerce" class="aegis-nav-item">
								<?php $renderer->render_ui_icon( 'cart', 'woocommerce' ); ?>
								<?php esc_html_e( 'WooCommerce', 'aegis' ); ?>
							</a>
						</nav>

						<div class="aegis-settings-content">
							<section id="wordpress" class="aegis-settings-section active">
								<?php $renderer->render_section_header( __( 'WordPress', 'aegis' ), __( 'Optional WordPress core script cuts and head/API cleanup on the public site. Image lazy-load and LCP fetchpriority are handled by WordPress core.', 'aegis' ), true, 'wordpress' ); ?>

								<div class="aegis-settings-grid aegis-settings-grid--features">
									<?php $renderer->render_block_feature_toggle( 'perf_disable_wp_embed', __( 'Disable oEmbed Script', 'aegis' ), __( 'Remove wp-embed and oEmbed discovery links on the frontend.', 'aegis' ), $options, 'editor-code' ); ?>
									<?php $renderer->render_block_feature_toggle( 'perf_disable_dashicons', __( 'Disable Dashicons for Guests', 'aegis' ), __( 'Stop loading dashicons for visitors who are not logged in.', 'aegis' ), $options, 'admin-appearance' ); ?>
									<?php $renderer->render_block_feature_toggle( 'perf_disable_frontend_heartbeat', __( 'Disable Frontend Heartbeat', 'aegis' ), __( 'Stop loading Heartbeat API scripts for guests on the frontend.', 'aegis' ), $options, 'heart' ); ?>
									<?php $renderer->render_block_feature_toggle( 'perf_disable_xmlrpc', __( 'Disable XML-RPC', 'aegis' ), __( 'Turn off XML-RPC and remove the X-Pingback response header. Disable if a remote client still needs xmlrpc.php.', 'aegis' ), $options, 'privacy' ); ?>
									<?php $renderer->render_block_feature_toggle( 'perf_remove_rsd_link', __( 'Remove RSD Link', 'aegis' ), __( 'Remove the Really Simple Discovery link from wp_head.', 'aegis' ), $options, 'admin-links' ); ?>
									<?php $renderer->render_block_feature_toggle( 'perf_remove_shortlink', __( 'Remove Shortlink', 'aegis' ), __( 'Remove the shortlink tag from wp_head and the Link response header.', 'aegis' ), $options, 'admin-links' ); ?>
									<?php $renderer->render_block_feature_toggle( 'perf_remove_generator', __( 'Remove Generator Tag', 'aegis' ), __( 'Remove the WordPress generator meta tag and feed generator output.', 'aegis' ), $options, 'wordpress' ); ?>
									<?php $renderer->render_block_feature_toggle( 'perf_remove_rest_api_links', __( 'Remove REST API Links', 'aegis' ), __( 'Remove REST API discovery links from wp_head and headers. Does not disable the REST API.', 'aegis' ), $options, 'rest-api' ); ?>
									<?php $renderer->render_block_feature_toggle( 'perf_remove_adjacent_posts', __( 'Remove Adjacent Posts Links', 'aegis' ), __( 'Remove previous/next post rel links from wp_head.', 'aegis' ), $options, 'leftright' ); ?>
									<?php $renderer->render_block_feature_toggle( 'perf_remove_emoji', __( 'Remove Emoji Scripts', 'aegis' ), __( 'Strip WordPress emoji detection scripts, styles, DNS prefetch, feeds, and TinyMCE emoji on frontend and admin.', 'aegis' ), $options, 'smiley', true ); ?>
								</div>
							</section>

							<section id="core-blocks" class="aegis-settings-section">
								<?php $renderer->render_section_header( __( 'Core Blocks', 'aegis' ), __( 'Gates for core block performance features. Slider image lazy-load stays under Blocks → Slider.', 'aegis' ), true, 'block-default' ); ?>

								<div class="aegis-settings-grid aegis-settings-grid--features">
									<?php $renderer->render_block_feature_toggle( 'query_loop_performance', __( 'Query Loop Performance', 'aegis' ), __( 'Unlock Pro Query Loop caching, lazy-load controls, skeleton loading during AJAX pagination, hover prefetch, and query optimizations. WordPress still lazy-loads Query Loop images when this is off.', 'aegis' ), $options, 'database', true ); ?>
									<?php $renderer->render_block_feature_toggle( 'embed_facades', __( 'Embed Facades', 'aegis' ), __( 'Load YouTube and Vimeo players only after click.', 'aegis' ), $options, 'video-alt3' ); ?>
								</div>

								<div class="aegis-settings-notice">
									<p>
										<?php
										printf(
											/* translators: %s: link to Blocks → Slider */
											esc_html__( 'Slider lazy-load remains at %s.', 'aegis' ),
											'<a href="' . esc_url( admin_url( 'admin.php?page=aegis-blocks#slider' ) ) . '">' . esc_html__( 'Aegis → Blocks → Slider', 'aegis' ) . '</a>'
										);
										?>
									</p>
								</div>
							</section>

							<section id="woocommerce" class="aegis-settings-section">
								<?php
								$renderer->render_section_header(
									__( 'WooCommerce', 'aegis' ),
									__( 'Optional storefront script cuts. Uses WooCommerce plugin detection (not the Integrations → WooCommerce toggle). Leave cart fragments on if a classic header mini-cart must update on every page. Targets classic script handles; block cart/mini-cart may not use wc-cart-fragments.', 'aegis' ),
									true,
									'cart',
									'woocommerce',
									'woocommerce',
									false
								);
								?>

								<div class="aegis-settings-grid aegis-settings-grid--features">
									<?php $renderer->render_block_feature_toggle( 'perf_woo_disable_cart_fragments', __( 'Disable Cart Fragments', 'aegis' ), __( 'Stop loading cart-fragments AJAX outside shop, product, cart, checkout, and account pages.', 'aegis' ), $options, 'cart', false, $woo_unavailable ); ?>
									<?php $renderer->render_block_feature_toggle( 'perf_woo_disable_assets_elsewhere', __( 'Disable Assets Elsewhere', 'aegis' ), __( 'Dequeue WooCommerce scripts and styles on pages that are not shop, product, cart, checkout, or account.', 'aegis' ), $options, 'performance', false, $woo_unavailable ); ?>
									<?php $renderer->render_block_feature_toggle( 'perf_woo_disable_password_strength', __( 'Limit Password Strength Meter', 'aegis' ), __( 'Load the password strength script only on account and checkout form pages (not the order-received thank-you screen).', 'aegis' ), $options, 'lock', false, $woo_unavailable ); ?>
								</div>
							</section>

							<div class="aegis-settings-footer">
								<?php submit_button( __( 'Save Settings', 'aegis' ), 'primary', 'submit', false ); ?>
							</div>
						</div>
					</div>
				</form>
			</div>
