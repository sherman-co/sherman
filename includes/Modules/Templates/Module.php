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
}
