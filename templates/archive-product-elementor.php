<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Custom product archive template for WooCommerce built with Elementor.

if ( function_exists( 'is_woocommerce' ) ) {
    get_header( 'shop' );
} else {
    get_header();
}

$archive_template_id = 0;

// Prefer unified settings array; fall back to legacy options.
if ( class_exists( '\ShermanCore\Core\Settings' ) ) {
    $all = \ShermanCore\Core\Settings::get_all();
    $archive_template_id = (int) ( $all['modules']['templates']['archive']['template_id'] ?? 0 );
}

if ( ! $archive_template_id ) {
    $archive_template_id = (int) get_option( 'sherman_core_product_archive_template_id', 0 );
}

$archive_template_id = (int) apply_filters( 'sherman_core_product_archive_template_id', $archive_template_id );

if ( class_exists( '\Elementor\Plugin' ) && $archive_template_id > 0 ) {
    echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $archive_template_id );
} else {
    if ( function_exists( 'woocommerce_content' ) ) {
        woocommerce_content();
    } else {
        if ( have_posts() ) {
            while ( have_posts() ) {
                the_post();
                if ( function_exists( 'wc_get_template_part' ) ) {
                    wc_get_template_part( 'content', 'product' );
                } else {
                    the_title( '<h2>', '</h2>' );
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
