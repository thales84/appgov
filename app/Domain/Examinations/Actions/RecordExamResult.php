<?php

namespace App\Domain\Examinations\Actions;

use App\Domain\Applications\Models\Application;
use App\Domain\Examinations\Enums\ExamResult;
use App\Domain\Examinations\Models\ExamAttempt;
use App\Domain\Examinations\Models\ExamSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RecordExamResult
{
    public function execute(
        Application $application,
        ExamSession $session,
        User $examiner,
        ExamResult $result,
        ?int $score = null,
        ?string $notes = null
    ): ExamAttempt {
        return DB::transaction(function () use ($application, $session, $examiner, $result, $score, $notes) {
            $nextAttemptNumber = ($application->examAttempts()->max('attempt_number') ?? 0) + 1;

            $attempt = ExamAttempt::create([
                'application_id' => $application->id,
                'exam_session_id' => $session->id,
                'examiner_id' => $examiner->id,
                'attempt_number' => $nextAttemptNumber,
                'score' => $score,
                'result' => $result,
                'notes' => $notes,
                'recorded_at' => now(),
            ]);

            activity('examinations')
                ->causedBy($examiner)
                ->performedOn($attempt)
                ->event('exam.result_recorded')
                ->withProperties([
                    'application_public_id' => $application->public_id,
                    'result' => $result->value,
                    'score' => $score,
                    'attempt_number' => $nextAttemptNumber,
                ])
                ->log("Exam attempt #{$nextAttemptNumber} result recorded: {$result->value}.");

            return $attempt;
        });
    }
}
