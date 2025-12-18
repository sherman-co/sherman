<?php
namespace ShermanCore\Core;

/**
 * Shared asset registration for the plugin.
 * Keeps module assets isolated, while still providing common handles that multiple widgets can depend on.
 */
final class Assets {

    public static function init(): void {
        add_action( 'wp_enqueue_scripts', [ __CLASS__, 'register_frontend' ] );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'register_admin' ] );
    }

    public static function register_frontend(): void {
        // Common stylesheet for Sherman widgets (notice styling, shared patterns).
        wp_register_style(
            'sherman-core-css',
            SHERMAN_CORE_NEXT_URL . 'assets/frontend/sherman-core.css',
            [],
            SHERMAN_CORE_NEXT_VERSION
        );
    }

    public static function register_admin(): void {
        // Reserved for shared admin assets (most admin assets are enqueued by the Admin controller on its page).
        // Kept intentionally minimal.
    }
}
