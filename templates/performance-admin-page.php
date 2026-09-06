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
use function __;
use function admin_url;
use function esc_html__;
use function esc_html_e;
use function esc_url;
use function submit_button;
use function wp_parse_args;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$options = wp_parse_args( $options, BlocksSettings::DEFAULTS );

?>
			<div class="aegis-settings-wrap">
				<h1 class="screen-reader-text"><?php esc_html_e( 'Performance', 'aegis' ); ?></h1>

				<?php $renderer->render_toolbar( 'performance' ); ?>

				<form method="post" action="#" class="aegis-settings-form aegis-performance-form">
					<div class="aegis-settings-layout aegis-settings-layout--no-nav">
						<div class="aegis-settings-content">
							<section id="wordpress" class="aegis-settings-section active">
								<?php $renderer->render_section_header( __( 'WordPress', 'aegis' ), __( 'Optional script and stylesheet cuts on the public frontend. Image lazy-load and LCP fetchpriority are handled by WordPress core.', 'aegis' ), true, 'wordpress' ); ?>

								<div class="aegis-settings-grid aegis-settings-grid--features">
									<?php $renderer->render_block_feature_toggle( 'perf_disable_wp_embed', __( 'Disable oEmbed Script', 'aegis' ), __( 'Remove wp-embed and oEmbed discovery links on the frontend.', 'aegis' ), $options, 'editor-code' ); ?>
									<?php $renderer->render_block_feature_toggle( 'perf_disable_dashicons', __( 'Disable Dashicons for Guests', 'aegis' ), __( 'Stop loading dashicons for visitors who are not logged in.', 'aegis' ), $options, 'admin-appearance' ); ?>
									<?php $renderer->render_block_feature_toggle( 'perf_reduce_heartbeat', __( 'Reduce Frontend Heartbeat', 'aegis' ), __( 'Disable Heartbeat API scripts for guests on the frontend.', 'aegis' ), $options, 'heart' ); ?>
									<?php $renderer->render_block_feature_toggle( 'perf_remove_emoji', __( 'Remove Emoji Scripts', 'aegis' ), __( 'Strip WordPress emoji detection scripts, styles, and DNS prefetch.', 'aegis' ), $options, 'smiley', true ); ?>
								</div>
							</section>

							<section id="blocks" class="aegis-settings-section">
								<?php $renderer->render_section_header( __( 'Blocks', 'aegis' ), __( 'Gates for block-level performance features. Slider image lazy-load stays under Blocks → Slider.', 'aegis' ), true, 'block-default' ); ?>

								<div class="aegis-settings-grid aegis-settings-grid--features">
									<?php $renderer->render_block_feature_toggle( 'query_loop_performance', __( 'Query Loop Performance', 'aegis' ), __( 'Unlock Pro Query Loop caching, skeleton loading during AJAX pagination, and hover prefetch. WordPress already lazy-loads Query Loop images.', 'aegis' ), $options, 'database', true ); ?>
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

							<div class="aegis-settings-footer">
								<?php submit_button( __( 'Save Settings', 'aegis' ), 'primary', 'submit', false ); ?>
							</div>
						</div>
					</div>
				</form>
			</div>
