<?php
namespace PS_Core\Dynamic_Tags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * پایه‌ی مشترک برای MSDS tags (فقط کمکی – اگر لازم شد میشه حذفش کرد و کد رو تکرار نوشت).
 */
abstract class Tag_MSDS_Base extends Tag {

    /**
     * همه‌ی این تگ‌ها در گروه ps-core هستند.
     */
    public function get_group() {
        return 'ps-core';
    }

    /**
     * کمک برای گرفتن متای محصول فعلی.
     *
     * @param string $meta_key
     * @return string
     */
    protected function get_msds_meta( $meta_key ) {
        if ( ! function_exists( '\ps_core_get_current_product_id' ) ) {
            return '';
        }

        $product_id = \ps_core_get_current_product_id();
        if ( ! $product_id ) {
            return '';
        }

        $value = get_post_meta( $product_id, $meta_key, true );

        return (string) $value;
    }
}

/**
 * MSDS URL
 */
class Tag_MSDS_URL extends Tag_MSDS_Base {

    public function get_name() {
        return 'ps-msds-url';
    }

    public function get_title() {
        return __( 'MSDS URL', 'sherman-core' );
    }

    public function get_categories() {
        return [ Module::URL_CATEGORY ];
    }

    public function render() {
        $url = $this->get_msds_meta( '_ps_msds_url' );
        if ( ! $url ) {
            return;
        }

        echo esc_url( $url );
    }
}

/**
 * MSDS File Name
 */
class Tag_MSDS_File_Name extends Tag_MSDS_Base {

    public function get_name() {
        return 'ps-msds-file-name';
    }

    public function get_title() {
        return __( 'MSDS File Name', 'sherman-core' );
    }

    public function get_categories() {
        return [ Module::TEXT_CATEGORY ];
    }

    public function render() {
        $name = $this->get_msds_meta( '_ps_msds_file_name' );

        // اگر خالی بود، می‌تونیم سعی کنیم از خود PDF title استفاده کنیم
        if ( '' === $name ) {
            $pdf_id = $this->get_msds_meta( '_ps_msds_pdf_id' );
            if ( $pdf_id ) {
                $pdf_title = get_the_title( (int) $pdf_id );
                if ( $pdf_title ) {
                    $name = $pdf_title;
                }
            }
        }

        if ( '' === $name ) {
            return;
        }

        echo esc_html( $name );
    }
}

/**
 * MSDS Available
 */
class Tag_MSDS_Available extends Tag_MSDS_Base {

    public function get_name() {
        return 'ps-msds-available';
    }

    public function get_title() {
        return __( 'MSDS Available', 'sherman-core' );
    }

    public function get_categories() {
        return [ Module::TEXT_CATEGORY ];
    }

    public function render() {
        $value = $this->get_msds_meta( '_ps_msds_available' );

        if ( '' === $value ) {
            $value = __( 'Available', 'sherman-core' );
        }

        echo esc_html( $value );
    }
}

/**
 * MSDS Check Now
 */
class Tag_MSDS_Check_Now extends Tag_MSDS_Base {

    public function get_name() {
        return 'ps-msds-check-now';
    }

    public function get_title() {
        return __( 'MSDS Check Now', 'sherman-core' );
    }

    public function get_categories() {
        return [ Module::TEXT_CATEGORY ];
    }

    public function render() {
        $value = $this->get_msds_meta( '_ps_msds_check_now' );

        if ( '' === $value ) {
            $value = __( 'Check Now', 'sherman-core' );
        }

        echo esc_html( $value );
    }
}
