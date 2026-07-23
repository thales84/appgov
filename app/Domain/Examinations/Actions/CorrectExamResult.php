<?php

namespace App\Domain\Examinations\Actions;

use App\Domain\Examinations\Enums\ExamResult;
use App\Domain\Examinations\Models\ExamAttempt;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CorrectExamResult
{
    public function execute(
        ExamAttempt $attempt,
        User $supervisor,
        ExamResult $newResult,
        ?int $newScore,
        string $correctionReason
    ): ExamAttempt {
        return DB::transaction(function () use ($attempt, $supervisor, $newResult, $newScore, $correctionReason) {
            $previousResult = $attempt->result->value;

            $attempt->update([
                'previous_result' => $previousResult,
                'result' => $newResult,
                'score' => $newScore,
                'notes' => trim(($attempt->notes ? $attempt->notes."\n" : '')."[Correction par {$supervisor->name} le ".now()->toIso8601String()." : {$correctionReason}]"),
                'validated_at' => now(),
            ]);

            activity('examinations')
                ->causedBy($supervisor)
                ->performedOn($attempt)
                ->event('exam.result_corrected')
                ->withProperties([
                    'attempt_public_id' => $attempt->public_id,
                    'previous_result' => $previousResult,
                    'new_result' => $newResult->value,
                    'new_score' => $newScore,
                    'reason' => $correctionReason,
                ])
                ->log("Exam attempt result corrected from {$previousResult} to {$newResult->value}.");

            return $attempt->fresh();
        });
    }
}
