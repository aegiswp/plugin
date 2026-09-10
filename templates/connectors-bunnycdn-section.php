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
								<?php $renderer->render_section_header( __( 'BunnyCDN', 'aegis' ), __( 'Stream hosting and CDN credentials for the Video block.', 'aegis' ), false, 'cloud', '', 'bunnycdn' ); ?>

								<?php
								$has_pro   = $renderer->is_aegis_pro_active();
								$parent_on = ! empty( $options['bunny_cdn'] );
								$extras    = array(
									'bunny_cdn_stream_library'   => array(
										'label' => __( 'Stream Library', 'aegis' ),
										'desc'  => __( 'Browse and select videos from your BunnyCDN Stream library in the block editor.', 'aegis' ),
										'icon'  => 'video-alt3',
									),
									'bunny_cdn_direct_upload'    => array(
										'label' => __( 'Direct Upload', 'aegis' ),
										'desc'  => __( 'Upload videos directly to BunnyCDN Stream from the block editor.', 'aegis' ),
										'icon'  => 'upload',
									),
									'bunny_cdn_hls_streaming'    => array(
										'label' => __( 'HLS Streaming', 'aegis' ),
										'desc'  => __( 'Adaptive bitrate HLS playback with quality switching. When off, the Stream iframe embed is used.', 'aegis' ),
										'icon'  => 'controls-play',
									),
									'bunny_cdn_ai_transcription' => array(
										'label' => __( 'AI Transcription', 'aegis' ),
										'desc'  => __( 'Automatic video transcription and captions powered by BunnyCDN Transcribe AI.', 'aegis' ),
										'icon'  => 'text',
									),
									'bunny_cdn_video_thumbnails' => array(
										'label' => __( 'Video Thumbnails', 'aegis' ),
										'desc'  => __( 'Use BunnyCDN-generated poster thumbnails on Stream video players.', 'aegis' ),
										'icon'  => 'format-image',
									),
								);
								?>

								<div class="aegis-settings-grid">
									<?php $renderer->render_integration_toggle( 'bunny_cdn', __( 'BunnyCDN', 'aegis' ), __( 'Enable BunnyCDN Stream integration for the Video block.', 'aegis' ), $options, '', 'cloud', Settings::OPTION, 'bunnycdn' ); ?>

									<?php foreach ( $extras as $extra_key => $extra_meta ) :
										$extra_locked  = ! $has_pro || ! $parent_on;
										$extra_checked = ! $extra_locked && ! empty( $options[ $extra_key ] );
										$card_class    = 'aegis-toggle-card aegis-toggle-subcard';
										if ( $extra_locked ) {
											$card_class .= ! $has_pro ? ' aegis-pro-feature aegis-toggle-disabled' : ' aegis-toggle-disabled';
										}
										?>
									<div class="<?php echo esc_attr( $card_class ); ?>">
										<div class="aegis-toggle-info">
											<div class="aegis-toggle-icon">
												<span class="dashicons dashicons-<?php echo esc_attr( $extra_meta['icon'] ); ?>"></span>
											</div>
											<div class="aegis-toggle-text">
												<h3>
													<?php echo esc_html( $extra_meta['label'] ); ?>
													<?php if ( ! $has_pro ) : ?>
													<span class="aegis-pro-badge">
														<span class="dashicons dashicons-star-filled"></span>
														<?php esc_html_e( 'Pro', 'aegis' ); ?>
													</span>
													<?php endif; ?>
												</h3>
												<p><?php echo esc_html( $extra_meta['desc'] ); ?></p>
											</div>
										</div>
										<label class="aegis-toggle">
											<input type="checkbox" name="<?php echo esc_attr( Settings::OPTION . "[{$extra_key}]" ); ?>" value="1" <?php checked( $extra_checked ); ?> <?php disabled( $extra_locked ); ?>>
											<span class="aegis-toggle-slider"></span>
										</label>
									</div>
									<?php endforeach; ?>
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
											<p><?php esc_html_e( 'Pull zone name, public hostname, and private URL token key.', 'aegis' ); ?></p>
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

									<!-- CDN Token Authentication Key -->
									<div class="aegis-api-field-row">
										<div class="aegis-api-field-info">
											<div class="aegis-api-field-icon">
												<span class="dashicons dashicons-lock"></span>
											</div>
											<div class="aegis-api-field-text">
												<label><?php esc_html_e( 'Token Authentication Key', 'aegis' ); ?></label>
												<p><?php esc_html_e( 'Pull zone URL token authentication key for private Stream playback. Found under CDN → Pull Zone → Security.', 'aegis' ); ?></p>
											</div>
										</div>
										<div class="aegis-api-field-input">
											<input type="password" 
												name="<?php echo esc_attr( Settings::BUNNYCDN_OPTION . '[cdn_token_auth_key]' ); ?>" 
												value="<?php echo esc_attr( Secrets::mask( $bunnycdn_settings['cdn_token_auth_key'] ?? '' ) ); ?>" 
												class="aegis-bunnycdn-field"
												placeholder="<?php esc_attr_e( 'Enter Token Auth Key', 'aegis' ); ?>"
												<?php disabled( ! $has_pro ); ?>>
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
												<p><?php esc_html_e( 'Stream library API key (AccessKey) for upload and library requests. Found in Stream → Library → API.', 'aegis' ); ?></p>
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
												<label><?php esc_html_e( 'Webhook Signing Secret', 'aegis' ); ?></label>
												<p><?php esc_html_e( 'Your Stream library Read-Only API key. Used to verify BunnyCDN HMAC webhook signatures (X-BunnyStream-Signature). Required for secure webhooks.', 'aegis' ); ?></p>
											</div>
										</div>
										<div class="aegis-api-field-input">
											<input type="password" 
												name="<?php echo esc_attr( Settings::BUNNYCDN_OPTION . '[webhook_secret]' ); ?>" 
												value="<?php echo esc_attr( Secrets::mask( $bunnycdn_settings['webhook_secret'] ?? '' ) ); ?>" 
												class="aegis-bunnycdn-field"
												placeholder="<?php esc_attr_e( 'Enter Read-Only API Key', 'aegis' ); ?>"
												<?php disabled( ! $has_pro ); ?>>
										</div>
									</div>

									<!-- Actions -->
									<div class="aegis-api-config-footer">
										<?php if ( $has_pro ) : ?>
											<?php $has_api_key = ( $bunnycdn_settings['api_key'] ?? '' ) !== ''; ?>
										<button type="button" class="button button-primary aegis-save-bunnycdn" <?php disabled( ! $has_api_key ); ?>>
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
