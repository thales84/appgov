<?php

namespace App\Domain\Applications\Models;

use App\Domain\Applications\Enums\DocumentStatus;
use App\Domain\Catalog\Models\DocumentRequirement;
use App\Domain\Shared\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmittedDocument extends Model
{
    use HasPublicId;

    protected $fillable = [
        'application_id',
        'document_requirement_id',
        'original_filename',
        'file_path',
        'disk',
        'mime_type',
        'file_size_bytes',
        'file_hash',
        'status',
        'quarantine_reason',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => DocumentStatus::class,
            'uploaded_at' => 'datetime',
            'file_size_bytes' => 'integer',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(DocumentRequirement::class, 'document_requirement_id');
    }
}
