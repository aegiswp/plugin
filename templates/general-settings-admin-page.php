<?php
/**
 * General settings admin page markup.
 *
 * @package Aegis\Plugin\General
 * @var array<string, bool>          $options
 * @var \Aegis\Plugin\Admin\Renderer $renderer
 */

declare( strict_types=1 );

use Aegis\Plugin\General\Settings;
use function __;
use function checked;
use function esc_attr;
use function esc_html_e;
use function settings_fields;
use function submit_button;
use function wp_parse_args;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$options = wp_parse_args( $options, Settings::DEFAULTS );

?>
			<div class="aegis-settings-wrap">
				<h1 class="screen-reader-text"><?php esc_html_e( 'Settings', 'aegis' ); ?></h1>

				<?php $renderer->render_toolbar( 'general' ); ?>

				<form method="post" action="#" class="aegis-settings-form aegis-general-settings-form">
					<?php settings_fields( 'aegis_general_settings_group' ); ?>

					<div class="aegis-settings-layout aegis-settings-layout--no-nav">
						<div class="aegis-settings-content">
							<section id="media" class="aegis-settings-section active">
								<?php $renderer->render_section_header( __( 'Media', 'aegis' ), __( 'Configure media upload and handling features.', 'aegis' ), true, 'format-image' ); ?>

								<div class="aegis-settings-grid">
									<div class="aegis-toggle-card">
										<div class="aegis-toggle-info">
											<div class="aegis-toggle-icon">
												<span class="dashicons dashicons-media-code"></span>
											</div>
											<div class="aegis-toggle-text">
												<h3><?php esc_html_e( 'SVG Uploads', 'aegis' ); ?></h3>
												<p><?php esc_html_e( 'Allow SVG file uploads in the Media Library. Files are sanitized on upload to remove potentially harmful code.', 'aegis' ); ?></p>
											</div>
										</div>
										<label class="aegis-toggle">
											<input type="checkbox" name="<?php echo esc_attr( Settings::OPTION . '[svg_upload]' ); ?>" value="1" <?php checked( $options['svg_upload'] ); ?>>
											<span class="aegis-toggle-slider"></span>
										</label>
									</div>

									<div class="aegis-toggle-suboptions">
										<div class="aegis-toggle-card aegis-toggle-subcard">
											<div class="aegis-toggle-info">
												<div class="aegis-toggle-icon">
													<span class="dashicons dashicons-art"></span>
												</div>
												<div class="aegis-toggle-text">
													<h3><?php esc_html_e( 'Strip Colors', 'aegis' ); ?></h3>
													<p><?php esc_html_e( 'Remove fill, stroke, and color attributes on upload and set fill to currentColor, allowing SVGs to inherit color from CSS.', 'aegis' ); ?></p>
												</div>
											</div>
											<label class="aegis-toggle">
												<input type="checkbox" name="<?php echo esc_attr( Settings::OPTION . '[svg_strip_colors]' ); ?>" value="1" <?php checked( $options['svg_strip_colors'] ); ?>>
												<span class="aegis-toggle-slider"></span>
											</label>
										</div>

										<div class="aegis-toggle-card aegis-toggle-subcard">
											<div class="aegis-toggle-info">
												<div class="aegis-toggle-icon">
													<span class="dashicons dashicons-image-crop"></span>
												</div>
												<div class="aegis-toggle-text">
													<h3><?php esc_html_e( 'Strip Dimensions', 'aegis' ); ?></h3>
													<p><?php esc_html_e( 'Remove width and height attributes from the root SVG element on upload. The viewBox is preserved so the SVG scales responsively.', 'aegis' ); ?></p>
												</div>
											</div>
											<label class="aegis-toggle">
												<input type="checkbox" name="<?php echo esc_attr( Settings::OPTION . '[svg_strip_dimensions]' ); ?>" value="1" <?php checked( $options['svg_strip_dimensions'] ); ?>>
												<span class="aegis-toggle-slider"></span>
											</label>
										</div>

										<div class="aegis-toggle-card aegis-toggle-subcard">
											<div class="aegis-toggle-info">
												<div class="aegis-toggle-icon">
													<span class="dashicons dashicons-editor-removeformatting"></span>
												</div>
												<div class="aegis-toggle-text">
													<h3><?php esc_html_e( 'Strip Styles', 'aegis' ); ?></h3>
													<p><?php esc_html_e( 'Remove inline style attributes from all SVG elements on upload. This helps ensure consistent styling through CSS classes.', 'aegis' ); ?></p>
												</div>
											</div>
											<label class="aegis-toggle">
												<input type="checkbox" name="<?php echo esc_attr( Settings::OPTION . '[svg_strip_styles]' ); ?>" value="1" <?php checked( $options['svg_strip_styles'] ); ?>>
												<span class="aegis-toggle-slider"></span>
											</label>
										</div>
									</div>
								</div>
							</section>

							<section id="data" class="aegis-settings-section">
								<?php $renderer->render_section_header( __( 'Data', 'aegis' ), __( 'Control what happens to Aegis settings, snippets, and Pro data when the plugins are removed.', 'aegis' ), false, 'database' ); ?>

								<div class="aegis-settings-grid">
									<div class="aegis-toggle-card">
										<div class="aegis-toggle-info">
											<div class="aegis-toggle-icon">
												<span class="dashicons dashicons-trash"></span>
											</div>
											<div class="aegis-toggle-text">
												<h3><?php esc_html_e( 'Delete data on uninstall', 'aegis' ); ?></h3>
												<p><?php esc_html_e( 'When enabled, deleting the Aegis plugin also deletes settings, snippets, hook patterns, analytics, and Pro license data. Leave this off if you may reinstall.', 'aegis' ); ?></p>
											</div>
										</div>
										<label class="aegis-toggle">
											<input type="checkbox" name="aegis_remove_data_on_uninstall" value="1" <?php checked( \Aegis\Plugin\Uninstall::should_remove_data() ); ?>>
											<span class="aegis-toggle-slider"></span>
										</label>
									</div>
								</div>

								<div class="aegis-settings-notice" style="margin-top:16px;">
									<p>
										<strong><?php esc_html_e( 'Delete data now', 'aegis' ); ?></strong><br>
										<?php esc_html_e( 'Immediately remove all Aegis data from this site without deleting the plugins. This cannot be undone.', 'aegis' ); ?>
									</p>
									<p>
										<button type="button" class="button button-secondary aegis-purge-data">
											<?php esc_html_e( 'Delete all Aegis data', 'aegis' ); ?>
										</button>
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
