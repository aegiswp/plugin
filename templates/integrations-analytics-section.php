<?php
/**
 * Analytics settings markup for Connectors (one section per provider).
 *
 * @package Aegis\Plugin\Analytics
 * @var array<string, mixed>             $options
 * @var bool                             $pro_active
 * @var bool                             $has_complianz
 * @var \Aegis\Plugin\Admin\Renderer     $renderer
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
							<section id="ga4" class="aegis-settings-section" data-aegis-analytics-settings>
								<div class="aegis-settings-section-header">
									<h2>
										<?php $renderer->render_ui_icon( 'chart-bar', 'google-analytics' ); ?>
										<?php esc_html_e( 'Google Analytics', 'aegis' ); ?>
									</h2>
									<p><?php esc_html_e( 'Basic page view tracking with Google Analytics 4.', 'aegis' ); ?></p>
								</div>

								<div class="aegis-settings-grid">
									<div class="aegis-toggle-card">
										<div class="aegis-toggle-info">
											<div class="aegis-toggle-icon">
												<?php $renderer->render_ui_icon( 'chart-bar', 'google-analytics' ); ?>
											</div>
											<div class="aegis-toggle-text">
												<h3><?php esc_html_e( 'Enable GA4', 'aegis' ); ?></h3>
												<p><?php esc_html_e( 'Load the Google Analytics 4 tracking script on your site.', 'aegis' ); ?></p>
											</div>
										</div>
										<label class="aegis-toggle">
											<input type="checkbox" name="aegis_analytics[ga4_enabled]" value="1" <?php checked( $options['ga4_enabled'] ); ?>>
											<span class="aegis-toggle-slider"></span>
										</label>
									</div>

									<div class="aegis-toggle-suboptions">
										<div class="aegis-api-field-row">
											<div class="aegis-api-field-info">
												<div class="aegis-api-field-icon">
													<span class="dashicons dashicons-admin-network"></span>
												</div>
												<div class="aegis-api-field-text">
													<label><?php esc_html_e( 'Measurement ID', 'aegis' ); ?></label>
													<p><?php esc_html_e( 'Your GA4 Measurement ID (e.g., G-XXXXXXXXXX).', 'aegis' ); ?></p>
												</div>
											</div>
											<div class="aegis-api-field-input">
												<input type="text"
													name="aegis_analytics[ga4_measurement_id]"
													value="<?php echo esc_attr( $options['ga4_measurement_id'] ); ?>"
													placeholder="G-XXXXXXXXXX"
													class="regular-text" />
											</div>
										</div>

										<div class="aegis-toggle-card aegis-toggle-subcard">
											<div class="aegis-toggle-info">
												<div class="aegis-toggle-icon">
													<span class="dashicons dashicons-hidden"></span>
												</div>
												<div class="aegis-toggle-text">
													<h3><?php esc_html_e( 'Anonymize IP', 'aegis' ); ?></h3>
													<p><?php esc_html_e( 'Send the legacy gtag anonymize_ip flag. GA4 already anonymizes IP addresses by default.', 'aegis' ); ?></p>
												</div>
											</div>
											<label class="aegis-toggle">
												<input type="checkbox" name="aegis_analytics[ga4_anonymize_ip]" value="1" <?php checked( $options['ga4_anonymize_ip'] ); ?>>
												<span class="aegis-toggle-slider"></span>
											</label>
										</div>
									</div>
								</div>
							</section>

							<section id="gtm" class="aegis-settings-section" data-aegis-analytics-settings>
								<div class="aegis-settings-section-header">
									<h2>
										<?php $renderer->render_ui_icon( 'tag', 'google-tag-manager' ); ?>
										<?php esc_html_e( 'Google Tag Manager', 'aegis' ); ?>
									</h2>
									<p><?php esc_html_e( 'Manage all your tags from a single container.', 'aegis' ); ?></p>
								</div>

								<div class="aegis-settings-grid">
									<div class="aegis-toggle-card">
										<div class="aegis-toggle-info">
											<div class="aegis-toggle-icon">
												<?php $renderer->render_ui_icon( 'tag', 'google-tag-manager' ); ?>
											</div>
											<div class="aegis-toggle-text">
												<h3><?php esc_html_e( 'Enable GTM', 'aegis' ); ?></h3>
												<p><?php esc_html_e( 'Load the Google Tag Manager container on your site.', 'aegis' ); ?></p>
											</div>
										</div>
										<label class="aegis-toggle">
											<input type="checkbox" name="aegis_analytics[gtm_enabled]" value="1" <?php checked( $options['gtm_enabled'] ); ?>>
											<span class="aegis-toggle-slider"></span>
										</label>
									</div>

									<div class="aegis-toggle-suboptions">
										<div class="aegis-api-field-row">
											<div class="aegis-api-field-info">
												<div class="aegis-api-field-icon">
													<span class="dashicons dashicons-admin-network"></span>
												</div>
												<div class="aegis-api-field-text">
													<label><?php esc_html_e( 'Container ID', 'aegis' ); ?></label>
													<p><?php esc_html_e( 'Your GTM Container ID (e.g., GTM-XXXXXXX).', 'aegis' ); ?></p>
												</div>
											</div>
											<div class="aegis-api-field-input">
												<input type="text"
													name="aegis_analytics[gtm_container_id]"
													value="<?php echo esc_attr( $options['gtm_container_id'] ); ?>"
													placeholder="GTM-XXXXXXX"
													class="regular-text" />
											</div>
										</div>

										<div class="aegis-toggle-card aegis-toggle-subcard <?php echo ! $pro_active ? 'aegis-pro-feature aegis-toggle-disabled' : ''; ?>">
											<div class="aegis-toggle-info">
												<div class="aegis-toggle-icon">
													<span class="dashicons dashicons-database"></span>
												</div>
												<div class="aegis-toggle-text">
													<h3>
														<?php esc_html_e( 'GTM Data Layer', 'aegis' ); ?>
														<?php if ( ! $pro_active ) : ?>
															<span class="aegis-pro-badge"><?php esc_html_e( 'Pro', 'aegis' ); ?></span>
														<?php endif; ?>
													</h3>
													<p><?php esc_html_e( 'Push page type, post metadata, and login state into dataLayer before the container loads. Consent Mode v2 (Pro) lives under Privacy and also applies to GTM-only setups.', 'aegis' ); ?></p>
												</div>
											</div>
											<label class="aegis-toggle">
												<input type="checkbox" name="aegis_analytics[gtm_data_layer]" value="1" <?php checked( $options['gtm_data_layer'] ); ?> <?php disabled( ! $pro_active ); ?>>
												<span class="aegis-toggle-slider"></span>
											</label>
										</div>
									</div>
								</div>
							</section>

							<section id="clarity" class="aegis-settings-section" data-aegis-analytics-settings>
								<div class="aegis-settings-section-header">
									<h2>
										<?php $renderer->render_ui_icon( 'visibility', 'microsoft-clarity' ); ?>
										<?php esc_html_e( 'Microsoft Clarity', 'aegis' ); ?>
									</h2>
									<p><?php esc_html_e( 'Free heatmaps and session recordings.', 'aegis' ); ?></p>
								</div>

								<div class="aegis-settings-grid">
									<div class="aegis-toggle-card">
										<div class="aegis-toggle-info">
											<div class="aegis-toggle-icon">
												<?php $renderer->render_ui_icon( 'visibility', 'microsoft-clarity' ); ?>
											</div>
											<div class="aegis-toggle-text">
												<h3><?php esc_html_e( 'Enable Clarity', 'aegis' ); ?></h3>
												<p><?php esc_html_e( 'Load the Microsoft Clarity tracking script in the page head.', 'aegis' ); ?></p>
											</div>
										</div>
										<label class="aegis-toggle">
											<input type="checkbox" name="aegis_analytics[clarity_enabled]" value="1" <?php checked( $options['clarity_enabled'] ); ?>>
											<span class="aegis-toggle-slider"></span>
										</label>
									</div>

									<div class="aegis-toggle-suboptions">
										<div class="aegis-api-field-row">
											<div class="aegis-api-field-info">
												<div class="aegis-api-field-icon">
													<span class="dashicons dashicons-admin-network"></span>
												</div>
												<div class="aegis-api-field-text">
													<label><?php esc_html_e( 'Project ID', 'aegis' ); ?></label>
													<p><?php esc_html_e( 'From Clarity → Settings → Overview (6–16 letters or digits). The script loads in the page head.', 'aegis' ); ?></p>
												</div>
											</div>
											<div class="aegis-api-field-input">
												<input type="text"
													name="aegis_analytics[clarity_project_id]"
													value="<?php echo esc_attr( $options['clarity_project_id'] ); ?>"
													placeholder="abcdefghij"
													pattern="[A-Za-z0-9]{6,16}"
													maxlength="16"
													autocomplete="off"
													spellcheck="false"
													class="regular-text" />
											</div>
										</div>
									</div>
								</div>
							</section>

							<section id="plausible" class="aegis-settings-section" data-aegis-analytics-settings>
								<div class="aegis-settings-section-header">
									<h2>
										<?php $renderer->render_ui_icon( 'chart-line', 'plausible' ); ?>
										<?php esc_html_e( 'Plausible', 'aegis' ); ?>
									</h2>
									<p><?php esc_html_e( 'Privacy-friendly, cookie-free analytics.', 'aegis' ); ?></p>
								</div>

								<div class="aegis-settings-grid">
									<div class="aegis-toggle-card">
										<div class="aegis-toggle-info">
											<div class="aegis-toggle-icon">
												<?php $renderer->render_ui_icon( 'chart-line', 'plausible' ); ?>
											</div>
											<div class="aegis-toggle-text">
												<h3><?php esc_html_e( 'Enable Plausible', 'aegis' ); ?></h3>
												<p><?php esc_html_e( 'Load the Plausible Analytics script in the page head.', 'aegis' ); ?></p>
											</div>
										</div>
										<label class="aegis-toggle">
											<input type="checkbox" name="aegis_analytics[plausible_enabled]" value="1" <?php checked( $options['plausible_enabled'] ); ?>>
											<span class="aegis-toggle-slider"></span>
										</label>
									</div>

									<div class="aegis-toggle-suboptions">
										<div class="aegis-api-field-row">
											<div class="aegis-api-field-info">
												<div class="aegis-api-field-icon">
													<span class="dashicons dashicons-admin-site-alt3"></span>
												</div>
												<div class="aegis-api-field-text">
													<label><?php esc_html_e( 'Domain', 'aegis' ); ?></label>
													<p><?php esc_html_e( 'Domain from your Plausible site settings (no https:// or www.). Comma-separate multiple hosts.', 'aegis' ); ?></p>
												</div>
											</div>
											<div class="aegis-api-field-input">
												<input type="text"
													name="aegis_analytics[plausible_domain]"
													value="<?php echo esc_attr( $options['plausible_domain'] ); ?>"
													placeholder="example.com"
													autocomplete="off"
													spellcheck="false"
													class="regular-text" />
											</div>
										</div>

										<div class="aegis-api-field-row">
											<div class="aegis-api-field-info">
												<div class="aegis-api-field-icon">
													<span class="dashicons dashicons-admin-links"></span>
												</div>
												<div class="aegis-api-field-text">
													<label><?php esc_html_e( 'Custom Script URL', 'aegis' ); ?></label>
													<p><?php esc_html_e( 'Optional. Self-hosted or first-party proxied script URL. Leave blank for plausible.io.', 'aegis' ); ?></p>
												</div>
											</div>
											<div class="aegis-api-field-input">
												<input type="url"
													name="aegis_analytics[plausible_script_url]"
													value="<?php echo esc_attr( $options['plausible_script_url'] ); ?>"
													placeholder="https://plausible.io/js/script.js"
													autocomplete="off"
													spellcheck="false"
													class="regular-text" />
											</div>
										</div>
									</div>
								</div>
							</section>

							<section id="fathom" class="aegis-settings-section" data-aegis-analytics-settings>
								<div class="aegis-settings-section-header">
									<h2>
										<?php $renderer->render_ui_icon( 'chart-area', 'fathom' ); ?>
										<?php esc_html_e( 'Fathom', 'aegis' ); ?>
									</h2>
									<p><?php esc_html_e( 'Simple, privacy-first website analytics.', 'aegis' ); ?></p>
								</div>

								<div class="aegis-settings-grid">
									<div class="aegis-toggle-card">
										<div class="aegis-toggle-info">
											<div class="aegis-toggle-icon">
												<?php $renderer->render_ui_icon( 'chart-area', 'fathom' ); ?>
											</div>
											<div class="aegis-toggle-text">
												<h3><?php esc_html_e( 'Enable Fathom', 'aegis' ); ?></h3>
												<p><?php esc_html_e( 'Load the Fathom Analytics script in the page head.', 'aegis' ); ?></p>
											</div>
										</div>
										<label class="aegis-toggle">
											<input type="checkbox" name="aegis_analytics[fathom_enabled]" value="1" <?php checked( $options['fathom_enabled'] ); ?>>
											<span class="aegis-toggle-slider"></span>
										</label>
									</div>

									<div class="aegis-toggle-suboptions">
										<div class="aegis-api-field-row">
											<div class="aegis-api-field-info">
												<div class="aegis-api-field-icon">
													<span class="dashicons dashicons-admin-network"></span>
												</div>
												<div class="aegis-api-field-text">
													<label><?php esc_html_e( 'Site ID', 'aegis' ); ?></label>
													<p><?php esc_html_e( 'From Fathom → Settings → Sites (5–10 letters or digits, e.g. ABCDEFG). The script loads in the page head.', 'aegis' ); ?></p>
												</div>
											</div>
											<div class="aegis-api-field-input">
												<input type="text"
													name="aegis_analytics[fathom_site_id]"
													value="<?php echo esc_attr( $options['fathom_site_id'] ); ?>"
													placeholder="ABCDEFG"
													pattern="[A-Za-z0-9]{5,10}"
													maxlength="10"
													autocomplete="off"
													spellcheck="false"
													class="regular-text" />
											</div>
										</div>

										<div class="aegis-api-field-row">
											<div class="aegis-api-field-info">
												<div class="aegis-api-field-icon">
													<span class="dashicons dashicons-admin-links"></span>
												</div>
												<div class="aegis-api-field-text">
													<label><?php esc_html_e( 'Custom Script URL', 'aegis' ); ?></label>
													<p><?php esc_html_e( 'Optional. Custom domain or proxied script URL. Leave blank for cdn.usefathom.com.', 'aegis' ); ?></p>
												</div>
											</div>
											<div class="aegis-api-field-input">
												<input type="url"
													name="aegis_analytics[fathom_script_url]"
													value="<?php echo esc_attr( $options['fathom_script_url'] ?? '' ); ?>"
													placeholder="https://cdn.usefathom.com/script.js"
													autocomplete="off"
													spellcheck="false"
													class="regular-text" />
											</div>
										</div>
									</div>
								</div>
							</section>

							<section id="matomo" class="aegis-settings-section" data-aegis-analytics-settings>
								<div class="aegis-settings-section-header">
									<h2>
										<?php $renderer->render_ui_icon( 'chart-pie', 'matomo' ); ?>
										<?php esc_html_e( 'Matomo', 'aegis' ); ?>
									</h2>
									<p><?php esc_html_e( 'Self-hosted or Matomo Cloud analytics.', 'aegis' ); ?></p>
								</div>

								<div class="aegis-settings-grid">
									<div class="aegis-toggle-card">
										<div class="aegis-toggle-info">
											<div class="aegis-toggle-icon">
												<?php $renderer->render_ui_icon( 'chart-pie', 'matomo' ); ?>
											</div>
											<div class="aegis-toggle-text">
												<h3><?php esc_html_e( 'Enable Matomo', 'aegis' ); ?></h3>
												<p><?php esc_html_e( 'Load the Matomo tracking script in the page head.', 'aegis' ); ?></p>
											</div>
										</div>
										<label class="aegis-toggle">
											<input type="checkbox" name="aegis_analytics[matomo_enabled]" value="1" <?php checked( $options['matomo_enabled'] ); ?>>
											<span class="aegis-toggle-slider"></span>
										</label>
									</div>
									<div class="aegis-toggle-suboptions">
										<div class="aegis-api-field-row">
											<div class="aegis-api-field-info">
												<div class="aegis-api-field-icon">
													<span class="dashicons dashicons-admin-site-alt3"></span>
												</div>
												<div class="aegis-api-field-text">
													<label><?php esc_html_e( 'Matomo URL', 'aegis' ); ?></label>
													<p><?php esc_html_e( 'Instance URL (https://… or host/path; trailing slash optional). Works with self-hosted and Matomo Cloud.', 'aegis' ); ?></p>
												</div>
											</div>
											<div class="aegis-api-field-input">
												<input type="text"
													name="aegis_analytics[matomo_url]"
													value="<?php echo esc_attr( $options['matomo_url'] ); ?>"
													placeholder="https://analytics.example.com"
													autocomplete="off"
													spellcheck="false"
													class="regular-text" />
											</div>
										</div>

										<div class="aegis-api-field-row">
											<div class="aegis-api-field-info">
												<div class="aegis-api-field-icon">
													<span class="dashicons dashicons-id"></span>
												</div>
												<div class="aegis-api-field-text">
													<label><?php esc_html_e( 'Site ID', 'aegis' ); ?></label>
													<p><?php esc_html_e( 'Positive integer Site ID from your Matomo dashboard.', 'aegis' ); ?></p>
												</div>
											</div>
											<div class="aegis-api-field-input">
												<input type="text"
													name="aegis_analytics[matomo_site_id]"
													value="<?php echo esc_attr( $options['matomo_site_id'] ); ?>"
													placeholder="1"
													pattern="[1-9][0-9]{0,9}"
													inputmode="numeric"
													autocomplete="off"
													spellcheck="false"
													class="regular-text" />
											</div>
										</div>

										<div class="aegis-toggle-card aegis-toggle-subcard">
											<div class="aegis-toggle-info">
												<div class="aegis-toggle-icon">
													<span class="dashicons dashicons-hidden"></span>
												</div>
												<div class="aegis-toggle-text">
													<h3><?php esc_html_e( 'Privacy Mode', 'aegis' ); ?></h3>
													<p><?php esc_html_e( 'Enable Do Not Track and disable Matomo cookies in the browser. Configure IP anonymization in your Matomo admin as well.', 'aegis' ); ?></p>
												</div>
											</div>
											<label class="aegis-toggle">
												<input type="checkbox" name="aegis_analytics[matomo_privacy_mode]" value="1" <?php checked( $options['matomo_privacy_mode'] ); ?>>
												<span class="aegis-toggle-slider"></span>
											</label>
										</div>
									</div>
								</div>
							</section>

							<section id="meta-pixel" class="aegis-settings-section" data-aegis-analytics-settings>
								<div class="aegis-settings-section-header">
									<h2>
										<?php $renderer->render_ui_icon( 'megaphone', 'meta' ); ?>
										<?php esc_html_e( 'Meta Pixel', 'aegis' ); ?>
									</h2>
									<p><?php esc_html_e( 'Facebook/Instagram conversion tracking.', 'aegis' ); ?></p>
								</div>

								<div class="aegis-settings-grid">
									<div class="aegis-toggle-card <?php echo ! $pro_active ? 'aegis-pro-feature aegis-toggle-disabled' : ''; ?>">
										<div class="aegis-toggle-info">
											<div class="aegis-toggle-icon">
												<?php $renderer->render_ui_icon( 'megaphone', 'meta' ); ?>
											</div>
											<div class="aegis-toggle-text">
												<h3>
													<?php esc_html_e( 'Enable Meta Pixel', 'aegis' ); ?>
													<?php if ( ! $pro_active ) : ?>
														<span class="aegis-pro-badge"><?php esc_html_e( 'Pro', 'aegis' ); ?></span>
													<?php endif; ?>
												</h3>
												<p><?php esc_html_e( 'Load the Meta Pixel base code in the page head for Facebook and Instagram ad tracking.', 'aegis' ); ?></p>
											</div>
										</div>
										<label class="aegis-toggle">
											<input type="checkbox" name="aegis_analytics[meta_pixel_enabled]" value="1" <?php checked( $options['meta_pixel_enabled'] ); ?> <?php disabled( ! $pro_active ); ?>>
											<span class="aegis-toggle-slider"></span>
										</label>
									</div>

									<?php if ( $pro_active ) : ?>
									<div class="aegis-toggle-suboptions">
										<div class="aegis-api-field-row">
											<div class="aegis-api-field-info">
												<div class="aegis-api-field-icon">
													<span class="dashicons dashicons-admin-network"></span>
												</div>
												<div class="aegis-api-field-text">
													<label><?php esc_html_e( 'Pixel ID', 'aegis' ); ?></label>
													<p><?php esc_html_e( 'Numeric Pixel ID from Meta Events Manager (5–20 digits).', 'aegis' ); ?></p>
												</div>
											</div>
											<div class="aegis-api-field-input">
												<input type="text"
													name="aegis_analytics[meta_pixel_id]"
													value="<?php echo esc_attr( $options['meta_pixel_id'] ); ?>"
													placeholder="123456789012345"
													pattern="[0-9]{5,20}"
													inputmode="numeric"
													autocomplete="off"
													spellcheck="false"
													class="regular-text" />
											</div>
										</div>

										<div class="aegis-toggle-card aegis-toggle-subcard">
											<div class="aegis-toggle-info">
												<div class="aegis-toggle-icon">
													<span class="dashicons dashicons-cart"></span>
												</div>
												<div class="aegis-toggle-text">
													<h3><?php esc_html_e( 'WooCommerce Events', 'aegis' ); ?></h3>
													<p><?php esc_html_e( 'Track ViewContent, AddToCart (after add), InitiateCheckout, and Purchase when WooCommerce is active.', 'aegis' ); ?></p>
												</div>
											</div>
											<label class="aegis-toggle">
												<input type="checkbox" name="aegis_analytics[meta_pixel_woo_events]" value="1" <?php checked( $options['meta_pixel_woo_events'] ); ?>>
												<span class="aegis-toggle-slider"></span>
											</label>
										</div>
									</div>
									<?php endif; ?>
								</div>
							</section>

							<section id="privacy" class="aegis-settings-section" data-aegis-analytics-settings>
								<div class="aegis-settings-section-header">
									<h2>
										<?php $renderer->render_ui_icon( 'shield' ); ?>
										<?php esc_html_e( 'Privacy', 'aegis' ); ?>
									</h2>
									<p><?php esc_html_e( 'Consent, Do Not Track / GPC, and script loading for all analytics providers.', 'aegis' ); ?></p>
								</div>

								<div class="aegis-settings-grid">
									<div class="aegis-toggle-card <?php echo ! $pro_active ? 'aegis-pro-feature aegis-toggle-disabled' : ''; ?>">
										<div class="aegis-toggle-info">
											<div class="aegis-toggle-icon">
												<span class="dashicons dashicons-lock"></span>
											</div>
											<div class="aegis-toggle-text">
												<h3>
													<?php esc_html_e( 'Consent Mode v2', 'aegis' ); ?>
													<?php if ( ! $pro_active ) : ?>
														<span class="aegis-pro-badge"><?php esc_html_e( 'Pro', 'aegis' ); ?></span>
													<?php endif; ?>
												</h3>
												<p><?php esc_html_e( 'Set denied Consent Mode defaults (with wait_for_update) until consent is granted when GA4 and/or GTM are enabled. With Complianz Integration enabled, Aegis updates consent from Complianz category events. Other CMPs can dispatch the aegis_analytics_consent event.', 'aegis' ); ?></p>
											</div>
										</div>
										<label class="aegis-toggle">
											<input type="checkbox" name="aegis_analytics[ga4_consent_mode]" value="1" <?php checked( $options['ga4_consent_mode'] ); ?> <?php disabled( ! $pro_active ); ?>>
											<span class="aegis-toggle-slider"></span>
										</label>
									</div>

									<div class="aegis-toggle-card <?php echo ! $has_complianz && ! $options['gdpr_consent_required'] ? 'aegis-toggle-disabled' : ''; ?>">
										<div class="aegis-toggle-info">
											<div class="aegis-toggle-icon">
												<span class="dashicons dashicons-shield"></span>
											</div>
											<div class="aegis-toggle-text">
												<h3><?php esc_html_e( 'Require Consent', 'aegis' ); ?></h3>
												<p><?php esc_html_e( 'Hold tracking scripts as text/plain until Complianz grants consent. Requires Complianz Integration and the Complianz plugin.', 'aegis' ); ?></p>
											</div>
										</div>
										<label class="aegis-toggle">
											<input type="checkbox" name="aegis_analytics[gdpr_consent_required]" value="1" <?php checked( $options['gdpr_consent_required'] ); ?> <?php disabled( ! $has_complianz && ! $options['gdpr_consent_required'] ); ?>>
											<span class="aegis-toggle-slider"></span>
										</label>
									</div>

									<div class="aegis-toggle-card">
										<div class="aegis-toggle-info">
											<div class="aegis-toggle-icon">
												<span class="dashicons dashicons-dismiss"></span>
											</div>
											<div class="aegis-toggle-text">
												<h3><?php esc_html_e( 'Respect DNT / GPC', 'aegis' ); ?></h3>
												<p><?php esc_html_e( 'Skip all tracking for visitors who send Do Not Track (DNT) or Global Privacy Control (Sec-GPC).', 'aegis' ); ?></p>
											</div>
										</div>
										<label class="aegis-toggle">
											<input type="checkbox" name="aegis_analytics[gdpr_respect_dnt]" value="1" <?php checked( $options['gdpr_respect_dnt'] ); ?>>
											<span class="aegis-toggle-slider"></span>
										</label>
									</div>

									<div class="aegis-toggle-card <?php echo ! $has_complianz && ! $options['gdpr_complianz'] ? 'aegis-toggle-disabled' : ''; ?>">
										<div class="aegis-toggle-info">
											<div class="aegis-toggle-icon">
												<span class="dashicons dashicons-admin-plugins"></span>
											</div>
											<div class="aegis-toggle-text">
												<h3>
													<?php esc_html_e( 'Complianz Integration', 'aegis' ); ?>
													<?php if ( ! $has_complianz ) : ?>
														<span class="aegis-plugin-status not-installed"><?php esc_html_e( 'Not Installed', 'aegis' ); ?></span>
													<?php else : ?>
														<span class="aegis-plugin-status active"><?php esc_html_e( 'Active', 'aegis' ); ?></span>
													<?php endif; ?>
												</h3>
												<p><?php esc_html_e( 'Add Complianz script attributes for Require Consent and update Consent Mode v2 from Complianz category events.', 'aegis' ); ?></p>
											</div>
										</div>
										<label class="aegis-toggle">
											<input type="checkbox" name="aegis_analytics[gdpr_complianz]" value="1" <?php checked( $options['gdpr_complianz'] ); ?> <?php disabled( ! $has_complianz && ! $options['gdpr_complianz'] ); ?>>
											<span class="aegis-toggle-slider"></span>
										</label>
									</div>

									<div class="aegis-toggle-card">
										<div class="aegis-toggle-info">
											<div class="aegis-toggle-icon">
												<span class="dashicons dashicons-download"></span>
											</div>
											<div class="aegis-toggle-text">
												<h3><?php esc_html_e( 'Local Script Loading', 'aegis' ); ?></h3>
												<p><?php esc_html_e( 'Cache GA4 and GTM scripts locally (Clarity, Plausible, Fathom, Matomo, and Meta are not proxied). Scripts refresh automatically every 24 hours.', 'aegis' ); ?></p>
											</div>
										</div>
										<label class="aegis-toggle">
											<input type="checkbox" name="aegis_analytics[local_scripts]" value="1" <?php checked( $options['local_scripts'] ); ?>>
											<span class="aegis-toggle-slider"></span>
										</label>
									</div>

									<div class="aegis-toggle-card <?php echo ! $pro_active ? 'aegis-pro-feature aegis-toggle-disabled' : ''; ?>">
										<div class="aegis-toggle-info">
											<div class="aegis-toggle-icon">
												<span class="dashicons dashicons-editor-code"></span>
											</div>
											<div class="aegis-toggle-text">
												<h3>
													<?php esc_html_e( 'Debug Mode', 'aegis' ); ?>
													<?php if ( ! $pro_active ) : ?>
														<span class="aegis-pro-badge"><?php esc_html_e( 'Pro', 'aegis' ); ?></span>
													<?php endif; ?>
												</h3>
												<p><?php esc_html_e( 'Load tracking for admins and log active providers, consent/DNT/GPC state, and script loading in the browser console.', 'aegis' ); ?></p>
											</div>
										</div>
										<label class="aegis-toggle">
											<input type="checkbox" name="aegis_analytics[gdpr_debug_mode]" value="1" <?php checked( $options['gdpr_debug_mode'] ); ?> <?php disabled( ! $pro_active ); ?>>
											<span class="aegis-toggle-slider"></span>
										</label>
									</div>
								</div>
							</section>
