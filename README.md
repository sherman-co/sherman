# Sherman Core Next

A modular WordPress plugin that extends Elementor + WooCommerce with reusable widgets, dynamic tags, template overrides, and product MSDS management. Designed to scale cleanly as new modules and settings are added over time.

## Key Features

### Elementor
- Widgets:
  - Breadcrumb
  - Product Gallery
  - Product Loop
- Dynamic Tags (grouped under a dedicated tag group):
  - Common site/post/product tags
  - MSDS-related tags (when MSDS module is enabled)

### WooCommerce
- Template Overrides:
  - Single Product template override (Elementor template ID)
  - Product Archive template override (Elementor template ID)
- MSDS (Material Safety Data Sheet) per product:
  - “Product MSDS” tab inside the WooCommerce product editor
  - Upload/select PDF from Media Library
  - Optional MSDS URL
  - Optional labels/text fields (e.g., “Available”, “Check Now”)
  - Dynamic Tags output MSDS data for Elementor layouts

### Global Header/Footer Overrides
- Select an Elementor template for Header and/or Footer and render site-wide.
- Scope controls (e.g., “Entire site”).
- Exclude rules by URL path to hide header/footer on specific pages.
- Optional per-page override (advanced) to disable or switch header/footer templates on a specific page.

## Requirements
- WordPress 6.x recommended
- Elementor (free) required for widgets/tags/templates rendering
- WooCommerce required for:
  - Product template overrides
  - MSDS product tab and metadata

## Installation
1. Upload the plugin ZIP in **WP Admin → Plugins → Add New → Upload Plugin**.
2. Activate **Sherman Core Next**.
3. Ensure Elementor (and WooCommerce if needed) are active.

## Configuration

### Admin Panel
Go to:
- **WP Admin → Sherman Core**

Modules are grouped by categories to avoid “settings sprawl” as the plugin grows.

Typical configuration flow:
1. Enable the desired module.
2. Open the module configuration.
3. Choose templates/settings as needed.

### WooCommerce Templates
- Enable Single Product override → select Elementor template.
- Enable Product Archive override → select Elementor template.

### Header/Footer Overrides (Entire Site)
- Enable Header override → select Elementor template.
- Enable Footer override → select Elementor template.
- Set scope to **Entire site**.
- Add exclusions if required (one per line):
  - `/cart`
  - `/checkout`
  - `/my-account`
  - `/blog/*`

### MSDS (per product)
1. Enable the MSDS module in Sherman Core admin panel.
2. Edit a WooCommerce product.
3. Open the **Product MSDS** tab inside Product Data.
4. Upload/select PDF and/or set MSDS URL and labels.
5. Use MSDS Dynamic Tags inside Elementor templates to render the fields.

## Migration from Legacy Plugin
This plugin includes a migration layer that attempts to read legacy option keys from older versions and map them into the new consolidated settings structure on first run.

If you previously used an older “Sherman Core” build, you may see your toggles and selections carried over automatically.

## Troubleshooting

### Header/Footer template is selected but not rendering
- Ensure Elementor is active.
- Confirm the Header/Footer overrides are enabled and scope is set to **Entire site**.
- Check exclusions list: a matching path will prevent rendering.
- If you are using Hello Elementor theme and see duplication, enable the compatibility toggle that disables Hello header/footer output when both overrides are active.

### Elementor CSS not updating
After changing templates or widget styles, regenerate Elementor CSS:
- **Elementor → Tools → Regenerate CSS & Data**

### MSDS tab does not appear
- Ensure WooCommerce is active.
- Ensure MSDS module is enabled in Sherman Core admin panel.
- Confirm you are editing a WooCommerce product (not a page/post).

## Roadmap (High-Level)
- UI for per-page header/footer overrides (no custom fields required).
- More granular rule engine (priority-based rulesets).
- Enhanced MSDS front-end components (optional button/link widgets).

## Contributing / Development
- Keep modules self-contained (own hooks, own assets, own settings schema).
- Avoid inline admin CSS: use admin assets.
- New modules must register:
  - Category placement
  - Dependencies
  - Settings groups/fields
  - Optional keywords for search

## License
Proprietary (or specify your intended license).
