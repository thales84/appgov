<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Analytics\Actions\LogAuditExport;
use App\Domain\Analytics\Services\GenerateOperationalReports;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminReportController extends Controller
{
    public function index(Request $request, GenerateOperationalReports $service): Response
    {
        $agent = $request->user();
        $assignment = $agent->activeAssignment();

        $reports = $service->execute($assignment?->organization_id);

        return Inertia::render('Admin/Reports/Index', [
            'reports' => $reports,
        ]);
    }

    public function export(
        Request $request,
        GenerateOperationalReports $service,
        LogAuditExport $logExport,
    ): StreamedResponse {
        $agent = $request->user();
        $assignment = $agent->activeAssignment();

        $reports = $service->execute($assignment?->organization_id);

        $logExport->execute($agent, 'operational_kpis', $request->all(), $assignment?->organization_id);

        return response()->streamDownload(function () use ($reports) {
            echo json_encode($reports, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }, 'Rapport_Operationnel_AppGov.json', ['Content-Type' => 'application/json']);
    }
}
