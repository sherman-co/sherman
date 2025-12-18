<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Custom product archive template for WooCommerce built with Elementor.
 * This file is loaded via the template_include filter from the Sherman Core plugin.
 */

// Use the shop header if available, otherwise default header.
if ( function_exists( 'is_woocommerce' ) ) {
    get_header( 'shop' );
} else {
    get_header();
}

// 1) Get Elementor template ID for product archives from plugin settings.
$archive_template_id = (int) get_option( 'sherman_core_product_archive_template_id', 0 );

// 2) Allow overriding the archive template ID via filter for advanced usage.
$archive_template_id = (int) apply_filters( 'sherman_core_product_archive_template_id', $archive_template_id );

if ( class_exists( '\Elementor\Plugin' ) && $archive_template_id > 0 ) {

    // 3) Render the selected Elementor template for the product archive.
    echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $archive_template_id );

} else {

    // 4) Fallback: use the default WooCommerce archive behavior.

    if ( function_exists( 'woocommerce_before_main_content' ) ) {
        do_action( 'woocommerce_before_main_content' );
    }

    if ( have_posts() ) {

        if ( function_exists( 'woocommerce_before_shop_loop' ) ) {
            do_action( 'woocommerce_before_shop_loop' );
        }

        if ( function_exists( 'woocommerce_product_loop_start' ) ) {
            woocommerce_product_loop_start();
        }

        while ( have_posts() ) {
            the_post();

            if ( function_exists( 'wc_get_template_part' ) ) {
                wc_get_template_part( 'content', 'product' );
            } else {
                the_title( '<h2>', '</h2>' );
                the_content();
            }
        }

        if ( function_exists( 'woocommerce_product_loop_end' ) ) {
            woocommerce_product_loop_end();
        }

        if ( function_exists( 'woocommerce_after_shop_loop' ) ) {
            do_action( 'woocommerce_after_shop_loop' );
        }

    } else {
        if ( function_exists( 'woocommerce_no_products_found' ) ) {
            do_action( 'woocommerce_no_products_found' );
        }
    }

    if ( function_exists( 'woocommerce_after_main_content' ) ) {
        do_action( 'woocommerce_after_main_content' );
    }
}

// Use the shop footer if available, otherwise default footer.
if ( function_exists( 'is_woocommerce' ) ) {
    get_footer( 'shop' );
} else {
    get_footer();
}
