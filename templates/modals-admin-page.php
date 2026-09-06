<?php
/**
 * Modals admin page markup.
 *
 * @package Aegis\Plugin\Modal
 * @var array<int, array<string, mixed>> $instances
 * @var bool                              $modal_enabled
 * @var bool                              $pro_active
 * @var bool                              $error
 * @var array<string, array<string, mixed>> $starters
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$blocks_url = admin_url( 'admin.php?page=aegis-blocks#modal' );
$create_url = static function ( string $starter ): string {
	return wp_nonce_url(
		admin_url( 'admin-post.php?action=aegis_create_modal&starter=' . rawurlencode( $starter ) ),
		'aegis_create_modal'
	);
};

?>
			<div class="aegis-settings-wrap aegis-modals-page">
				<h1 class="screen-reader-text"><?php esc_html_e( 'Modals', 'aegis' ); ?></h1>

				<?php if ( $error ) : ?>
					<div class="aegis-notice aegis-notice-error">
						<?php esc_html_e( 'Could not create the modal. Try again or insert the Modal block from the editor.', 'aegis' ); ?>
					</div>
				<?php endif; ?>

				<?php if ( ! $modal_enabled ) : ?>
					<div class="aegis-hooks-info">
						<span class="dashicons dashicons-warning"></span>
						<div>
							<strong><?php esc_html_e( 'Modal features are off', 'aegis' ); ?></strong>
							<p>
								<?php
								printf(
									/* translators: %s: link to Blocks → Modal */
									esc_html__( 'Turn on at least one trigger at %s so the Modal block is available in the editor.', 'aegis' ),
									'<a href="' . esc_url( $blocks_url ) . '">' . esc_html__( 'Aegis → Blocks → Modal', 'aegis' ) . '</a>'
								);
								?>
							</p>
						</div>
					</div>
				<?php endif; ?>

				<div class="wp-filter aegis-toolbar">
					<div class="aegis-toolbar-left filter-items">
						<a href="<?php echo esc_url( $create_url( 'blank' ) ); ?>" class="button button-primary aegis-modals-add-new">
							<span class="dashicons dashicons-plus-alt2"></span>
							<?php esc_html_e( 'Add New', 'aegis' ); ?>
						</a>
						<button type="button" class="button aegis-export-btn">
							<?php esc_html_e( 'Export', 'aegis' ); ?>
						</button>
						<button type="button" class="button aegis-import-btn">
							<?php esc_html_e( 'Import', 'aegis' ); ?>
						</button>
						<input type="file" id="aegis-import-file" accept=".json" hidden>
					</div>
					<div class="aegis-toolbar-right search-form">
						<p class="search-box">
							<label for="aegis-search-modals"><?php esc_html_e( 'Search', 'aegis' ); ?></label>
							<input type="search" id="aegis-search-modals" class="aegis-search-input">
						</p>
					</div>
				</div>

				<?php if ( $instances === array() ) : ?>
					<div class="aegis-modals-empty">
						<h2><?php esc_html_e( 'No modals yet', 'aegis' ); ?></h2>
						<p><?php esc_html_e( 'Click Add New to start from a blank modal or a pattern.', 'aegis' ); ?></p>
						<p class="aegis-modals-empty-action">
							<a href="<?php echo esc_url( $create_url( 'blank' ) ); ?>" class="button button-primary aegis-modals-add-new">
								<span class="dashicons dashicons-plus-alt2"></span>
								<?php esc_html_e( 'Add New', 'aegis' ); ?>
							</a>
						</p>
					</div>
				<?php else : ?>
					<div class="aegis-hooks-reference aegis-modals-list">
						<div class="aegis-hooks-reference-header">
							<span class="dashicons dashicons-editor-expand"></span>
							<span><?php esc_html_e( 'Modals on this site', 'aegis' ); ?></span>
						</div>
						<div class="aegis-admin-table-wrap">
							<table class="aegis-admin-table">
								<thead>
									<tr>
										<th><?php esc_html_e( 'Modal', 'aegis' ); ?></th>
										<th><?php esc_html_e( 'Location', 'aegis' ); ?></th>
										<th><?php esc_html_e( 'Trigger', 'aegis' ); ?></th>
										<th><?php esc_html_e( 'Date', 'aegis' ); ?></th>
										<th><?php esc_html_e( 'Status', 'aegis' ); ?></th>
										<th><?php esc_html_e( 'Actions', 'aegis' ); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ( $instances as $instance ) : ?>
										<?php
										$post_id           = (int) ( $instance['post_id'] ?? 0 );
										$edit_url          = $post_id > 0 ? (string) get_edit_post_link( $post_id, 'raw' ) : '';
										$can_edit          = $edit_url !== '' && current_user_can( 'edit_post', $post_id );
										$host              = (string) ( $instance['post_title'] ?? '' );
										$modal_id          = (string) ( $instance['modal_id'] ?? '' );
										$title             = (string) ( $instance['title'] ?? '' );
										$enabled           = ! isset( $instance['enabled'] ) || ! empty( $instance['enabled'] );
										$trigger_enabled   = ! empty( $instance['trigger_enabled'] );
										$post_status_label = (string) ( $instance['post_status_label'] ?? '' );
										$author            = (string) ( $instance['author'] ?? '' );
										$date_status       = (string) ( $instance['date_status'] ?? '' );
										$date_display      = (string) ( $instance['date_display'] ?? '' );

										if ( $host === '' ) {
											$host = '#' . (string) $post_id;
										}

										if ( $title === '' ) {
											$title = __( '(no title)', 'aegis' );
										}
										?>
										<tr
											data-post-id="<?php echo esc_attr( (string) $post_id ); ?>"
											data-modal-id="<?php echo esc_attr( $modal_id ); ?>"
											data-block-index="<?php echo esc_attr( (string) (int) ( $instance['block_index'] ?? 0 ) ); ?>"
											data-post-status-label="<?php echo esc_attr( $post_status_label ); ?>"
											data-trigger-enabled="<?php echo $trigger_enabled ? '1' : '0'; ?>"
										>
											<td>
												<strong><?php echo esc_html( $title ); ?></strong>
												<?php if ( $author !== '' && $author !== '—' ) : ?>
													<span class="aegis-hooks-meta"><?php echo esc_html( $author ); ?></span>
												<?php endif; ?>
											</td>
											<td>
												<?php echo esc_html( $host ); ?>
												<span class="aegis-hooks-meta"><?php echo esc_html( (string) ( $instance['post_type_label'] ?? '' ) ); ?></span>
												<?php if ( $modal_id !== '' ) : ?>
													<button
														type="button"
														class="aegis-modals-id"
														data-copy="<?php echo esc_attr( $modal_id ); ?>"
														title="<?php esc_attr_e( 'Copy modal ID', 'aegis' ); ?>"
													>
														<code><?php echo esc_html( $modal_id ); ?></code>
														<span class="dashicons dashicons-clipboard" aria-hidden="true"></span>
														<span class="screen-reader-text"><?php esc_html_e( 'Copy modal ID', 'aegis' ); ?></span>
													</button>
												<?php endif; ?>
											</td>
											<td><?php echo esc_html( (string) ( $instance['trigger_label'] ?? '' ) ); ?></td>
											<td>
												<?php if ( $date_status !== '' ) : ?>
													<?php echo esc_html( $date_status ); ?>
												<?php endif; ?>
												<span class="aegis-hooks-meta"><?php echo esc_html( $date_display !== '' ? $date_display : (string) ( $instance['date'] ?? '—' ) ); ?></span>
											</td>
											<td class="aegis-modals-status">
												<?php if ( ! $enabled ) : ?>
													<span class="aegis-status-disabled"><?php esc_html_e( 'Disabled', 'aegis' ); ?></span>
												<?php elseif ( ! $trigger_enabled ) : ?>
													<span class="aegis-status-disabled"><?php esc_html_e( 'Trigger off', 'aegis' ); ?></span>
												<?php else : ?>
													<span class="aegis-status-enabled"><?php echo esc_html( $post_status_label ); ?></span>
												<?php endif; ?>
											</td>
											<td>
												<?php if ( $can_edit ) : ?>
													<div class="aegis-hooks-row-actions">
														<label class="aegis-toggle" title="<?php esc_attr_e( 'Enable or disable this modal', 'aegis' ); ?>">
															<input type="checkbox" class="aegis-modal-enabled-toggle" value="1" <?php checked( $enabled ); ?> />
															<span class="aegis-toggle-slider"></span>
														</label>
														<a class="button button-compact" href="<?php echo esc_url( $edit_url ); ?>"><?php esc_html_e( 'Edit', 'aegis' ); ?></a>
														<button type="button" class="button button-compact aegis-modal-delete"><?php esc_html_e( 'Delete', 'aegis' ); ?></button>
													</div>
												<?php else : ?>
													—
												<?php endif; ?>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					</div>
				<?php endif; ?>

				<dialog id="aegis-modals-chooser" class="aegis-modals-chooser" aria-labelledby="aegis-modals-chooser-title">
					<div class="aegis-modals-chooser-header">
						<h2 id="aegis-modals-chooser-title"><?php esc_html_e( 'Add New', 'aegis' ); ?></h2>
						<button type="button" class="aegis-modals-chooser-close" aria-label="<?php esc_attr_e( 'Close', 'aegis' ); ?>">
							<span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
						</button>
					</div>
					<div class="aegis-modals-chooser-body">
						<p class="aegis-modals-chooser-intro"><?php esc_html_e( 'Start from a blank modal or a pattern.', 'aegis' ); ?></p>
						<div class="aegis-modals-starters">
							<?php foreach ( $starters as $slug => $starter ) : ?>
								<?php
								$is_pro_starter = ! empty( $starter['pro'] );
								$card_classes   = 'aegis-license-feature-card aegis-modals-starter';
								if ( $is_pro_starter && ! $pro_active ) {
									$card_classes .= ' aegis-pro-feature';
								}
								?>
								<a class="<?php echo esc_attr( $card_classes ); ?>" href="<?php echo esc_url( $create_url( (string) $slug ) ); ?>">
									<div class="aegis-license-feature-icon">
										<span class="dashicons dashicons-<?php echo esc_attr( (string) $starter['icon'] ); ?>"></span>
									</div>
									<h3>
										<?php echo esc_html( (string) $starter['label'] ); ?>
										<?php if ( $is_pro_starter && ! $pro_active ) : ?>
											<span class="aegis-pro-badge"><?php esc_html_e( 'Pro', 'aegis' ); ?></span>
										<?php endif; ?>
									</h3>
									<p><?php echo esc_html( (string) $starter['description'] ); ?></p>
								</a>
							<?php endforeach; ?>
						</div>
					</div>
				</dialog>
			</div>
