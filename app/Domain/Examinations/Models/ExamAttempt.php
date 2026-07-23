<?php

namespace App\Domain\Examinations\Models;

use App\Domain\Applications\Models\Application;
use App\Domain\Examinations\Enums\ExamResult;
use App\Domain\Shared\Concerns\HasPublicId;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamAttempt extends Model
{
    use HasPublicId;

    protected $fillable = [
        'application_id',
        'exam_session_id',
        'examiner_id',
        'attempt_number',
        'score',
        'result',
        'notes',
        'previous_result',
        'recorded_at',
        'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'attempt_number' => 'integer',
            'score' => 'integer',
            'result' => ExamResult::class,
            'recorded_at' => 'datetime',
            'validated_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ExamSession::class, 'exam_session_id');
    }

    public function examiner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'examiner_id');
    }
}
