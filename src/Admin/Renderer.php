<?php
/**
 * Admin Renderer
 *
 * All admin page render methods and reusable UI component helpers
 * for the Aegis settings dashboard.
 *
 * @package Aegis
 * @since   1.0.0
 */

declare(strict_types=1);

namespace Aegis\Plugin\Admin;

use Aegis\Plugin\Settings\Repository as SettingsRepository;

/**
 * Admin Renderer class.
 */
class Renderer {

	/**
	 * Aegis brand mark as a data URI for wp-admin menu registration.
	 */
	public const BRAND_ICON_DATA_URI = 'data:image/svg+xml;base64,PHN2ZyB2aWV3Qm94PSIwIDAgMjQgMjQiIGZpbGw9ImN1cnJlbnRDb2xvciIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cGF0aCBmaWxsLXJ1bGU9ImV2ZW5vZGQiIGQ9Ik0xMC4wNiA3Ljc1IEwxMi4wMiAzLjg3IEwxMy45NSA3LjcyIEwxNi42NSA5LjI5IEwxMi4wMyAwIEw3LjM0IDkuMyBMMTAuMDYgNy43NSBaIE0xOC4zNyAxMi43MiBMMTguMiAxMi4zNiBMMTUuNSAxMC43OSBMMTcuMDIgMTMuNjggTDIwLjA1IDE4LjYxIEwxMi4wMiAxNS4xNyBMMy45OCAxOC42MiBMNi45NiAxMy42OCBMOC4zOSAxMC44MSBMNS42NyAxMi4zOSBMNS41IDEyLjcxIEwwIDIyLjg3IEwxMi4wMSAxNi44NyBMMjQgMjIuOTQgTDE4LjM3IDEyLjcyIFoiLz48L3N2Zz4=';

	/**
	 * User meta key: dashboard Getting Started section has been dismissed.
	 */
	public const GETTING_STARTED_DISMISSED_META = 'aegis_dashboard_getting_started_dismissed';

	/**
	 * Output the inline Aegis brand mark SVG.
	 *
	 * @param int $size Icon width and height in pixels.
	 * @return void
	 */
	public static function render_brand_icon( int $size = 24 ): void {
		echo self::get_brand_icon_html( $size ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Brand mark SVG wrapped for inline use (admin bar, menus).
	 *
	 * @param int    $size  Icon width and height in pixels.
	 * @param string $class Wrapper class. Use `ab-icon` in the admin bar.
	 */
	public static function get_brand_icon_html( int $size = 24, string $class = 'aegis-brand-icon' ): string {
		return sprintf(
			'<span class="%2$s" aria-hidden="true"><svg viewBox="0 0 24 24" width="%1$d" height="%1$d" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10.06 7.75 L12.02 3.87 L13.95 7.72 L16.65 9.29 L12.03 0 L7.34 9.3 L10.06 7.75 Z M18.37 12.72 L18.2 12.36 L15.5 10.79 L17.02 13.68 L20.05 18.61 L12.02 15.17 L3.98 18.62 L6.96 13.68 L8.39 10.81 L5.67 12.39 L5.5 12.71 L0 22.87 L12.01 16.87 L24 22.94 L18.37 12.72 Z"/></svg></span>',
			$size,
			esc_attr( $class )
		);
	}

	/**
	 * Render the full-bleed Aegis header (identity, current screen, actions).
	 *
	 * @return void
	 */
	public function render_top_bar(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page          = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['page'] ) ) : 'aegis-dashboard';
		$pro_active    = $this->is_aegis_pro_active();
		$chrome        = $this->current_admin_chrome( $page );
		$current_label = $chrome['label'];
		$description   = $chrome['description'];

		$docs_url      = 'https://developer.wordpress.org/themes/';
		$github_url    = 'https://github.com/aegiswp/theme';
		$changelog_url = 'https://github.com/aegiswp/theme/releases';
		$support_url   = 'https://www.facebook.com/groups/aegiswp';
		$donate_url    = 'https://paypal.me/aedonation';
		?>
		<div class="aegis-top-bar">
			<div class="aegis-top-bar-inner">
				<div class="aegis-top-bar-left">
					<a class="aegis-top-bar-brand" href="<?php echo esc_url( admin_url( 'admin.php?page=aegis-dashboard' ) ); ?>">
						<?php self::render_brand_icon( 20 ); ?>
						<span class="aegis-top-bar-product"><?php esc_html_e( 'Aegis', 'aegis' ); ?></span>
						<span class="aegis-top-bar-version"><?php echo esc_html( \Aegis\Plugin\VERSION ); ?></span>
					</a>
					<?php if ( $current_label !== '' ) : ?>
						<span class="aegis-top-bar-divider" aria-hidden="true"></span>
						<span class="aegis-top-bar-screen"><?php echo esc_html( $current_label ); ?></span>
					<?php endif; ?>
					<?php if ( $pro_active ) : ?>
						<span class="aegis-top-bar-badge"><?php esc_html_e( 'Pro', 'aegis' ); ?></span>
					<?php endif; ?>
				</div>
				<div class="aegis-top-bar-right">
					<?php if ( ! $pro_active ) : ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=aegis-license' ) ); ?>" class="aegis-top-bar-link">
							<?php esc_html_e( 'Get Pro', 'aegis' ); ?>
						</a>
					<?php endif; ?>
					<?php
					$settings_url = $this->settings_url_for_page( $page );
					if ( $settings_url !== '' ) :
						?>
						<a href="<?php echo esc_url( $settings_url ); ?>" class="aegis-top-bar-link">
							<span class="dashicons dashicons-admin-settings" aria-hidden="true"></span>
							<span><?php esc_html_e( 'Settings', 'aegis' ); ?></span>
						</a>
					<?php endif; ?>
					<details class="aegis-top-bar-resources">
						<summary>
							<span class="dashicons dashicons-book" aria-hidden="true"></span>
							<span><?php esc_html_e( 'Resources', 'aegis' ); ?></span>
						</summary>
						<div class="aegis-top-bar-resources-menu">
							<a href="<?php echo esc_url( $docs_url ); ?>" target="_blank" rel="noopener noreferrer">
								<span class="dashicons dashicons-book"></span>
								<?php esc_html_e( 'Documentation', 'aegis' ); ?>
							</a>
							<a href="<?php echo esc_url( $support_url ); ?>" target="_blank" rel="noopener noreferrer">
								<span class="dashicons dashicons-groups"></span>
								<?php esc_html_e( 'Support Group', 'aegis' ); ?>
							</a>
							<a href="<?php echo esc_url( $changelog_url ); ?>" target="_blank" rel="noopener noreferrer">
								<span class="dashicons dashicons-backup"></span>
								<?php esc_html_e( 'Changelog', 'aegis' ); ?>
							</a>
							<a href="<?php echo esc_url( $github_url ); ?>" target="_blank" rel="noopener noreferrer">
								<span class="dashicons dashicons-editor-code"></span>
								<?php esc_html_e( 'GitHub', 'aegis' ); ?>
							</a>
							<?php if ( ! $pro_active ) : ?>
								<a href="<?php echo esc_url( $donate_url ); ?>" target="_blank" rel="noopener noreferrer">
									<span class="dashicons dashicons-heart"></span>
									<?php esc_html_e( 'Donate', 'aegis' ); ?>
								</a>
							<?php endif; ?>
						</div>
					</details>
				</div>
			</div>
			<?php if ( $description !== '' ) : ?>
				<div class="aegis-page-lede">
					<p><?php echo wp_kses( $description, array( 'a' => array( 'href' => true ) ) ); ?></p>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Current TopBar label and SubBar description for an Aegis admin screen.
	 *
	 * @return array{label: string, description: string}
	 */
	private function current_admin_chrome( string $page ): array {
		$tabs = apply_filters(
			'aegis_admin_tabs',
			array(
				'dashboard' => array(
					'label' => __( 'Dashboard', 'aegis' ),
					'url'   => admin_url( 'admin.php?page=aegis-dashboard' ),
				),
			)
		);

		$tab_key = $this->tab_key_for_page( $page );
		$label   = get_admin_page_title();

		foreach ( $tabs as $key => $tab ) {
			if ( ! is_array( $tab ) || empty( $tab['url'] ) || empty( $tab['label'] ) ) {
				continue;
			}

			$query = wp_parse_url( (string) $tab['url'], PHP_URL_QUERY );
			if ( ! is_string( $query ) ) {
				continue;
			}

			$args = array();
			parse_str( $query, $args );

			if ( isset( $args['page'] ) && (string) $args['page'] === $page ) {
				$label   = (string) $tab['label'];
				$tab_key = is_string( $key ) ? $key : $tab_key;
				break;
			}
		}

		$description = $this->get_admin_page_description( $tab_key );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$subtab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( (string) $_GET['tab'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$is_editing = $page === 'aegis-snippets' && isset( $_GET['edit'] );

		if ( $is_editing ) {
			$label       = __( 'Edit Snippet', 'aegis' );
			$description = __( 'Write the code, choose a type, and pick where it should run.', 'aegis' );
		} elseif ( $page === 'aegis-snippets' && $subtab === 'settings' ) {
			$description = __( 'Control PHP snippets, Safe Mode, frontend previews, and custom injection locations.', 'aegis' );
		} elseif ( $page === 'aegis-hook-patterns' ) {
			$description = sprintf(
				'%s <a href="%s">%s</a>',
				esc_html__( 'Inject block layouts at theme and WordPress hooks.', 'aegis' ),
				esc_url( admin_url( 'admin.php?page=aegis-snippets' ) ),
				esc_html__( 'Use a code snippet instead', 'aegis' )
			);
		} elseif ( $page === 'aegis-visibility-presets' ) {
			$description = sprintf(
				'%s <a href="%s">%s</a>',
				esc_html__( 'Save reusable visibility rule sets for the block editor.', 'aegis' ),
				esc_url( admin_url( 'admin.php?page=aegis-settings' ) ),
				esc_html__( 'Manage condition types', 'aegis' )
			);
		}

		return array(
			'label'       => $label,
			'description' => $description,
		);
	}

	/**
	 * Settings screen for the current Aegis page, if it has one.
	 */
	private function settings_url_for_page( string $page ): string {
		if ( $page === 'aegis-snippets' ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( (string) $_GET['tab'] ) ) : '';

			if ( $tab === 'settings' ) {
				return '';
			}

			return admin_url( 'admin.php?page=aegis-snippets&tab=settings' );
		}

		$urls = array(
			'aegis-modals'              => admin_url( 'admin.php?page=aegis-blocks#modal' ),
			'aegis-hook-patterns'       => admin_url( 'admin.php?page=aegis-settings' ),
			'aegis-visibility-presets'  => admin_url( 'admin.php?page=aegis-settings' ),
		);

		return $urls[ $page ] ?? '';
	}

	private function tab_key_for_page( string $page ): string {
		$map = array(
			'aegis-dashboard'          => 'dashboard',
			'aegis-blocks'             => 'blocks',
			'aegis-modals'             => 'modals',
			'aegis-hook-patterns'      => 'hook-patterns',
			'aegis-snippets'           => 'snippets',
			'aegis-visibility-presets' => 'visibility-presets',
			'aegis-settings'           => 'conditional-logic',
			'aegis-integrations'       => 'integrations',
			'aegis-connectors'         => 'connectors',
			'aegis-performance'        => 'performance',
			'aegis-general-settings'   => 'general-settings',
			'aegis-license'          => 'license',
			'aegis-video-analytics'  => 'video-analytics',
			'aegis-global-styles'    => 'global-styles',
		);

		return $map[ $page ] ?? '';
	}

	/**
	 * Render the page tabs navigation.
	 *
	 * @param string $active_tab The currently active tab.
	 * @return void
	 */
	public function render_page_tabs( string $active_tab = 'dashboard' ): void {
		$this->render_admin_chrome( $active_tab );
	}

	/**
	 * Render the WordPress admin page wrapper (legacy shell removed — use WP admin menu).
	 *
	 * @param string $active_tab        Unused. Kept for backward compatibility.
	 * @param string $page_title        Unused.
	 * @param string $page_description  Unused.
	 */
	public function render_admin_chrome( string $active_tab = 'dashboard', string $page_title = '', string $page_description = '' ): void {
		unset( $active_tab, $page_title, $page_description );
	}

	/**
	 * Close the admin shell opened by render_admin_chrome().
	 */
	public function render_admin_chrome_close(): void {
		// No-op — custom sidebar shell removed.
	}

	/**
	 * Dashicon slug for a primary admin navigation item.
	 */
	private function get_tab_icon( string $tab_key ): string {
		$icons = array(
			'dashboard'         => 'dashboard',
			'blocks'            => 'block-default',
			'conditional-logic' => 'filter',
			'integrations'      => 'admin-plugins',
			'connectors'        => 'cloud',
			'hook-patterns'      => 'admin-links',
			'snippets'           => 'editor-code',
			'visibility-presets' => 'visibility',
			'modals'            => 'welcome-widgets-menus',
			'general-settings'  => 'admin-settings',
			'license'           => 'awards',
		);

		return $icons[ $tab_key ] ?? 'admin-generic';
	}

	/**
	 * Default page description for primary admin screens.
	 */
	private function get_admin_page_description( string $tab_key ): string {
		$descriptions = array(
			'dashboard'         => __( 'Overview of your Aegis setup, quick actions, and system status.', 'aegis' ),
			'blocks'            => __( 'Enable or disable blocks enhancements provided by the framework.', 'aegis' ),
			'modals'            => __( 'Modals on this site. Feature flags stay on Blocks → Modal.', 'aegis' ),
			'hook-patterns'      => __( 'Inject block layouts at theme and WordPress hooks.', 'aegis' ),
			'snippets'           => __( 'Add CSS, JavaScript, HTML, or PHP at injection locations across your site.', 'aegis' ),
			'visibility-presets' => __( 'Save reusable visibility rule sets for the block editor.', 'aegis' ),
			'conditional-logic' => __( 'Enable or disable conditional logic features for the block editor.', 'aegis' ),
			'integrations'      => __( 'Enable or disable third-party plugin integrations.', 'aegis' ),
			'connectors'        => __( 'Connect BunnyCDN, Google Maps, and analytics providers.', 'aegis' ),
			'performance'       => __( 'Site-wide frontend optimizations. WordPress already lazy-loads images and may prefetch in-viewport links.', 'aegis' ),
			'general-settings'  => __( 'Configure general theme settings and features.', 'aegis' ),
			'license'           => __( 'Activate your license key to unlock Pro features and receive automatic updates.', 'aegis' ),
			'video-analytics'   => __( 'View video performance metrics, audience retention, and engagement data.', 'aegis' ),
		);

		return $descriptions[ $tab_key ] ?? '';
	}

	/**
	 * Render horizontal page tabs (legacy — shell sidebar replaces this UI).
	 *
	 * @param string $active_tab The currently active tab.
	 * @deprecated 1.1.0 Use render_admin_chrome().
	 */
	public function render_page_tabs_legacy( string $active_tab = 'dashboard' ): void {
		$tabs = array(
			'dashboard' => array(
				'label' => __( 'Dashboard', 'aegis' ),
				'url'   => admin_url( 'admin.php?page=aegis-dashboard' ),
			),
		);

		$tabs = apply_filters( 'aegis_admin_tabs', $tabs );

		?>
		<div class="aegis-page-tabs" role="tablist">
			<?php foreach ( $tabs as $tab_key => $tab ) : ?>
				<?php
				if ( ! is_array( $tab ) || ! isset( $tab['url'], $tab['label'] ) ) {
					continue;
				}
				?>
				<a href="<?php echo esc_url( $tab['url'] ); ?>"
					class="aegis-page-tab <?php echo $active_tab === $tab_key ? 'active' : ''; ?>"
					role="tab"
					aria-selected="<?php echo $active_tab === $tab_key ? 'true' : 'false'; ?>">
					<?php echo esc_html( $tab['label'] ); ?>
				</a>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Render a toggle control.
	 *
	 * @param string $group   Settings group.
	 * @param string $key     Setting key.
	 * @param string $label   Toggle label.
	 * @param string $desc    Toggle description.
	 * @param array  $options Current options.
	 * @param string $icon         Optional dashicon name.
	 * @param string $option_name  Option key for input name (default: conditional logic).
	 * @param string $plugin_check    Optional Integrations Registry key. Auto-detected for plugin-dependent conditionals.
	 * @param bool   $force_disabled  Extra lock (parent integration off).
	 * @return void
	 */
	public function render_toggle( string $group, string $key, string $label, string $desc, array $options, string $icon = 'admin-generic', string $option_name = SettingsRepository::OPTION_NAME, string $plugin_check = '', bool $force_disabled = false ): void {
		$name     = $option_name . "[{$group}][{$key}]";
		$defaults = $this->get_conditional_defaults_for_option( $option_name );

		if ( $plugin_check === '' && $option_name === SettingsRepository::OPTION_NAME && class_exists( '\Aegis\Plugin\Conditionals\Settings' ) ) {
			$plugin_check = \Aegis\Plugin\Conditionals\Settings::plugin_check_for( $group, $key );
		}

		$plugin_status = $plugin_check !== '' ? $this->get_plugin_status( $plugin_check ) : array( 'class' => '', 'label' => '' );
		$plugin_active = $plugin_check === '' || $plugin_status['class'] === 'active';
		$is_pro        = $option_name === SettingsRepository::OPTION_NAME
			&& class_exists( '\Aegis\Plugin\Conditionals\Settings' )
			&& \Aegis\Plugin\Conditionals\Settings::is_pro_feature( $group, $key );
		$pro_locked    = $is_pro && ! $this->is_aegis_pro_active();
		$is_disabled   = ! $plugin_active || $pro_locked || $force_disabled;
		$checked       = ! $is_disabled && ( $options[ $group ][ $key ] ?? $defaults[ $group ][ $key ] );
		$classes       = 'aegis-toggle-card aegis-toggle-subcard';

		if ( $pro_locked ) {
			$classes .= ' aegis-pro-feature';
		}

		if ( $is_disabled ) {
			$classes .= ' aegis-toggle-disabled';
		}
		?>
		<div class="<?php echo esc_attr( $classes ); ?>">
			<div class="aegis-toggle-info">
				<div class="aegis-toggle-icon">
					<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
				</div>
				<div class="aegis-toggle-text">
					<h3>
						<?php echo esc_html( $label ); ?>
						<?php if ( $plugin_check !== '' && ! $plugin_active ) : ?>
							<span class="aegis-plugin-status <?php echo esc_attr( $plugin_status['class'] ); ?>">
								<?php echo esc_html( $plugin_status['label'] ); ?>
							</span>
						<?php endif; ?>
						<?php if ( $pro_locked ) : ?>
							<span class="aegis-pro-badge">
								<span class="dashicons dashicons-star-filled"></span>
								<?php esc_html_e( 'Pro Feature', 'aegis' ); ?>
							</span>
						<?php endif; ?>
					</h3>
					<p><?php echo esc_html( $desc ); ?></p>
				</div>
			</div>
			<label class="aegis-toggle">
				<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1" <?php checked( $checked ); ?> <?php disabled( $is_disabled ); ?>>
				<span class="aegis-toggle-slider"></span>
			</label>
		</div>
		<?php
	}

	/**
	 * Render a block toggle control.
	 *
	 * @param string $key         Setting key.
	 * @param string $label       Toggle label.
	 * @param string $desc        Toggle description.
	 * @param array  $options     Current options.
	 * @param string $icon        Dashicon name.
	 * @param string $option_name Option name to read defaults from.
	 * @return void
	 */
	public function render_block_toggle( string $key, string $label, string $desc, array $options, string $icon = 'block-default', string $option_name = SettingsRepository::BLOCKS_OPTION ): void {
		$name     = $option_name . "[{$key}]";
		$defaults = $this->get_blocks_defaults_for_option( $option_name );
		$checked  = $options[ $key ] ?? $defaults[ $key ];
		?>
		<div class="aegis-toggle-card">
			<div class="aegis-toggle-info">
				<div class="aegis-toggle-icon">
					<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
				</div>
				<div class="aegis-toggle-text">
					<h3><?php echo esc_html( $label ); ?></h3>
					<p><?php echo esc_html( $desc ); ?></p>
				</div>
			</div>
			<label class="aegis-toggle">
				<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1" <?php checked( $checked ); ?>>
				<span class="aegis-toggle-slider"></span>
			</label>
		</div>
		<?php
	}

	/**
	 * Render a block toggle control with features support.
	 *
	 * @param string $key         Setting key.
	 * @param string $label       Toggle label.
	 * @param string $desc        Toggle description.
	 * @param array  $options     Current options.
	 * @param string $icon        Dashicon name.
	 * @param string $option_name Option name to read defaults from.
	 * @return void
	 */
	public function render_block_toggle_with_features( string $key, string $label, string $desc, array $options, string $icon = 'block-default', string $option_name = SettingsRepository::BLOCKS_OPTION ): void {
		$name     = $option_name . "[{$key}]";
		$defaults = $this->get_blocks_defaults_for_option( $option_name );
		$checked  = $options[ $key ] ?? $defaults[ $key ];
		?>
		<div class="aegis-toggle-card aegis-toggle-main">
			<div class="aegis-toggle-info">
				<div class="aegis-toggle-icon">
					<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
				</div>
				<div class="aegis-toggle-text">
					<h3><?php echo esc_html( $label ); ?></h3>
					<p><?php echo esc_html( $desc ); ?></p>
				</div>
			</div>
			<label class="aegis-toggle">
				<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1" <?php checked( $checked ); ?> class="aegis-block-main-toggle">
				<span class="aegis-toggle-slider"></span>
			</label>
		</div>
		<?php
	}

	/**
	 * Render a block feature toggle control (sub-option).
	 *
	 * @param string $key            Setting key.
	 * @param string $label          Toggle label.
	 * @param string $desc           Toggle description.
	 * @param array  $options        Current options.
	 * @param string $icon           Dashicon name.
	 * @param bool   $is_pro         Whether this is a Pro feature.
	 * @param bool   $force_disabled Force the toggle to be disabled (e.g., when handled by another plugin).
	 * @param string $option_name    Option name to read defaults from.
	 * @return void
	 */
	public function render_block_feature_toggle( string $key, string $label, string $desc, array $options, string $icon = 'admin-generic', bool $is_pro = false, bool $force_disabled = false, string $option_name = SettingsRepository::BLOCKS_OPTION ): void {
		$name        = $option_name . "[{$key}]";
		$defaults    = $this->get_blocks_defaults_for_option( $option_name );
		$checked     = $options[ $key ] ?? $defaults[ $key ] ?? false;
		$has_pro     = $this->is_aegis_pro_active();
		$is_disabled = ( $is_pro && ! $has_pro ) || $force_disabled;

		$classes = 'aegis-toggle-card aegis-toggle-subcard';
		if ( $is_pro && ! $has_pro ) {
			$classes .= ' aegis-pro-feature';
		}
		if ( $is_disabled ) {
			$classes .= ' aegis-toggle-disabled';
		}
		?>
		<div class="<?php echo esc_attr( $classes ); ?>">
			<div class="aegis-toggle-info">
				<div class="aegis-toggle-icon">
					<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
				</div>
				<div class="aegis-toggle-text">
					<h3>
						<?php echo esc_html( $label ); ?>
						<?php if ( $is_pro && ! $has_pro ) : ?>
							<span class="aegis-pro-badge">
								<span class="dashicons dashicons-star-filled"></span>
								<?php echo $has_pro ? esc_html__( 'Pro', 'aegis' ) : esc_html__( 'Pro Feature', 'aegis' ); ?>
							</span>
						<?php endif; ?>
					</h3>
					<p><?php echo esc_html( $desc ); ?></p>
				</div>
			</div>
			<label class="aegis-toggle">
				<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1" <?php checked( $checked ); ?> <?php disabled( $is_disabled ); ?>>
				<span class="aegis-toggle-slider"></span>
			</label>
		</div>
		<?php
	}

	/**
	 * Check if Aegis Pro plugin is active.
	 *
	 * @return bool
	 */
	public function is_aegis_pro_active(): bool {
		return defined( 'AEGIS_PRO_VERSION' ) || class_exists( 'Aegis_Pro' ) || class_exists( 'AegisPro\\Plugin' );
	}

	/**
	 * Render an integration toggle control.
	 *
	 * @param string $key          Setting key.
	 * @param string $label        Toggle label.
	 * @param string $desc         Toggle description.
	 * @param array  $options      Current options.
	 * @param string $plugin_check Plugin check identifier.
	 * @param string $icon         Dashicon name.
	 * @param string $option_name  Option name to read defaults from.
	 * @return void
	 */
	public function render_integration_toggle( string $key, string $label, string $desc, array $options, string $plugin_check = '', string $icon = 'admin-plugins', string $option_name = SettingsRepository::INTEGRATIONS_OPTION ): void {
		$defaults      = class_exists( '\Aegis\Plugin\Integrations\Settings' )
			? \Aegis\Plugin\Integrations\Settings::INTEGRATION_DEFAULTS
			: ( class_exists( \Aegis\Plugin\Settings\Repository::class )
				? \Aegis\Plugin\Settings\Repository::INTEGRATION_DEFAULTS
				: array() );
		$name          = $option_name . "[{$key}]";
		$checked       = $options[ $key ] ?? $defaults[ $key ];
		$plugin_status = $this->get_plugin_status( $plugin_check );
		$is_installed  = $plugin_check === '' || $plugin_status['class'] === 'active';
		$is_enabled    = $is_installed && $checked;
		?>
		<div class="aegis-toggle-card <?php echo ! $is_installed ? 'aegis-toggle-disabled' : ''; ?>">
			<div class="aegis-toggle-info">
				<div class="aegis-toggle-icon">
					<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
				</div>
				<div class="aegis-toggle-text">
					<h3>
						<?php echo esc_html( $label ); ?>
						<?php if ( $plugin_check ) : ?>
							<span class="aegis-plugin-status <?php echo esc_attr( $plugin_status['class'] ); ?>">
								<?php echo esc_html( $plugin_status['label'] ); ?>
							</span>
						<?php endif; ?>
					</h3>
					<p><?php echo esc_html( $desc ); ?></p>
				</div>
			</div>
			<label class="aegis-toggle">
				<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1" <?php checked( $is_enabled ); ?> <?php disabled( ! $is_installed ); ?>>
				<span class="aegis-toggle-slider"></span>
			</label>
		</div>
		<?php
		$seo_plugins = array( 'rank_math', 'yoast_seo', 'aioseo', 'seopress' );

		if ( in_array( $key, $seo_plugins, true ) && $is_installed ) :
			$plugin_label = $label;
			$pro_active   = $this->is_aegis_pro_active();
			$seo_locked   = ! $is_enabled;
			$sitemap_lock = $seo_locked || ! $pro_active;
			?>
		<div class="aegis-toggle-suboptions">
			<div class="aegis-toggle-card aegis-toggle-subcard <?php echo $sitemap_lock ? ( ! $pro_active ? 'aegis-pro-feature aegis-toggle-disabled' : 'aegis-toggle-disabled' ) : ''; ?>">
				<div class="aegis-toggle-info">
					<div class="aegis-toggle-icon">
						<span class="dashicons dashicons-video-alt3"></span>
					</div>
					<div class="aegis-toggle-text">
						<h3>
							<?php esc_html_e( 'Video Sitemap', 'aegis' ); ?>
							<?php if ( ! $pro_active ) : ?>
							<span class="aegis-pro-badge">
								<span class="dashicons dashicons-star-filled"></span>
								<?php esc_html_e( 'Pro', 'aegis' ); ?>
							</span>
							<?php endif; ?>
						</h3>
						<p>
							<?php
							printf(
								/* translators: %s: SEO plugin name */
								esc_html__( 'Let %s handle Video Sitemap instead of the Blocks → Video extra. When enabled, that extra is disabled; Aegis still adds videos to this plugin\'s sitemap.', 'aegis' ),
								esc_html( $plugin_label )
							);
							?>
						</p>
					</div>
				</div>
				<label class="aegis-toggle">
					<input type="checkbox" name="<?php echo esc_attr( $option_name . '[seo_video_sitemap]' ); ?>" value="1" <?php checked( $is_enabled && $pro_active && ! empty( $options['seo_video_sitemap'] ) ); ?> <?php disabled( $sitemap_lock ); ?>>
					<span class="aegis-toggle-slider"></span>
				</label>
			</div>

			<div class="aegis-toggle-card aegis-toggle-subcard<?php echo $seo_locked ? ' aegis-toggle-disabled' : ''; ?>">
				<div class="aegis-toggle-info">
					<div class="aegis-toggle-icon">
						<span class="dashicons dashicons-editor-help"></span>
					</div>
					<div class="aegis-toggle-text">
						<h3><?php esc_html_e( 'FAQ Schema', 'aegis' ); ?></h3>
						<p>
							<?php
							printf(
								/* translators: %s: SEO plugin name */
								esc_html__( 'Let %s handle FAQ Schema for Accordion List. When enabled, built-in FAQPage markup is suppressed.', 'aegis' ),
								esc_html( $plugin_label )
							);
							?>
						</p>
					</div>
				</div>
				<label class="aegis-toggle">
					<input type="checkbox" name="<?php echo esc_attr( $option_name . '[seo_faq_schema]' ); ?>" value="1" <?php checked( $is_enabled && ! empty( $options['seo_faq_schema'] ) ); ?> <?php disabled( $seo_locked ); ?>>
					<span class="aegis-toggle-slider"></span>
				</label>
			</div>

			<div class="aegis-toggle-card aegis-toggle-subcard<?php echo $seo_locked ? ' aegis-toggle-disabled' : ''; ?>">
				<div class="aegis-toggle-info">
					<div class="aegis-toggle-icon">
						<span class="dashicons dashicons-calendar-alt"></span>
					</div>
					<div class="aegis-toggle-text">
						<h3><?php esc_html_e( 'Event Schema', 'aegis' ); ?></h3>
						<p>
							<?php
							printf(
								/* translators: %s: SEO plugin name */
								esc_html__( 'Let %s handle Event Schema for the Countdown block. When enabled, built-in Event markup is suppressed.', 'aegis' ),
								esc_html( $plugin_label )
							);
							?>
						</p>
					</div>
				</div>
				<label class="aegis-toggle">
					<input type="checkbox" name="<?php echo esc_attr( $option_name . '[seo_event_schema]' ); ?>" value="1" <?php checked( $is_enabled && ! empty( $options['seo_event_schema'] ) ); ?> <?php disabled( $seo_locked ); ?>>
					<span class="aegis-toggle-slider"></span>
				</label>
			</div>

			<div class="aegis-toggle-card aegis-toggle-subcard<?php echo $seo_locked ? ' aegis-toggle-disabled' : ''; ?>">
				<div class="aegis-toggle-info">
					<div class="aegis-toggle-icon">
						<span class="dashicons dashicons-location"></span>
					</div>
					<div class="aegis-toggle-text">
						<h3><?php esc_html_e( 'Local Business Schema', 'aegis' ); ?></h3>
						<p>
							<?php
							printf(
								/* translators: %s: SEO plugin name */
								esc_html__( 'Let %s handle Local Business Schema for the Map block. When enabled, built-in LocalBusiness markup is suppressed.', 'aegis' ),
								esc_html( $plugin_label )
							);
							?>
						</p>
					</div>
				</div>
				<label class="aegis-toggle">
					<input type="checkbox" name="<?php echo esc_attr( $option_name . '[seo_local_schema]' ); ?>" value="1" <?php checked( $is_enabled && ! empty( $options['seo_local_schema'] ) ); ?> <?php disabled( $seo_locked ); ?>>
					<span class="aegis-toggle-slider"></span>
				</label>
			</div>

			<div class="aegis-toggle-card aegis-toggle-subcard<?php echo $seo_locked ? ' aegis-toggle-disabled' : ''; ?>">
				<div class="aegis-toggle-info">
					<div class="aegis-toggle-icon">
						<span class="dashicons dashicons-format-video"></span>
					</div>
					<div class="aegis-toggle-text">
						<h3><?php esc_html_e( 'Video Schema', 'aegis' ); ?></h3>
						<p>
							<?php
							printf(
								/* translators: %s: SEO plugin name */
								esc_html__( 'Let %s handle Video Schema for the Video block. When enabled, built-in VideoObject markup is suppressed.', 'aegis' ),
								esc_html( $plugin_label )
							);
							?>
						</p>
					</div>
				</div>
				<label class="aegis-toggle">
					<input type="checkbox" name="<?php echo esc_attr( $option_name . '[seo_video_schema]' ); ?>" value="1" <?php checked( $is_enabled && ! empty( $options['seo_video_schema'] ) ); ?> <?php disabled( $seo_locked ); ?>>
					<span class="aegis-toggle-slider"></span>
				</label>
			</div>
		</div>
		<?php endif; ?>
		<?php
		// Co-Authors Plus extras — gated on the plugin and parent integration.
		if ( $key === 'co_authors_plus' && defined( 'Aegis\\Plugin\\VERSION' ) ) :
			$pro_active          = $this->is_aegis_pro_active();
			$cap_locked          = ! $is_installed || ! $pro_active || ! $is_enabled;
			$cap_social_checked  = ! $cap_locked && ( $options['cap_social_links'] ?? false );
			$cap_roles_checked   = ! $cap_locked && ( $options['cap_role_badges'] ?? false );
			$cap_pattern_options = get_option( 'aegis_pattern_control', array() );
			$cap_keep_patterns   = ! $cap_locked && ( $cap_pattern_options['coauthors_keep_patterns'] ?? false );
			?>
		<div class="aegis-toggle-suboptions">
			<div class="aegis-toggle-card aegis-toggle-subcard aegis-toggle-subcard-accent">
				<div class="aegis-toggle-info">
					<div class="aegis-toggle-icon">
						<span class="dashicons dashicons-info"></span>
					</div>
					<div class="aegis-toggle-text">
						<p style="margin:0;"><?php esc_html_e( 'Co-Authors Plus integration is provided by the Aegis plugin with improved guest author support.', 'aegis' ); ?></p>
					</div>
				</div>
			</div>
			<div class="aegis-toggle-card aegis-toggle-subcard">
				<div class="aegis-toggle-info">
					<div class="aegis-toggle-icon">
						<span class="dashicons dashicons-admin-site-alt3"></span>
					</div>
					<div class="aegis-toggle-text">
						<h3><?php esc_html_e( 'Author Schema', 'aegis' ); ?></h3>
						<p><?php esc_html_e( 'Outputs JSON-LD Person schema for each co-author on singular posts.', 'aegis' ); ?></p>
					</div>
				</div>
			</div>

			<?php
			$cap_social_key = 'cap_social_links';
			?>
			<div class="aegis-toggle-card aegis-toggle-subcard <?php echo $cap_locked ? ( $pro_active ? 'aegis-toggle-disabled' : 'aegis-pro-feature aegis-toggle-disabled' ) : ''; ?>">
				<div class="aegis-toggle-info">
					<div class="aegis-toggle-icon">
						<span class="dashicons dashicons-share"></span>
					</div>
					<div class="aegis-toggle-text">
						<h3>
							<?php esc_html_e( 'Social Links', 'aegis' ); ?>
							<?php if ( ! $is_installed ) : ?>
							<span class="aegis-plugin-status <?php echo esc_attr( $plugin_status['class'] ); ?>">
								<?php echo esc_html( $plugin_status['label'] ); ?>
							</span>
							<?php endif; ?>
							<?php if ( ! $pro_active ) : ?>
							<span class="aegis-pro-badge">
								<span class="dashicons dashicons-star-filled"></span>
								<?php esc_html_e( 'Pro', 'aegis' ); ?>
							</span>
							<?php endif; ?>
						</h3>
						<p><?php esc_html_e( 'Display social media links for each guest author (Facebook, LinkedIn, GitHub, Instagram, Bluesky, Website).', 'aegis' ); ?></p>
					</div>
				</div>
				<label class="aegis-toggle">
					<input type="checkbox" name="<?php echo esc_attr( $option_name . "[{$cap_social_key}]" ); ?>" value="1" <?php checked( $cap_social_checked ); ?> <?php disabled( $cap_locked ); ?>>
					<span class="aegis-toggle-slider"></span>
				</label>
			</div>

			<?php
			$cap_roles_key = 'cap_role_badges';
			?>
			<div class="aegis-toggle-card aegis-toggle-subcard <?php echo $cap_locked ? ( $pro_active ? 'aegis-toggle-disabled' : 'aegis-pro-feature aegis-toggle-disabled' ) : ''; ?>">
				<div class="aegis-toggle-info">
					<div class="aegis-toggle-icon">
						<span class="dashicons dashicons-nametag"></span>
					</div>
					<div class="aegis-toggle-text">
						<h3>
							<?php esc_html_e( 'Role Badges', 'aegis' ); ?>
							<?php if ( ! $is_installed ) : ?>
							<span class="aegis-plugin-status <?php echo esc_attr( $plugin_status['class'] ); ?>">
								<?php echo esc_html( $plugin_status['label'] ); ?>
							</span>
							<?php endif; ?>
							<?php if ( ! $pro_active ) : ?>
							<span class="aegis-pro-badge">
								<span class="dashicons dashicons-star-filled"></span>
								<?php esc_html_e( 'Pro', 'aegis' ); ?>
							</span>
							<?php endif; ?>
						</h3>
						<p><?php esc_html_e( 'Show contribution role badges below author names (e.g., Researcher, Editor, Photographer).', 'aegis' ); ?></p>
					</div>
				</div>
				<label class="aegis-toggle">
					<input type="checkbox" name="<?php echo esc_attr( $option_name . "[{$cap_roles_key}]" ); ?>" value="1" <?php checked( $cap_roles_checked ); ?> <?php disabled( $cap_locked ); ?>>
					<span class="aegis-toggle-slider"></span>
				</label>
			</div>

			<div class="aegis-toggle-card aegis-toggle-subcard <?php echo $cap_locked ? ( $pro_active ? 'aegis-toggle-disabled' : 'aegis-pro-feature aegis-toggle-disabled' ) : ''; ?>">
				<div class="aegis-toggle-info">
					<div class="aegis-toggle-icon">
						<span class="dashicons dashicons-layout"></span>
					</div>
					<div class="aegis-toggle-text">
						<h3>
							<?php esc_html_e( 'Co-Authors Plus Patterns', 'aegis' ); ?>
							<?php if ( ! $is_installed ) : ?>
							<span class="aegis-plugin-status <?php echo esc_attr( $plugin_status['class'] ); ?>">
								<?php echo esc_html( $plugin_status['label'] ); ?>
							</span>
							<?php endif; ?>
							<?php if ( ! $pro_active ) : ?>
							<span class="aegis-pro-badge">
								<span class="dashicons dashicons-star-filled"></span>
								<?php esc_html_e( 'Pro', 'aegis' ); ?>
							</span>
							<?php endif; ?>
						</h3>
						<p><?php esc_html_e( 'Remove default Co-Authors Plus block patterns from the editor.', 'aegis' ); ?></p>
					</div>
				</div>
				<label class="aegis-toggle">
					<input type="checkbox" name="aegis_pattern_control[coauthors_keep_patterns]" value="1" <?php checked( $cap_keep_patterns ); ?> <?php disabled( $cap_locked ); ?>>
					<span class="aegis-toggle-slider"></span>
				</label>
			</div>
		</div>
		<?php endif; ?>
		<?php
	}

	/**
	 * Pro pattern-control sub-toggles for a plugin on Integrations.
	 *
	 * @param string               $integration_key Integration key.
	 * @param array<string, mixed> $pattern_options Stored aegis_pattern_control values.
	 */
	public function render_pattern_control_toggles( string $integration_key, array $pattern_options ): void {
		$has_pro = $this->is_aegis_pro_active();
		$cards   = array();

		if ( $integration_key === 'woocommerce' ) {
			$cards = array(
				array(
					'key'   => 'woocommerce_keep_patterns',
					'icon'  => 'layout',
					'title' => __( 'WooCommerce Patterns', 'aegis' ),
					'desc'  => __( 'Keep WooCommerce\'s own block patterns in the inserter alongside Aegis patterns.', 'aegis' ),
				),
				array(
					'key'   => 'woocommerce_keep_templates',
					'icon'  => 'media-default',
					'title' => __( 'WooCommerce Templates', 'aegis' ),
					'desc'  => __( 'Remove default WooCommerce block templates from the editor.', 'aegis' ),
				),
			);
		} elseif ( $integration_key === 'learndash' ) {
			$cards = array(
				array(
					'key'   => 'learndash_keep_patterns',
					'icon'  => 'layout',
					'title' => __( 'LearnDash Patterns', 'aegis' ),
					'desc'  => __( 'Remove default LearnDash block patterns from the editor.', 'aegis' ),
				),
			);
		} elseif ( $integration_key === 'lifter_lms' ) {
			$cards = array(
				array(
					'key'   => 'lifterlms_keep_patterns',
					'icon'  => 'layout',
					'title' => __( 'LifterLMS Patterns', 'aegis' ),
					'desc'  => __( 'Remove default LifterLMS block patterns from the editor.', 'aegis' ),
				),
			);
		} elseif ( $integration_key === 'sensei_lms' ) {
			$cards = array(
				array(
					'key'   => 'sensei_keep_patterns',
					'icon'  => 'layout',
					'title' => __( 'Sensei LMS Patterns', 'aegis' ),
					'desc'  => __( 'Remove default Sensei LMS block patterns from the editor.', 'aegis' ),
				),
			);
		} elseif ( $integration_key === 'fluent_forms' ) {
			$cards = array(
				array(
					'key'   => 'fluentforms_keep_patterns',
					'icon'  => 'layout',
					'title' => __( 'Fluent Forms Patterns', 'aegis' ),
					'desc'  => __( 'Remove default Fluent Forms block patterns from the editor.', 'aegis' ),
				),
			);
		} elseif ( $integration_key === 'fluent_booking' ) {
			$cards = array(
				array(
					'key'   => 'fluentbooking_keep_patterns',
					'icon'  => 'layout',
					'title' => __( 'Fluent Booking Patterns', 'aegis' ),
					'desc'  => __( 'Remove default Fluent Booking block patterns from the editor.', 'aegis' ),
				),
			);
		}

		if ( $cards === array() ) {
			return;
		}

		$plugin_status = $this->get_plugin_status( $integration_key );
		$plugin_active = $plugin_status['class'] === 'active';
		$parent_on     = class_exists( \Aegis\Plugin\Integrations\Settings::class )
			&& \Aegis\Plugin\Integrations\Settings::is_integration_enabled( $integration_key );

		foreach ( $cards as $card ) :
			$is_disabled = ! $has_pro || ! $plugin_active || ! $parent_on;
			$checked     = ! $is_disabled && ! empty( $pattern_options[ $card['key'] ] );
			$classes     = 'aegis-toggle-card aegis-toggle-subcard';

			if ( ! $has_pro ) {
				$classes .= ' aegis-pro-feature';
			}

			if ( $is_disabled ) {
				$classes .= ' aegis-toggle-disabled';
			}
			?>
		<div class="<?php echo esc_attr( $classes ); ?>">
			<div class="aegis-toggle-info">
				<div class="aegis-toggle-icon">
					<span class="dashicons dashicons-<?php echo esc_attr( $card['icon'] ); ?>"></span>
				</div>
				<div class="aegis-toggle-text">
					<h3>
						<?php echo esc_html( $card['title'] ); ?>
						<?php if ( ! $plugin_active ) : ?>
						<span class="aegis-plugin-status <?php echo esc_attr( $plugin_status['class'] ); ?>">
							<?php echo esc_html( $plugin_status['label'] ); ?>
						</span>
						<?php endif; ?>
						<?php if ( ! $has_pro ) : ?>
						<span class="aegis-pro-badge">
							<span class="dashicons dashicons-star-filled"></span>
							<?php esc_html_e( 'Pro', 'aegis' ); ?>
						</span>
						<?php endif; ?>
					</h3>
					<p><?php echo esc_html( $card['desc'] ); ?></p>
				</div>
			</div>
			<label class="aegis-toggle">
				<input type="checkbox" name="<?php echo esc_attr( 'aegis_pattern_control[' . $card['key'] . ']' ); ?>" value="1" <?php checked( $checked ); ?> <?php disabled( $is_disabled ); ?>>
				<span class="aegis-toggle-slider"></span>
			</label>
		</div>
			<?php
		endforeach;
	}

	/**
	 * Get plugin status.
	 *
	 * @param string $plugin_check Plugin check identifier.
	 * @return array Status with class and label.
	 */
	private function get_plugin_status( string $plugin_check ): array {
		if ( empty( $plugin_check ) ) {
			return array(
				'class' => '',
				'label' => '',
			);
		}

		if ( class_exists( \Aegis\Plugin\Integrations\Registry::class ) ) {
			return \Aegis\Plugin\Integrations\Registry::get_plugin_status( $plugin_check );
		}

		return array(
			'class' => 'not-installed',
			'label' => __( 'Not Installed', 'aegis' ),
		);
	}

	/**
	 * Default array for conditional logic toggles by option name.
	 *
	 * @param string $option_name Settings option key.
	 * @return array<string, array<string, bool>>
	 */
	private function get_conditional_defaults_for_option( string $option_name ): array {
		if ( $option_name === 'aegis_conditional_logic' && class_exists( '\Aegis\Plugin\Conditionals\Settings' ) ) {
			return \Aegis\Plugin\Conditionals\Settings::DEFAULTS;
		}

		return class_exists( \Aegis\Plugin\Settings\Repository::class )
			? \Aegis\Plugin\Settings\Repository::DEFAULTS
			: array();
	}

	/**
	 * Default array for block toggles by option name.
	 *
	 * @param string $option_name Settings option key.
	 * @return array<string, bool>
	 */
	private function get_blocks_defaults_for_option( string $option_name ): array {
		if ( $option_name === 'aegis_blocks' && class_exists( '\Aegis\Plugin\Blocks\Settings' ) ) {
			return \Aegis\Plugin\Blocks\Settings::DEFAULTS;
		}

		return class_exists( \Aegis\Plugin\Settings\Repository::class )
			? \Aegis\Plugin\Settings\Repository::BLOCKS_DEFAULTS
			: array();
	}

	/**
	 * Render the toolbar with search and export/import.
	 *
	 * @param string $group Settings group for export/import actions.
	 * @return void
	 */
	public function render_toolbar( string $group = '' ): void {
		?>
		<div class="wp-filter aegis-toolbar">
			<div class="aegis-toolbar-left filter-items">
				<?php if ( $group ) : ?>
				<button type="button" class="button aegis-reset-settings" data-group="<?php echo esc_attr( $group ); ?>">
					<?php esc_html_e( 'Reset Defaults', 'aegis' ); ?>
				</button>
				<?php endif; ?>
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
					<label for="aegis-search-settings"><?php esc_html_e( 'Search settings', 'aegis' ); ?></label>
					<input type="search" id="aegis-search-settings" class="aegis-search-input">
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Render section header with bulk actions.
	 *
	 * @param string $title       Section title.
	 * @param string $description Section description.
	 * @param bool   $show_bulk_actions Whether to show bulk action buttons.
	 * @param string $icon             Optional dashicon name for the title.
	 * @param string $plugin_check     Optional Integrations Registry key for a plugin-gated section.
	 * @return void
	 */
	public function render_section_header( string $title, string $description, bool $show_bulk_actions = true, string $icon = '', string $plugin_check = '' ): void {
		$pro_installed = $this->is_aegis_pro_active() ? 'true' : 'false';
		$plugin_status = $plugin_check !== '' ? $this->get_plugin_status( $plugin_check ) : array( 'class' => '', 'label' => '' );
		$plugin_active = $plugin_check === '' || $plugin_status['class'] === 'active';

		if ( ! $plugin_active ) {
			$plugin_label = '';
			if ( class_exists( \Aegis\Plugin\Integrations\Registry::class ) ) {
				$integration  = \Aegis\Plugin\Integrations\Registry::get( $plugin_check )
					?? \Aegis\Plugin\Integrations\Registry::get_by_plugin_check( $plugin_check );
				$plugin_label = $integration['label'] ?? '';
			}

			if ( $plugin_label !== '' ) {
				$description .= ' ' . sprintf(
					/* translators: %s: plugin name */
					__( '%s must be installed and active to enable these conditions.', 'aegis' ),
					$plugin_label
				);
			}
		}
		?>
		<div class="aegis-section-header">
			<div class="aegis-section-title">
				<h2>
					<?php if ( $icon ) : ?>
					<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
					<?php endif; ?>
					<?php echo esc_html( $title ); ?>
					<?php if ( $plugin_check !== '' ) : ?>
						<span class="aegis-plugin-status <?php echo esc_attr( $plugin_status['class'] ); ?>">
							<?php echo esc_html( $plugin_status['label'] ); ?>
						</span>
					<?php endif; ?>
				</h2>
				<p class="description"><?php echo esc_html( $description ); ?></p>
			</div>
			<?php if ( $show_bulk_actions && $plugin_active ) : ?>
			<div class="aegis-bulk-actions">
				<button type="button" class="button aegis-bulk-enable" data-pro-installed="<?php echo esc_attr( $pro_installed ); ?>"><?php esc_html_e( 'Enable All', 'aegis' ); ?></button>
				<button type="button" class="button aegis-bulk-disable" data-pro-installed="<?php echo esc_attr( $pro_installed ); ?>"><?php esc_html_e( 'Disable All', 'aegis' ); ?></button>
			</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Dark stacked subheading used to split groups inside one settings tab.
	 *
	 * Matches Connectors → BunnyCDN API / CDN / Storage headers.
	 */
	public function render_stack_header( string $title, string $description, string $icon = '' ): void {
		?>
		<div class="aegis-api-section-header">
			<div class="aegis-api-config-title">
				<div class="aegis-api-config-heading">
					<?php if ( $icon !== '' ) : ?>
					<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
					<?php endif; ?>
					<span><?php echo esc_html( $title ); ?></span>
				</div>
				<p><?php echo esc_html( $description ); ?></p>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the Aegis dashboard admin page.
	 *
	 * @return void
	 */
	public function render_dashboard_page(): void {
		$theme = wp_get_theme();

		// Gather live stats.
		$block_settings = class_exists( '\Aegis\Plugin\Blocks\Settings' )
			? \Aegis\Plugin\Blocks\Settings::get_settings()
			: get_option( SettingsRepository::BLOCKS_OPTION, array() );
		$active_blocks  = count( array_filter( is_array( $block_settings ) ? $block_settings : array() ) );

		$conditionals        = SettingsRepository::get_settings();
		$active_conditionals = 0;
		foreach ( $conditionals as $group ) {
			if ( is_array( $group ) ) {
				$active_conditionals += count( array_filter( $group ) );
			}
		}

		$integrations        = get_option( SettingsRepository::INTEGRATIONS_OPTION, array() );
		$active_integrations = count( array_filter( is_array( $integrations ) ? $integrations : array() ) );

		$hook_patterns_count = 0;
		if ( class_exists( \Aegis\Plugin\Hooks\PatternsManager::class ) ) {
			$hook_post_type = \Aegis\Plugin\Hooks\PatternsManager::POST_TYPE;
			if ( post_type_exists( $hook_post_type ) ) {
				$hook_counts         = wp_count_posts( $hook_post_type );
				$hook_patterns_count = isset( $hook_counts->publish ) ? (int) $hook_counts->publish : 0;
			}
		}

		$snippets_count = 0;
		if ( class_exists( \Aegis\Plugin\Snippets\Storage::class ) ) {
			$snippets_count = count( \Aegis\Plugin\Snippets\Storage::get_snippets() );
		}

		$pro_active              = defined( 'AEGIS_PRO_VERSION' );
		$show_getting_started    = get_user_meta( get_current_user_id(), self::GETTING_STARTED_DISMISSED_META, true ) !== '1';
		$hook_patterns_available = class_exists( \Aegis\Plugin\Hooks\PatternsManager::class );
		?>
		<div class="wrap aegis-admin-page">
			<div class="aegis-settings-wrap">
				<h1 class="screen-reader-text"><?php esc_html_e( 'Dashboard', 'aegis' ); ?></h1>

				<!-- At-a-Glance Stats -->
				<div class="aegis-dashboard-stats">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=aegis-blocks' ) ); ?>" class="aegis-stat-card">
						<div class="aegis-stat-icon aegis-stat-icon--blue">
							<span class="dashicons dashicons-block-default"></span>
						</div>
						<div class="aegis-stat-content">
							<span class="aegis-stat-value"><?php echo esc_html( (string) $active_blocks ); ?></span>
							<span class="aegis-stat-label"><?php echo esc_html( _n( 'Active Block', 'Active Blocks', $active_blocks, 'aegis' ) ); ?></span>
						</div>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=aegis-settings' ) ); ?>" class="aegis-stat-card">
						<div class="aegis-stat-icon aegis-stat-icon--purple">
							<span class="dashicons dashicons-lock"></span>
						</div>
						<div class="aegis-stat-content">
							<span class="aegis-stat-value"><?php echo esc_html( (string) $active_conditionals ); ?></span>
							<span class="aegis-stat-label"><?php echo esc_html( _n( 'Conditional', 'Conditionals', $active_conditionals, 'aegis' ) ); ?></span>
						</div>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=aegis-integrations' ) ); ?>" class="aegis-stat-card">
						<div class="aegis-stat-icon aegis-stat-icon--green">
							<span class="dashicons dashicons-admin-plugins"></span>
						</div>
						<div class="aegis-stat-content">
							<span class="aegis-stat-value"><?php echo esc_html( (string) $active_integrations ); ?></span>
							<span class="aegis-stat-label"><?php echo esc_html( _n( 'Integration', 'Integrations', $active_integrations, 'aegis' ) ); ?></span>
						</div>
					</a>
					<?php if ( $hook_patterns_available ) : ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=aegis-hook-patterns' ) ); ?>" class="aegis-stat-card">
						<div class="aegis-stat-icon aegis-stat-icon--amber">
							<span class="dashicons dashicons-editor-code"></span>
						</div>
						<div class="aegis-stat-content">
							<span class="aegis-stat-value"><?php echo esc_html( (string) $hook_patterns_count ); ?></span>
							<span class="aegis-stat-label"><?php echo esc_html( _n( 'Hook Pattern', 'Hook Patterns', $hook_patterns_count, 'aegis' ) ); ?></span>
						</div>
					</a>
					<?php endif; ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=aegis-snippets' ) ); ?>" class="aegis-stat-card">
						<div class="aegis-stat-icon aegis-stat-icon--teal">
							<span class="dashicons dashicons-editor-code"></span>
						</div>
						<div class="aegis-stat-content">
							<span class="aegis-stat-value"><?php echo esc_html( (string) $snippets_count ); ?></span>
							<span class="aegis-stat-label"><?php echo esc_html( _n( 'Code Snippet', 'Code Snippets', $snippets_count, 'aegis' ) ); ?></span>
						</div>
					</a>
				</div>

				<!-- Shortcuts: Site Editor destinations plus Aegis tools that are easy to miss. -->
				<div class="aegis-dashboard-section">
					<h2><?php esc_html_e( 'Shortcuts', 'aegis' ); ?></h2>
					<div class="aegis-dashboard-actions">
						<a href="<?php echo esc_url( admin_url( 'site-editor.php' ) ); ?>" class="aegis-action-card">
							<div class="aegis-action-card-icon">
								<span class="dashicons dashicons-admin-appearance"></span>
							</div>
							<span class="aegis-action-card-title"><?php esc_html_e( 'Site Editor', 'aegis' ); ?></span>
							<span class="aegis-action-card-desc"><?php esc_html_e( 'Edit your site visually', 'aegis' ); ?></span>
						</a>
						<a href="<?php echo esc_url( admin_url( 'site-editor.php?path=%2Fwp_template' ) ); ?>" class="aegis-action-card">
							<div class="aegis-action-card-icon">
								<span class="dashicons dashicons-screenoptions"></span>
							</div>
							<span class="aegis-action-card-title"><?php esc_html_e( 'Templates', 'aegis' ); ?></span>
							<span class="aegis-action-card-desc"><?php esc_html_e( 'Manage page templates', 'aegis' ); ?></span>
						</a>
						<a href="<?php echo esc_url( admin_url( 'site-editor.php?path=%2Fpatterns' ) ); ?>" class="aegis-action-card">
							<div class="aegis-action-card-icon">
								<span class="dashicons dashicons-layout"></span>
							</div>
							<span class="aegis-action-card-title"><?php esc_html_e( 'Patterns', 'aegis' ); ?></span>
							<span class="aegis-action-card-desc"><?php esc_html_e( 'Browse block patterns', 'aegis' ); ?></span>
						</a>
						<a href="<?php echo esc_url( admin_url( 'site-editor.php?path=%2Fwp_global_styles' ) ); ?>" class="aegis-action-card">
							<div class="aegis-action-card-icon">
								<span class="dashicons dashicons-art"></span>
							</div>
							<span class="aegis-action-card-title"><?php esc_html_e( 'Global Styles', 'aegis' ); ?></span>
							<span class="aegis-action-card-desc"><?php esc_html_e( 'Customize colors & fonts', 'aegis' ); ?></span>
						</a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=aegis-snippets' ) ); ?>" class="aegis-action-card">
							<div class="aegis-action-card-icon">
								<span class="dashicons dashicons-editor-code"></span>
							</div>
							<span class="aegis-action-card-title"><?php esc_html_e( 'Code Snippets', 'aegis' ); ?></span>
							<span class="aegis-action-card-desc"><?php esc_html_e( 'Custom CSS, JS, HTML, and PHP', 'aegis' ); ?></span>
						</a>
						<?php if ( $hook_patterns_available ) : ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=aegis-hook-patterns' ) ); ?>" class="aegis-action-card">
							<div class="aegis-action-card-icon">
								<span class="dashicons dashicons-admin-links"></span>
							</div>
							<span class="aegis-action-card-title"><?php esc_html_e( 'Hook Patterns', 'aegis' ); ?></span>
							<span class="aegis-action-card-desc"><?php esc_html_e( 'Inject layouts at theme hooks', 'aegis' ); ?></span>
						</a>
						<?php endif; ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=aegis-performance' ) ); ?>" class="aegis-action-card">
							<div class="aegis-action-card-icon">
								<span class="dashicons dashicons-performance"></span>
							</div>
							<span class="aegis-action-card-title"><?php esc_html_e( 'Performance', 'aegis' ); ?></span>
							<span class="aegis-action-card-desc"><?php esc_html_e( 'Frontend scripts, embeds, and Query Loop gates', 'aegis' ); ?></span>
						</a>
						<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=page' ) ); ?>" class="aegis-action-card">
							<div class="aegis-action-card-icon">
								<span class="dashicons dashicons-plus-alt2"></span>
							</div>
							<span class="aegis-action-card-title"><?php esc_html_e( 'New Page', 'aegis' ); ?></span>
							<span class="aegis-action-card-desc"><?php esc_html_e( 'Create a new page', 'aegis' ); ?></span>
						</a>
					</div>
				</div>

				<?php if ( $show_getting_started ) : ?>
				<div class="aegis-dashboard-section aegis-dashboard-getting-started-section">
					<div class="aegis-dashboard-getting-started-header">
						<h2><?php esc_html_e( 'Getting Started', 'aegis' ); ?></h2>
						<button type="button" class="button-link aegis-dismiss-getting-started">
							<?php esc_html_e( 'Dismiss', 'aegis' ); ?>
						</button>
					</div>
					<div class="aegis-dashboard-getting-started">
						<a href="<?php echo esc_url( admin_url( 'site-editor.php' ) ); ?>" class="aegis-license-feature-card">
							<div class="aegis-license-feature-icon">
								<span class="dashicons dashicons-admin-customizer"></span>
							</div>
							<h3><?php esc_html_e( 'Customize Your Theme', 'aegis' ); ?></h3>
							<p><?php esc_html_e( 'Use the Site Editor to customize templates, headers, footers, and global styles with full block control.', 'aegis' ); ?></p>
						</a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=aegis-settings' ) ); ?>" class="aegis-license-feature-card">
							<div class="aegis-license-feature-icon">
								<span class="dashicons dashicons-visibility"></span>
							</div>
							<h3><?php esc_html_e( 'Manage Block Visibility', 'aegis' ); ?></h3>
							<p><?php esc_html_e( 'Show or hide any block based on user roles, capabilities, devices, schedules, and custom conditions.', 'aegis' ); ?></p>
						</a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=aegis-integrations' ) ); ?>" class="aegis-license-feature-card">
							<div class="aegis-license-feature-icon">
								<span class="dashicons dashicons-admin-plugins"></span>
							</div>
							<h3><?php esc_html_e( 'Extend with Integrations', 'aegis' ); ?></h3>
							<p><?php esc_html_e( 'Connect WooCommerce, LMS plugins, and other tools to enhance your block-powered site.', 'aegis' ); ?></p>
						</a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=aegis-snippets' ) ); ?>" class="aegis-license-feature-card">
							<div class="aegis-license-feature-icon">
								<span class="dashicons dashicons-editor-code"></span>
							</div>
							<h3><?php esc_html_e( 'Add Code Snippets', 'aegis' ); ?></h3>
							<p><?php esc_html_e( 'Inject custom CSS, JavaScript, HTML, or PHP at theme and integration hook locations.', 'aegis' ); ?></p>
						</a>
					</div>
				</div>
				<?php endif; ?>

				<div class="aegis-dashboard-columns aegis-dashboard-footer">
					<!-- System Info -->
					<div class="aegis-dashboard-section">
						<div class="aegis-dashboard-system-card">
							<div class="aegis-dashboard-system-card-header">
								<span class="dashicons dashicons-info-outline"></span>
								<span><?php esc_html_e( 'System Info', 'aegis' ); ?></span>
							</div>
							<div class="aegis-system-table">
								<div class="aegis-system-row">
									<span class="aegis-system-label"><?php esc_html_e( 'Theme Version', 'aegis' ); ?></span>
									<span class="aegis-system-value"><?php echo esc_html( $theme->get( 'Version' ) ); ?></span>
								</div>
								<div class="aegis-system-row">
									<span class="aegis-system-label"><?php esc_html_e( 'Aegis Plugin', 'aegis' ); ?></span>
									<span class="aegis-system-value"><?php echo esc_html( \Aegis\Plugin\VERSION ); ?></span>
								</div>
								<div class="aegis-system-row">
									<span class="aegis-system-label"><?php esc_html_e( 'WordPress', 'aegis' ); ?></span>
									<span class="aegis-system-value"><?php echo esc_html( get_bloginfo( 'version' ) ); ?></span>
								</div>
								<div class="aegis-system-row">
									<span class="aegis-system-label"><?php esc_html_e( 'PHP', 'aegis' ); ?></span>
									<span class="aegis-system-value"><?php echo esc_html( PHP_VERSION ); ?></span>
								</div>
								<?php if ( $pro_active ) : ?>
									<div class="aegis-system-row">
										<span class="aegis-system-label"><?php esc_html_e( 'Aegis Pro', 'aegis' ); ?></span>
										<span class="aegis-system-value"><?php echo esc_html( AEGIS_PRO_VERSION ); ?></span>
									</div>
								<?php endif; ?>
							</div>
						</div>
					</div>

					<!-- Pro Status -->
					<div class="aegis-dashboard-section">
						<div class="aegis-dashboard-system-card">
							<div class="aegis-dashboard-system-card-header">
								<span class="dashicons dashicons-star-filled"></span>
								<span><?php esc_html_e( 'Aegis Pro', 'aegis' ); ?></span>
							</div>
							<div class="aegis-dashboard-system-card-body">
								<?php if ( $pro_active ) : ?>
									<?php
									if ( class_exists( '\Aegis\Pro\License\Settings' ) ) {
										$license_key = \Aegis\Pro\License\Settings::get_license_key();
										$is_valid    = \Aegis\Pro\License\Settings::is_valid();
										$masked_key  = \Aegis\Pro\License\Settings::get_masked_license_key();
									} else {
										$license_key = get_option( 'aegis_pro_license_key', '' );
										$is_valid    = get_option( 'aegis_pro_license_status', '' ) === 'valid';
										$masked_key  = $license_key !== '' ? substr( $license_key, 0, 8 ) . '••••••••' : '';
									}
									?>
									<div class="aegis-pro-status aegis-pro-status--<?php echo $is_valid ? 'active' : 'inactive'; ?>">
										<span class="dashicons dashicons-<?php echo $is_valid ? 'yes-alt' : 'warning'; ?>"></span>
										<div>
											<strong><?php echo $is_valid ? esc_html__( 'License Active', 'aegis' ) : esc_html__( 'License Inactive', 'aegis' ); ?></strong>
											<?php if ( ! empty( $license_key ) ) : ?>
												<p class="aegis-license-key"><?php echo esc_html( $masked_key ); ?></p>
											<?php else : ?>
												<p><?php esc_html_e( 'Enter your license key to activate Pro features.', 'aegis' ); ?></p>
											<?php endif; ?>
										</div>
									</div>
								<?php else : ?>
									<p class="aegis-dashboard-pro-desc"><?php esc_html_e( 'Unlock advanced features, integrations, and professional tools.', 'aegis' ); ?></p>
									<div class="aegis-dashboard-pro-actions">
										<a href="<?php echo esc_url( admin_url( 'admin.php?page=aegis-license' ) ); ?>" class="button button-primary">
											<span class="dashicons dashicons-cart"></span>
											<?php esc_html_e( 'Get Aegis Pro', 'aegis' ); ?>
										</a>
									</div>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>

			</div>
		</div>
		<?php
	}

	/**
	 * Render shell chrome for the Integrations admin page.
	 *
	 * @return void
	 */
	public function render_integrations_chrome(): void {
		$this->render_admin_chrome( 'integrations' );
	}

	/**
	 * Render shell chrome for the Connectors admin page.
	 *
	 * @return void
	 */
	public function render_connectors_chrome(): void {
		$this->render_admin_chrome( 'connectors' );
	}

	/**
	 * Render shell chrome for the Blocks admin page.
	 *
	 * @return void
	 */
	public function render_blocks_chrome(): void {
		$this->render_admin_chrome( 'blocks' );
	}

	/**
	 * Render shell chrome for the Modals admin page.
	 *
	 * @return void
	 */
	public function render_modals_chrome(): void {
		$this->render_admin_chrome( 'modals' );
	}

	/**
	 * Render shell chrome for the Hooks admin page.
	 *
	 * @return void
	 */
	public function render_hooks_chrome(): void {
		$this->render_admin_chrome( 'hook-patterns' );
	}

	/**
	 * Render shell chrome for the Visibility Presets admin page.
	 *
	 * @return void
	 */
	public function render_visibility_presets_chrome(): void {
		$this->render_admin_chrome( 'visibility-presets' );
	}

	/**
	 * Render shell chrome for the Code Snippets admin page.
	 */
	public function render_snippets_chrome(): void {
		$this->render_admin_chrome( 'snippets' );
	}

	/**
	 * Render shell chrome for the Conditionals admin page.
	 *
	 * @return void
	 */
	public function render_conditionals_chrome(): void {
		$this->render_admin_chrome( 'conditional-logic' );
	}

	/**
	 * Render shell chrome for the General Settings admin page.
	 *
	 * @return void
	 */
	public function render_general_settings_chrome(): void {
		$this->render_admin_chrome( 'general-settings' );
	}

	/**
	 * Render shell chrome for the License admin page.
	 *
	 * @return void
	 */
	public function render_license_chrome(): void {
		$this->render_admin_chrome( 'license' );
	}
}
