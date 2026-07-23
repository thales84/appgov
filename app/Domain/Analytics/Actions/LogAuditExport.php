<?php

namespace App\Domain\Analytics\Actions;

use App\Domain\Analytics\Models\AuditExport;
use App\Models\User;

class LogAuditExport
{
    public function execute(User $user, string $exportType, ?array $filters = null, ?int $organizationId = null): AuditExport
    {
        $export = AuditExport::create([
            'user_id' => $user->id,
            'organization_id' => $organizationId,
            'export_type' => $exportType,
            'filters' => $filters,
            'exported_at' => now(),
        ]);

        activity('analytics')
            ->causedBy($user)
            ->performedOn($export)
            ->event('analytics.export_logged')
            ->withProperties([
                'export_type' => $exportType,
                'filters' => $filters,
            ])
            ->log("Data export '{$exportType}' performed by user #{$user->id}.");

        return $export;
    }
}
