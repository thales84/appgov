<?php

namespace App\Http\Controllers\Agent;

use App\Domain\Applications\Actions\RequestApplicationCorrection;
use App\Domain\Applications\Models\Application;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AgentCorrectionController extends Controller
{
    public function store(
        Request $request,
        Application $application,
        RequestApplicationCorrection $action,
    ): RedirectResponse {
        $agent = $request->user();
        $assignment = $agent->activeAssignment();

        abort_unless(
            $assignment && $application->procedureVersion->service->organization_id === $assignment->organization_id,
            403
        );

        $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $action->execute($application, $agent, $request->input('reason'));

        return back()->with('status', 'application.correction_requested');
    }
}
