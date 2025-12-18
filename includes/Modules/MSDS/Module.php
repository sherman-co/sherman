<?php
namespace ShermanCore\Modules\MSDS;

use ShermanCore\Core\AbstractModule;

final class Module extends AbstractModule {

    public function id(): string { return 'msds'; }

    public function manifest(): array {
        return [
            'id'          => $this->id(),
            'title'       => __( 'MSDS', 'sherman-core' ),
            'description' => __( 'Adds MSDS management UI and product attachments integration.', 'sherman-core' ),
            'category'    => 'woocommerce',
            'order'       => 40,
            'dependencies'=> [ 'WooCommerce' ],
            'keywords'    => [ 'msds', 'product', 'sds' ],
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
        return class_exists( '\\WooCommerce' ) || function_exists( 'WC' );
    }

    protected function boot(): void {
        // Module migration will be added in next iterations.
    }
}
