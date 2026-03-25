<?php

namespace Orbistra\ModuleSdk;

use Inertia\Inertia;
use Inertia\Response;

/**
 * Utility for external module endpoints to return Inertia responses
 * without needing to know the host's Inertia component name.
 *
 * Usage in a vendor endpoint:
 *   public function __invoke(Request $request): Response
 *   {
 *       return ModulePageResponse::render(
 *           schema: PageSchema::index('CRM Contacts', $this->columns()),
 *           data: Contact::paginate(20),
 *           module: 'crm',
 *       );
 *   }
 */
final class ModulePageResponse
{
    /**
     * The Inertia component registered in the host app that renders external module pages.
     */
    private const HOST_COMPONENT = 'ExternalModule/Page';

    /**
     * Render a JSON-schema driven module page.
     *
     * @param PageSchema $schema  The page layout / field / column definitions
     * @param mixed      $data    Paginated collection or plain array (passed as 'data' prop)
     * @param string     $module  The module slug (for breadcrumb / back-link context)
     * @param array      $extra   Any additional props to merge into the page
     */
    public static function render(
        PageSchema $schema,
        mixed $data = null,
        string $module = '',
        array $extra = [],
    ): Response {
        return Inertia::render(self::HOST_COMPONENT, array_merge([
            'schema' => $schema->toArray(),
            'data'   => $data,
            'module' => $module,
        ], $extra));
    }
}
