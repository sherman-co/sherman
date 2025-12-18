<?php
namespace ShermanCore\Modules\ElementorWidgets\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;

final class ProductGalleryWidget extends Widget_Base {

    public function get_name() {
        return 'sherman_product_gallery';
    }

    public function get_title() {
        return __( 'Sherman Product Gallery', 'sherman-core' );
    }

    public function get_icon() {
        return 'eicon-gallery-grid';
    }

    public function get_categories() {
        return [ 'sherman-core' ];
    }

    public function get_keywords() {
        return [ 'gallery', 'product gallery', 'woocommerce', 'image', 'sherman' ];
    }
       
    public function get_style_depends() {
        // از همون فایل CSS پلاگین استفاده می‌کنیم (offcanvas.css)
        return [ 'sherman-core-css' ];
    }

    protected function register_controls() {

        // Content (فعلاً گزینه خاصی لازم نیست)
        $this->start_controls_section(
            'section_content',
            [
                'label' => __( 'Content', 'sherman-core' ),
            ]
        );

        $this->add_control(
            'note',
            [
                'type'            => Controls_Manager::RAW_HTML,
                'raw'             => __( 'This widget outputs the default WooCommerce product gallery for the current product. Make sure you set a preview product in Elementor.', 'sherman-core' ),
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
            ]
        );

        $this->end_controls_section();

        // Style: Wrapper
        $this->start_controls_section(
            'section_style_wrapper',
            [
                'label' => __( 'Wrapper', 'sherman-core' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'wrapper_padding',
            [
                'label'      => __( 'Padding', 'sherman-core' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .sherman-product-gallery-wrapper' =>
                        'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'wrapper_bg',
                'selector' => '{{WRAPPER}} .sherman-product-gallery-wrapper',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'wrapper_border',
                'selector' => '{{WRAPPER}} .sherman-product-gallery-wrapper',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'wrapper_shadow',
                'selector' => '{{WRAPPER}} .sherman-product-gallery-wrapper',
            ]
        );

        $this->end_controls_section();
    }

        protected function render() {

        // اگر ووکامرس فعال نیست
        if ( ! function_exists( 'woocommerce_show_product_images' ) || ! function_exists( 'wc_get_product' ) ) {
            if ( current_user_can( 'manage_options' ) ) {
                echo '<div class="sherman-product-gallery-wrapper"><div class="sherman-core-widget-notice">';
                esc_html_e( 'WooCommerce is not active. Product gallery cannot be displayed.', 'sherman-core' );
                echo '</div></div>';
            }
            return;
        }

        global $product, $post;

        // بکاپ از مقدار فعلی
        $original_product = $product;

        $product_id = 0;

        // تلاش ۱: helper خود پلاگین
        if ( function_exists( 'ps_core_get_current_product_id' ) ) {
            $product_id = (int) ps_core_get_current_product_id();
        }

        // تلاش ۲: اگر helper چیزی نداد اما $post محصول است
        if ( ! $product_id && isset( $post ) && $post instanceof \WP_Post && 'product' === $post->post_type ) {
            $product_id = (int) $post->ID;
        }

        $wc_product = null;

        if ( $product_id ) {
            $wc_product = wc_get_product( $product_id );
        }

        if ( ! $wc_product && $product instanceof \WC_Product ) {
            $wc_product = $product;
        }

        if ( ! $wc_product ) {
            if ( current_user_can( 'manage_options' ) ) {
                echo '<div class="sherman-product-gallery-wrapper"><div class="sherman-core-widget-notice">';
                esc_html_e( 'No product context. Please set a Preview Product in Elementor or view this on a single product page.', 'sherman-core' );
                echo '</div></div>';
            }
            return;
        }
        // حتماً global $product یک WC_Product معتبر باشد
        $product = $wc_product;

        // 🔥 استایل اینلاین فقط برای این ویجت
        ?>
        <style>
            .sherman-product-gallery-wrapper {
                width: 100%;
                max-width: 100%;
            }

            .sherman-product-gallery-wrapper .woocommerce-product-gallery {
                margin: 0;
                max-width: 100%;
            }

            .sherman-product-gallery-wrapper .woocommerce-product-gallery__image img {
                width: 100%;
                height: auto;
                display: block;
                border-radius: 8px;
            }

            .sherman-product-gallery-wrapper .flex-viewport {
                max-width: 100% !important;
                height: auto !important;
                overflow: hidden;
            }

            .sherman-product-gallery-wrapper .flex-control-thumbs {
                margin: 14px 0 0;
                padding: 0;
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                list-style: none;
            }

            .sherman-product-gallery-wrapper .flex-control-thumbs li {
                width: 64px;
                margin: 0;
            }

            .sherman-product-gallery-wrapper .flex-control-thumbs img {
                display: block;
                width: 100%;
                height: auto;
                border-radius: 4px;
                border: 1px solid rgba(0, 0, 0, 0.07);
                box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
                opacity: 0.7;
                transition: all 0.2s ease;
                cursor: pointer;
            }

            .sherman-product-gallery-wrapper .flex-control-thumbs img.flex-active,
            .sherman-product-gallery-wrapper .flex-control-thumbs img:hover {
                opacity: 1;
                transform: translateY(-2px);
                box-shadow: 0 4px 10px rgba(15, 23, 42, 0.18);
            }

            /* دکمه پیش‌فرض زوم ووکامرس را کلاً قایم کن */
            .sherman-product-gallery-wrapper .woocommerce-product-gallery__trigger {
                display: none !important;
            }

            /* زوم عجیبِ js هم قایم */
            .sherman-product-gallery-wrapper img.zoomImg {
                display: none !important;
            }
        </style>
        <?php

        echo '<div class="sherman-product-gallery-wrapper">';

        ob_start();
        woocommerce_show_product_images();
        $html = ob_get_clean();

        echo $html;

        echo '</div>';

        // برگردوندن $product به حالت قبلی
        $product = $original_product;
    }

}
