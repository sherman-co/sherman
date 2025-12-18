<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Custom single product template for WooCommerce built with Elementor.

$all = [];
if ( class_exists( '\\ShermanCore\\Core\\Settings' ) ) {
    $all = \ShermanCore\Core\Settings::get_all();
}

$tpls = $all['modules']['templates'] ?? [];

$apply_header = ( $tpls['header']['enabled'] ?? 'no' ) === 'yes'
    && (int) ( $tpls['header']['template_id'] ?? 0 ) > 0
    && class_exists( '\\Elementor\\Plugin' )
    && ( ( $tpls['header']['scope'] ?? 'woocommerce' ) === 'global' || ( function_exists( 'is_woocommerce' ) && is_woocommerce() ) );

$apply_footer = ( $tpls['footer']['enabled'] ?? 'no' ) === 'yes'
    && (int) ( $tpls['footer']['template_id'] ?? 0 ) > 0
    && class_exists( '\\Elementor\\Plugin' )
    && ( ( $tpls['footer']['scope'] ?? 'woocommerce' ) === 'global' || ( function_exists( 'is_woocommerce' ) && is_woocommerce() ) );

if ( $apply_header ) {
    echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( (int) $tpls['header']['template_id'] );
} else {
    if ( function_exists( 'is_woocommerce' ) ) {
        get_header( 'shop' );
    } else {
        get_header();
    }
}

$template_id = (int) apply_filters( 'sherman_core_product_template_id', 0 );
if ( ! $template_id ) {
    $template_id = (int) ( $tpls['single_product']['template_id'] ?? 0 );
}
if ( ! $template_id ) {
    $template_id = (int) get_option( 'sherman_core_product_template_id', 0 );
}

if ( class_exists( '\\Elementor\\Plugin' ) && $template_id > 0 ) {
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

if ( $apply_footer ) {
    echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( (int) $tpls['footer']['template_id'] );
} else {
    if ( function_exists( 'is_woocommerce' ) ) {
        get_footer( 'shop' );
    } else {
        get_footer();
    }
}
