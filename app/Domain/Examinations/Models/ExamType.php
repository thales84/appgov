<?php

namespace App\Domain\Examinations\Models;

use App\Domain\Shared\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamType extends Model
{
    use HasPublicId;

    protected $fillable = [
        'code',
        'name_fr',
        'name_en',
        'passing_score',
    ];

    protected function casts(): array
    {
        return [
            'passing_score' => 'integer',
        ];
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ExamSession::class);
    }
}
