<?php

namespace Orbistra\ModuleSdk;

interface ModuleDefinitionContract
{
    // ── Identity ──────────────────────────────────────────────────────────────

    public function slug(): string;
    public function name(): string;
    public function description(): string;
    public function icon(): string;
    public function version(): string;
    public function order(): int;
    public function isCore(): bool;

    // ── Author / marketplace metadata ─────────────────────────────────────────

    public function author(): string;
    public function authorUrl(): string;
    public function homepage(): string;
    public function composerPackage(): string;

    // ── Settings integration ──────────────────────────────────────────────────

    public function hasSettings(): bool;
    public function settingsRoute(): ?string;
    public function settingsLabel(): string;
    public function settingsIcon(): string;

    // ── Menu registration ─────────────────────────────────────────────────────

    /**
     * Admin sidebar menu items.
     * Shape: ['label', 'icon', 'route', 'permission', 'order', 'children'?]
     */
    public function adminMenuItems(): array;

    /**
     * App (non-admin) sidebar menu items.
     */
    public function appMenuItems(): array;

    // ── Permissions ───────────────────────────────────────────────────────────

    /**
     * Returns an array of PermissionDefinition objects this module needs.
     * These are upserted by ModuleRegistry::syncPermissions().
     */
    public function permissions(): array;

    // ── Database ──────────────────────────────────────────────────────────────

    /**
     * PostgreSQL schema name for this module's tables.
     * Null = module uses an existing schema or manages its own.
     */
    public function schemaName(): ?string;
}
