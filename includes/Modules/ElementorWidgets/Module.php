<?php
namespace ShermanCore\Modules\ElementorWidgets;

use ShermanCore\Core\AbstractModule;

final class Module extends AbstractModule {

    public function id(): string { return 'elementor_widgets'; }

    public function manifest(): array {
        return [
            'id'          => $this->id(),
            'title'       => __( 'Elementor Widgets', 'sherman-core' ),
            'description' => __( 'Registers Sherman Elementor widgets (Breadcrumb, Product Gallery, Product Loop).', 'sherman-core' ),
            'category'    => 'elementor',
            'order'       => 20,
            'dependencies'=> [ 'Elementor' ],
            'keywords'    => [ 'breadcrumb', 'gallery', 'loop' ],
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
        return did_action( 'elementor/loaded' ) || class_exists( '\\Elementor\\Plugin' );
    }

    protected function boot(): void {
        // Module migration will be added in next iterations.
    }
}
