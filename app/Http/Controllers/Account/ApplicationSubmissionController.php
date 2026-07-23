<?php

namespace App\Http\Controllers\Account;

use App\Domain\Applications\Actions\SubmitApplication;
use App\Domain\Applications\Models\Application;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ApplicationSubmissionController extends Controller
{
    public function store(
        Application $application,
        SubmitApplication $action,
    ): RedirectResponse {
        Gate::authorize('submit', $application);

        $action->execute($application);

        return to_route('account.applications.show', $application)
            ->with('status', 'application.submitted');
    }
}
