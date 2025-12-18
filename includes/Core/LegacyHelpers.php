<?php
// phpcs:ignoreFile

/**
 * Legacy helpers kept for backward compatibility with the old PS Core/Sherman Core codebase.
 *
 * Several migrated Elementor Dynamic Tags refer to these global functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'ps_core_get_current_product_id' ) ) {
    /**
     * Get the current context ID.
     *
     * Historically this returned the current WooCommerce product ID, but some legacy tags also
     * use it as a "current post ID" helper. We keep the behavior permissive.
     */
    function ps_core_get_current_product_id(): int {
        global $product, $post;

        if ( isset( $product ) && $product instanceof \WC_Product ) {
            return (int) $product->get_id();
        }

        if ( isset( $post ) && $post instanceof \WP_Post ) {
            return (int) $post->ID;
        }

        if ( function_exists( 'get_the_ID' ) ) {
            $id = (int) get_the_ID();
            if ( $id > 0 ) {
                return $id;
            }
        }

        return 0;
    }
}
