<?php

namespace App\Services\Tenant;

class PermissionTableConfigurator
{
    /**
     * @param  array<string, string>  $baseTableNames
     */
    public function __construct(
        protected array $baseTableNames = []
    ) {}

    public function applyForTenant(?int $tenantId): void
    {
        $tableNames = $this->baseTableNames;

        if ($tenantId !== null) {
            $tableNames = array_map(
                fn (string $name) => sprintf('%s_%s', $name, $tenantId),
                $this->baseTableNames
            );
        }

        config(['permission.table_names' => $tableNames]);
    }
}
