<?php
namespace ShermanCore\Modules\Templates;

use ShermanCore\Core\AbstractModule;

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
            'keywords'    => [ 'single product', 'archive', 'template_include' ],
            'settings_groups' => [
                'basics' => [
                    'title' => __( 'Basics', 'sherman-core' ),
                    'description' => __( 'Enable/disable and core options.', 'sherman-core' ),
                    'advanced' => false,
                ],
                'advanced' => [
                    'title' => __( 'Advanced', 'sherman-core' ),
                    'description' => __( 'More options will be added during migration.', 'sherman-core' ),
                    'advanced' => true,
                ],
            ],
        ];
    }

    public function dependencies_ok(): bool {
        return ( class_exists( '\\WooCommerce' ) || function_exists( 'WC' ) ) && ( did_action( 'elementor/loaded' ) || class_exists( '\\Elementor\\Plugin' ) );
    }

    protected function boot(): void {
        // Module migration will be added in next iterations.
    }
}
