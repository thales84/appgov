<?php

namespace App\Domain\Production\Models;

use App\Domain\Organizations\Models\Organization;
use App\Domain\Shared\Concerns\HasPublicId;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionBatch extends Model
{
    use HasPublicId;

    protected $fillable = [
        'organization_id',
        'batch_number',
        'status',
        'created_by_user_id',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(ProductionOrder::class);
    }
}
