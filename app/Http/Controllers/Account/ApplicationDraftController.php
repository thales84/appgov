<?php

namespace App\Http\Controllers\Account;

use App\Domain\Applications\Actions\UpdateApplicationDraft;
use App\Domain\Applications\Models\Application;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ApplicationDraftController extends Controller
{
    public function update(
        Request $request,
        Application $application,
        UpdateApplicationDraft $action,
    ): RedirectResponse {
        Gate::authorize('update', $application);

        $responses = $request->input('responses', []);
        $participant = $request->input('participant');

        $action->execute($application, $responses, $participant);

        return back()->with('status', 'application.draft_saved');
    }
}
