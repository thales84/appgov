<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Catalog\Enums\ServiceColor;
use App\Domain\Shared\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceCategory extends Model
{
    use HasPublicId;

    protected $fillable = [
        'code',
        'name_fr',
        'name_en',
        'description_fr',
        'description_en',
        'color_key',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'color_key' => ServiceColor::class,
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }
}
