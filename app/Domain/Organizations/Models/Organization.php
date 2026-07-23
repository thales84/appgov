<?php

namespace App\Domain\Organizations\Models;

use App\Domain\Appointments\Models\Location;
use App\Domain\Organizations\Enums\OrganizationType;
use App\Domain\Shared\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    use HasPublicId;

    protected $fillable = [
        'code',
        'type',
        'name_fr',
        'name_en',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => OrganizationType::class,
            'is_active' => 'boolean',
        ];
    }

    public function units(): HasMany
    {
        return $this->hasMany(AdministrativeUnit::class);
    }

    public function agentAssignments(): HasMany
    {
        return $this->hasMany(AgentAssignment::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }
}
