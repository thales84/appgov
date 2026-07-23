<?php

namespace App\Domain\Appointments\Models;

use App\Domain\Organizations\Models\AdministrativeUnit;
use App\Domain\Organizations\Models\Organization;
use App\Domain\Organizations\Models\Territory;
use App\Domain\Shared\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasPublicId;

    protected $fillable = [
        'organization_id',
        'administrative_unit_id',
        'territory_id',
        'code',
        'name_fr',
        'name_en',
        'address',
        'daily_capacity',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'daily_capacity' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function administrativeUnit(): BelongsTo
    {
        return $this->belongsTo(AdministrativeUnit::class);
    }

    public function territory(): BelongsTo
    {
        return $this->belongsTo(Territory::class);
    }

    public function slots(): HasMany
    {
        return $this->hasMany(AppointmentSlot::class);
    }
}
