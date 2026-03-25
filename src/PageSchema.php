<?php

namespace Orbistra\ModuleSdk;

/**
 * Defines the JSON-driven UI schema for a module page.
 * Rendered by the host app's ExternalModule/Page.jsx via ModulePageRenderer.
 *
 * Instead of shipping React JSX, vendors return a PageSchema from their
 * endpoint and call ModulePageResponse::render() — the host renders it
 * using its own existing shadcn/ui + Tabler icons components.
 */
final class PageSchema
{
    public function __construct(
        /** 'index' | 'show' | 'create' | 'edit' | 'custom' */
        public readonly string $type,
        public readonly string $title,
        public readonly string $description = '',

        /** Column definitions for 'index' pages */
        public readonly array $columns = [],

        /** Field definitions for 'create' / 'edit' pages */
        public readonly array $fields = [],

        /** Row-level and page-level action buttons */
        public readonly array $actions = [],

        /** Sidebar/top filter definitions for 'index' pages */
        public readonly array $filters = [],

        /** Tabs for 'show' pages */
        public readonly array $tabs = [],

        /** Stats/metric cards shown above a table */
        public readonly array $stats = [],
    ) {}

    // ── Factory methods ───────────────────────────────────────────────────────

    public static function index(string $title, array $columns, array $actions = [], array $filters = [], array $stats = []): self
    {
        return new self(type: 'index', title: $title, columns: $columns, actions: $actions, filters: $filters, stats: $stats);
    }

    public static function show(string $title, array $tabs = [], array $actions = []): self
    {
        return new self(type: 'show', title: $title, tabs: $tabs, actions: $actions);
    }

    public static function create(string $title, array $fields, array $actions = []): self
    {
        return new self(type: 'create', title: $title, fields: $fields, actions: $actions);
    }

    public static function edit(string $title, array $fields, array $actions = []): self
    {
        return new self(type: 'edit', title: $title, fields: $fields, actions: $actions);
    }

    // ── Serialise ─────────────────────────────────────────────────────────────

    public function toArray(): array
    {
        return array_filter([
            'type'        => $this->type,
            'title'       => $this->title,
            'description' => $this->description ?: null,
            'columns'     => $this->columns ?: null,
            'fields'      => $this->fields ?: null,
            'actions'     => $this->actions ?: null,
            'filters'     => $this->filters ?: null,
            'tabs'        => $this->tabs ?: null,
            'stats'       => $this->stats ?: null,
        ], fn($v) => $v !== null);
    }
}
