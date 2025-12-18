<?php
namespace ShermanCore\Modules\Templates;

use ShermanCore\Core\AbstractModule;
use ShermanCore\Core\Settings;

final class Module extends AbstractModule {

    public function id(): string { return 'templates'; }

    public function manifest(): array {
        return [
            'id'          => $this->id(),
            'title'       => __( 'Templates Override', 'sherman-core' ),
            'description' => __( 'Overrides WooCommerce single/archive templates using Elementor templates.', 'sherman-core' ),
            'category'    => 'woocommerce',
            'order'       => 50,
            'dependencies'=> [ 'WooCommerce', 'Elementor' ],
            'keywords'    => [ 'single product', 'archive', 'template_include', 'elementor template' ],
            'settings_groups' => [
                'basics' => [
                    'title'       => __( 'Basics', 'sherman-core' ),
                    'description' => __( 'Enable and select templates for WooCommerce pages.', 'sherman-core' ),
                    'advanced'    => false,
                ],
                'advanced' => [
                    'title'       => __( 'Advanced', 'sherman-core' ),
                    'description' => __( 'More options will be added during migration.', 'sherman-core' ),
                    'advanced'    => true,
                ],
            ],
        ];
    }


    public function dependencies_ok(): bool {
        $wc_ok = ( class_exists( '\\WooCommerce' ) || function_exists( 'WC' ) );
        $el_ok = ( did_action( 'elementor/loaded' ) || class_exists( '\\Elementor\\Plugin' ) );
        return $wc_ok && $el_ok;
    }

    protected function boot(): void {
        add_filter( 'template_include', [ $this, 'filter_template_include' ], 98 );
        add_filter( 'sherman_core_product_template_id', [ $this, 'filter_single_template_id' ] );
        add_filter( 'sherman_core_product_archive_template_id', [ $this, 'filter_archive_template_id' ] );

        // Site-wide header/footer override (without editing theme files).
        // We render the selected Elementor templates via safe theme hooks.
        add_action( 'wp_body_open', [ $this, 'maybe_render_header' ], 5 );
        add_action( 'wp_footer', [ $this, 'maybe_render_footer' ], 0 );

        // Hello Elementor duplication guard: the Hello theme uses a single toggle for both header+footer.
        // If BOTH overrides are active for a given request, we can safely turn off theme header/footer output.
        add_filter( 'hello_elementor_header_footer', [ $this, 'filter_hello_header_footer' ], 20 );
    }

    /**
     * Disable Hello Elementor theme header/footer output when BOTH overrides are active.
     * This prevents duplicate headers/footers without requiring edits in header.php/footer.php.
     */
    public function filter_hello_header_footer( $enabled ) {
        if ( is_admin() ) { return $enabled; }
        $settings = Settings::get_all();
        $mods = $settings['modules']['templates'] ?? [];

        $auto = ( $mods['auto_disable_hello_header_footer'] ?? 'yes' ) === 'yes';
        if ( ! $auto ) { return $enabled; }

        $header = $this->resolve_header_context();
        $footer = $this->resolve_footer_context();
        if ( ( $header['enabled'] ?? false ) && ( $footer['enabled'] ?? false ) ) {
            return false;
        }
        return $enabled;
    }

    public function maybe_render_header(): void {
        if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) { return; }
        $ctx = $this->resolve_header_context();
        if ( empty( $ctx['enabled'] ) || empty( $ctx['template_id'] ) ) { return; }
        $this->render_elementor_template( (int) $ctx['template_id'] );
    }

    public function maybe_render_footer(): void {
        if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) { return; }
        $ctx = $this->resolve_footer_context();
        if ( empty( $ctx['enabled'] ) || empty( $ctx['template_id'] ) ) { return; }
        $this->render_elementor_template( (int) $ctx['template_id'] );
    }

    public function filter_template_include( $template ) {
        if ( is_admin() ) { return $template; }

        $settings = Settings::get_all();
        $mods = $settings['modules']['templates'] ?? [];

        $single_enabled  = ( $mods['single_product']['enabled'] ?? 'no' ) === 'yes';
        $archive_enabled = ( $mods['archive_product']['enabled'] ?? 'no' ) === 'yes';

        if ( $single_enabled && function_exists( 'is_product' ) && is_product() ) {
            $path = SHERMAN_CORE_NEXT_DIR . 'templates/single-product-elementor.php';
            if ( file_exists( $path ) ) { return $path; }
        }

        if ( $archive_enabled && $this->is_product_archive() ) {
            $path = SHERMAN_CORE_NEXT_DIR . 'templates/archive-product-elementor.php';
            if ( file_exists( $path ) ) { return $path; }
        }

        return $template;
    }

    public function filter_single_template_id( int $legacy_id ): int {
        $s = Settings::get_all();
        $id = (int) ( $s['modules']['templates']['single_product']['template_id'] ?? 0 );
        return $id > 0 ? $id : $legacy_id;
    }

    public function filter_archive_template_id( int $legacy_id ): int {
        $s = Settings::get_all();
        $id = (int) ( $s['modules']['templates']['archive_product']['template_id'] ?? 0 );
        return $id > 0 ? $id : $legacy_id;
    }

    private function is_product_archive(): bool {
        if ( function_exists( 'is_shop' ) && is_shop() ) { return true; }
        if ( function_exists( 'is_product_category' ) && is_product_category() ) { return true; }
        if ( function_exists( 'is_product_tag' ) && is_product_tag() ) { return true; }
        return false;
    }

    private function render_elementor_template( int $template_id ): void {
        if ( $template_id <= 0 ) { return; }
        if ( ! class_exists( '\\Elementor\\Plugin' ) ) { return; }
        echo \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $template_id );
    }

    /**
     * Resolve header rendering context for the current request.
     * Supports:
     * - Global settings (enabled/template_id/scope)
     * - Exclude list (paths)
     * - Per-page override via post meta
     */
    private function resolve_header_context(): array {
        $settings = Settings::get_all();
        $mods = $settings['modules']['templates'] ?? [];

        $ctx = [
            'enabled'     => ( $mods['header']['enabled'] ?? 'no' ) === 'yes',
            'template_id' => (int) ( $mods['header']['template_id'] ?? 0 ),
            'scope'       => (string) ( $mods['header']['scope'] ?? 'woocommerce' ),
            'exclude'     => (string) ( $mods['header']['exclude_paths'] ?? '' ),
        ];

        // Per-post overrides.
        $meta = $this->get_singular_overrides();
        if ( ! empty( $meta['disable_header'] ) ) {
            $ctx['enabled'] = false;
            $ctx['template_id'] = 0;
        } elseif ( ! empty( $meta['header_template_id'] ) ) {
            $ctx['enabled'] = true;
            $ctx['template_id'] = (int) $meta['header_template_id'];
        }

        // Scope check.
        if ( $ctx['enabled'] && $ctx['scope'] === 'woocommerce' && ! $this->is_woocommerce_request() ) {
            $ctx['enabled'] = false;
        }

        // Exclude list.
        if ( $ctx['enabled'] && $this->is_path_excluded( $ctx['exclude'] ) ) {
            $ctx['enabled'] = false;
        }

        if ( $ctx['template_id'] <= 0 ) { $ctx['enabled'] = false; }
        return $ctx;
    }

    private function resolve_footer_context(): array {
        $settings = Settings::get_all();
        $mods = $settings['modules']['templates'] ?? [];

        $ctx = [
            'enabled'     => ( $mods['footer']['enabled'] ?? 'no' ) === 'yes',
            'template_id' => (int) ( $mods['footer']['template_id'] ?? 0 ),
            'scope'       => (string) ( $mods['footer']['scope'] ?? 'woocommerce' ),
            'exclude'     => (string) ( $mods['footer']['exclude_paths'] ?? '' ),
        ];

        // Per-post overrides.
        $meta = $this->get_singular_overrides();
        if ( ! empty( $meta['disable_footer'] ) ) {
            $ctx['enabled'] = false;
            $ctx['template_id'] = 0;
        } elseif ( ! empty( $meta['footer_template_id'] ) ) {
            $ctx['enabled'] = true;
            $ctx['template_id'] = (int) $meta['footer_template_id'];
        }

        // Scope check.
        if ( $ctx['enabled'] && $ctx['scope'] === 'woocommerce' && ! $this->is_woocommerce_request() ) {
            $ctx['enabled'] = false;
        }

        // Exclude list.
        if ( $ctx['enabled'] && $this->is_path_excluded( $ctx['exclude'] ) ) {
            $ctx['enabled'] = false;
        }

        if ( $ctx['template_id'] <= 0 ) { $ctx['enabled'] = false; }
        return $ctx;
    }

    private function get_singular_overrides(): array {
        if ( ! is_singular() ) { return []; }
        $post_id = get_queried_object_id();
        if ( ! $post_id ) { return []; }

        $disable_header = get_post_meta( $post_id, '_sherman_core_disable_header', true );
        $disable_footer = get_post_meta( $post_id, '_sherman_core_disable_footer', true );
        $header_tpl     = absint( get_post_meta( $post_id, '_sherman_core_header_template_id', true ) );
        $footer_tpl     = absint( get_post_meta( $post_id, '_sherman_core_footer_template_id', true ) );

        return [
            'disable_header'     => ( $disable_header === 'yes' || $disable_header === '1' || $disable_header === 1 ),
            'disable_footer'     => ( $disable_footer === 'yes' || $disable_footer === '1' || $disable_footer === 1 ),
            'header_template_id' => $header_tpl,
            'footer_template_id' => $footer_tpl,
        ];
    }

    private function is_woocommerce_request(): bool {
        // Covers WooCommerce core pages and product archives.
        if ( function_exists( 'is_woocommerce' ) && is_woocommerce() ) { return true; }
        if ( function_exists( 'is_cart' ) && is_cart() ) { return true; }
        if ( function_exists( 'is_checkout' ) && is_checkout() ) { return true; }
        if ( function_exists( 'is_account_page' ) && is_account_page() ) { return true; }
        if ( function_exists( 'is_product' ) && is_product() ) { return true; }
        if ( $this->is_product_archive() ) { return true; }
        return false;
    }

    private function is_path_excluded( string $raw_list ): bool {
        $raw_list = trim( $raw_list );
        if ( $raw_list === '' ) { return false; }

        $path = $this->current_path();
        $patterns = preg_split( '/[\r\n]+/', $raw_list );
        if ( ! is_array( $patterns ) ) { return false; }

        foreach ( $patterns as $p ) {
            $p = trim( (string) $p );
            if ( $p === '' ) { continue; }

            // If user pasted full URL, reduce to path.
            if ( preg_match( '#^https?://#i', $p ) ) {
                $pp = wp_parse_url( $p );
                if ( is_array( $pp ) && ! empty( $pp['path'] ) ) {
                    $p = (string) $pp['path'];
                }
            }

            $p = '/' . ltrim( $p, '/' );
            $p_norm = $this->normalize_path( $p );
            $path_norm = $this->normalize_path( $path );

            // Wildcard prefix match (e.g. /blog/*).
            if ( substr( $p_norm, -2 ) === '/*' ) {
                $prefix = rtrim( substr( $p_norm, 0, -1 ), '/' );
                if ( $prefix === '' ) { continue; }
                if ( strpos( $path_norm, $prefix ) === 0 ) { return true; }
                continue;
            }

            if ( $p_norm === $path_norm ) { return true; }
        }

        return false;
    }

    private function current_path(): string {
        $uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
        $path = (string) wp_parse_url( $uri, PHP_URL_PATH );
        if ( $path === '' ) { $path = '/'; }
        return '/' . ltrim( $path, '/' );
    }

    private function normalize_path( string $path ): string {
        $path = '/' . ltrim( $path, '/' );
        if ( $path !== '/' ) {
            $path = rtrim( $path, '/' );
        }
        return $path;
    }
}
