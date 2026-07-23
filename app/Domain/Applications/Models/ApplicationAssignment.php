<?php

namespace App\Domain\Applications\Models;

use App\Domain\Applications\Enums\AssignmentStatus;
use App\Domain\Organizations\Models\AdministrativeUnit;
use App\Domain\Organizations\Models\Organization;
use App\Domain\Organizations\Models\Territory;
use App\Domain\Shared\Concerns\HasPublicId;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationAssignment extends Model
{
    use HasPublicId;

    protected $fillable = [
        'application_id',
        'organization_id',
        'administrative_unit_id',
        'territory_id',
        'assigned_by_user_id',
        'assigned_to_user_id',
        'status',
        'assigned_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => AssignmentStatus::class,
            'assigned_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
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

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }
}
