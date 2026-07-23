<?php

namespace App\Domain\Organizations\Models;

use App\Domain\Organizations\Enums\TerritoryType;
use App\Domain\Shared\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Territory extends Model
{
    use HasPublicId;

    protected $fillable = [
        'parent_id',
        'code',
        'type',
        'name_fr',
        'name_en',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => TerritoryType::class,
            'is_active' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
