<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Catalog\Actions\CreateServiceCategory;
use App\Domain\Catalog\Actions\CreateServiceWithDraft;
use App\Domain\Catalog\Data\CatalogViewData;
use App\Domain\Catalog\Models\Service;
use App\Domain\Catalog\Models\ServiceCategory;
use App\Domain\Organizations\Models\Organization;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Catalog\StoreServiceCategoryRequest;
use App\Http\Requests\Admin\Catalog\StoreServiceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Service::class);
        $actor = request()->user();
        $organizationIds = $actor->activeOrganizationIds();

        $services = Service::query()
            ->withinOrganizations($organizationIds)
            ->with(['category', 'organization', 'latestProcedureVersion'])
            ->orderBy('name_fr')
            ->paginate(15)
            ->through(fn (Service $service) => CatalogViewData::adminServiceSummary($service));

        $categories = ServiceCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (ServiceCategory $category) => [
                'publicId' => $category->public_id,
                'name' => ['fr' => $category->name_fr, 'en' => $category->name_en],
                'colorKey' => $category->color_key->value,
            ]);

        $organizations = Organization::query()
            ->whereIn('id', $organizationIds)
            ->where('is_active', true)
            ->orderBy('name_fr')
            ->get()
            ->map(fn (Organization $organization) => [
                'publicId' => $organization->public_id,
                'name' => ['fr' => $organization->name_fr, 'en' => $organization->name_en],
            ]);

        return Inertia::render('Admin/Catalog/Index', [
            'services' => $services,
            'categories' => $categories,
            'organizations' => $organizations,
            'canCreate' => $actor->can('create', Service::class),
            'canCreateCategory' => $actor->can('create', ServiceCategory::class),
        ]);
    }

    public function store(StoreServiceRequest $request, CreateServiceWithDraft $action): RedirectResponse
    {
        $version = $action->execute($request->user(), $request->validated());

        return to_route('admin.catalog.versions.show', $version)
            ->with('status', 'catalog.admin.created');
    }

    public function storeCategory(
        StoreServiceCategoryRequest $request,
        CreateServiceCategory $action,
    ): RedirectResponse {
        $action->execute($request->user(), $request->validated());

        return to_route('admin.catalog.index')
            ->with('status', 'catalog.admin.categoryCreated');
    }
}
