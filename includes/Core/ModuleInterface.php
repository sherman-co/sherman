<?php
namespace ShermanCore\Core;

interface ModuleInterface {
    public function id(): string;
    public function manifest(): array;
    public function dependencies_ok(): bool;
    public function register_hooks(): void;
}
