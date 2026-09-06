# Installation

This page describes how to install and activate the Aegis companion plugin.

## Prerequisites

Ensure your environment meets the [[requirements]]. The **Aegis theme must be installed and active** before you activate the plugin.

## Installation Order

1. Install and activate the **Aegis theme**.
2. Install and activate the **Aegis plugin**.
3. Optionally install **Aegis Pro** for advanced features.

The plugin is a companion to the theme — it will show an admin notice and refuse to load if the Aegis theme is not active.

**WooCommerce shops:** For WooCommerce block patterns (cart, checkout, product grids, store headers), also install **WooCommerce**. Patterns register only when both the plugin and WooCommerce are active. See [[../features/plugin-patterns]].

## Installation Methods

### WordPress Admin Upload

1. Download or obtain the `aegis` plugin folder or ZIP.
2. Log in to WordPress admin.
3. Navigate to **Plugins → Add New → Upload Plugin**.
4. Choose the ZIP file and click **Install Now**.
5. Click **Activate Plugin**.

### Manual Installation

1. Upload the `aegis` folder to `/wp-content/plugins/`.
2. Activate through **Plugins** in WordPress admin.

## After Activation

All features are **off by default** on a fresh install. Enable only what you need:

1. Open **Aegis → Dashboard** in the admin menu.
2. Enable block features at **Aegis → Blocks**.
3. Enable integrations at **Aegis → Integrations**.
4. Optionally configure conditionals, snippets, analytics, **Aegis → Performance**, and **Aegis → Settings**.

Existing sites keep previously implicit-on features enabled. Uninstall does not delete data unless **Delete data on uninstall** is enabled under **Aegis → Settings**.

## Next Steps

- [[dashboard-overview]] — Admin menu tour
- [[../../themes/aegis/docs/getting-started/installation|Theme Installation]]
- [[../../aegis-pro/docs/getting-started/installation|Pro Installation]]
