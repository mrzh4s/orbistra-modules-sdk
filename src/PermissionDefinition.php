<?php

namespace Orbistra\ModuleSdk;

/**
 * Declares a permission that this module requires.
 * Passed to ModuleRegistry::syncPermissions() which upserts them into auth.permissions.
 */
final readonly class PermissionDefinition
{
    public function __construct(
        public string $name,
        public string $displayName,
        public string $description = '',
        public string $module = '',
    ) {}

    /**
     * Fluent constructor.
     */
    public static function make(string $name, string $displayName, string $description = '', string $module = ''): self
    {
        return new self($name, $displayName, $description, $module);
    }
}
