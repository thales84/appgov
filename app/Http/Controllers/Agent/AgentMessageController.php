<?php

namespace App\Http\Controllers\Agent;

use App\Domain\Applications\Models\Application;
use App\Domain\Applications\Models\ApplicationMessage;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AgentMessageController extends Controller
{
    public function store(Request $request, Application $application): RedirectResponse
    {
        $agent = $request->user();
        $assignment = $agent->activeAssignment();

        abort_unless(
            $assignment && $application->procedureVersion->service->organization_id === $assignment->organization_id,
            403
        );

        $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'is_internal' => ['required', 'boolean'],
        ]);

        ApplicationMessage::create([
            'application_id' => $application->id,
            'sender_id' => $agent->id,
            'is_internal' => (bool) $request->input('is_internal'),
            'message' => $request->input('message'),
        ]);

        return back()->with('status', 'message.sent');
    }
}
