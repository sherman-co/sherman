<?php
namespace PS_Core\Dynamic_Tags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Tag_Post_URL extends Tag {

    /**
     * اسم داخلی تگ
     */
    public function get_name() {
        return 'ps-post-url';
    }

    /**
     * اسم نمایشی در UI المنتور
     */
    public function get_title() {
        return __( 'PS Post URL', 'sherman-core' );
    }

    /**
     * گروه: همون PS Core
     */
    public function get_group() {
        return 'ps-core';
    }

    /**
     * این تگ تو دسته URL باشه
     */
    public function get_categories() {
        return [ Module::URL_CATEGORY ];
    }

    /**
     * خروجی تگ (permalink پست/محصول جاری)
     */
    public function render() {

        $post_id = 0;

        // اگر helper محصول داریم (برای ووکامرس)
        if ( function_exists( 'ps_core_get_current_product_id' ) ) {
            $post_id = (int) ps_core_get_current_product_id();
        }

        // اگر هنوز خالیه، از کانتکست فعلی استفاده کن
        if ( ! $post_id ) {
            $post_id = get_the_ID();
        }

        if ( ! $post_id ) {
            return;
        }

        $url = get_permalink( $post_id );

        if ( ! $url ) {
            return;
        }

        echo esc_url( $url );
    }
}
