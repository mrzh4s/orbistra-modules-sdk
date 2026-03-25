# Orbistra Module SDK

The official SDK for building external modules for the [Orbistra](https://orbistra.io) platform. This package provides the contracts and base classes that modules must implement to integrate with the platform's module registry, menu system, permissions, and UI renderer.

## Requirements

- PHP 8.4+
- Laravel 13+
- Orbistra platform 1.0.0+

## Installation

```bash
composer require orbistra/sdk
```

## Overview

An Orbistra module is a self-contained feature package that integrates into the platform. Modules can:

- Register admin and app sidebar menus
- Declare permissions synced into the platform's permission system
- Add settings pages to the admin panel
- Bring their own database migrations and routes
- Render UI pages using the platform's component library (no React required)
- Bind ports (interfaces) to adapters following Hexagonal Architecture

---

## Creating a Module

### 1. Module Definition

Create a class extending `AbstractModuleDefinition`. Only `slug()` and `name()` are required — everything else has sensible defaults.

```php
<?php

namespace Acme\CrmModule;

use Orbistra\SDK\AbstractModuleDefinition;
use Orbistra\SDK\PermissionDefinition;

class CrmModule extends AbstractModuleDefinition
{
    public function slug(): string { return 'crm'; }
    public function name(): string { return 'CRM'; }
    public function description(): string { return 'Contact and lead management.'; }
    public function icon(): string { return 'IconUsers'; }
    public function version(): string { return '1.0.0'; }
    public function order(): int { return 60; }

    // Author & marketplace metadata
    public function author(): string { return 'Acme Corp'; }
    public function authorUrl(): string { return 'https://acme.example'; }
    public function homepage(): string { return 'https://acme.example/orbistra-crm'; }
    public function composerPackage(): string { return 'acme/crm-module'; }

    // Settings page
    public function hasSettings(): bool { return true; }
    public function settingsRoute(): ?string { return 'crm.settings'; }
    public function settingsLabel(): string { return 'CRM'; }
    public function settingsIcon(): string { return 'IconUsers'; }

    // Admin sidebar menu
    public function adminMenuItems(): array
    {
        return [
            [
                'label'      => 'CRM',
                'icon'       => 'IconUsers',
                'route'      => null,
                'permission' => 'crm_contacts.view',
                'order'      => 60,
                'children'   => [
                    ['label' => 'Contacts', 'icon' => 'IconAddressBook', 'route' => 'crm.contacts.index', 'permission' => 'crm_contacts.view'],
                    ['label' => 'Leads',    'icon' => 'IconTarget',      'route' => 'crm.leads.index',    'permission' => 'crm_leads.view'],
                ],
            ],
        ];
    }

    // Permissions
    public function permissions(): array
    {
        return [
            PermissionDefinition::make('crm_contacts.view',   'View Contacts',   'Can view the contacts list', 'crm'),
            PermissionDefinition::make('crm_contacts.manage', 'Manage Contacts', 'Can create, edit and delete contacts', 'crm'),
            PermissionDefinition::make('crm_leads.view',      'View Leads',      'Can view the leads list', 'crm'),
            PermissionDefinition::make('crm_leads.manage',    'Manage Leads',    'Can create, edit and delete leads', 'crm'),
        ];
    }

    // Optional: custom PostgreSQL schema for this module's tables
    public function schemaName(): ?string { return 'crm'; }
}
```

### 2. Service Provider

Create a class extending `ModuleServiceProvider`. Only `moduleClass()` is required.

```php
<?php

namespace Acme\CrmModule;

use Orbistra\SDK\ModuleServiceProvider;

class CrmServiceProvider extends ModuleServiceProvider
{
    protected function moduleClass(): string
    {
        return CrmModule::class;
    }

    protected function routeFiles(): array
    {
        return [__DIR__.'/../routes/crm.php'];
    }

    protected function migrationPaths(): array
    {
        return [__DIR__.'/../database/migrations'];
    }

    // Hexagonal Architecture: bind ports (interfaces) to adapters (implementations)
    protected function portBindings(): array
    {
        return [
            ContactRepositoryInterface::class => EloquentContactRepository::class,
        ];
    }
}
```

### 3. Package Configuration

In your `composer.json`, declare the service provider for Laravel's package auto-discovery:

```json
{
    "name": "acme/crm-module",
    "type": "library",
    "require": {
        "orbistra/sdk": "^1.0.0"
    },
    "autoload": {
        "psr-4": {
            "Acme\\CrmModule\\": "src/"
        }
    },
    "extra": {
        "laravel": {
            "providers": ["Acme\\CrmModule\\CrmServiceProvider"]
        }
    }
}
```

### 4. Install in the Platform

```bash
composer require acme/crm-module
```

Laravel's package auto-discovery loads the service provider automatically. On boot, the module is registered with the platform's `ModuleRegistry`, migrations are loaded, and routes are registered. The module then appears in **Admin → Modules** where it can be toggled on or off.

---

## Rendering Pages

External modules can return JSON-schema driven pages rather than shipping React components. The platform renders these using its own Shadcn UI + Tabler icons components.

### PageSchema

Use `PageSchema` factory methods to describe your page layout:

```php
use Orbistra\SDK\PageSchema;

// Index / list page
$schema = PageSchema::index(
    title: 'Contacts',
    columns: [
        ['key' => 'name',  'label' => 'Name',  'type' => 'text'],
        ['key' => 'email', 'label' => 'Email', 'type' => 'email'],
        ['key' => 'phone', 'label' => 'Phone', 'type' => 'phone'],
    ],
    actions: [
        ['label' => 'Edit',   'icon' => 'IconEdit',  'route' => 'crm.contacts.edit'],
        ['label' => 'Delete', 'icon' => 'IconTrash', 'route' => 'crm.contacts.destroy'],
    ],
    filters: [
        ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['active', 'inactive']],
    ],
    stats: [
        ['label' => 'Total Contacts', 'value' => 1240, 'icon' => 'IconUsers'],
    ],
);

// Show / detail page
$schema = PageSchema::show(
    title: 'Contact Details',
    tabs: [...],
    actions: [...],
);

// Create form
$schema = PageSchema::create(
    title: 'New Contact',
    fields: [
        ['key' => 'name',  'label' => 'Full Name', 'type' => 'text',  'required' => true],
        ['key' => 'email', 'label' => 'Email',     'type' => 'email', 'required' => true],
    ],
    actions: [...],
);

// Edit form
$schema = PageSchema::edit('Edit Contact', $fields, $actions);
```

### ModulePageResponse

Use `ModulePageResponse::render()` in your controller to return the page as an Inertia response:

```php
use Orbistra\SDK\ModulePageResponse;
use Orbistra\SDK\PageSchema;

class ContactIndexEndpoint
{
    public function __invoke(): \Inertia\Response
    {
        return ModulePageResponse::render(
            schema: PageSchema::index('Contacts', $this->columns(), $this->actions()),
            data: Contact::paginate(20),
            module: 'crm',
        );
    }
}
```

The platform's `ExternalModule/Page` React component receives `schema`, `data`, and `module` as props and handles all rendering.

---

## API Reference

### `ModuleDefinitionContract`

The full interface all modules must satisfy.

| Method | Returns | Required | Description |
|--------|---------|----------|-------------|
| `slug()` | `string` | Yes | Unique identifier, e.g. `crm` |
| `name()` | `string` | Yes | Display name |
| `description()` | `string` | No | Short description |
| `icon()` | `string` | No | Tabler icon name, e.g. `IconUsers` |
| `version()` | `string` | No | Semver string, default `1.0.0` |
| `order()` | `int` | No | Sort order in UI, default `100` |
| `isCore()` | `bool` | No | Core modules cannot be disabled |
| `author()` | `string` | No | Vendor name |
| `authorUrl()` | `string` | No | Vendor URL |
| `homepage()` | `string` | No | Module homepage |
| `composerPackage()` | `string` | No | Composer package name |
| `hasSettings()` | `bool` | No | Whether module has a settings page |
| `settingsRoute()` | `?string` | No | Named route to the settings page |
| `settingsLabel()` | `string` | No | Label in settings sidebar |
| `settingsIcon()` | `string` | No | Icon in settings sidebar |
| `adminMenuItems()` | `array` | No | Admin sidebar menu items |
| `appMenuItems()` | `array` | No | App sidebar menu items |
| `permissions()` | `array` | No | Array of `PermissionDefinition` objects |
| `schemaName()` | `?string` | No | PostgreSQL schema for this module's tables |

### `AbstractModuleDefinition`

Implements `ModuleDefinitionContract` with the defaults above. Extend this for your module definition. Only `slug()` and `name()` are abstract.

### `ModuleServiceProvider`

Base `ServiceProvider` for external modules.

| Method | Returns | Required | Description |
|--------|---------|----------|-------------|
| `moduleClass()` | `string` | Yes | FQN of the module definition class |
| `routeFiles()` | `array` | No | Absolute paths to route files |
| `migrationPaths()` | `array` | No | Absolute paths to migration directories |
| `portBindings()` | `array` | No | `[InterfaceFQN => ConcreteFQN]` bindings |

### `PermissionDefinition`

Readonly DTO declaring a permission.

```php
PermissionDefinition::make(
    name: 'crm_contacts.view',
    displayName: 'View Contacts',
    description: 'Can view the contacts list',  // optional
    module: 'crm',                               // optional
);
```

### `PageSchema`

| Factory | Page Type | Required Args | Optional Args |
|---------|-----------|---------------|---------------|
| `PageSchema::index()` | Table / list | `title`, `columns` | `actions`, `filters`, `stats` |
| `PageSchema::show()` | Detail view | `title` | `tabs`, `actions` |
| `PageSchema::create()` | Create form | `title`, `fields` | `actions` |
| `PageSchema::edit()` | Edit form | `title`, `fields` | `actions` |

### `ModulePageResponse`

```php
ModulePageResponse::render(
    schema: PageSchema,   // page layout definition
    data: mixed,          // paginated collection or plain array
    module: string,       // module slug for breadcrumb context
    extra: array,         // additional Inertia props (optional)
): \Inertia\Response
```

---

## Menu Item Shape

Each item in `adminMenuItems()` / `appMenuItems()` follows this structure:

```php
[
    'label'      => 'My Section',       // required — display text
    'icon'       => 'IconBox',          // Tabler icon name
    'route'      => 'my.route.name',    // named route, null for group headers
    'permission' => 'my_thing.view',    // permission gate (optional)
    'order'      => 50,                 // sort order
    'children'   => [                   // nested items (optional)
        ['label' => '...', 'icon' => '...', 'route' => '...', 'permission' => '...'],
    ],
]
```

---

## Namespace

All SDK classes live under `Orbistra\SDK`:

```
Orbistra\SDK\ModuleDefinitionContract
Orbistra\SDK\AbstractModuleDefinition
Orbistra\SDK\ModuleServiceProvider
Orbistra\SDK\PermissionDefinition
Orbistra\SDK\PageSchema
Orbistra\SDK\ModulePageResponse
```

---

## License

MIT
