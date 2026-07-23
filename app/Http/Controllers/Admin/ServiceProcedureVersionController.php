<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Catalog\Actions\CreateProcedureVersion;
use App\Domain\Catalog\Models\Service;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Catalog\CreateProcedureVersionRequest;
use Illuminate\Http\RedirectResponse;

class ServiceProcedureVersionController extends Controller
{
    public function store(
        CreateProcedureVersionRequest $request,
        Service $service,
        CreateProcedureVersion $action,
    ): RedirectResponse {
        $version = $action->execute($service, $request->user());

        return to_route('admin.catalog.versions.show', $version)
            ->with('status', 'catalog.admin.versionCreated');
    }
}
