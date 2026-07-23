<?php

namespace App\Domain\Analytics\Services;

use App\Domain\Applications\Models\Application;
use App\Domain\Payments\Models\Payment;
use App\Domain\Production\Models\IssuedDocument;
use Illuminate\Support\Facades\DB;

class GenerateOperationalReports
{
    public function execute(?int $organizationId = null): array
    {
        $query = Application::query();

        if ($organizationId) {
            $query->whereHas('procedureVersion.service', function ($q) use ($organizationId) {
                $q->where('organization_id', $organizationId);
            });
        }

        $totalApplications = (clone $query)->count();

        $statusCounts = (clone $query)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();

        $totalRevenueMinor = Payment::query()
            ->when($organizationId, function ($q) use ($organizationId) {
                $q->whereHas('invoice.application.procedureVersion.service', function ($sq) use ($organizationId) {
                    $sq->where('organization_id', $organizationId);
                });
            })
            ->sum('amount_minor');

        $issuedDocumentsCount = IssuedDocument::query()
            ->when($organizationId, function ($q) use ($organizationId) {
                $q->whereHas('application.procedureVersion.service', function ($sq) use ($organizationId) {
                    $sq->where('organization_id', $organizationId);
                });
            })
            ->count();

        return [
            'totalApplications' => $totalApplications,
            'statusCounts' => $statusCounts,
            'totalRevenueMinor' => (int) $totalRevenueMinor,
            'currency' => 'EUR',
            'issuedDocumentsCount' => $issuedDocumentsCount,
            'generatedAt' => now()->toIso8601String(),
        ];
    }
}
