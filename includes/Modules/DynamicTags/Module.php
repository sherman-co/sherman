<?php
namespace ShermanCore\Modules\DynamicTags;

use ShermanCore\Core\AbstractModule;

final class Module extends AbstractModule {

    public function id(): string { return 'dynamic_tags'; }

    public function manifest(): array {
        return [
            'id'          => $this->id(),
            'title'       => __( 'Dynamic Tags', 'sherman-core' ),
            'description' => __( 'Registers custom Elementor Dynamic Tags (PS Core tags).', 'sherman-core' ),
            'category'    => 'elementor',
            'order'       => 30,
            'dependencies'=> [ 'Elementor' ],
            'keywords'    => [ 'dynamic tags', 'ps core', 'tags' ],
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
