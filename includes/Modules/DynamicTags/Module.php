<?php
namespace ShermanCore\Modules\DynamicTags;

use ShermanCore\Core\AbstractModule;
use ShermanCore\Core\Settings;

final class Module extends AbstractModule {

    public function id(): string { return 'dynamic_tags'; }

    public function manifest(): array {
        return [
            'id'          => $this->id(),
            'title'       => __( 'Dynamic Tags', 'sherman-core' ),
            'description' => __( 'Registers custom Elementor Dynamic Tags (PS Core tags).', 'sherman-core' ),
            'category'    => 'elementor',
            'order'       => 30,
            'dependencies'=> [ 'Elementor' ],
            'keywords'    => [ 'dynamic tags', 'ps core', 'tags' ],
            'settings_groups' => [
                'basics' => [
                    'title' => __( 'Basics', 'sherman-core' ),
                    'description' => __( 'Enable/disable and core options.', 'sherman-core' ),
                    'advanced' => false,
                ],
                'advanced' => [
                    'title' => __( 'Advanced', 'sherman-core' ),
                    'description' => __( 'More options will be added during migration.', 'sherman-core' ),
                    'advanced' => true,
                ],
            ],
        ];
    }

    public function dependencies_ok(): bool {
        return did_action( 'elementor/loaded' ) || class_exists( '\\Elementor\\Plugin' );
    }

    protected function boot(): void {
        add_action( 'elementor/dynamic_tags/register', [ $this, 'register_dynamic_tags' ] );
    }

    /**
     * Register PS Core dynamic tags group and its enabled tags.
     */
    public function register_dynamic_tags( $dynamic_tags_manager ): void {
        if ( ! class_exists( '\\Elementor\\Core\\DynamicTags\\Tag' ) ) {
            return;
        }

        $settings = Settings::get_all();

        // Check per-tag enabled toggles.
        $defs = $this->definitions();
        if ( empty( $defs ) ) {
            return;
        }

        $has_enabled = false;
        foreach ( $defs as $slug => $def ) {
            if ( $this->is_tag_enabled( $slug, $settings ) ) {
                $has_enabled = true;
                break;
            }
        }

        if ( ! $has_enabled ) {
            return;
        }

        $dynamic_tags_manager->register_group(
            'ps-core',
            [ 'title' => __( 'PS Core', 'sherman-core' ) ]
        );

        foreach ( $defs as $slug => $def ) {
            if ( ! $this->is_tag_enabled( $slug, $settings ) ) {
                continue;
            }

            $file = $def['file'] ?? '';
            if ( $file && file_exists( $file ) ) {
                require_once $file;
            }

            $class = $def['class'] ?? '';
            if ( $class && class_exists( $class ) ) {
                $dynamic_tags_manager->register( new $class() );
            }
        }
    }

    private function is_tag_enabled( string $slug, array $settings ): bool {
        // New settings array.
        $val = $settings['modules']['dynamic_tags']['tags'][ $slug ]['enabled'] ?? null;
        if ( $val !== null ) {
            return $val === 'yes';
        }

        // Legacy fallback (if migration hasn't run for some reason).
        $legacy_key = $this->definitions()[ $slug ]['option_key'] ?? '';
        if ( $legacy_key ) {
            return get_option( $legacy_key, 'yes' ) === 'yes';
        }

        return true;
    }

    /**
     * Definitions mirror the legacy plugin so option keys and tag classes remain stable.
     */
    private function definitions(): array {
        $base = __DIR__ . '/tags';
        return [
            'ps_site_url' => [
                'option_key' => 'sherman_core_enable_tag_ps_site_url',
                'file'  => $base . '/class-ps-tag-site-url.php',
                'class' => '\\PS_Core\\Dynamic_Tags\\Tag_Site_URL',
            ],
            'ps_msds_url' => [
                'option_key' => 'sherman_core_enable_tag_ps_msds_url',
                'file'  => $base . '/class-ps-tag-msds.php',
                'class' => '\\PS_Core\\Dynamic_Tags\\Tag_MSDS_URL',
            ],
            'ps_msds_file_name' => [
                'option_key' => 'sherman_core_enable_tag_ps_msds_file_name',
                'file'  => $base . '/class-ps-tag-msds.php',
                'class' => '\\PS_Core\\Dynamic_Tags\\Tag_MSDS_File_Name',
            ],
            'ps_msds_available' => [
                'option_key' => 'sherman_core_enable_tag_ps_msds_available',
                'file'  => $base . '/class-ps-tag-msds.php',
                'class' => '\\PS_Core\\Dynamic_Tags\\Tag_MSDS_Available',
            ],
            'ps_msds_check_now' => [
                'option_key' => 'sherman_core_enable_tag_ps_msds_check_now',
                'file'  => $base . '/class-ps-tag-msds.php',
                'class' => '\\PS_Core\\Dynamic_Tags\\Tag_MSDS_Check_Now',
            ],
            'ps_product_name' => [
                'option_key' => 'sherman_core_enable_tag_ps_product_name',
                'file'  => $base . '/class-ps-tag-product.php',
                'class' => '\\PS_Core\\Dynamic_Tags\\Tag_Product_Name',
            ],
            'ps_product_short_description' => [
                'option_key' => 'sherman_core_enable_tag_ps_product_short_description',
                'file'  => $base . '/class-ps-tag-product.php',
                'class' => '\\PS_Core\\Dynamic_Tags\\Tag_Product_Short_Description',
            ],
            'ps_product_description' => [
                'option_key' => 'sherman_core_enable_tag_ps_product_description',
                'file'  => $base . '/class-ps-tag-product.php',
                'class' => '\\PS_Core\\Dynamic_Tags\\Tag_Product_Description',
            ],
            'ps_product_sku' => [
                'option_key' => 'sherman_core_enable_tag_ps_product_sku',
                'file'  => $base . '/class-ps-tag-product.php',
                'class' => '\\PS_Core\\Dynamic_Tags\\Tag_Product_SKU',
            ],
            'ps_product_categories' => [
                'option_key' => 'sherman_core_enable_tag_ps_product_categories',
                'file'  => $base . '/class-ps-tag-product.php',
                'class' => '\\PS_Core\\Dynamic_Tags\\Tag_Product_Categories',
            ],
            'ps_product_tags' => [
                'option_key' => 'sherman_core_enable_tag_ps_product_tags',
                'file'  => $base . '/class-ps-tag-product.php',
                'class' => '\\PS_Core\\Dynamic_Tags\\Tag_Product_Tags',
            ],
            'ps_product_add_to_cart' => [
                'option_key' => 'sherman_core_enable_tag_ps_product_add_to_cart',
                'file'  => $base . '/class-ps-tag-product.php',
                'class' => '\\PS_Core\\Dynamic_Tags\\Tag_Product_Add_To_Cart',
            ],
            'ps_product_additional_info' => [
                'option_key' => 'sherman_core_enable_tag_ps_product_additional_info',
                'file'  => $base . '/class-ps-tag-product.php',
                'class' => '\\PS_Core\\Dynamic_Tags\\Tag_Product_Additional_Info',
            ],
            'ps_post_main_image' => [
                'option_key' => 'sherman_core_enable_tag_ps_post_main_image',
                'file'  => $base . '/class-ps-tag-post-main-image.php',
                'class' => '\\PS_Core\\Dynamic_Tags\\Tag_Post_Main_Image',
            ],
            'ps_post_url' => [
                'option_key' => 'sherman_core_enable_tag_ps_post_url',
                'file'  => $base . '/class-ps-tag-post-url.php',
                'class' => '\\PS_Core\\Dynamic_Tags\\Tag_Post_URL',
            ],
        ];
    }
}
