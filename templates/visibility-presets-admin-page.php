<?php
/**
 * Visibility Presets admin page markup.
 *
 * @package Aegis\Plugin\VisibilityPresets
 * @var array<int, array<string, mixed>> $presets
 * @var bool                              $pro_active
 * @var string                            $create_url
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$empty         = $presets === array();
$conditionals_url = admin_url( 'admin.php?page=aegis-settings' );

?>
			<div id="visibility-presets" class="aegis-settings-wrap aegis-presets-page">
				<h1 class="screen-reader-text"><?php esc_html_e( 'Visibility Presets', 'aegis' ); ?></h1>
				<?php if ( ! $pro_active ) : ?>
					<div class="aegis-hooks-info">
						<span class="dashicons dashicons-lock"></span>
						<div>
							<strong><?php esc_html_e( 'Visibility presets need Aegis Pro', 'aegis' ); ?></strong>
							<p>
								<?php
								printf(
									/* translators: %s: link to Conditionals */
									esc_html__( 'Save reusable visibility rule sets and apply them from any block. Condition types are toggled at %s.', 'aegis' ),
									'<a href="' . esc_url( $conditionals_url ) . '">' . esc_html__( 'Conditionals', 'aegis' ) . '</a>'
								);
								?>
							</p>
						</div>
					</div>
				<?php endif; ?>

				<div class="wp-filter aegis-toolbar">
					<div class="aegis-toolbar-left filter-items">
						<?php if ( $pro_active ) : ?>
							<button type="button" class="button button-primary aegis-preset-add-new">
								<span class="dashicons dashicons-plus-alt2"></span>
								<?php esc_html_e( 'Add New', 'aegis' ); ?>
							</button>
						<?php else : ?>
							<a href="<?php echo esc_url( $create_url ); ?>" class="button button-primary">
								<span class="dashicons dashicons-plus-alt2"></span>
								<?php esc_html_e( 'Add New', 'aegis' ); ?>
								<span class="aegis-pro-badge"><?php esc_html_e( 'Pro', 'aegis' ); ?></span>
							</a>
						<?php endif; ?>
					</div>
					<div class="aegis-toolbar-right search-form">
						<p class="search-box">
							<label for="aegis-preset-search"><?php esc_html_e( 'Search', 'aegis' ); ?></label>
							<input type="search" id="aegis-preset-search" class="aegis-search-input">
						</p>
					</div>
				</div>

				<div class="aegis-hooks-empty aegis-presets-empty" <?php echo $empty ? '' : 'hidden'; ?>>
					<h2><?php esc_html_e( 'No presets yet', 'aegis' ); ?></h2>
					<?php if ( $pro_active ) : ?>
						<p><?php esc_html_e( 'Click Add New to create a reusable visibility rule set, then apply it from any block in the editor.', 'aegis' ); ?></p>
					<?php else : ?>
						<p><?php esc_html_e( 'Install Aegis Pro to save reusable visibility rule sets for the block editor.', 'aegis' ); ?></p>
					<?php endif; ?>
					<p class="aegis-hooks-empty-action">
						<?php if ( $pro_active ) : ?>
							<button type="button" class="button button-primary aegis-preset-add-new">
								<span class="dashicons dashicons-plus-alt2"></span>
								<?php esc_html_e( 'Add New', 'aegis' ); ?>
							</button>
						<?php else : ?>
							<a href="<?php echo esc_url( $create_url ); ?>" class="button button-primary">
								<span class="dashicons dashicons-plus-alt2"></span>
								<?php esc_html_e( 'Add New', 'aegis' ); ?>
								<span class="aegis-pro-badge"><?php esc_html_e( 'Pro', 'aegis' ); ?></span>
							</a>
						<?php endif; ?>
					</p>
				</div>

				<div class="aegis-hooks-reference aegis-presets-list" <?php echo $empty ? 'hidden' : ''; ?>>
					<div class="aegis-hooks-reference-header">
						<span class="dashicons dashicons-visibility"></span>
						<span><?php esc_html_e( 'Your Presets', 'aegis' ); ?></span>
					</div>
					<div class="aegis-admin-table-wrap">
						<table class="aegis-admin-table aegis-presets-table">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Name', 'aegis' ); ?></th>
									<th><?php esc_html_e( 'Date', 'aegis' ); ?></th>
									<th><?php esc_html_e( 'Actions', 'aegis' ); ?></th>
								</tr>
							</thead>
							<tbody id="aegis-visibility-presets-list">
								<?php foreach ( $presets as $preset ) : ?>
									<?php
									$id   = (string) ( $preset['id'] ?? '' );
									$name = (string) ( $preset['name'] ?? '' );
									?>
									<tr data-preset-id="<?php echo esc_attr( $id ); ?>" data-search="<?php echo esc_attr( strtolower( $name ) ); ?>" data-visibility="<?php echo esc_attr( (string) wp_json_encode( $preset['visibility'] ?? array() ) ); ?>">
										<td>
											<strong><?php echo esc_html( $name !== '' ? $name : __( '(no title)', 'aegis' ) ); ?></strong>
										</td>
										<td>
											<?php esc_html_e( 'Last Modified', 'aegis' ); ?>
											<span class="aegis-hooks-meta"><?php echo esc_html( (string) ( $preset['date_label'] ?? '—' ) ); ?></span>
										</td>
										<td>
											<div class="aegis-hooks-row-actions">
												<button type="button" class="button button-compact aegis-preset-edit" data-id="<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Edit', 'aegis' ); ?></button>
												<button type="button" class="button button-compact aegis-delete-preset" data-id="<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Delete', 'aegis' ); ?></button>
											</div>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>

				<?php if ( $pro_active ) : ?>
					<div class="aegis-hooks-reference aegis-preset-editor" hidden>
						<div class="aegis-hooks-reference-header">
							<span class="dashicons dashicons-visibility"></span>
							<span class="aegis-preset-editor-title"><?php esc_html_e( 'New Preset', 'aegis' ); ?></span>
						</div>
						<div class="aegis-snippet-metabox-field">
							<label for="aegis_preset_name"><?php esc_html_e( 'Name', 'aegis' ); ?></label>
							<input type="text" id="aegis_preset_name" class="widefat" />
							<input type="hidden" id="aegis_preset_id" value="" />
						</div>
						<div class="aegis-snippet-metabox-field">
							<label for="aegis_preset_visibility"><?php esc_html_e( 'Visibility rules', 'aegis' ); ?></label>
							<textarea id="aegis_preset_visibility" rows="8" class="widefat code" placeholder='{"userStatus":"logged-in"}'></textarea>
							<p class="description"><?php esc_html_e( 'Applied in the block editor from Apply Preset. JSON matches the block visibility object.', 'aegis' ); ?></p>
						</div>
						<div class="aegis-preset-editor-actions">
							<button type="button" class="button" id="aegis-preset-cancel"><?php esc_html_e( 'Cancel', 'aegis' ); ?></button>
							<button type="button" class="button button-primary" id="aegis-save-preset"><?php esc_html_e( 'Save', 'aegis' ); ?></button>
						</div>
					</div>
				<?php endif; ?>
			</div>
