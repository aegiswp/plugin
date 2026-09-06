<?php
/**
 * Hooks admin page markup.
 *
 * @package Aegis\Plugin\Hooks
 * @var array<int, array<string, mixed>> $instances
 * @var bool                              $pro_active
 * @var bool                              $cpt_ready
 * @var string                            $create_url
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$snippets_url = admin_url( 'admin.php?page=aegis-snippets' );

?>
			<div class="aegis-settings-wrap aegis-hooks-page">
				<h1 class="screen-reader-text"><?php esc_html_e( 'Hooks', 'aegis' ); ?></h1>
				<?php if ( ! $pro_active ) : ?>
					<div class="aegis-hooks-info">
						<span class="dashicons dashicons-lock"></span>
						<div>
							<strong><?php esc_html_e( 'Hook patterns need Aegis Pro', 'aegis' ); ?></strong>
							<p>
								<?php
								printf(
									/* translators: %s: link to Code Snippets */
									esc_html__( 'Inject block layouts at theme and WordPress hooks. For PHP, CSS, or HTML, use %s.', 'aegis' ),
									'<a href="' . esc_url( $snippets_url ) . '">' . esc_html__( 'Code Snippets', 'aegis' ) . '</a>'
								);
								?>
							</p>
						</div>
					</div>
				<?php endif; ?>

				<div class="wp-filter aegis-toolbar">
					<div class="aegis-toolbar-left filter-items">
						<a href="<?php echo esc_url( $create_url ); ?>" class="button button-primary">
							<span class="dashicons dashicons-plus-alt2"></span>
							<?php esc_html_e( 'Add New', 'aegis' ); ?>
							<?php if ( ! $pro_active ) : ?>
								<span class="aegis-pro-badge"><?php esc_html_e( 'Pro', 'aegis' ); ?></span>
							<?php endif; ?>
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
							<label for="aegis-search-hooks"><?php esc_html_e( 'Search', 'aegis' ); ?></label>
							<input type="search" id="aegis-search-hooks" class="aegis-search-input">
						</p>
					</div>
				</div>

				<?php if ( $instances === array() ) : ?>
					<div class="aegis-hooks-empty">
						<h2><?php esc_html_e( 'No hook patterns yet', 'aegis' ); ?></h2>
						<?php if ( $cpt_ready ) : ?>
							<p><?php esc_html_e( 'Click Add New to create a pattern, pick a hook location, add blocks, and publish.', 'aegis' ); ?></p>
						<?php else : ?>
							<p><?php esc_html_e( 'Install Aegis Pro to inject block patterns at hook locations.', 'aegis' ); ?></p>
						<?php endif; ?>
						<p class="aegis-hooks-empty-action">
							<a href="<?php echo esc_url( $create_url ); ?>" class="button button-primary">
								<span class="dashicons dashicons-plus-alt2"></span>
								<?php esc_html_e( 'Add New', 'aegis' ); ?>
								<?php if ( ! $pro_active ) : ?>
									<span class="aegis-pro-badge"><?php esc_html_e( 'Pro', 'aegis' ); ?></span>
								<?php endif; ?>
							</a>
						</p>
					</div>
				<?php else : ?>
					<div class="aegis-hooks-reference aegis-hooks-list">
						<div class="aegis-hooks-reference-header">
							<span class="dashicons dashicons-layout"></span>
							<span><?php esc_html_e( 'Hook patterns on this site', 'aegis' ); ?></span>
						</div>
						<div class="aegis-admin-table-wrap">
							<table class="aegis-admin-table">
								<thead>
									<tr>
										<th><?php esc_html_e( 'Pattern', 'aegis' ); ?></th>
										<th><?php esc_html_e( 'Location', 'aegis' ); ?></th>
										<th><?php esc_html_e( 'Priority', 'aegis' ); ?></th>
										<th><?php esc_html_e( 'Conditions', 'aegis' ); ?></th>
										<th><?php esc_html_e( 'Status', 'aegis' ); ?></th>
										<th><?php esc_html_e( 'Actions', 'aegis' ); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ( $instances as $instance ) : ?>
										<?php
										$post_id           = (int) ( $instance['post_id'] ?? 0 );
										$edit_url          = (string) ( $instance['edit_url'] ?? '' );
										$can_edit          = $edit_url !== '' && current_user_can( 'edit_post', $post_id );
										$hook              = (string) ( $instance['hook'] ?? '' );
										$enabled           = ! empty( $instance['enabled'] );
										$conditional       = ! empty( $instance['conditional'] );
										$rule_count        = (int) ( $instance['rule_count'] ?? 0 );
										$post_status_label = (string) ( $instance['post_status_label'] ?? '' );
										$title             = (string) ( $instance['title'] ?? '' );
										if ( $title === '' ) {
											$title = __( '(no title)', 'aegis' );
										}
										?>
										<tr
											data-post-id="<?php echo esc_attr( (string) $post_id ); ?>"
											data-post-status-label="<?php echo esc_attr( $post_status_label ); ?>"
										>
											<td>
												<strong><?php echo esc_html( $title ); ?></strong>
												<span class="aegis-hooks-meta"><?php echo esc_html( (string) ( $instance['author'] ?? '' ) ); ?></span>
											</td>
											<td>
												<?php echo esc_html( (string) ( $instance['hook_label'] ?? '' ) ); ?>
												<?php if ( $hook !== '' ) : ?>
													<button
														type="button"
														class="aegis-hooks-copy"
														data-copy="<?php echo esc_attr( $hook ); ?>"
														title="<?php esc_attr_e( 'Copy hook name', 'aegis' ); ?>"
													>
														<code><?php echo esc_html( $hook ); ?></code>
														<span class="dashicons dashicons-clipboard" aria-hidden="true"></span>
														<span class="screen-reader-text"><?php esc_html_e( 'Copy hook name', 'aegis' ); ?></span>
													</button>
												<?php endif; ?>
											</td>
											<td><?php echo esc_html( (string) (int) ( $instance['priority'] ?? 10 ) ); ?></td>
											<td>
												<?php if ( $conditional ) : ?>
													<?php
													$conditions_label = $rule_count > 1
														? sprintf(
															/* translators: %d: number of condition rules */
															__( '%d rules', 'aegis' ),
															$rule_count
														)
														: __( 'Conditional', 'aegis' );
													?>
													<?php if ( $can_edit ) : ?>
														<a class="aegis-hooks-conditional" href="<?php echo esc_url( $edit_url ); ?>" title="<?php esc_attr_e( 'Edit conditions in the pattern editor', 'aegis' ); ?>">
															<?php echo esc_html( $conditions_label ); ?>
														</a>
													<?php else : ?>
														<span class="aegis-hooks-conditional"><?php echo esc_html( $conditions_label ); ?></span>
													<?php endif; ?>
												<?php else : ?>
													<span class="aegis-hooks-always"><?php esc_html_e( 'Always', 'aegis' ); ?></span>
												<?php endif; ?>
											</td>
											<td class="aegis-hooks-status">
												<?php if ( ! $enabled ) : ?>
													<span class="aegis-status-disabled"><?php esc_html_e( 'Disabled', 'aegis' ); ?></span>
												<?php else : ?>
													<span class="aegis-status-enabled"><?php echo esc_html( $post_status_label ); ?></span>
												<?php endif; ?>
											</td>
											<td>
												<?php if ( $can_edit ) : ?>
													<div class="aegis-hooks-row-actions">
														<label class="aegis-toggle" title="<?php esc_attr_e( 'Enable or disable this hook pattern', 'aegis' ); ?>">
															<input type="checkbox" class="aegis-hook-enabled-toggle" value="1" <?php checked( $enabled ); ?> />
															<span class="aegis-toggle-slider"></span>
														</label>
														<a class="button button-compact" href="<?php echo esc_url( $edit_url ); ?>"><?php esc_html_e( 'Edit', 'aegis' ); ?></a>
														<?php if ( current_user_can( 'delete_post', $post_id ) ) : ?>
															<button type="button" class="button button-compact aegis-hook-delete"><?php esc_html_e( 'Delete', 'aegis' ); ?></button>
														<?php endif; ?>
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
			</div>
