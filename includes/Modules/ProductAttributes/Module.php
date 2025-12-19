<?php
namespace ShermanCore\Modules\ProductAttributes;

use ShermanCore\Core\AbstractModule;

final class Module extends AbstractModule {

    public function id(): string { return 'product_attributes'; }

    public function manifest(): array {
        return [
            'id'          => $this->id(),
            'title'       => __( 'Product Attributes', 'sherman-core' ),
            'description' => __( 'Adds a shortcode to display a product attributes grid.', 'sherman-core' ),
            'category'    => 'woocommerce',
            'order'       => 46,
            'dependencies'=> [ 'WooCommerce' ],
            'keywords'    => [ 'shortcode', 'attributes', 'product' ],
        ];
    }

    public function dependencies_ok(): bool {
        return class_exists( '\\WooCommerce' ) || function_exists( 'WC' );
    }

    protected function boot(): void {
        require_once __DIR__ . '/Shortcode.php';
        add_action( 'wp_enqueue_scripts', [ Shortcode::class, 'register_assets' ] );
        add_action( 'init', [ Shortcode::class, 'init' ] );
    }
}
