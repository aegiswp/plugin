<?php
/**
 * Modal Block - Server-side Render
 *
 * Renders the modal block on the frontend with full accessibility support.
 *
 * @package Aegis
 * @since   1.0.0
 */

declare(strict_types=1);

use Aegis\Framework\ServiceProvider;

defined('ABSPATH') || exit;

if ( isset( $attributes['isEnabled'] ) && ! $attributes['isEnabled'] ) {
	return;
}

$modal_id = ! empty( $attributes['modalId'] ) ? $attributes['modalId'] : 'aegis-modal-' . wp_unique_id();

$modal_type           = $attributes['modalType'] ?? 'popup';
$trigger_type         = $attributes['triggerType'] ?? 'button';
$trigger_text         = $attributes['triggerText'] ?? __( 'Open Modal', 'aegis' );
$trigger_icon         = $attributes['triggerIcon'] ?? '';
$trigger_image_url    = $attributes['triggerImageUrl'] ?? '';
$trigger_image_alt    = $attributes['triggerImageAlt'] ?? '';
$modal_title          = $attributes['modalTitle'] ?? '';
$animation            = $attributes['animation'] ?? 'fade';
$animation_duration   = $attributes['animationDuration'] ?? 200;
$close_on_esc         = $attributes['closeOnEsc'] ?? true;
$close_on_overlay     = $attributes['closeOnOverlay'] ?? true;
$show_close_button    = $attributes['showCloseButton'] ?? true;
$close_button_pos     = $attributes['closeButtonPosition'] ?? 'outside';
$prevent_body_scroll  = $attributes['preventBodyScroll'] ?? true;
$focus_trap           = $attributes['focusTrap'] ?? true;
$return_focus         = $attributes['returnFocus'] ?? true;
$width                = $attributes['width'] ?? '500px';
$max_width            = $attributes['maxWidth'] ?? '90vw';
$height               = $attributes['height'] ?? 'auto';
$max_height           = $attributes['maxHeight'] ?? '90vh';
$overlay_color        = $attributes['overlayColor'] ?? '';
$overlay_blur         = $attributes['overlayBlur'] ?? 0;
$background_color     = $attributes['backgroundColor'] ?? '';
$border_radius        = $attributes['borderRadius'] ?? '8px';
$padding              = $attributes['padding'] ?? '24px';

$scroll_trigger_percent  = $attributes['scrollTriggerPercent'] ?? 50;
$scroll_trigger_once     = $attributes['scrollTriggerOnce'] ?? true;
$exit_intent_sensitivity = $attributes['exitIntentSensitivity'] ?? 20;
$exit_intent_delay       = $attributes['exitIntentDelay'] ?? 0;
$timed_trigger_delay     = $attributes['timedTriggerDelay'] ?? 5000;
$auto_close_delay        = $attributes['autoCloseDelay'] ?? 0;
$show_once               = $attributes['showOnce'] ?? false;
$show_once_expiry        = $attributes['showOnceExpiry'] ?? 7;
$device_visibility       = $attributes['deviceVisibility'] ?? array( 'desktop', 'tablet', 'mobile' );

$trigger_features = array(
	'button'      => 'modal_click',
	'icon'        => 'modal_icon',
	'text'        => 'modal_text',
	'image'       => 'modal_image',
	'scroll'      => 'modal_scroll_depth',
	'exit-intent' => 'modal_exit_intent',
	'timed'       => 'modal_time_delay',
);

$click_triggers = array( 'button', 'text', 'icon', 'image' );

if ( isset( $trigger_features[ $trigger_type ] ) && ! ServiceProvider::is_block_enabled( $trigger_features[ $trigger_type ] ) ) {
	$trigger_type = 'button';

	foreach ( $click_triggers as $candidate ) {
		if ( ServiceProvider::is_block_enabled( $trigger_features[ $candidate ] ) ) {
			$trigger_type = $candidate;
			break;
		}
	}
}

if ( in_array( $modal_type, array( 'off-canvas', 'bottom-sheet' ), true ) && ! ServiceProvider::is_block_enabled( 'modal_offcanvas' ) ) {
	$modal_type = 'popup';
}

if ( 'fullscreen' === $modal_type && ! ServiceProvider::is_block_enabled( 'modal_fullscreen' ) ) {
	$modal_type = 'popup';
}

$allowed_animations = array( 'fade', 'slide-up', 'slide-down', 'slide-left', 'slide-right', 'zoom', 'none' );

if ( 'slide' === $animation ) {
	$animation = 'slide-up';
}

if ( ! ServiceProvider::is_block_enabled( 'modal_animations' ) ) {
	$animation          = 'none';
	$animation_duration = 0;
} elseif ( ! in_array( $animation, $allowed_animations, true ) ) {
	$animation = 'fade';
}

if ( ! ServiceProvider::is_block_enabled( 'modal_auto_close' ) ) {
	$auto_close_delay = 0;
}

if ( ! ServiceProvider::is_block_enabled( 'modal_show_once' ) ) {
	$show_once = false;
}

if ( ! ServiceProvider::is_block_enabled( 'modal_device_visibility' ) ) {
	$device_visibility = array( 'desktop', 'tablet', 'mobile' );
}

$overlay_value = is_string( $overlay_color ) ? trim( $overlay_color ) : '';
$bg_value      = is_string( $background_color ) ? trim( $background_color ) : '';

$legacy_overlays = array(
	'',
	'rgba(0, 0, 0, 0.5)',
	'rgba(0,0,0,0.5)',
	'var(--wp--preset--color--neutral-950, rgba(0, 0, 0, 0.5))',
);
$legacy_backgrounds = array(
	'',
	'#fff',
	'#ffffff',
	'white',
	'var(--wp--preset--color--neutral-0, #ffffff)',
	'var(--wp--preset--color--neutral-0, #fff)',
);

$custom_overlay = $overlay_value !== '' && ! in_array( strtolower( $overlay_value ), array_map( 'strtolower', $legacy_overlays ), true );
$custom_bg      = $bg_value !== '' && ! in_array( strtolower( $bg_value ), array_map( 'strtolower', $legacy_backgrounds ), true );

$modal_styles = sprintf(
	'--aegis-modal-width: %s; --aegis-modal-max-width: %s; --aegis-modal-height: %s; --aegis-modal-max-height: %s; --aegis-modal-overlay-blur: %spx; --aegis-modal-radius: %s; --aegis-modal-padding: %s; --aegis-modal-duration: %sms;',
	esc_attr( $width ),
	esc_attr( $max_width ),
	esc_attr( $height ),
	esc_attr( $max_height ),
	esc_attr( (string) $overlay_blur ),
	esc_attr( $border_radius ),
	esc_attr( $padding ),
	esc_attr( (string) $animation_duration )
);

if ( $custom_overlay ) {
	$modal_styles .= ' --aegis-modal-overlay-color: ' . esc_attr( $overlay_value ) . ';';
}

if ( $custom_bg ) {
	$modal_styles .= ' --aegis-modal-bg: ' . esc_attr( $bg_value ) . ';';
}

$data_attrs = sprintf(
	'data-modal-id="%s" data-modal-type="%s" data-trigger-type="%s" data-animation="%s" data-close-esc="%s" data-close-overlay="%s" data-prevent-scroll="%s" data-focus-trap="%s" data-return-focus="%s" data-scroll-trigger-percent="%s" data-scroll-trigger-once="%s" data-exit-intent-sensitivity="%s" data-exit-intent-delay="%s" data-timed-trigger-delay="%s" data-auto-close-delay="%s" data-show-once="%s" data-show-once-expiry="%s" data-device-visibility="%s"',
	esc_attr( $modal_id ),
	esc_attr( $modal_type ),
	esc_attr( $trigger_type ),
	esc_attr( $animation ),
	$close_on_esc ? 'true' : 'false',
	$close_on_overlay ? 'true' : 'false',
	$prevent_body_scroll ? 'true' : 'false',
	$focus_trap ? 'true' : 'false',
	$return_focus ? 'true' : 'false',
	esc_attr( (string) $scroll_trigger_percent ),
	$scroll_trigger_once ? 'true' : 'false',
	esc_attr( (string) $exit_intent_sensitivity ),
	esc_attr( (string) $exit_intent_delay ),
	esc_attr( (string) $timed_trigger_delay ),
	esc_attr( (string) $auto_close_delay ),
	$show_once ? 'true' : 'false',
	esc_attr( (string) $show_once_expiry ),
	esc_attr( implode( ',', is_array( $device_visibility ) ? $device_visibility : array( 'desktop', 'tablet', 'mobile' ) ) )
);

$title_id = $modal_id . '-title';

$wrapper_classes = array(
	'wp-block-aegis-modal',
	'aegis-modal-wrapper',
	'aegis-modal-type-' . $modal_type,
);

if ( ! empty( $attributes['className'] ) ) {
	$wrapper_classes[] = $attributes['className'];
}

if ( ! empty( $attributes['align'] ) ) {
	$wrapper_classes[] = 'align' . $attributes['align'];
}

$wrapper_class = implode( ' ', $wrapper_classes );

$close_button_svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>';
$default_trigger_icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M18 4H6c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 14H6V6h12v12z"/></svg>';

$allowed_svg = [
	'svg'  => [
		'xmlns'       => true,
		'viewbox'     => true,
		'width'       => true,
		'height'      => true,
		'aria-hidden' => true,
		'focusable'   => true,
	],
	'path' => [
		'd' => true,
	],
];

$show_click_trigger = in_array( $trigger_type, $click_triggers, true )
	&& isset( $trigger_features[ $trigger_type ] )
	&& ServiceProvider::is_block_enabled( $trigger_features[ $trigger_type ] );

?>
<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $data_attrs is built entirely from esc_attr() calls in sprintf above. ?>
<div class="<?php echo esc_attr( $wrapper_class ); ?>" <?php echo $data_attrs; ?> style="<?php echo esc_attr( $modal_styles ); ?>">

	<?php if ( $show_click_trigger ) : ?>
		<?php if ( 'button' === $trigger_type ) : ?>
			<button
				type="button"
				class="aegis-modal-trigger aegis-modal-trigger--button wp-element-button"
				aria-haspopup="dialog"
				aria-expanded="false"
				aria-controls="<?php echo esc_attr( $modal_id ); ?>"
			>
				<span class="aegis-modal-trigger__text"><?php echo esc_html( $trigger_text ); ?></span>
			</button>
		<?php elseif ( 'text' === $trigger_type ) : ?>
			<button
				type="button"
				class="aegis-modal-trigger aegis-modal-trigger--text"
				aria-haspopup="dialog"
				aria-expanded="false"
				aria-controls="<?php echo esc_attr( $modal_id ); ?>"
			>
				<span class="aegis-modal-trigger__text"><?php echo esc_html( $trigger_text ); ?></span>
			</button>
		<?php elseif ( 'icon' === $trigger_type ) : ?>
			<button
				type="button"
				class="aegis-modal-trigger aegis-modal-trigger--icon"
				aria-haspopup="dialog"
				aria-expanded="false"
				aria-controls="<?php echo esc_attr( $modal_id ); ?>"
			>
				<span class="aegis-modal-trigger__icon" aria-hidden="true"><?php echo $trigger_icon ? wp_kses_post( $trigger_icon ) : wp_kses( $default_trigger_icon, $allowed_svg ); ?></span>
				<span class="screen-reader-text"><?php echo esc_html( $trigger_text ); ?></span>
			</button>
		<?php elseif ( 'image' === $trigger_type && $trigger_image_url ) : ?>
			<button
				type="button"
				class="aegis-modal-trigger aegis-modal-trigger--image"
				aria-haspopup="dialog"
				aria-expanded="false"
				aria-controls="<?php echo esc_attr( $modal_id ); ?>"
			>
				<img
					src="<?php echo esc_url( $trigger_image_url ); ?>"
					alt="<?php echo esc_attr( $trigger_image_alt ?: $trigger_text ); ?>"
					class="aegis-modal-trigger__image"
				/>
			</button>
		<?php elseif ( 'image' === $trigger_type ) : ?>
			<button
				type="button"
				class="aegis-modal-trigger aegis-modal-trigger--button wp-element-button"
				aria-haspopup="dialog"
				aria-expanded="false"
				aria-controls="<?php echo esc_attr( $modal_id ); ?>"
			>
				<span class="aegis-modal-trigger__text"><?php echo esc_html( $trigger_text ); ?></span>
			</button>
		<?php endif; ?>
	<?php endif; ?>

	<div
		id="<?php echo esc_attr( $modal_id ); ?>"
		class="aegis-modal aegis-modal--<?php echo esc_attr( $modal_type ); ?> aegis-modal--<?php echo esc_attr( $animation ); ?>"
		role="dialog"
		aria-modal="true"
		aria-labelledby="<?php echo esc_attr( $title_id ); ?>"
		aria-hidden="true"
		tabindex="-1"
		hidden
	>
		<div class="aegis-modal__overlay" <?php echo $close_on_overlay ? 'data-close-modal' : ''; ?>></div>

		<?php if ( $show_close_button && 'outside' === $close_button_pos ) : ?>
			<button
				type="button"
				class="aegis-modal__close aegis-modal__close--outside"
				aria-label="<?php esc_attr_e( 'Close modal', 'aegis' ); ?>"
				data-close-modal
			>
				<?php echo wp_kses( $close_button_svg, $allowed_svg ); ?>
			</button>
		<?php endif; ?>

		<div class="aegis-modal__container">

			<?php if ( $show_close_button && 'inside' === $close_button_pos ) : ?>
				<button
					type="button"
					class="aegis-modal__close aegis-modal__close--inside"
					aria-label="<?php esc_attr_e( 'Close modal', 'aegis' ); ?>"
					data-close-modal
				>
					<?php echo wp_kses( $close_button_svg, $allowed_svg ); ?>
				</button>
			<?php endif; ?>

			<div class="aegis-modal__content" role="document">
				<?php if ( $modal_title ) : ?>
					<h2 id="<?php echo esc_attr( $title_id ); ?>" class="aegis-modal__title">
						<?php echo esc_html( $modal_title ); ?>
					</h2>
				<?php else : ?>
					<span id="<?php echo esc_attr( $title_id ); ?>" class="screen-reader-text">
						<?php esc_html_e( 'Modal dialog', 'aegis' ); ?>
					</span>
				<?php endif; ?>

				<div class="aegis-modal__body">
					<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>

			</div>
		</div>
	</div>

</div>
