<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Catalog\Actions\UpdateProcedureVersion;
use App\Domain\Catalog\Data\CatalogViewData;
use App\Domain\Catalog\Models\ProcedureVersion;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Catalog\UpdateProcedureVersionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ProcedureVersionController extends Controller
{
    public function show(ProcedureVersion $version): Response
    {
        Gate::authorize('view', $version);

        $version->load([
            'service.category',
            'service.organization',
            'service.procedureVersions',
            'steps',
            'formFields.step',
            'documentRequirements.step',
            'rules',
            'feeSchedules.step',
        ]);

        return Inertia::render('Admin/Catalog/VersionEditor', [
            'version' => CatalogViewData::versionEditor($version, request()->user()),
            'options' => [
                'stepTypes' => ['form', 'review', 'payment', 'appointment', 'examination', 'decision', 'production', 'delivery'],
                'fieldTypes' => ['text', 'textarea', 'select', 'radio', 'checkbox', 'date', 'number', 'email', 'tel'],
                'ruleTypes' => ['eligibility', 'validation', 'transition'],
            ],
        ]);
    }

    public function update(
        UpdateProcedureVersionRequest $request,
        ProcedureVersion $version,
        UpdateProcedureVersion $action,
    ): RedirectResponse {
        $action->execute($version, $request->user(), $request->validated());

        return to_route('admin.catalog.versions.show', $version)
            ->with('status', 'catalog.admin.saved');
    }
}
