<?php
namespace PS_Core\Dynamic_Tags;

use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Modules\DynamicTags\Module;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Tag_Post_Main_Image extends Data_Tag {

    /**
     * اسم داخلی تگ
     */
    public function get_name() {
        return 'ps-post-main-image';
    }

    /**
     * عنوانی که در UI المنتور می‌بینی
     */
    public function get_title() {
        return __( 'PS Post Main Image', 'sherman-core' );
    }

    /**
     * گروهی که این تگ داخلش قرار می‌گیره
     * همون "ps-core" که از قبل ساختیم
     */
    public function get_group() {
        return 'ps-core';
    }

    /**
     * نوع دیتایی که این تگ برمی‌گردونه
     * اینجا IMAGE_CATEGORY یعنی برای Image / Background / Media قابل استفاده‌ست
     */
    public function get_categories() {
        return [ Module::IMAGE_CATEGORY ];
    }

    /**
     * مقدار داینامیک برمی‌گردونه: id + url تصویر
     *
     * @param array $options
     * @return array
     */
    public function get_value( array $options = [] ) {

        $post_id = 0;

        // اگر helper خود پلاگین برای محصول موجود است، اول از اون استفاده کن
        if ( function_exists( 'ps_core_get_current_product_id' ) ) {
            $post_id = (int) ps_core_get_current_product_id();
        }

        // اگر هنوز چیزی نداریم، از کانتکست فعلی المنتور استفاده کن
        if ( ! $post_id ) {
            $post_id = get_the_ID();
        }

        if ( ! $post_id ) {
            return [];
        }

        $attachment_id = 0;

        // ۱) اول تصویر شاخص
        $attachment_id = get_post_thumbnail_id( $post_id );

        // ۲) اگر تصویر شاخص نداشت و محصول ووکامرس بود → اولین تصویر گالری
        if ( ! $attachment_id && class_exists( 'WC_Product' ) ) {
            $product = wc_get_product( $post_id );
            if ( $product instanceof \WC_Product ) {
                $gallery_ids = $product->get_gallery_image_ids();
                if ( ! empty( $gallery_ids ) ) {
                    $attachment_id = (int) $gallery_ids[0];
                }
            }
        }

        // ۳) اگر باز هم چیزی نبود → اولین attachment مرتبط با پست
        if ( ! $attachment_id ) {
            $attachments = get_posts( [
                'post_type'      => 'attachment',
                'numberposts'    => 1,
                'post_status'    => 'inherit',
                'post_parent'    => $post_id,
                'orderby'        => 'menu_order',
                'order'          => 'ASC',
                'fields'         => 'ids',
            ] );

            if ( ! empty( $attachments ) ) {
                $attachment_id = (int) $attachments[0];
            }
        }

        // اگر هیچ عکسی پیدا نشد
        if ( ! $attachment_id ) {
            return [];
        }

        // المنتور می‌تونه سایز رو از options بده؛ اگر نبود، full
        $size = ! empty( $options['size'] ) ? $options['size'] : 'full';
        $url  = wp_get_attachment_image_url( $attachment_id, $size );

        if ( ! $url ) {
            return [];
        }

        return [
            'id'  => $attachment_id,
            'url' => $url,
        ];
    }
}
