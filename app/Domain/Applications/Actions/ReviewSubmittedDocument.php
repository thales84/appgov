<?php

namespace App\Domain\Applications\Actions;

use App\Domain\Applications\Enums\DocumentStatus;
use App\Domain\Applications\Models\DocumentReview;
use App\Domain\Applications\Models\SubmittedDocument;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReviewSubmittedDocument
{
    public function execute(SubmittedDocument $document, User $reviewer, bool $isValid, ?string $notes = null): DocumentReview
    {
        return DB::transaction(function () use ($document, $reviewer, $isValid, $notes) {
            $newDocumentStatus = $isValid ? DocumentStatus::Valid : DocumentStatus::Invalid;

            $document->update([
                'status' => $newDocumentStatus,
                'quarantine_reason' => $isValid ? null : $notes,
            ]);

            $review = DocumentReview::create([
                'submitted_document_id' => $document->id,
                'reviewer_id' => $reviewer->id,
                'status' => $isValid ? 'valid' : 'invalid',
                'notes' => $notes,
                'reviewed_at' => now(),
            ]);

            activity('applications')
                ->causedBy($reviewer)
                ->performedOn($document)
                ->event('document.reviewed')
                ->withProperties([
                    'document_public_id' => $document->public_id,
                    'is_valid' => $isValid,
                    'notes' => $notes,
                ])
                ->log('Document review performed: '.($isValid ? 'Valid' : 'Invalid'));

            return $review;
        });
    }
}
