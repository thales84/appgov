<?php

namespace App\Domain\Applications\Models;

use App\Domain\Applications\Enums\ParticipantType;
use App\Domain\Shared\Concerns\HasPublicId;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationParticipant extends Model
{
    use HasPublicId;

    protected $fillable = [
        'application_id',
        'participant_type',
        'user_id',
        'identity_data',
    ];

    protected function casts(): array
    {
        return [
            'participant_type' => ParticipantType::class,
            'identity_data' => 'array',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
