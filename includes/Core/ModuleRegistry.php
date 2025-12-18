<?php
namespace ShermanCore\Core;

use ShermanCore\Modules\Offcanvas\Module as OffcanvasModule;
use ShermanCore\Modules\ElementorWidgets\Module as ElementorWidgetsModule;
use ShermanCore\Modules\DynamicTags\Module as DynamicTagsModule;
use ShermanCore\Modules\MSDS\Module as MSDSModule;
use ShermanCore\Modules\Templates\Module as TemplatesModule;

final class ModuleRegistry {

    public static function categories(): array {
        return [
            'general'       => [ 'title' => __( 'General', 'sherman-core' ),         'order' => 10 ],
            'frontend_ui'   => [ 'title' => __( 'Frontend UI', 'sherman-core' ),     'order' => 20 ],
            'elementor'     => [ 'title' => __( 'Elementor', 'sherman-core' ),       'order' => 30 ],
            'woocommerce'   => [ 'title' => __( 'WooCommerce', 'sherman-core' ),     'order' => 40 ],
            'content_data'  => [ 'title' => __( 'Content & Data', 'sherman-core' ),  'order' => 50 ],
            'advanced'      => [ 'title' => __( 'Advanced', 'sherman-core' ),        'order' => 60 ],
        ];
    }

    /** @return ModuleInterface[] */
    public static function modules(): array {
        return [
            new OffcanvasModule(),
            new ElementorWidgetsModule(),
            new DynamicTagsModule(),
            new MSDSModule(),
            new TemplatesModule(),
        ];
    }

    public static function sort_modules( array $modules ): array {
        usort( $modules, static function ( ModuleInterface $a, ModuleInterface $b ) {
            return (int) ( $a->manifest()['order'] ?? 100 ) <=> (int) ( $b->manifest()['order'] ?? 100 );
        } );
        return $modules;
    }
}
