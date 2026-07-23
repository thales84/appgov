<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Catalog\Enums\ProcedureVersionStatus;
use App\Domain\Organizations\Models\Organization;
use App\Domain\Shared\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Service extends Model
{
    use HasPublicId;

    protected $fillable = [
        'organization_id',
        'service_category_id',
        'code',
        'name_fr',
        'name_en',
        'description_fr',
        'description_en',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function procedureVersions(): HasMany
    {
        return $this->hasMany(ProcedureVersion::class);
    }

    public function currentProcedureVersion(): HasOne
    {
        return $this->hasOne(ProcedureVersion::class)
            ->ofMany(['version_number' => 'max'], function (Builder $query): void {
                $query
                    ->whereIn('status', [
                        ProcedureVersionStatus::Published->value,
                        ProcedureVersionStatus::Retired->value,
                    ])
                    ->where('effective_from', '<=', now());
            })
            ->where('status', ProcedureVersionStatus::Published->value)
            ->where(function (Builder $query): void {
                $query->whereNull('effective_until')->orWhere('effective_until', '>', now());
            });
    }

    public function latestProcedureVersion(): HasOne
    {
        return $this->hasOne(ProcedureVersion::class)
            ->ofMany('version_number', 'max');
    }

    public function scopeWithinOrganizations(Builder $query, array $organizationIds): Builder
    {
        return $query->whereIn('organization_id', $organizationIds);
    }
}
