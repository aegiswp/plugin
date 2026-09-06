# WooCommerce Checkout

The Aegis theme includes the multi-step checkout FSE template. Checkout JavaScript and CSS live in the companion plugin and load only when the WooCommerce integration toggle is on.

## Requirements

1. **Aegis theme** active (required — plugin is theme-only)
2. WooCommerce active
3. **Aegis → Integrations → WooCommerce** integration enabled

## WooCommerce Block Patterns

Shop, cart, checkout, product, and header patterns that contain `wp:woocommerce/*` blocks are owned by the **companion plugin** (`patterns/woocommerce/`). They appear in the inserter only when WooCommerce is active. FSE templates for those pages remain in the theme. See [[plugin-patterns]].

## Multi-Step Checkout

The theme provides `page-checkout-multi-step.html` template with:

- Shipping, Payment, and Review steps
- Progress indicator
- Form validation per step
- Order summary sidebar

Assets are loaded by `Aegis\Plugin\Integrations\WooCommerce\MultiStepCheckout` when the WooCommerce integration is enabled in plugin settings.

## Enabling

1. Enable WooCommerce integration at **Aegis → Integrations**.
2. In the Site Editor, assign the **Multi-Step Checkout** template to your checkout page.

See [[../../themes/aegis/docs/features/woocommerce-integration|Theme WooCommerce Integration]] for template details.

## Injection Hooks

When WooCommerce integration is active, the plugin bridges these snippet/hook locations:

- `aegis_before_woocommerce_checkout` / `aegis_after_woocommerce_checkout`
- `aegis_before_woocommerce_cart` / `aegis_after_woocommerce_cart`

## Next Steps

- [[../../themes/aegis/docs/features/woocommerce-integration|Theme WooCommerce Templates]]
- [[integrations-dashboard]] — Enable WooCommerce integration
- [[conditional-logic]] — WooCommerce conditions
