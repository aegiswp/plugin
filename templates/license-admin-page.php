<?php
/**
 * Free-plugin License / Get Pro admin page markup.
 *
 * @package Aegis\Plugin\License
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$purchase_url = 'https://www.atmostfear-entertainment.com/aegis/pro';
$plugins_url  = admin_url( 'plugins.php' );
$donate_url   = 'https://paypal.me/aedonation';

$features = array(
	array(
		'icon'  => 'visibility',
		'title' => __( 'Visibility Presets', 'aegis' ),
		'desc'  => __( 'Save reusable visibility rule sets and apply them from any block.', 'aegis' ),
	),
	array(
		'icon'  => 'layout',
		'title' => __( 'Hook Patterns', 'aegis' ),
		'desc'  => __( 'Inject block layouts at theme and WordPress hooks.', 'aegis' ),
	),
	array(
		'icon'  => 'video-alt3',
		'title' => __( 'Video Player Pro', 'aegis' ),
		'desc'  => __( 'Chapters, analytics, BunnyCDN streaming, and LMS integrations.', 'aegis' ),
	),
	array(
		'icon'  => 'lock',
		'title' => __( 'Advanced Conditionals', 'aegis' ),
		'desc'  => __( 'Cookie, referral, ACF, Meta Box, post meta, and location-based visibility.', 'aegis' ),
	),
	array(
		'icon'  => 'database',
		'title' => __( 'Query Loop Pro', 'aegis' ),
		'desc'  => __( 'Advanced filtering, custom field queries, related posts, and AJAX pagination.', 'aegis' ),
	),
	array(
		'icon'  => 'slides',
		'title' => __( 'Modal Pro', 'aegis' ),
		'desc'  => __( 'Exit intent, scroll depth, time delay, auto close, show once, and device visibility.', 'aegis' ),
	),
);

?>
			<div class="aegis-settings-wrap">
				<h1 class="screen-reader-text"><?php esc_html_e( 'License', 'aegis' ); ?></h1>

				<div class="aegis-license-section">
					<div class="aegis-license-activation">
						<div class="aegis-license-activation-body">
							<div class="aegis-license-status-row">
								<div class="aegis-pro-status aegis-pro-status--inactive">
									<span class="dashicons dashicons-info-outline"></span>
									<div>
										<strong><?php esc_html_e( 'Aegis Pro is not installed', 'aegis' ); ?></strong>
										<p><?php esc_html_e( 'Install and activate Aegis Pro to unlock Presets, Hook Patterns, and other Pro features. Enter your license key on this screen after Pro is active.', 'aegis' ); ?></p>
									</div>
								</div>
							</div>
							<p class="aegis-license-help">
								<a class="button button-secondary" href="<?php echo esc_url( $plugins_url ); ?>">
									<?php esc_html_e( 'Open Plugins', 'aegis' ); ?>
								</a>
							</p>
						</div>
					</div>
				</div>

				<div class="aegis-license-section">
					<h2><?php esc_html_e( 'Unlock the Full Power of Aegis', 'aegis' ); ?></h2>
					<p class="aegis-license-section-desc"><?php esc_html_e( 'Aegis Pro extends every block with advanced features, integrations, and professional tools.', 'aegis' ); ?></p>

					<div class="aegis-license-features">
						<?php foreach ( $features as $feature ) : ?>
							<div class="aegis-license-feature-card">
								<div class="aegis-license-feature-icon">
									<span class="dashicons dashicons-<?php echo esc_attr( (string) $feature['icon'] ); ?>"></span>
								</div>
								<h3><?php echo esc_html( (string) $feature['title'] ); ?></h3>
								<p><?php echo esc_html( (string) $feature['desc'] ); ?></p>
							</div>
						<?php endforeach; ?>
					</div>

					<div class="aegis-license-purchase-cta">
						<a href="<?php echo esc_url( $purchase_url ); ?>" target="_blank" rel="noopener noreferrer" class="button button-primary button-hero">
							<span class="dashicons dashicons-cart"></span>
							<?php esc_html_e( 'Get Aegis Pro', 'aegis' ); ?>
						</a>
						<span class="aegis-license-purchase-note"><?php esc_html_e( 'One-time purchase. Lifetime updates. 14-day money-back guarantee.', 'aegis' ); ?></span>
					</div>
				</div>

				<div class="aegis-license-section">
					<div class="aegis-license-donate-card">
						<div class="aegis-license-donate-icon">
							<span class="dashicons dashicons-heart"></span>
						</div>
						<div class="aegis-license-donate-content">
							<h3><?php esc_html_e( 'Support Open-Source Development', 'aegis' ); ?></h3>
							<p><?php esc_html_e( 'Aegis is built and maintained by a small independent team. If a Pro license isn\'t in your budget right now, you can still support our work with a donation.', 'aegis' ); ?></p>
							<a href="<?php echo esc_url( $donate_url ); ?>" target="_blank" rel="noopener noreferrer" class="button">
								<span class="dashicons dashicons-heart"></span>
								<?php esc_html_e( 'Make a Donation', 'aegis' ); ?>
							</a>
						</div>
					</div>
				</div>
			</div>
