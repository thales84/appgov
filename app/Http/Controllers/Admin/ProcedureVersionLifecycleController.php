<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Catalog\Actions\PublishProcedureVersion;
use App\Domain\Catalog\Actions\RetireProcedureVersion;
use App\Domain\Catalog\Actions\ReturnProcedureVersionToDraft;
use App\Domain\Catalog\Actions\SubmitProcedureVersionForReview;
use App\Domain\Catalog\Models\ProcedureVersion;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Catalog\PublishProcedureVersionRequest;
use App\Http\Requests\Admin\Catalog\RetireProcedureVersionRequest;
use App\Http\Requests\Admin\Catalog\ReturnProcedureVersionToDraftRequest;
use App\Http\Requests\Admin\Catalog\SubmitProcedureVersionRequest;
use Illuminate\Http\RedirectResponse;

class ProcedureVersionLifecycleController extends Controller
{
    public function submit(
        SubmitProcedureVersionRequest $request,
        ProcedureVersion $version,
        SubmitProcedureVersionForReview $action,
    ): RedirectResponse {
        $action->execute($version, $request->user());

        return to_route('admin.catalog.versions.show', $version)
            ->with('status', 'catalog.admin.submitted');
    }

    public function returnToDraft(
        ReturnProcedureVersionToDraftRequest $request,
        ProcedureVersion $version,
        ReturnProcedureVersionToDraft $action,
    ): RedirectResponse {
        $action->execute($version, $request->user(), $request->validated('reason'));

        return to_route('admin.catalog.versions.show', $version)
            ->with('status', 'catalog.admin.returned');
    }

    public function publish(
        PublishProcedureVersionRequest $request,
        ProcedureVersion $version,
        PublishProcedureVersion $action,
    ): RedirectResponse {
        $action->execute($version, $request->user());

        return to_route('admin.catalog.versions.show', $version)
            ->with('status', 'catalog.admin.published');
    }

    public function retire(
        RetireProcedureVersionRequest $request,
        ProcedureVersion $version,
        RetireProcedureVersion $action,
    ): RedirectResponse {
        $action->execute($version, $request->user(), $request->validated('reason'));

        return to_route('admin.catalog.versions.show', $version)
            ->with('status', 'catalog.admin.retired');
    }
}
