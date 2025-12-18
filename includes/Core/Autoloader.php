<?php
namespace ShermanCore\Core;

final class Autoloader {
    public static function register(): void {
        spl_autoload_register( [ __CLASS__, 'autoload' ] );
    }
    private static function autoload( string $class ): void {
        if ( strpos( $class, 'ShermanCore\\' ) !== 0 ) { return; }
        $relative = substr( $class, strlen( 'ShermanCore\\' ) );
        $relative = str_replace( '\\', DIRECTORY_SEPARATOR, $relative );
        $path = SHERMAN_CORE_NEXT_DIR . 'includes' . DIRECTORY_SEPARATOR . $relative . '.php';
        if ( is_readable( $path ) ) { require_once $path; }
    }
}
