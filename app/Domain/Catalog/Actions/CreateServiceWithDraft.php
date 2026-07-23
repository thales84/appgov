<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Enums\ProcedureVersionStatus;
use App\Domain\Catalog\Models\ProcedureVersion;
use App\Domain\Catalog\Models\Service;
use App\Domain\Catalog\Models\ServiceCategory;
use App\Domain\Organizations\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class CreateServiceWithDraft
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $actor, array $data): ProcedureVersion
    {
        $organization = $this->resolveOrganization($actor, $data['organization_public_id'] ?? null);
        $category = ServiceCategory::query()
            ->where('public_id', $data['category_public_id'])
            ->where('is_active', true)
            ->firstOrFail();

        return DB::transaction(function () use ($actor, $category, $data, $organization): ProcedureVersion {
            $service = Service::create([
                'organization_id' => $organization->id,
                'service_category_id' => $category->id,
                'code' => $data['code'],
                'name_fr' => $data['name_fr'],
                'name_en' => $data['name_en'],
                'description_fr' => $data['description_fr'],
                'description_en' => $data['description_en'],
                'is_active' => true,
            ]);

            $version = $service->procedureVersions()->create([
                'version_number' => 1,
                'status' => ProcedureVersionStatus::Draft,
                'title_fr' => $data['name_fr'],
                'title_en' => $data['name_en'],
                'summary_fr' => $data['description_fr'],
                'summary_en' => $data['description_en'],
                'created_by_user_id' => $actor->id,
            ]);

            activity('catalog')
                ->causedBy($actor)
                ->performedOn($service)
                ->event('catalog.service.created')
                ->withProperties([
                    'organization_public_id' => $organization->public_id,
                    'procedure_version_public_id' => $version->public_id,
                ])
                ->log('Catalog service and first draft created.');

            return $version;
        });
    }

    private function resolveOrganization(User $actor, ?string $publicId): Organization
    {
        $query = Organization::query()
            ->whereIn('id', $actor->activeOrganizationIds())
            ->where('is_active', true);

        if ($publicId) {
            $query->where('public_id', $publicId);
        }

        $organization = $query->orderBy('id')->first();

        if (! $organization) {
            throw new AuthorizationException;
        }

        return $organization;
    }
}
