<?php

namespace App\Http\Controllers\Agent;

use App\Domain\Applications\Actions\ReviewSubmittedDocument;
use App\Domain\Applications\Models\Application;
use App\Domain\Applications\Models\SubmittedDocument;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AgentDocumentReviewController extends Controller
{
    public function store(
        Request $request,
        Application $application,
        SubmittedDocument $document,
        ReviewSubmittedDocument $action,
    ): RedirectResponse {
        $agent = $request->user();
        $assignment = $agent->activeAssignment();

        abort_unless(
            $assignment && $application->procedureVersion->service->organization_id === $assignment->organization_id,
            403
        );

        $request->validate([
            'is_valid' => ['required', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $action->execute(
            $document,
            $agent,
            (bool) $request->input('is_valid'),
            $request->input('notes')
        );

        return back()->with('status', 'document.reviewed');
    }
}
