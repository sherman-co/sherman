<?php
/**
 * Plugin Name:       Sherman Core Next
 * Description:       Modular Sherman Core plugin (next-generation architecture).
 * Version:           0.2.5
 * Author:            Sherman
 * Text Domain:       sherman-core
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'SHERMAN_CORE_NEXT_VERSION', '0.2.5' );
define( 'SHERMAN_CORE_NEXT_FILE', __FILE__ );
define( 'SHERMAN_CORE_NEXT_DIR', plugin_dir_path( __FILE__ ) );
define( 'SHERMAN_CORE_NEXT_URL', plugin_dir_url( __FILE__ ) );

require_once SHERMAN_CORE_NEXT_DIR . 'includes/Core/Autoloader.php';
\ShermanCore\Core\Autoloader::register();

add_action( 'plugins_loaded', static function () {
    ( new \ShermanCore\Core\Bootstrap() )->init();
} );
