<?php

namespace Orbistra\ModuleSdk;

/**
 * Default implementations for ModuleDefinitionContract.
 * External vendors extend this class and override only what they need.
 * Internal Orbistra modules extend app/Core/Modules/ModuleDefinition.php which
 * also extends this class (via alias) to stay in sync.
 */
abstract class AbstractModuleDefinition implements ModuleDefinitionContract
{
    // ── Identity (must override) ──────────────────────────────────────────────

    abstract public function slug(): string;
    abstract public function name(): string;

    // ── Identity (optional override) ─────────────────────────────────────────

    public function description(): string { return ''; }
    public function icon(): string { return 'IconBox'; }
    public function version(): string { return '1.0.0'; }
    public function order(): int { return 100; }
    public function isCore(): bool { return false; }

    // ── Author / marketplace metadata ─────────────────────────────────────────

    public function author(): string { return ''; }
    public function authorUrl(): string { return ''; }
    public function homepage(): string { return ''; }
    public function composerPackage(): string { return ''; }

    // ── Settings ──────────────────────────────────────────────────────────────

    public function hasSettings(): bool { return false; }
    public function settingsRoute(): ?string { return null; }
    public function settingsLabel(): string { return $this->name(); }
    public function settingsIcon(): string { return 'IconSettings'; }

    // ── Menu ──────────────────────────────────────────────────────────────────

    public function adminMenuItems(): array { return []; }
    public function appMenuItems(): array { return []; }

    // ── Permissions ───────────────────────────────────────────────────────────

    /** @return PermissionDefinition[] */
    public function permissions(): array { return []; }

    // ── Database ──────────────────────────────────────────────────────────────

    public function schemaName(): ?string { return null; }
}
