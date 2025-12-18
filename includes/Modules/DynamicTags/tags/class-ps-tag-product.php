<?php
namespace PS_Core\Dynamic_Tags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Base helper برای گرفتن محصول جاری
 */
abstract class Tag_Product_Base extends Tag {

    /**
     * گرفتن شیء محصول ووکامرس برای کانتکست فعلی
     *
     * @return \WC_Product|null
     */
    protected function get_product() {
        if ( function_exists( 'ps_core_get_current_product_id' ) ) {
            $product_id = (int) \ps_core_get_current_product_id();
        } else {
            global $product, $post;
            $product_id = 0;

            if ( $product instanceof \WC_Product ) {
                $product_id = $product->get_id();
            } elseif ( $post instanceof \WP_Post && 'product' === $post->post_type ) {
                $product_id = $post->ID;
            }
        }

        if ( ! $product_id ) {
            return null;
        }

        $wc_product = wc_get_product( $product_id );
        if ( ! $wc_product || ! $wc_product instanceof \WC_Product ) {
            return null;
        }

        return $wc_product;
    }

    public function get_group() {
        // مثل بقیه تگ‌ها در گروه PS Core
        return 'ps-core';
    }
}

/**
 * 1) Product Name
 */
class Tag_Product_Name extends Tag_Product_Base {

    public function get_name() {
        return 'ps-product-name';
    }

    public function get_title() {
        return __( 'Product Name', 'sherman-core' );
    }

    public function get_categories() {
        return [ Module::TEXT_CATEGORY ];
    }

    public function render() {
        $product = $this->get_product();
        if ( ! $product ) {
            return;
        }

        echo esc_html( $product->get_name() );
    }
}

/**
 * 2) Product Short Description
 */
class Tag_Product_Short_Description extends Tag_Product_Base {

    public function get_name() {
        return 'ps-product-short-description';
    }

    public function get_title() {
        return __( 'Product Short Description', 'sherman-core' );
    }

    public function get_categories() {
        return [ Module::TEXT_CATEGORY ];
    }

    public function render() {
        $product = $this->get_product();
        if ( ! $product ) {
            return;
        }

        $short_desc = $product->get_short_description();
        if ( $short_desc ) {
            echo wp_kses_post( $short_desc );
        }
    }
}

/**
 * 3) Product Description (Full)
 */
class Tag_Product_Description extends Tag_Product_Base {

    public function get_name() {
        return 'ps-product-description';
    }

    public function get_title() {
        return __( 'Product Description', 'sherman-core' );
    }

    public function get_categories() {
        return [ Module::TEXT_CATEGORY ];
    }

    public function render() {
        $product = $this->get_product();
        if ( ! $product ) {
            return;
        }

        $desc = $product->get_description();
        if ( $desc ) {
            echo wp_kses_post( $desc );
        }
    }
}

/**
 * 4) Product SKU
 */
class Tag_Product_SKU extends Tag_Product_Base {

    public function get_name() {
        return 'ps-product-sku';
    }

    public function get_title() {
        return __( 'Product SKU', 'sherman-core' );
    }

    public function get_categories() {
        return [ Module::TEXT_CATEGORY ];
    }

    public function render() {
        $product = $this->get_product();
        if ( ! $product ) {
            return;
        }

        $sku = $product->get_sku();
        if ( $sku ) {
            echo esc_html( $sku );
        }
    }
}

/**
 * 5) Product Categories
 */
class Tag_Product_Categories extends Tag_Product_Base {

    public function get_name() {
        return 'ps-product-categories';
    }

    public function get_title() {
        return __( 'Product Categories', 'sherman-core' );
    }

    public function get_categories() {
        return [ Module::TEXT_CATEGORY ];
    }

    public function render() {
        $product = $this->get_product();
        if ( ! $product ) {
            return;
        }

        $product_id = $product->get_id();

        if ( ! function_exists( 'wc_get_product_category_list' ) ) {
            return;
        }

        $cats_html = wc_get_product_category_list(
            $product_id,
            ', '
        );

        if ( $cats_html ) {
            echo wp_kses_post( $cats_html );
        }
    }
}

/**
 * 6) Product Tags
 */
class Tag_Product_Tags extends Tag_Product_Base {

    public function get_name() {
        return 'ps-product-tags';
    }

    public function get_title() {
        return __( 'Product Tags', 'sherman-core' );
    }

    public function get_categories() {
        return [ Module::TEXT_CATEGORY ];
    }

    public function render() {
        $product = $this->get_product();
        if ( ! $product ) {
            return;
        }

        $product_id = $product->get_id();

        if ( ! function_exists( 'wc_get_product_tag_list' ) ) {
            return;
        }

        $tags_html = wc_get_product_tag_list(
            $product_id,
            ', '
        );

        if ( $tags_html ) {
            echo wp_kses_post( $tags_html );
        }
    }
}

/**
 * 7) Add to Cart Button (full form)
 */
class Tag_Product_Add_To_Cart extends Tag_Product_Base {

    public function get_name() {
        return 'ps-product-add-to-cart';
    }

    public function get_title() {
        return __( 'Product Add to Cart', 'sherman-core' );
    }

    public function get_categories() {
        // چون خروجی HTML فرم است، در فیلدهای متن/HTML استفاده می‌شود
        return [ Module::TEXT_CATEGORY ];
    }

    public function render() {
        $product = $this->get_product();
        if ( ! $product ) {
            return;
        }

        if ( ! function_exists( 'woocommerce_template_single_add_to_cart' ) ) {
            return;
        }

        // اجرای تمپلیت پیش‌فرض ووکامرس
        ob_start();
        woocommerce_template_single_add_to_cart();
        $html = ob_get_clean();

        echo $html; // عمداً بدون esc_ چون HTML فرم است
    }
}

/**
 * 8) Additional Info (Attributes table)
 */
class Tag_Product_Additional_Info extends Tag_Product_Base {

    public function get_name() {
        return 'ps-product-additional-info';
    }

    public function get_title() {
        return __( 'Product Additional Info', 'sherman-core' );
    }

    public function get_categories() {
        return [ Module::TEXT_CATEGORY ];
    }

    public function render() {
        $product = $this->get_product();
        if ( ! $product ) {
            return;
        }

        if ( ! method_exists( $product, 'get_attributes' ) ) {
            return;
        }

        $attributes = $product->get_attributes();

        if ( empty( $attributes ) ) {
            return;
        }

        if ( ! function_exists( 'wc_display_product_attributes' ) ) {
            return;
        }

        ob_start();
        wc_display_product_attributes( $product );
        $html = ob_get_clean();

        echo $html; // HTML جدول
    }
}
