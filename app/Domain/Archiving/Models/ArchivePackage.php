<?php

namespace App\Domain\Archiving\Models;

use App\Domain\Applications\Models\Application;
use App\Domain\Shared\Concerns\HasPublicId;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArchivePackage extends Model
{
    use HasPublicId;

    protected $fillable = [
        'application_id',
        'package_hash',
        'storage_path',
        'manifest',
        'sealed_at',
        'archived_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'manifest' => 'array',
            'sealed_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by_user_id');
    }
}
