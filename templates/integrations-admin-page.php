<?php
/**
 * Integrations admin page markup.
 *
 * @package Aegis\Plugin\Integrations
 * @var array<string, bool>              $options
 * @var \Aegis\Plugin\Admin\Renderer     $renderer
 */

declare( strict_types=1 );

use Aegis\Plugin\Integrations\Registry;
use Aegis\Plugin\Integrations\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pattern_options = get_option( Settings::PATTERN_CONTROL_OPTION, Settings::PATTERN_CONTROL_DEFAULTS );
$pattern_options = wp_parse_args(
	is_array( $pattern_options ) ? $pattern_options : array(),
	Settings::PATTERN_CONTROL_DEFAULTS
);
$sections        = Registry::get_integrations_admin_sections();

?>
			<div class="aegis-settings-wrap">
				<h1 class="screen-reader-text"><?php esc_html_e( 'Integrations', 'aegis' ); ?></h1>

				<?php $renderer->render_toolbar( 'integrations' ); ?>

				<form method="post" action="#" class="aegis-settings-form aegis-integrations-form">
					<?php settings_fields( 'aegis_integrations_group' ); ?>

					<div class="aegis-settings-layout">
						<nav class="aegis-settings-nav">
							<?php foreach ( $sections as $index => $section ) : ?>
							<a href="#<?php echo esc_attr( $section['id'] ); ?>" class="aegis-nav-item<?php echo $index === 0 ? ' active' : ''; ?>">
								<span class="dashicons dashicons-<?php echo esc_attr( $section['icon'] ); ?>"></span>
								<?php echo esc_html( $section['label'] ); ?>
							</a>
							<?php endforeach; ?>
						</nav>

						<div class="aegis-settings-content">
							<?php foreach ( $sections as $index => $section ) : ?>
							<section id="<?php echo esc_attr( $section['id'] ); ?>" class="aegis-settings-section<?php echo $index === 0 ? ' active' : ''; ?>">
								<?php $renderer->render_section_header( $section['label'], $section['description'], true, $section['icon'] ); ?>

								<?php foreach ( $section['plugins'] as $tab ) : ?>
								<div class="aegis-plugin-stack" id="<?php echo esc_attr( $tab['id'] ); ?>" data-integration-key="<?php echo esc_attr( $tab['key'] ); ?>">
									<?php $renderer->render_stack_header( $tab['label'], $tab['description'], $tab['icon'] ); ?>
									<div class="aegis-settings-section__content">
										<div class="aegis-settings-grid aegis-settings-grid--features">
											<?php
											$renderer->render_integration_toggle(
												$tab['key'],
												$tab['label'],
												$tab['description'],
												$options,
												$tab['plugin_check'],
												$tab['icon']
											);
											$renderer->render_pattern_control_toggles( $tab['key'], $pattern_options );
											\Aegis\Plugin\Conditionals\IntegrationsPanel::render( $renderer, $tab['key'] );
											?>
										</div>
									</div>
								</div>
								<?php endforeach; ?>
							</section>
							<?php endforeach; ?>

							<div class="aegis-settings-footer">
								<?php submit_button( __( 'Save Settings', 'aegis' ), 'primary', 'submit', false ); ?>
							</div>
						</div>
					</div>
				</form>
			</div>
