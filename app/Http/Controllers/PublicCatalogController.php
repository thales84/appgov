<?php

namespace App\Http\Controllers;

use App\Domain\Catalog\Data\CatalogViewData;
use App\Domain\Catalog\Models\Service;
use App\Domain\Catalog\Models\ServiceCategory;
use App\Http\Requests\CatalogIndexRequest;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;

class PublicCatalogController extends Controller
{
    public function index(CatalogIndexRequest $request): Response
    {
        $filters = $request->validated();

        $services = Service::query()
            ->with(['category', 'organization', 'currentProcedureVersion'])
            ->where('is_active', true)
            ->whereHas('organization', fn (Builder $query) => $query->where('is_active', true))
            ->whereHas('currentProcedureVersion')
            ->when($filters['category'] ?? null, function (Builder $query, string $category): void {
                $query->whereHas('category', fn (Builder $query) => $query->where('public_id', $category));
            })
            ->when($filters['q'] ?? null, function (Builder $query, string $search): void {
                $escaped = addcslashes($search, '%_');
                $query->where(function (Builder $query) use ($escaped): void {
                    $query
                        ->where('name_fr', 'like', "%$escaped%")
                        ->orWhere('name_en', 'like', "%$escaped%")
                        ->orWhere('description_fr', 'like', "%$escaped%")
                        ->orWhere('description_en', 'like', "%$escaped%");
                });
            })
            ->orderBy('name_fr')
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Service $service) => CatalogViewData::publicServiceSummary($service));

        $categories = ServiceCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (ServiceCategory $category) => [
                'publicId' => $category->public_id,
                'code' => $category->code,
                'name' => ['fr' => $category->name_fr, 'en' => $category->name_en],
                'description' => ['fr' => $category->description_fr, 'en' => $category->description_en],
                'colorKey' => $category->color_key->value,
            ]);

        return Inertia::render('Public/Catalog/Index', [
            'services' => $services,
            'categories' => $categories,
            'filters' => [
                'q' => $filters['q'] ?? '',
                'category' => $filters['category'] ?? '',
            ],
        ]);
    }

    public function show(Service $service): Response
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

        return Inertia::render('Public/Catalog/Show', [
            'service' => CatalogViewData::publicServiceDetail($service),
        ]);
    }
}
