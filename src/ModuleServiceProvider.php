<?php

namespace Orbistra\SDK;

use Illuminate\Support\ServiceProvider;

/**
 * Base ServiceProvider for external Orbistra modules.
 *
 * Vendors extend this and implement moduleClass(). Everything else is optional.
 *
 * Usage in vendor's composer.json:
 *   "extra": { "laravel": { "providers": ["Vendor\\MyModule\\MyServiceProvider"] } }
 *
 * Example:
 *   class CrmServiceProvider extends ModuleServiceProvider
 *   {
 *       protected function moduleClass(): string { return CrmModule::class; }
 *
 *       protected function routeFiles(): array {
 *           return [__DIR__.'/../routes/crm.php'];
 *       }
 *
 *       protected function migrationPaths(): array {
 *           return [__DIR__.'/../database/migrations'];
 *       }
 *   }
 */
abstract class ModuleServiceProvider extends ServiceProvider
{
    /**
     * The module definition class to register.
     * Must be a class implementing ModuleDefinitionContract.
     */
    abstract protected function moduleClass(): string;

    /**
     * Absolute paths to route files to load.
     */
    protected function routeFiles(): array { return []; }

    /**
     * Absolute paths to migration directories to load.
     */
    protected function migrationPaths(): array { return []; }

    /**
     * Interface → implementation bindings (Ports & Adapters).
     * Bound in register() so they are available before boot().
     *
     * @return array<class-string, class-string>
     */
    protected function portBindings(): array { return []; }

    public function register(): void
    {
        foreach ($this->portBindings() as $abstract => $concrete) {
            $this->app->bind($abstract, $concrete);
        }
    }

    public function boot(): void
    {
        // Load migrations via standard Laravel package API
        foreach ($this->migrationPaths() as $path) {
            $this->loadMigrationsFrom($path);
        }

        // Register module with ModuleRegistry.
        // callAfterResolving handles boot order: if the registry singleton already
        // exists (AppServiceProvider ran first), the callback fires immediately.
        // If not, it is queued and fires when the singleton is first resolved.
        $this->callAfterResolving(
            \App\Core\Modules\ModuleRegistry::class,
            function ($registry) {
                $moduleClass = $this->moduleClass();
                $registry->register(new $moduleClass());
            }
        );

        // Load routes after application is fully bootstrapped
        $this->booted(function () {
            foreach ($this->routeFiles() as $file) {
                require $file;
            }
        });
    }
}
