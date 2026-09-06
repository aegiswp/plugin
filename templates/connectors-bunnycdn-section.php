<?php
/**
 * BunnyCDN connector settings section.
 *
 * @package Aegis\Plugin\Connectors
 * @var array<string, bool>              $options
 * @var array<string, string>            $bunnycdn_settings
 * @var \Aegis\Plugin\Admin\Renderer     $renderer
 */

declare( strict_types=1 );

use Aegis\Plugin\Integrations\Settings;
use Aegis\Plugin\Integrations\Secrets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
							<section id="bunnycdn" class="aegis-settings-section active">
								<?php $renderer->render_section_header( __( 'BunnyCDN', 'aegis' ), __( 'Stream hosting, CDN, and storage credentials for the Video block.', 'aegis' ), false ); ?>

								<?php
								$has_pro = $renderer->is_aegis_pro_active();
								?>

								<div class="aegis-settings-grid">
									<?php $renderer->render_integration_toggle( 'bunny_cdn', __( 'BunnyCDN', 'aegis' ), __( 'Enable BunnyCDN Stream integration for the Video block.', 'aegis' ), $options, '', 'cloud' ); ?>

									<?php
									// Stream Library
									$stream_key     = 'bunny_cdn_stream_library';
									$stream_checked = $options[ $stream_key ] ?? false;
									?>
									<div class="aegis-toggle-card aegis-toggle-subcard <?php echo ! $has_pro ? 'aegis-pro-feature aegis-toggle-disabled' : ''; ?>">
										<div class="aegis-toggle-info">
											<div class="aegis-toggle-icon">
												<span class="dashicons dashicons-video-alt3"></span>
											</div>
											<div class="aegis-toggle-text">
												<h3>
													<?php esc_html_e( 'Stream Library', 'aegis' ); ?>
													<?php if ( ! $has_pro ) : ?>
													<span class="aegis-pro-badge">
														<span class="dashicons dashicons-star-filled"></span>
														<?php esc_html_e( 'Pro', 'aegis' ); ?>
													</span>
													<?php endif; ?>
												</h3>
												<p><?php esc_html_e( 'Access and manage your BunnyCDN Stream video library directly from WordPress.', 'aegis' ); ?></p>
											</div>
										</div>
										<label class="aegis-toggle">
											<input type="checkbox" name="<?php echo esc_attr( Settings::OPTION . "[{$stream_key}]" ); ?>" value="1" <?php checked( $stream_checked ); ?> <?php disabled( ! $has_pro ); ?>>
											<span class="aegis-toggle-slider"></span>
										</label>
									</div>

									<?php
									// Direct Upload
									$upload_key     = 'bunny_cdn_direct_upload';
									$upload_checked = $options[ $upload_key ] ?? false;
									?>
									<div class="aegis-toggle-card aegis-toggle-subcard <?php echo ! $has_pro ? 'aegis-pro-feature aegis-toggle-disabled' : ''; ?>">
										<div class="aegis-toggle-info">
											<div class="aegis-toggle-icon">
												<span class="dashicons dashicons-upload"></span>
											</div>
											<div class="aegis-toggle-text">
												<h3>
													<?php esc_html_e( 'Direct Upload', 'aegis' ); ?>
													<?php if ( ! $has_pro ) : ?>
													<span class="aegis-pro-badge">
														<span class="dashicons dashicons-star-filled"></span>
														<?php esc_html_e( 'Pro', 'aegis' ); ?>
													</span>
													<?php endif; ?>
												</h3>
												<p><?php esc_html_e( 'Upload videos directly to BunnyCDN Stream from the block editor.', 'aegis' ); ?></p>
											</div>
										</div>
										<label class="aegis-toggle">
											<input type="checkbox" name="<?php echo esc_attr( Settings::OPTION . "[{$upload_key}]" ); ?>" value="1" <?php checked( $upload_checked ); ?> <?php disabled( ! $has_pro ); ?>>
											<span class="aegis-toggle-slider"></span>
										</label>
									</div>

									<?php
									// HLS Streaming
									$hls_key     = 'bunny_cdn_hls_streaming';
									$hls_checked = $options[ $hls_key ] ?? false;
									?>
									<div class="aegis-toggle-card aegis-toggle-subcard <?php echo ! $has_pro ? 'aegis-pro-feature aegis-toggle-disabled' : ''; ?>">
										<div class="aegis-toggle-info">
											<div class="aegis-toggle-icon">
												<span class="dashicons dashicons-controls-play"></span>
											</div>
											<div class="aegis-toggle-text">
												<h3>
													<?php esc_html_e( 'HLS Streaming', 'aegis' ); ?>
													<?php if ( ! $has_pro ) : ?>
													<span class="aegis-pro-badge">
														<span class="dashicons dashicons-star-filled"></span>
														<?php esc_html_e( 'Pro', 'aegis' ); ?>
													</span>
													<?php endif; ?>
												</h3>
												<p><?php esc_html_e( 'Enable adaptive bitrate HLS streaming for optimal playback quality.', 'aegis' ); ?></p>
											</div>
										</div>
										<label class="aegis-toggle">
											<input type="checkbox" name="<?php echo esc_attr( Settings::OPTION . "[{$hls_key}]" ); ?>" value="1" <?php checked( $hls_checked ); ?> <?php disabled( ! $has_pro ); ?>>
											<span class="aegis-toggle-slider"></span>
										</label>
									</div>

									<?php
									// AI Transcription
									$transcription_key     = 'bunny_cdn_ai_transcription';
									$transcription_checked = $options[ $transcription_key ] ?? false;
									?>
									<div class="aegis-toggle-card aegis-toggle-subcard <?php echo ! $has_pro ? 'aegis-pro-feature aegis-toggle-disabled' : ''; ?>">
										<div class="aegis-toggle-info">
											<div class="aegis-toggle-icon">
												<span class="dashicons dashicons-text"></span>
											</div>
											<div class="aegis-toggle-text">
												<h3>
													<?php esc_html_e( 'AI Transcription', 'aegis' ); ?>
													<?php if ( ! $has_pro ) : ?>
													<span class="aegis-pro-badge">
														<span class="dashicons dashicons-star-filled"></span>
														<?php esc_html_e( 'Pro', 'aegis' ); ?>
													</span>
													<?php endif; ?>
												</h3>
												<p><?php esc_html_e( 'Automatic video transcription and captions powered by AI.', 'aegis' ); ?></p>
											</div>
										</div>
										<label class="aegis-toggle">
											<input type="checkbox" name="<?php echo esc_attr( Settings::OPTION . "[{$transcription_key}]" ); ?>" value="1" <?php checked( $transcription_checked ); ?> <?php disabled( ! $has_pro ); ?>>
											<span class="aegis-toggle-slider"></span>
										</label>
									</div>

									<?php
									// Video Thumbnails
									$thumbnails_key     = 'bunny_cdn_video_thumbnails';
									$thumbnails_checked = $options[ $thumbnails_key ] ?? false;
									?>
									<div class="aegis-toggle-card aegis-toggle-subcard <?php echo ! $has_pro ? 'aegis-pro-feature aegis-toggle-disabled' : ''; ?>">
										<div class="aegis-toggle-info">
											<div class="aegis-toggle-icon">
												<span class="dashicons dashicons-format-image"></span>
											</div>
											<div class="aegis-toggle-text">
												<h3>
													<?php esc_html_e( 'Video Thumbnails', 'aegis' ); ?>
													<?php if ( ! $has_pro ) : ?>
													<span class="aegis-pro-badge">
														<span class="dashicons dashicons-star-filled"></span>
														<?php esc_html_e( 'Pro', 'aegis' ); ?>
													</span>
													<?php endif; ?>
												</h3>
												<p><?php esc_html_e( 'Auto-generate video thumbnails and preview sprites from BunnyCDN.', 'aegis' ); ?></p>
											</div>
										</div>
										<label class="aegis-toggle">
											<input type="checkbox" name="<?php echo esc_attr( Settings::OPTION . "[{$thumbnails_key}]" ); ?>" value="1" <?php checked( $thumbnails_checked ); ?> <?php disabled( ! $has_pro ); ?>>
											<span class="aegis-toggle-slider"></span>
										</label>
									</div>

									<?php
									// Video Watermark
									$watermark_key     = 'bunny_cdn_video_watermark';
									$watermark_checked = $options[ $watermark_key ] ?? false;
									?>
									<div class="aegis-toggle-card aegis-toggle-subcard <?php echo ! $has_pro ? 'aegis-pro-feature aegis-toggle-disabled' : ''; ?>">
										<div class="aegis-toggle-info">
											<div class="aegis-toggle-icon">
												<span class="dashicons dashicons-shield"></span>
											</div>
											<div class="aegis-toggle-text">
												<h3>
													<?php esc_html_e( 'Video Watermark', 'aegis' ); ?>
													<?php if ( ! $has_pro ) : ?>
													<span class="aegis-pro-badge">
														<span class="dashicons dashicons-star-filled"></span>
														<?php esc_html_e( 'Pro', 'aegis' ); ?>
													</span>
													<?php endif; ?>
												</h3>
												<p><?php esc_html_e( 'Add watermarks to videos for branding and copyright protection.', 'aegis' ); ?></p>
											</div>
										</div>
										<label class="aegis-toggle">
											<input type="checkbox" name="<?php echo esc_attr( Settings::OPTION . "[{$watermark_key}]" ); ?>" value="1" <?php checked( $watermark_checked ); ?> <?php disabled( ! $has_pro ); ?>>
											<span class="aegis-toggle-slider"></span>
										</label>
									</div>
								</div>

								<!-- BunnyCDN API Configuration -->
								<div class="aegis-api-config <?php echo ! $has_pro ? 'aegis-pro-feature aegis-api-config-disabled' : ''; ?>">
									<div class="aegis-api-config-header">
										<div class="aegis-api-config-title">
											<div class="aegis-api-config-heading">
												<span class="dashicons dashicons-admin-network"></span>
												<span><?php esc_html_e( 'BunnyCDN API', 'aegis' ); ?></span>
											</div>
											<p><?php esc_html_e( 'Account credentials used to authenticate with BunnyCDN.', 'aegis' ); ?></p>
										</div>
										<?php if ( ! $has_pro ) : ?>
										<span class="aegis-pro-badge">
											<span class="dashicons dashicons-lock"></span>
											<?php esc_html_e( 'Pro', 'aegis' ); ?>
										</span>
										<?php endif; ?>
									</div>

									<div class="aegis-api-config-body">
									<!-- Account API Key -->
									<div class="aegis-api-field-row">
										<div class="aegis-api-field-info">
											<div class="aegis-api-field-icon">
												<span class="dashicons dashicons-admin-network"></span>
											</div>
											<div class="aegis-api-field-text">
												<label><?php esc_html_e( 'Account API Key', 'aegis' ); ?></label>
												<p><?php esc_html_e( 'Found in your BunnyCDN dashboard under Account → API.', 'aegis' ); ?></p>
											</div>
										</div>
										<div class="aegis-api-field-input">
											<input type="password" 
												name="<?php echo esc_attr( Settings::BUNNYCDN_OPTION . '[api_key]' ); ?>" 
												value="<?php echo esc_attr( Secrets::mask( $bunnycdn_settings['api_key'] ) ); ?>" 
												class="aegis-bunnycdn-field"
												placeholder="<?php esc_attr_e( 'Enter API Key', 'aegis' ); ?>"
												<?php disabled( ! $has_pro ); ?>>
										</div>
									</div>
									</div>

									<div class="aegis-api-section-header">
										<div class="aegis-api-config-title">
											<div class="aegis-api-config-heading">
												<span class="dashicons dashicons-performance"></span>
												<span><?php esc_html_e( 'CDN Settings', 'aegis' ); ?></span>
											</div>
											<p><?php esc_html_e( 'Pull zone name and public CDN hostname.', 'aegis' ); ?></p>
										</div>
									</div>

									<div class="aegis-api-config-body">
									<!-- Pull Zone -->
									<div class="aegis-api-field-row">
										<div class="aegis-api-field-info">
											<div class="aegis-api-field-icon">
												<span class="dashicons dashicons-networking"></span>
											</div>
											<div class="aegis-api-field-text">
												<label><?php esc_html_e( 'Pull Zone Name', 'aegis' ); ?></label>
												<p><?php esc_html_e( 'The name of your CDN pull zone.', 'aegis' ); ?></p>
											</div>
										</div>
										<div class="aegis-api-field-input">
											<input type="text" 
												name="<?php echo esc_attr( Settings::BUNNYCDN_OPTION . '[cdn_pullzone]' ); ?>" 
												value="<?php echo esc_attr( $bunnycdn_settings['cdn_pullzone'] ); ?>" 
												class="aegis-bunnycdn-field"
												placeholder="<?php esc_attr_e( 'my-pullzone', 'aegis' ); ?>"
												<?php disabled( ! $has_pro ); ?>>
										</div>
									</div>

									<!-- CDN Hostname -->
									<div class="aegis-api-field-row">
										<div class="aegis-api-field-info">
											<div class="aegis-api-field-icon">
												<span class="dashicons dashicons-admin-site-alt3"></span>
											</div>
											<div class="aegis-api-field-text">
												<label><?php esc_html_e( 'CDN Hostname', 'aegis' ); ?></label>
												<p><?php esc_html_e( 'Your CDN hostname or custom domain.', 'aegis' ); ?></p>
											</div>
										</div>
										<div class="aegis-api-field-input">
											<input type="text" 
												name="<?php echo esc_attr( Settings::BUNNYCDN_OPTION . '[cdn_hostname]' ); ?>" 
												value="<?php echo esc_attr( $bunnycdn_settings['cdn_hostname'] ); ?>" 
												class="aegis-bunnycdn-field"
												placeholder="<?php esc_attr_e( 'cdn.example.com', 'aegis' ); ?>"
												<?php disabled( ! $has_pro ); ?>>
										</div>
									</div>
									</div>

									<div class="aegis-api-section-header">
										<div class="aegis-api-config-title">
											<div class="aegis-api-config-heading">
												<span class="dashicons dashicons-cloud"></span>
												<span><?php esc_html_e( 'Storage Settings', 'aegis' ); ?></span>
											</div>
											<p><?php esc_html_e( 'Storage zone, API key, and region.', 'aegis' ); ?></p>
										</div>
									</div>

									<div class="aegis-api-config-body">
									<!-- Storage Zone -->
									<div class="aegis-api-field-row">
										<div class="aegis-api-field-info">
											<div class="aegis-api-field-icon">
												<span class="dashicons dashicons-portfolio"></span>
											</div>
											<div class="aegis-api-field-text">
												<label><?php esc_html_e( 'Storage Zone Name', 'aegis' ); ?></label>
												<p><?php esc_html_e( 'The name of your storage zone.', 'aegis' ); ?></p>
											</div>
										</div>
										<div class="aegis-api-field-input">
											<input type="text" 
												name="<?php echo esc_attr( Settings::BUNNYCDN_OPTION . '[storage_zone]' ); ?>" 
												value="<?php echo esc_attr( $bunnycdn_settings['storage_zone'] ); ?>" 
												class="aegis-bunnycdn-field"
												placeholder="<?php esc_attr_e( 'my-storage-zone', 'aegis' ); ?>"
												<?php disabled( ! $has_pro ); ?>>
										</div>
									</div>

									<!-- Storage API Key -->
									<div class="aegis-api-field-row">
										<div class="aegis-api-field-info">
											<div class="aegis-api-field-icon">
												<span class="dashicons dashicons-lock"></span>
											</div>
											<div class="aegis-api-field-text">
												<label><?php esc_html_e( 'Storage API Key', 'aegis' ); ?></label>
												<p><?php esc_html_e( 'Storage zone password/API key.', 'aegis' ); ?></p>
											</div>
										</div>
										<div class="aegis-api-field-input">
											<input type="password" 
												name="<?php echo esc_attr( Settings::BUNNYCDN_OPTION . '[storage_api_key]' ); ?>" 
												value="<?php echo esc_attr( Secrets::mask( $bunnycdn_settings['storage_api_key'] ) ); ?>" 
												class="aegis-bunnycdn-field"
												placeholder="<?php esc_attr_e( 'Enter Storage API Key', 'aegis' ); ?>"
												<?php disabled( ! $has_pro ); ?>>
										</div>
									</div>

									<!-- Storage Region -->
									<div class="aegis-api-field-row">
										<div class="aegis-api-field-info">
											<div class="aegis-api-field-icon">
												<span class="dashicons dashicons-location-alt"></span>
											</div>
											<div class="aegis-api-field-text">
												<label><?php esc_html_e( 'Storage Region', 'aegis' ); ?></label>
												<p><?php esc_html_e( 'Select your storage zone region.', 'aegis' ); ?></p>
											</div>
										</div>
										<div class="aegis-api-field-input">
											<select name="<?php echo esc_attr( Settings::BUNNYCDN_OPTION . '[storage_region]' ); ?>" 
												class="aegis-bunnycdn-field"
												<?php disabled( ! $has_pro ); ?>>
												<option value="de" <?php selected( $bunnycdn_settings['storage_region'], 'de' ); ?>><?php esc_html_e( 'Falkenstein (DE)', 'aegis' ); ?></option>
												<option value="ny" <?php selected( $bunnycdn_settings['storage_region'], 'ny' ); ?>><?php esc_html_e( 'New York (US)', 'aegis' ); ?></option>
												<option value="la" <?php selected( $bunnycdn_settings['storage_region'], 'la' ); ?>><?php esc_html_e( 'Los Angeles (US)', 'aegis' ); ?></option>
												<option value="sg" <?php selected( $bunnycdn_settings['storage_region'], 'sg' ); ?>><?php esc_html_e( 'Singapore (SG)', 'aegis' ); ?></option>
												<option value="syd" <?php selected( $bunnycdn_settings['storage_region'], 'syd' ); ?>><?php esc_html_e( 'Sydney (AU)', 'aegis' ); ?></option>
												<option value="uk" <?php selected( $bunnycdn_settings['storage_region'], 'uk' ); ?>><?php esc_html_e( 'London (UK)', 'aegis' ); ?></option>
												<option value="se" <?php selected( $bunnycdn_settings['storage_region'], 'se' ); ?>><?php esc_html_e( 'Stockholm (SE)', 'aegis' ); ?></option>
												<option value="br" <?php selected( $bunnycdn_settings['storage_region'], 'br' ); ?>><?php esc_html_e( 'São Paulo (BR)', 'aegis' ); ?></option>
												<option value="jh" <?php selected( $bunnycdn_settings['storage_region'], 'jh' ); ?>><?php esc_html_e( 'Johannesburg (ZA)', 'aegis' ); ?></option>
											</select>
										</div>
									</div>
									</div>

									<div class="aegis-api-section-header">
										<div class="aegis-api-config-title">
											<div class="aegis-api-config-heading">
												<span class="dashicons dashicons-video-alt3"></span>
												<span><?php esc_html_e( 'Stream Settings', 'aegis' ); ?></span>
											</div>
											<p><?php esc_html_e( 'Stream library ID, API key, and webhook secret.', 'aegis' ); ?></p>
										</div>
									</div>

									<div class="aegis-api-config-body">
									<!-- Stream Library ID -->
									<div class="aegis-api-field-row">
										<div class="aegis-api-field-info">
											<div class="aegis-api-field-icon">
												<span class="dashicons dashicons-id"></span>
											</div>
											<div class="aegis-api-field-text">
												<label><?php esc_html_e( 'Stream Library ID', 'aegis' ); ?></label>
												<p><?php esc_html_e( 'Found in Stream → Library → API.', 'aegis' ); ?></p>
											</div>
										</div>
										<div class="aegis-api-field-input">
											<input type="text" 
												name="<?php echo esc_attr( Settings::BUNNYCDN_OPTION . '[stream_library_id]' ); ?>" 
												value="<?php echo esc_attr( $bunnycdn_settings['stream_library_id'] ); ?>" 
												class="aegis-bunnycdn-field"
												placeholder="<?php esc_attr_e( '12345', 'aegis' ); ?>"
												<?php disabled( ! $has_pro ); ?>>
										</div>
									</div>

									<!-- Stream API Key -->
									<div class="aegis-api-field-row">
										<div class="aegis-api-field-info">
											<div class="aegis-api-field-icon">
												<span class="dashicons dashicons-admin-network"></span>
											</div>
											<div class="aegis-api-field-text">
												<label><?php esc_html_e( 'Stream API Key', 'aegis' ); ?></label>
												<p><?php esc_html_e( 'Stream library API key for authentication.', 'aegis' ); ?></p>
											</div>
										</div>
										<div class="aegis-api-field-input">
											<input type="password" 
												name="<?php echo esc_attr( Settings::BUNNYCDN_OPTION . '[stream_api_key]' ); ?>" 
												value="<?php echo esc_attr( Secrets::mask( $bunnycdn_settings['stream_api_key'] ) ); ?>" 
												class="aegis-bunnycdn-field"
												placeholder="<?php esc_attr_e( 'Enter Stream API Key', 'aegis' ); ?>"
												<?php disabled( ! $has_pro ); ?>>
										</div>
									</div>

									<!-- Webhook Secret -->
									<div class="aegis-api-field-row">
										<div class="aegis-api-field-info">
											<div class="aegis-api-field-icon">
												<span class="dashicons dashicons-shield"></span>
											</div>
											<div class="aegis-api-field-text">
												<label><?php esc_html_e( 'Webhook Secret', 'aegis' ); ?></label>
												<p><?php esc_html_e( 'Required for transcoding webhooks. Set the same value in your BunnyCDN Stream library settings.', 'aegis' ); ?></p>
											</div>
										</div>
										<div class="aegis-api-field-input">
											<input type="password" 
												name="<?php echo esc_attr( Settings::BUNNYCDN_OPTION . '[webhook_secret]' ); ?>" 
												value="<?php echo esc_attr( Secrets::mask( $bunnycdn_settings['webhook_secret'] ?? '' ) ); ?>" 
												class="aegis-bunnycdn-field"
												placeholder="<?php esc_attr_e( 'Enter Webhook Secret', 'aegis' ); ?>"
												<?php disabled( ! $has_pro ); ?>>
										</div>
									</div>

									<!-- Actions -->
									<div class="aegis-api-config-footer">
										<?php if ( $has_pro ) : ?>
											<?php $has_api_key = ( $bunnycdn_settings['api_key'] ?? '' ) !== ''; ?>
										<button type="button" class="button button-primary aegis-save-bunnycdn">
											<span class="dashicons dashicons-saved"></span>
											<span class="aegis-button-label"><?php esc_html_e( 'Save API Settings', 'aegis' ); ?></span>
										</button>
										<button type="button" class="button aegis-test-bunnycdn" <?php disabled( ! $has_api_key ); ?>>
											<span class="dashicons dashicons-yes-alt"></span>
											<span class="aegis-button-label"><?php esc_html_e( 'Test Connection', 'aegis' ); ?></span>
										</button>
										<?php else : ?>
										<div class="aegis-api-pro-notice">
											<span class="dashicons dashicons-lock"></span>
											<?php esc_html_e( 'BunnyCDN API configuration requires Aegis Pro.', 'aegis' ); ?>
										</div>
										<?php endif; ?>
									</div>
									</div>
								</div>
							</section>
