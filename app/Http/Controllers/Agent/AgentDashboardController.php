<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AgentDashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $assignment = $request->user()
            ->agentAssignments()
            ->active()
            ->with(['organization', 'administrativeUnit', 'territory'])
            ->firstOrFail();

        return Inertia::render('Agent/Dashboard', [
            'scope' => [
                'organization' => $assignment->organization->name_fr,
                'unit' => $assignment->administrativeUnit?->name_fr,
                'territory' => $assignment->territory?->name_fr,
            ],
        ]);
    }
}
