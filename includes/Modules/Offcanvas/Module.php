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
        // Register assets via Elementor hooks (legacy-compatible), and also register on front-end.
        add_action( 'elementor/frontend/after_register_styles', [ $this, 'register_assets' ] );
        add_action( 'elementor/frontend/after_register_scripts', [ $this, 'register_assets' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
        add_action( 'elementor/widgets/register', [ $this, 'register_widget' ] );
    }

    public function register_assets(): void {
        // Legacy handles expected by existing Elementor pages.
        $css_handle_legacy = 'sherman-core-css';
        $js_handle_legacy  = 'sherman-core-js';

        // New internal handle (kept for future use).
        $css_handle_new = 'sherman-core-offcanvas';
        $js_handle_new  = 'sherman-core-offcanvas';

        // NOTE: sherman-core-css is also registered by the shared Assets registrar.
        // We keep it pointing to the shared stylesheet (which includes offcanvas rules)
        // to remain compatible with legacy widgets.
        wp_register_style(
            $css_handle_legacy,
            SHERMAN_CORE_NEXT_URL . 'assets/frontend/sherman-core.css',
            [ 'elementor-frontend' ],
            SHERMAN_CORE_NEXT_VERSION
        );
        wp_register_script(
            $js_handle_legacy,
            SHERMAN_CORE_NEXT_URL . 'assets/frontend/offcanvas.js',
            [ 'elementor-frontend' ],
            SHERMAN_CORE_NEXT_VERSION,
            true
        );

        // Alias the new handle to the same assets.
        wp_register_style(
            $css_handle_new,
            SHERMAN_CORE_NEXT_URL . 'assets/frontend/offcanvas.css',
            [ 'elementor-frontend' ],
            SHERMAN_CORE_NEXT_VERSION
        );
        wp_register_script(
            $js_handle_new,
            SHERMAN_CORE_NEXT_URL . 'assets/frontend/offcanvas.js',
            [ 'elementor-frontend' ],
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
