<?php
namespace ShermanCore\Modules\Offcanvas;

use ShermanCore\Core\AbstractModule;

final class Module extends AbstractModule {

    public function id(): string {
        return 'offcanvas';
    }

    public function manifest(): array {
        return [
            'id'          => $this->id(),
            'title'       => __( 'Offcanvas', 'sherman-core' ),
            'description' => __( 'Adds an Elementor offcanvas widget (no Pro required).', 'sherman-core' ),
            'category'    => 'frontend_ui',
            'order'       => 10,
            'dependencies'=> [ 'Elementor' ],
            'keywords'    => [ 'drawer', 'panel', 'popup', 'template' ],
            'settings_groups' => [
                'basics' => [
                    'title' => __( 'Basics', 'sherman-core' ),
                    'description' => __( 'Enable the widget and load required assets.', 'sherman-core' ),
                    'advanced' => false,
                ],
                'behavior' => [
                    'title' => __( 'Behavior', 'sherman-core' ),
                    'description' => __( 'Controls ESC behavior and scroll lock.', 'sherman-core' ),
                    'advanced' => false,
                ],
                'display' => [
                    'title' => __( 'Display', 'sherman-core' ),
                    'description' => __( 'Panel size and spacing.', 'sherman-core' ),
                    'advanced' => true,
                ],
            ],
        ];
    }

    public function dependencies_ok(): bool {
        return did_action( 'elementor/loaded' ) || class_exists( '\\Elementor\\Plugin' );
    }

    protected function boot(): void {
        add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
        add_action( 'elementor/widgets/register', [ $this, 'register_widget' ] );
    }

    public function register_assets(): void {
        wp_register_style(
            'sherman-core-offcanvas',
            SHERMAN_CORE_NEXT_URL . 'assets/frontend/offcanvas.css',
            [],
            SHERMAN_CORE_NEXT_VERSION
        );
        wp_register_script(
            'sherman-core-offcanvas',
            SHERMAN_CORE_NEXT_URL . 'assets/frontend/offcanvas.js',
            [],
            SHERMAN_CORE_NEXT_VERSION,
            true
        );
    }

    public function register_widget( $widgets_manager ): void {
        if ( ! class_exists( '\\Elementor\\Widget_Base' ) ) {
            return;
        }
        require_once __DIR__ . '/OffcanvasWidget.php';
        $widgets_manager->register( new OffcanvasWidget() );
    }
}
