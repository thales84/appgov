<?php

namespace App\Domain\Examinations\Models;

use App\Domain\Appointments\Models\Location;
use App\Domain\Shared\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamSession extends Model
{
    use HasPublicId;

    protected $fillable = [
        'location_id',
        'exam_type_id',
        'session_date',
        'capacity',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
            'capacity' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function examType(): BelongsTo
    {
        return $this->belongsTo(ExamType::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }
}
