<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Custom single product template for WooCommerce built with Elementor.

if ( function_exists( 'is_woocommerce' ) ) {
    get_header( 'shop' );
} else {
    get_header();
}

$template_id = 0;

// Prefer unified settings array; fall back to legacy options.
if ( class_exists( '\ShermanCore\Core\Settings' ) ) {
    $all = \ShermanCore\Core\Settings::get_all();
    $template_id = (int) ( $all['modules']['templates']['single']['template_id'] ?? 0 );
}

if ( ! $template_id ) {
    $template_id = (int) get_option( 'sherman_core_product_template_id', 0 );
}

$template_id = (int) apply_filters( 'sherman_core_product_template_id', $template_id );

if ( class_exists( '\Elementor\Plugin' ) && $template_id > 0 ) {
    echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $template_id );
} else {
    if ( function_exists( 'woocommerce_content' ) ) {
        woocommerce_content();
    } else {
        if ( have_posts() ) {
            while ( have_posts() ) {
                the_post();
                if ( function_exists( 'wc_get_template_part' ) ) {
                    wc_get_template_part( 'content', 'single-product' );
                } else {
                    the_content();
                }
            }
        }
    }
}

if ( function_exists( 'is_woocommerce' ) ) {
    get_footer( 'shop' );
} else {
    get_footer();
}
