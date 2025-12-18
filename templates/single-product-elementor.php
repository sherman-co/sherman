<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Custom single product template for WooCommerce built with Elementor.
 * This file is loaded via the template_include filter from the Sherman Core plugin.
 */

// Use the shop header if the theme/WooCommerce provides it.
if ( function_exists( 'is_woocommerce' ) ) {
    get_header( 'shop' );
} else {
    get_header();
}

// 1) Get Elementor template ID from plugin settings.
$template_id = 0;

// Prefer the new unified settings array, fall back to legacy options.
if ( class_exists( '\ShermanCore\Core\Settings' ) ) {
     = \ShermanCoreCoreSettings::get_all();
     = (int) (  ?? 0 );
}
if ( !  ) {
     = (int) get_option( 'sherman_core_product_template_id', 0 );
}

// 2) Allow overriding the template ID via filter (for advanced customization).
$template_id = (int) apply_filters( 'sherman_core_product_template_id', $template_id );

if ( class_exists( '\Elementor\Plugin' ) && $template_id > 0 ) {

    // 3) Render the selected Elementor template.
    echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $template_id );

} else {

    // 4) If Elementor is not available or no template is selected,
    //    fall back to the default WooCommerce single product behavior.

    if ( function_exists( 'woocommerce_content' ) ) {

        // This is what the default WooCommerce single-product template does.
        woocommerce_content();

    } else {

        // Very basic fallback if WooCommerce content function is not available.
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

// Use the shop footer if available, otherwise default footer.
if ( function_exists( 'is_woocommerce' ) ) {
    get_footer( 'shop' );
} else {
    get_footer();
}
