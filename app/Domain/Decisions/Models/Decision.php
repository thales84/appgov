<?php

namespace App\Domain\Decisions\Models;

use App\Domain\Applications\Models\Application;
use App\Domain\Decisions\Enums\DecisionType;
use App\Domain\Shared\Concerns\HasPublicId;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Decision extends Model
{
    use HasPublicId;

    protected $fillable = [
        'application_id',
        'decision_maker_id',
        'decision_type',
        'reason_fr',
        'reason_en',
        'decided_at',
    ];

    protected function casts(): array
    {
        return [
            'decision_type' => DecisionType::class,
            'decided_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function decisionMaker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decision_maker_id');
    }
}
