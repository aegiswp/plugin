<?php
/**
 * Connectors admin page markup.
 *
 * @package Aegis\Plugin\Connectors
 * @var array<string, bool>              $options
 * @var array<string, string>            $bunnycdn_settings
 * @var \Aegis\Plugin\Admin\Renderer     $renderer
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$bunnycdn_template = __DIR__ . '/connectors-bunnycdn-section.php';

?>
			<div class="aegis-settings-wrap">
				<h1 class="screen-reader-text"><?php esc_html_e( 'Connectors', 'aegis' ); ?></h1>

				<?php $renderer->render_toolbar( 'integrations' ); ?>

				<form method="post" action="#" class="aegis-settings-form aegis-integrations-form">
					<?php settings_fields( 'aegis_integrations_group' ); ?>

					<div class="aegis-settings-layout">
						<nav class="aegis-settings-nav">
							<a href="#bunnycdn" class="aegis-nav-item active">
								<span class="dashicons dashicons-cloud"></span>
								<?php esc_html_e( 'BunnyCDN', 'aegis' ); ?>
							</a>
							<?php do_action( 'aegis_connectors_nav_items' ); ?>
						</nav>

						<div class="aegis-settings-content">
							<?php
							if ( file_exists( $bunnycdn_template ) ) {
								include $bunnycdn_template;
							}

							do_action( 'aegis_connectors_maps_section' );
							do_action( 'aegis_connectors_analytics_section' );
							?>

							<div class="aegis-settings-footer">
								<?php submit_button( __( 'Save Settings', 'aegis' ), 'primary', 'submit', false ); ?>
							</div>
						</div>
					</div>
				</form>
			</div>
