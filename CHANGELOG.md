# Changelog

All notable changes to this project will be documented in this file.

The format is based on Keep a Changelog and this project follows Semantic Versioning.

## [0.2.8] - 2025-12-19
### Added
- WooCommerce module: **Product Attributes**
  - Adds the shortcode `[sherman_product_attributes]` to render a simple product attributes grid on single product pages.
  - Frontend CSS moved to a dedicated asset file and styled for LTR layout with `Baloo 2` font-family.

## [0.2.7.1] - 2025-12-18
### Fixed
- Fixed a fatal error in Sherman Product Loop where `ProductLoopService::query_products()` was called but not defined.

## [0.2.7] - 2025-12-18
### Added
- Sherman Product Loop: Pagination modes and front-end loading
  - **None** (existing behavior)
  - **Numbers** pagination (server-rendered)
  - **Load More** button (AJAX)
  - **Infinite Scroll** (AJAX)
- Optional **SEO fallback pagination** (hidden crawlable links) for Load More / Infinite.
- Optional **URL Sync** for Load More / Infinite (History API), with safe pretty-URL mode on product archives.

## [0.2.6] - 2025-12-18
### Added
- Dynamic Tag: **PS Context Title** (context-aware title for the current request)
  - Product category/tag archive: term name
  - Shop page: shop page title
  - Singular: current post/page/product title
  - Search: `Search: {query}`
  - Fallback: WP document title
- Per Product Category **Archive Template Override**
  - Adds an Elementor template selector in **Products → Categories → Add/Edit**
  - When viewing a product category archive, the selected template (if any) overrides the global archive template.


## [0.2.5] - 2025-12-18
### Added
- Global Header/Footer rendering via hooks:
  - Header output on `wp_body_open`
  - Footer output on `wp_footer`
- Exclude rules for Header/Footer based on URL path (one per line), including basic wildcard/prefix patterns (e.g. `/blog/*`).
- Per-page overrides via post meta (advanced):
  - Disable header/footer on a specific page
  - Use a different header/footer template ID for a specific page

### Improved
- Optional compatibility switch to prevent duplicated header/footer output with Hello Elementor theme (when both header and footer overrides are enabled).

## [0.2.4] - 2025-12-18
### Added
- MSDS feature completed end-to-end (WooCommerce):
  - Product MSDS tab in WooCommerce product edit
  - Media uploader for MSDS PDF
  - MSDS fields stored per product and exposed via Dynamic Tags
- Header/Footer template selection UI (initial):
  - Enable override + choose template + scope

## [0.2.3] - 2025-12-18
### Fixed
- Offcanvas widget fatal error: missing `Elementor\Group_Control_Typography` import (class not found).
- PHP parse error in `templates/single-product-elementor.php`.

## [0.2.2] - 2025-12-18
### Added
- WooCommerce Templates selection UI:
  - Single product template override
  - Product archive template override
- Offcanvas style controls:
  - Trigger icon size
  - Trigger text typography and color

## [0.2.1] - 2025-12-18
### Added
- Dynamic Tags module enabled and registered.
- First-run migration layer for legacy options.

### Fixed
- Offcanvas backward compatibility (restored legacy widget name and asset handles).

## [0.2.0] - 2025-12-18
### Added
- New modular plugin foundation and module registry.
- Elementor Widgets module (Breadcrumb, Product Gallery, Product Loop) grouped under a dedicated Elementor category.
