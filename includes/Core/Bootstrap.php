<?php
namespace ShermanCore\Core;

use ShermanCore\Admin\Admin;

final class Bootstrap {

    public function init(): void {
        load_plugin_textdomain(
            'sherman-core',
            false,
            dirname( plugin_basename( SHERMAN_CORE_NEXT_FILE ) ) . '/languages'
        );

        Assets::init();
        ElementorSupport::init();

        // One-time migration from the legacy (pre-Next) options.
        Migrator::maybe_migrate();

        ( new Admin() )->init();

        foreach ( ModuleRegistry::sort_modules( ModuleRegistry::modules() ) as $module ) {
            $module->register_hooks();
        }
    }
}
