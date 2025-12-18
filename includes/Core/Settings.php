<?php
namespace ShermanCore\Core;

final class Settings {
    public const OPTION_NAME = 'sherman_core_settings';

    public static function defaults(): array {
        return [
            'modules' => [
                'offcanvas'         => [ 'enabled' => 'yes' ],
                'elementor_widgets' => [ 'enabled' => 'yes' ],
                'dynamic_tags'      => [
                    'enabled' => 'yes',
                    // Per-tag toggles (legacy-compatible). These are used by the Dynamic Tags module.
                    'tags'    => [
                        'ps_site_url'                 => [ 'enabled' => 'yes' ],
                        'ps_msds_url'                 => [ 'enabled' => 'yes' ],
                        'ps_msds_file_name'           => [ 'enabled' => 'yes' ],
                        'ps_msds_available'           => [ 'enabled' => 'yes' ],
                        'ps_msds_check_now'           => [ 'enabled' => 'yes' ],
                        'ps_product_name'             => [ 'enabled' => 'yes' ],
                        'ps_product_short_description'=> [ 'enabled' => 'yes' ],
                        'ps_product_description'      => [ 'enabled' => 'yes' ],
                        'ps_product_sku'              => [ 'enabled' => 'yes' ],
                        'ps_product_categories'       => [ 'enabled' => 'yes' ],
                        'ps_product_tags'             => [ 'enabled' => 'yes' ],
                        'ps_product_add_to_cart'      => [ 'enabled' => 'yes' ],
                        'ps_product_additional_info'  => [ 'enabled' => 'yes' ],
                        'ps_post_main_image'          => [ 'enabled' => 'yes' ],
                        'ps_post_url'                 => [ 'enabled' => 'yes' ],
                    ],
                ],
                'msds'              => [ 'enabled' => 'yes' ],
                'templates'         => [
                    'enabled' => 'yes',
                    'single_product'  => [ 'enabled' => 'no', 'template_id' => 0 ],
                    'archive_product' => [ 'enabled' => 'no', 'template_id' => 0 ],
                ],
            ],
            'ui' => [ 'show_advanced' => 'no' ],
        ];
    }

    public static function get_all(): array {
        $stored = get_option( self::OPTION_NAME, [] );
        if ( ! is_array( $stored ) ) { $stored = []; }
        return self::merge_deep( self::defaults(), $stored );
    }

    public static function is_module_enabled( string $module_id ): bool {
        $all = self::get_all();
        return ( $all['modules'][ $module_id ]['enabled'] ?? 'no' ) === 'yes';
    }

    public static function sanitize( $input ): array {
        $defaults = self::defaults();
        $clean = self::merge_deep( $defaults, is_array( $input ) ? $input : [] );
        $clean = self::sanitize_yes_no_recursive( $clean, $defaults );

        $clean['modules']['templates']['single_product']['template_id']  = absint( $clean['modules']['templates']['single_product']['template_id'] ?? 0 );
        $clean['modules']['templates']['archive_product']['template_id'] = absint( $clean['modules']['templates']['archive_product']['template_id'] ?? 0 );

        return $clean;
    }

    private static function sanitize_yes_no_recursive( array $value, array $shape ): array {
        foreach ( $shape as $k => $v ) {
            if ( is_array( $v ) ) {
                $value[ $k ] = self::sanitize_yes_no_recursive( is_array( $value[ $k ] ?? null ) ? $value[ $k ] : [], $v );
                continue;
            }
            if ( $v === 'yes' || $v === 'no' ) {
                $raw = $value[ $k ] ?? 'no';
                $value[ $k ] = ( $raw === 'yes' || $raw === 1 || $raw === '1' || $raw === true || $raw === 'on' ) ? 'yes' : 'no';
            }
        }
        return $value;
    }

    private static function merge_deep( array $base, array $over ): array {
        foreach ( $over as $k => $v ) {
            if ( is_array( $v ) && isset( $base[ $k ] ) && is_array( $base[ $k ] ) ) {
                $base[ $k ] = self::merge_deep( $base[ $k ], $v );
            } else {
                $base[ $k ] = $v;
            }
        }
        return $base;
    }
}
