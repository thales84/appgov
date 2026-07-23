<?php

namespace App\Http\Controllers\Agent;

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Applications\Models\Application;
use App\Domain\Production\Actions\CompleteProductionAndIssueDocument;
use App\Domain\Production\Actions\CreateProductionOrder;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AgentProductionController extends Controller
{
    public function index(Request $request): Response
    {
        $agent = $request->user();
        $assignment = $agent->activeAssignment();

        abort_unless($assignment, 403, 'No active organizational assignment.');

        $applications = Application::query()
            ->with(['citizen', 'productionOrder', 'issuedDocument'])
            ->whereHas('procedureVersion.service', function ($q) use ($assignment) {
                $q->where('organization_id', $assignment->organization_id);
            })
            ->whereIn('status', [ApplicationStatus::Approved, ApplicationStatus::InProduction, ApplicationStatus::Available])
            ->orderBy('updated_at', 'desc')
            ->paginate(15);

        return Inertia::render('Agent/Production/Index', [
            'applications' => $applications->through(fn ($app) => [
                'publicId' => $app->public_id,
                'reference' => $app->reference,
                'status' => $app->status->value,
                'statusLabel' => $app->status->label(app()->getLocale()),
                'citizenName' => $app->citizen->name,
                'productionStatus' => $app->productionOrder?->status?->value,
                'documentNumber' => $app->issuedDocument?->document_number,
                'updatedAt' => $app->updated_at->toIso8601String(),
            ]),
        ]);
    }

    public function startProduction(
        Request $request,
        Application $application,
        CreateProductionOrder $action,
    ): RedirectResponse {
        $agent = $request->user();
        $assignment = $agent->activeAssignment();

        abort_unless(
            $assignment && $application->procedureVersion->service->organization_id === $assignment->organization_id,
            403
        );

        $action->execute($application, $agent);

        return back()->with('status', 'production.started');
    }

    public function completeProduction(
        Request $request,
        Application $application,
        CompleteProductionAndIssueDocument $action,
    ): RedirectResponse {
        $agent = $request->user();
        $assignment = $agent->activeAssignment();

        abort_unless(
            $assignment && $application->procedureVersion->service->organization_id === $assignment->organization_id,
            403
        );

        $request->validate([
            'quality_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $action->execute($application, $agent, $request->input('quality_notes'));

        return back()->with('status', 'production.completed');
    }
}
