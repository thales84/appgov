<?php

namespace App\Domain\Identity\Models;

use App\Domain\Identity\Enums\IdentityLevel;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CitizenProfile extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'preferred_locale',
        'identity_level',
    ];

    protected function casts(): array
    {
        return [
            'identity_level' => IdentityLevel::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
