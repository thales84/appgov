<?php

namespace App\Http\Controllers\Account;

use App\Domain\Applications\Actions\StartApplication;
use App\Domain\Applications\Data\ApplicationViewData;
use App\Domain\Applications\Models\Application;
use App\Domain\Catalog\Data\CatalogViewData;
use App\Domain\Catalog\Models\Service;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\StartApplicationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ApplicationController extends Controller
{
    public function start(Service $service): Response
    {
        $service->load([
            'category',
            'organization',
            'currentProcedureVersion.steps',
            'currentProcedureVersion.formFields.step',
            'currentProcedureVersion.documentRequirements.step',
            'currentProcedureVersion.rules',
            'currentProcedureVersion.feeSchedules.step',
        ]);

        abort_unless(
            $service->is_active
            && $service->organization->is_active
            && $service->currentProcedureVersion,
            404
        );

        return Inertia::render('Account/Applications/Start', [
            'service' => CatalogViewData::publicServiceDetail($service),
        ]);
    }

    public function store(
        StartApplicationRequest $request,
        Service $service,
        StartApplication $action,
    ): RedirectResponse {
        $application = $action->execute($request->user(), $service);

        return to_route('account.applications.show', $application)
            ->with('status', 'application.started');
    }

    public function show(Application $application): Response
    {
        Gate::authorize('view', $application);

        $application->load([
            'procedureVersion.service.organization',
            'procedureVersion.steps',
            'procedureVersion.formFields.step',
            'procedureVersion.documentRequirements',
            'procedureVersion.feeSchedules',
            'participants',
            'submittedDocuments.requirement',
            'events',
        ]);

        return Inertia::render('Account/Applications/Show', [
            'application' => ApplicationViewData::fromApplication($application),
        ]);
    }
}
