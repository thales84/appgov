<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AccessControlSeeder extends Seeder
{
    /**
     * @var array<string, list<string>>
     */
    private const ROLES = [
        'agent_reception' => ['platform.access.agent', 'applications.receive'],
        'agent_caseworker' => ['platform.access.agent', 'applications.review'],
        'agent_examiner' => ['platform.access.agent', 'exams.record'],
        'agent_finance' => ['platform.access.agent', 'payments.review'],
        'agent_production' => ['platform.access.agent', 'titles.produce'],
        'agent_delivery' => ['platform.access.agent', 'titles.deliver'],
        'agent_supervisor' => [
            'platform.access.agent',
            'applications.receive',
            'applications.review',
            'reports.view',
        ],
        'catalog_administrator' => [
            'platform.access.agent',
            'catalog.manage',
            'catalog.view',
            'catalog.edit',
            'catalog.submit_review',
        ],
        'auditor' => ['platform.access.agent', 'audit.view', 'reports.view'],
        'platform_administrator' => [
            'platform.access.agent',
            'platform.manage',
            'catalog.manage',
            'catalog.view',
            'catalog.edit',
            'catalog.submit_review',
            'catalog.publish',
            'catalog.retire',
            'catalog.categories.manage',
            'reports.view',
            'audit.view',
        ],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        collect(self::ROLES)
            ->flatten()
            ->unique()
            ->each(fn (string $permission) => Permission::findOrCreate($permission, 'web'));

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::ROLES as $roleName => $permissions) {
            Role::findOrCreate($roleName, 'web')->syncPermissions($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
