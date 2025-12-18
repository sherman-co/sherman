<?php
namespace ShermanCore\Modules\ElementorWidgets;

use ShermanCore\Core\AbstractModule;

final class Module extends AbstractModule {

    public function id(): string { return 'elementor_widgets'; }

    public function manifest(): array {
        return [
            'id'          => $this->id(),
            'title'       => __( 'Elementor Widgets', 'sherman-core' ),
            'description' => __( 'Adds Sherman widgets for Elementor: Breadcrumb, Product Gallery, and Product Loop.', 'sherman-core' ),
            'category'    => 'elementor',
            'order'       => 20,
            'dependencies'=> [
                'elementor' => __( 'Elementor', 'sherman-core' ),
            ],
            'settings_groups' => [
                'basics' => [
                    'title' => __( 'Basics', 'sherman-core' ),
                    'description' => __( 'Enable/disable this widget pack.', 'sherman-core' ),
                    'advanced' => false,
                ],
                'debug' => [
                    'title' => __( 'Debug', 'sherman-core' ),
                    'description' => __( 'Admin-only diagnostics shown inside the editor when context is missing.', 'sherman-core' ),
                    'advanced' => true,
                ],
            ],
        ];
    }

    public function dependencies_ok(): bool {
        return did_action( 'elementor/loaded' ) || class_exists( '\\Elementor\\Plugin' );
    }

    protected function boot(): void {
        add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
    }

    public function register_widgets( $widgets_manager ): void {
        if ( ! class_exists( '\\Elementor\\Widget_Base' ) ) { return; }

        require_once __DIR__ . '/Widgets/BreadcrumbWidget.php';
        require_once __DIR__ . '/Widgets/ProductGalleryWidget.php';
        require_once __DIR__ . '/Widgets/ProductLoopWidget.php';

        $widgets_manager->register( new \ShermanCore\Modules\ElementorWidgets\Widgets\BreadcrumbWidget() );
        $widgets_manager->register( new \ShermanCore\Modules\ElementorWidgets\Widgets\ProductGalleryWidget() );
        $widgets_manager->register( new \ShermanCore\Modules\ElementorWidgets\Widgets\ProductLoopWidget() );
    }
}
