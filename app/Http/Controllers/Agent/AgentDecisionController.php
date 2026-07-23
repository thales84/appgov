<?php

namespace App\Http\Controllers\Agent;

use App\Domain\Applications\Models\Application;
use App\Domain\Decisions\Actions\RecordApplicationDecision;
use App\Domain\Decisions\Enums\DecisionType;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AgentDecisionController extends Controller
{
    public function store(
        Request $request,
        Application $application,
        RecordApplicationDecision $action,
    ): RedirectResponse {
        $agent = $request->user();
        $assignment = $agent->activeAssignment();

        abort_unless(
            $assignment && $application->procedureVersion->service->organization_id === $assignment->organization_id,
            403
        );

        $request->validate([
            'decision_type' => ['required', Rule::enum(DecisionType::class)],
            'reason_fr' => ['required', 'string', 'min:5', 'max:2000'],
            'reason_en' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        $decisionType = DecisionType::from($request->input('decision_type'));

        $action->execute(
            $application,
            $agent,
            $decisionType,
            $request->input('reason_fr'),
            $request->input('reason_en')
        );

        return back()->with('status', 'decision.recorded');
    }
}
