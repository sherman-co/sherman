<?php
namespace ShermanCore\Core;

/**
 * One-time migration helper from the legacy plugin options into the new unified settings array.
 *
 * This keeps backward compatibility for existing installs where settings were stored as many
 * individual options (e.g. sherman_core_enable_offcanvas, per-tag toggles, template IDs, etc.).
 */
final class Migrator {

    private const DONE_FLAG = 'sherman_core_next_migrated_v1';

    public static function maybe_migrate(): void {
        // Already migrated.
        if ( get_option( self::DONE_FLAG ) === 'yes' ) {
            return;
        }

        // If there is no evidence of legacy options, do nothing.
        if ( ! self::legacy_options_exist() ) {
            return;
        }

        $current = Settings::get_all();

        // Global module toggles.
        $current['modules']['offcanvas']['enabled']         = self::yn( get_option( 'sherman_core_enable_offcanvas', $current['modules']['offcanvas']['enabled'] ) );
        $current['modules']['dynamic_tags']['enabled']      = self::yn( get_option( 'sherman_core_enable_dynamic_tags', $current['modules']['dynamic_tags']['enabled'] ) );
        $current['modules']['msds']['enabled']              = self::yn( get_option( 'sherman_core_enable_msds', $current['modules']['msds']['enabled'] ) );
        $current['modules']['templates']['single_product']['enabled']  = self::yn( get_option( 'sherman_core_enable_product_template', $current['modules']['templates']['single_product']['enabled'] ) );
        $current['modules']['templates']['archive_product']['enabled'] = self::yn( get_option( 'sherman_core_enable_product_archive_template', $current['modules']['templates']['archive_product']['enabled'] ) );

        // Template IDs.
        $current['modules']['templates']['single_product']['template_id']  = absint( get_option( 'sherman_core_product_template_id', $current['modules']['templates']['single_product']['template_id'] ?? 0 ) );
        $current['modules']['templates']['archive_product']['template_id'] = absint( get_option( 'sherman_core_product_archive_template_id', $current['modules']['templates']['archive_product']['template_id'] ?? 0 ) );

        // Dynamic tag per-tag toggles.
        $defs = self::dynamic_tag_definitions();
        foreach ( $defs as $slug => $def ) {
            $opt_key = $def['option_key'] ?? '';
            if ( ! $opt_key ) { continue; }
            $current['modules']['dynamic_tags']['tags'][ $slug ]['enabled'] = self::yn( get_option( $opt_key, $current['modules']['dynamic_tags']['tags'][ $slug ]['enabled'] ?? 'yes' ) );
        }

        update_option( Settings::OPTION_NAME, $current, false );
        update_option( self::DONE_FLAG, 'yes', false );
    }

    private static function legacy_options_exist(): bool {
        $keys = [
            'sherman_core_enable_offcanvas',
            'sherman_core_enable_dynamic_tags',
            'sherman_core_enable_msds',
            'sherman_core_enable_product_template',
            'sherman_core_product_template_id',
            'sherman_core_enable_product_archive_template',
            'sherman_core_product_archive_template_id',
        ];

        foreach ( $keys as $k ) {
            if ( get_option( $k, null ) !== null ) {
                return true;
            }
        }

        // Also check for at least one per-tag toggle.
        foreach ( self::dynamic_tag_definitions() as $def ) {
            $k = $def['option_key'] ?? '';
            if ( $k && get_option( $k, null ) !== null ) {
                return true;
            }
        }

        return false;
    }

    private static function yn( $raw ): string {
        return ( $raw === 'yes' || $raw === 1 || $raw === '1' || $raw === true || $raw === 'on' ) ? 'yes' : 'no';
    }

    /**
     * Legacy dynamic tag definitions (slug => option_key mapping).
     */
    private static function dynamic_tag_definitions(): array {
        return [
            'ps_site_url' => [ 'option_key' => 'sherman_core_enable_tag_ps_site_url' ],
            'ps_msds_url' => [ 'option_key' => 'sherman_core_enable_tag_ps_msds_url' ],
            'ps_msds_file_name' => [ 'option_key' => 'sherman_core_enable_tag_ps_msds_file_name' ],
            'ps_msds_available' => [ 'option_key' => 'sherman_core_enable_tag_ps_msds_available' ],
            'ps_msds_check_now' => [ 'option_key' => 'sherman_core_enable_tag_ps_msds_check_now' ],
            'ps_product_name' => [ 'option_key' => 'sherman_core_enable_tag_ps_product_name' ],
            'ps_product_short_description' => [ 'option_key' => 'sherman_core_enable_tag_ps_product_short_description' ],
            'ps_product_description' => [ 'option_key' => 'sherman_core_enable_tag_ps_product_description' ],
            'ps_product_sku' => [ 'option_key' => 'sherman_core_enable_tag_ps_product_sku' ],
            'ps_product_categories' => [ 'option_key' => 'sherman_core_enable_tag_ps_product_categories' ],
            'ps_product_tags' => [ 'option_key' => 'sherman_core_enable_tag_ps_product_tags' ],
            'ps_product_add_to_cart' => [ 'option_key' => 'sherman_core_enable_tag_ps_product_add_to_cart' ],
            'ps_product_additional_info' => [ 'option_key' => 'sherman_core_enable_tag_ps_product_additional_info' ],
            'ps_post_main_image' => [ 'option_key' => 'sherman_core_enable_tag_ps_post_main_image' ],
            'ps_post_url' => [ 'option_key' => 'sherman_core_enable_tag_ps_post_url' ],
        ];
    }
}
