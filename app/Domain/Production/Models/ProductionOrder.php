<?php

namespace App\Domain\Production\Models;

use App\Domain\Applications\Models\Application;
use App\Domain\Production\Enums\ProductionOrderStatus;
use App\Domain\Shared\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionOrder extends Model
{
    use HasPublicId;

    protected $fillable = [
        'application_id',
        'production_batch_id',
        'status',
        'quality_notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProductionOrderStatus::class,
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductionBatch::class, 'production_batch_id');
    }
}
