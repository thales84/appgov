<?php

namespace App\Http\Controllers\Agent;

use App\Domain\Applications\Data\ApplicationViewData;
use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Applications\Models\Application;
use App\Domain\Applications\Services\TransitionApplicationWorkflow;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AgentApplicationController extends Controller
{
    public function index(Request $request): Response
    {
        $agent = $request->user();
        $assignment = $agent->activeAssignment();

        abort_unless($assignment, 403, 'No active organizational assignment.');

        $query = Application::query()
            ->with(['procedureVersion.service', 'citizen'])
            ->whereHas('procedureVersion.service', function ($q) use ($assignment) {
                $q->where('organization_id', $assignment->organization_id);
            });

        if ($assignment->administrative_unit_id) {
            $query->where(function ($q) use ($assignment) {
                $q->where('assigned_unit_id', $assignment->administrative_unit_id)
                    ->orWhereNull('assigned_unit_id');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        } else {
            $query->where('status', '!=', ApplicationStatus::Draft);
        }

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhereHas('citizen', fn ($u) => $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }

        $applications = $query->orderBy('updated_at', 'desc')->paginate(15)->withQueryString();

        return Inertia::render('Agent/Applications/Index', [
            'applications' => $applications->through(fn ($app) => [
                'publicId' => $app->public_id,
                'reference' => $app->reference,
                'status' => $app->status->value,
                'statusLabel' => $app->status->label(app()->getLocale()),
                'citizenName' => $app->citizen->name,
                'procedureTitle' => ['fr' => $app->procedureVersion->title_fr, 'en' => $app->procedureVersion->title_en],
                'submittedAt' => $app->submitted_at?->toIso8601String(),
                'updatedAt' => $app->updated_at->toIso8601String(),
            ]),
            'filters' => $request->only(['status', 'q']),
        ]);
    }

    public function show(Request $request, Application $application): Response
    {
        $agent = $request->user();
        $assignment = $agent->activeAssignment();

        abort_unless(
            $assignment && $application->procedureVersion->service->organization_id === $assignment->organization_id,
            403
        );

        $application->load([
            'procedureVersion.service.organization',
            'procedureVersion.steps',
            'procedureVersion.formFields.step',
            'procedureVersion.documentRequirements',
            'procedureVersion.feeSchedules',
            'participants',
            'submittedDocuments.requirement',
            'events',
            'messages.sender',
            'decisions.decisionMaker',
        ]);

        return Inertia::render('Agent/Applications/Show', [
            'application' => array_merge(ApplicationViewData::fromApplication($application), [
                'messages' => $application->messages->map(fn ($m) => [
                    'publicId' => $m->public_id,
                    'senderName' => $m->sender->name,
                    'isAgent' => $m->sender->isAgent(),
                    'isInternal' => $m->is_internal,
                    'message' => $m->message,
                    'createdAt' => $m->created_at->toIso8601String(),
                ]),
                'decisions' => $application->decisions->map(fn ($d) => [
                    'publicId' => $d->public_id,
                    'decisionMakerName' => $d->decisionMaker->name,
                    'decisionType' => $d->decision_type->value,
                    'decisionTypeLabel' => $d->decision_type->label(app()->getLocale()),
                    'reason' => ['fr' => $d->reason_fr, 'en' => $d->reason_en],
                    'decidedAt' => $d->decided_at->toIso8601String(),
                ]),
            ]),
        ]);
    }

    public function startReview(
        Request $request,
        Application $application,
        TransitionApplicationWorkflow $transitionAction,
    ): RedirectResponse {
        $agent = $request->user();
        $assignment = $agent->activeAssignment();

        abort_unless(
            $assignment && $application->procedureVersion->service->organization_id === $assignment->organization_id,
            403
        );

        $transitionAction->execute(
            $application,
            ApplicationStatus::UnderReview,
            $agent,
            'Instruction démarrée par l\'agent',
            'Caseworker review started'
        );

        return back()->with('status', 'application.review_started');
    }
}
