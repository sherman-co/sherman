# Sherman Core Next

Sherman Core Next is a modular WordPress plugin that extends **Elementor** and **WooCommerce** with reusable widgets, dynamic tags, product MSDS management, and template overrides.

## Features

### Elementor
- Widgets
  - Breadcrumb
  - Product Gallery
  - Product Loop
    - Supports pagination modes: None, Numbers, Load More (AJAX), Infinite Scroll (AJAX)
    - Optional SEO fallback pagination (hidden crawlable links) for AJAX modes
    - Optional URL sync (History API) for AJAX modes
- Dynamic Tags (PS Core group)
  - Site/Post/Product tags
  - MSDS tags (when MSDS module is enabled)
  - Context-aware tags (e.g., **PS Context Title**)

### WooCommerce
- Template Overrides
  - Single Product Elementor template override
  - Product Archive Elementor template override
  - Per Product Category archive template override
- MSDS (Material Safety Data Sheet)
  - Product-level MSDS tab in WooCommerce product editor
  - MSDS PDF selection via Media Library and optional URL/labels
  - MSDS values available through Dynamic Tags

### Site-wide Header/Footer Overrides
- Select Elementor templates for Header/Footer and render via theme hooks (no theme file edits required).
- Scope controls (WooCommerce only vs Entire site).
- Exclude-by-path list.
- Optional duplication guard for Hello Elementor theme.

## Requirements
- WordPress 6.x (recommended)
- Elementor (required for widgets/templates/dynamic tags)
- WooCommerce (required for product templates and MSDS)

## Installation
1. Upload the plugin ZIP in **WP Admin → Plugins → Add New → Upload Plugin**.
2. Activate **Sherman Core Next**.
3. Ensure Elementor (and WooCommerce, if you use WooCommerce features) are active.

## Configuration

### Global WooCommerce Templates
Go to **WP Admin → Sherman Core → Templates Override**:
- Enable **Single product** override and select a template.
- Enable **Product archive** override and select a template.

### Per Product Category Archive Template
Go to **Products → Categories → Add/Edit** and use:
- **Archive template override (Sherman Core)**

If set, this template will be used when visiting that specific product category archive.

### Header/Footer Override
Go to **WP Admin → Sherman Core → Templates Override**:
- Enable Header/Footer override
- Choose template
- Choose scope:
  - WooCommerce pages only
  - Entire site
- Optionally provide exclusions (one per line), e.g.:
  - `/cart`
  - `/checkout`
  - `/my-account`
  - `/blog/*`

### Product Loop Pagination
In the **Sherman Product Loop** widget (Elementor editor):
- Set **Products per page**.
- Set **Pagination Mode**:
  - **Numbers** renders classic pagination links.
  - **Load More** / **Infinite Scroll** loads next pages via AJAX.
- Optional:
  - **Max Pages** (limits how far load more/scroll can go)
  - **SEO Fallback Pagination** (outputs hidden crawlable links)
  - **URL Sync** (updates the URL as pages are loaded)

### MSDS
1. Enable the MSDS module in **Sherman Core** settings.
2. Edit a WooCommerce product.
3. Open the **Product MSDS** tab and select an MSDS PDF / set optional fields.
4. Use MSDS Dynamic Tags in Elementor templates.

## Troubleshooting

### Product Loop "Load More" / "Infinite" does not load
- Ensure the **Elementor Widgets** module is enabled.
- Confirm the loop has a valid **Elementor template** selected.
- Confirm **WooCommerce** is active.
- If caching/minification is enabled, clear caches after updating the plugin.

### Header/Footer is selected but does not render
- Ensure Elementor is active.
- Ensure the override is enabled and the scope matches the current page.
- Check the exclude paths list.
- If using Hello Elementor and you see duplicates, enable the Hello duplication guard option.

### Elementor styles do not update
Regenerate Elementor CSS:
- **Elementor → Tools → Regenerate CSS & Data**

## License
Specify your intended license (GPLv2, proprietary, etc.).
