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
		// Register assets for widgets that need front-end behavior.
		add_action( 'elementor/frontend/after_register_styles', [ $this, 'register_assets' ] );
		add_action( 'elementor/frontend/after_register_scripts', [ $this, 'register_assets' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );

		// Widgets.
		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );

		// AJAX for Product Loop pagination (Load More / Infinite).
		add_action( 'wp_ajax_sherman_product_loop_load', [ $this, 'ajax_product_loop_load' ] );
		add_action( 'wp_ajax_nopriv_sherman_product_loop_load', [ $this, 'ajax_product_loop_load' ] );
    }

	public function register_assets(): void {
		wp_register_style(
			'sherman-core-product-loop',
			SHERMAN_CORE_NEXT_URL . 'assets/frontend/product-loop.css',
			[ 'elementor-frontend' ],
			SHERMAN_CORE_NEXT_VERSION
		);

		wp_register_script(
			'sherman-core-product-loop',
			SHERMAN_CORE_NEXT_URL . 'assets/frontend/product-loop.js',
			[ 'elementor-frontend' ],
			SHERMAN_CORE_NEXT_VERSION,
			true
		);

		wp_localize_script(
			'sherman-core-product-loop',
			'ShermanProductLoop',
			[
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'sherman_product_loop' ),
			]
		);
	}

    public function register_widgets( $widgets_manager ): void {
        if ( ! class_exists( '\\Elementor\\Widget_Base' ) ) { return; }

        require_once __DIR__ . '/Widgets/BreadcrumbWidget.php';
        require_once __DIR__ . '/Widgets/ProductGalleryWidget.php';
		require_once __DIR__ . '/ProductLoopService.php';
        require_once __DIR__ . '/Widgets/ProductLoopWidget.php';

        $widgets_manager->register( new \ShermanCore\Modules\ElementorWidgets\Widgets\BreadcrumbWidget() );
        $widgets_manager->register( new \ShermanCore\Modules\ElementorWidgets\Widgets\ProductGalleryWidget() );
        $widgets_manager->register( new \ShermanCore\Modules\ElementorWidgets\Widgets\ProductLoopWidget() );
    }

	public function ajax_product_loop_load(): void {
		if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
			wp_send_json_error( [ 'message' => 'Invalid request.' ], 400 );
		}

		check_ajax_referer( 'sherman_product_loop', 'nonce' );

		if ( ! function_exists( 'wc_get_product' ) || ! class_exists( '\\Elementor\\Plugin' ) ) {
			wp_send_json_error( [ 'message' => 'Missing dependencies.' ], 400 );
		}

		require_once __DIR__ . '/ProductLoopService.php';

		$template_id = isset( $_POST['template_id'] ) ? (int) $_POST['template_id'] : 0;
		$page        = isset( $_POST['page'] ) ? max( 1, (int) $_POST['page'] ) : 1;
		$raw_settings = [];
		if ( isset( $_POST['settings'] ) ) {
			$raw = wp_unslash( $_POST['settings'] );
			$decoded = json_decode( (string) $raw, true );
			if ( is_array( $decoded ) ) {
				$raw_settings = $decoded;
			}
		}

		$settings = ProductLoopService::sanitize_settings( $raw_settings );
		$result   = ProductLoopService::query_products( $settings, $page );

		$html = '';
		if ( ! empty( $result['ids'] ) && $template_id ) {
			$html = ProductLoopService::render_items_html( $result['ids'], $template_id );
		}

		wp_send_json_success( [
			'html'         => $html,
			'current_page' => (int) $result['current_page'],
			'max_pages'    => (int) $result['max_pages'],
			'has_more'     => (bool) $result['has_more'],
		] );
	}
}
