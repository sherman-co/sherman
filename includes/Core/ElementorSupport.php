<?php
namespace ShermanCore\Core;

/**
 * Small Elementor glue so all Sherman widgets can live under one category.
 * This is intentionally in Core so modules don't have to duplicate it.
 */
final class ElementorSupport {

    public static function init(): void {
        // If Elementor isn't active, this hook will never fire. Safe and cheap.
        add_action( 'elementor/init', [ __CLASS__, 'hook_category_registration' ] );
    }

    public static function hook_category_registration(): void {
        add_action( 'elementor/elements/categories_registered', [ __CLASS__, 'register_category' ] );
    }

    public static function register_category( $elements_manager ): void {
        if ( ! method_exists( $elements_manager, 'add_category' ) ) { return; }

        $elements_manager->add_category(
            'sherman-core',
            [
                'title' => __( 'Sherman Core', 'sherman-core' ),
                'icon'  => 'fa fa-plug',
            ]
        );
    }
}
