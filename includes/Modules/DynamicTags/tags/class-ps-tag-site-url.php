<?php
namespace PS_Core\Dynamic_Tags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Tag_Site_URL extends Tag {

    public function get_name() {
        // اسم داخلی تگ
        return 'ps-site-url';
    }

    public function get_title() {
        // اسم نمایشی در UI
        return __( 'PS Site URL', 'sherman-core' );
    }

    public function get_group() {
        // همون گروهی که در پلاگین ثبت کردیم: ps-core
        return 'ps-core';
    }

    public function get_categories() {
        // این تگ در فیلدهای URL نمایش داده می‌شود
        return [ Module::URL_CATEGORY ];
    }

    public function render() {
        echo esc_url( get_site_url() );
    }
}
