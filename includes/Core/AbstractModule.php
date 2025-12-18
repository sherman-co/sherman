<?php
namespace ShermanCore\Core;

abstract class AbstractModule implements ModuleInterface {

    public function dependencies_ok(): bool { return true; }

    protected function is_enabled(): bool {
        return Settings::is_module_enabled( $this->id() );
    }

    public function register_hooks(): void {
        if ( ! $this->is_enabled() ) { return; }
        if ( ! $this->dependencies_ok() ) { return; }
        $this->boot();
    }

    abstract protected function boot(): void;
}
