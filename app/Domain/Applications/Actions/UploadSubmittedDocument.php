<?php

namespace App\Domain\Applications\Actions;

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Applications\Enums\DocumentStatus;
use App\Domain\Applications\Models\Application;
use App\Domain\Applications\Models\SubmittedDocument;
use App\Domain\Catalog\Models\DocumentRequirement;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use LogicException;

class UploadSubmittedDocument
{
    public function execute(Application $application, DocumentRequirement $requirement, UploadedFile $file): SubmittedDocument
    {
        if (! in_array($application->status, [ApplicationStatus::Draft, ApplicationStatus::CorrectionRequested])) {
            throw new LogicException('Documents can only be uploaded when the application is in draft or correction mode.');
        }

        // Check file requirement compatibility
        if ($requirement->procedure_version_id !== $application->procedure_version_id) {
            throw new LogicException('Document requirement does not belong to this application procedure version.');
        }

        // Validate MIME type
        $allowedMimes = $requirement->allowed_mime_types ?? ['application/pdf', 'image/jpeg', 'image/png'];
        $clientMime = $file->getClientMimeType();
        $mime = $file->getMimeType() ?: $clientMime;

        if (! in_array($mime, $allowedMimes) && ! in_array($clientMime, $allowedMimes)) {
            throw ValidationException::withMessages([
                'file' => [__('The uploaded file type is not allowed for :requirement.', ['requirement' => $requirement->name_fr])],
            ]);
        }

        // Validate Max Size (KB)
        $maxKb = $requirement->max_file_size_kb ?? 10240; // Default 10 MB
        if (($file->getSize() / 1024) > $maxKb) {
            throw ValidationException::withMessages([
                'file' => [__('The uploaded file exceeds the maximum allowed size of :max KB.', ['max' => $maxKb])],
            ]);
        }

        // Calculate SHA-256 hash
        $fileHash = hash_file('sha256', $file->getRealPath());

        // Extension & Safe storage path
        $extension = strtolower($file->getClientOriginalExtension() ?: 'bin');
        $storagePath = "documents/{$application->public_id}/{$fileHash}.{$extension}";

        // Store file on private disk
        Storage::disk('private')->putFileAs(
            "documents/{$application->public_id}",
            $file,
            "{$fileHash}.{$extension}"
        );

        return DB::transaction(function () use ($application, $requirement, $file, $storagePath, $fileHash, $mime) {
            // Replaced old document for same requirement if present
            $existing = SubmittedDocument::query()
                ->where('application_id', $application->id)
                ->where('document_requirement_id', $requirement->id)
                ->whereIn('status', [DocumentStatus::Pending, DocumentStatus::Valid])
                ->first();

            if ($existing) {
                $existing->update(['status' => DocumentStatus::Replaced]);
            }

            $submittedDoc = SubmittedDocument::create([
                'application_id' => $application->id,
                'document_requirement_id' => $requirement->id,
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $storagePath,
                'disk' => 'private',
                'mime_type' => $mime,
                'file_size_bytes' => $file->getSize(),
                'file_hash' => $fileHash,
                'status' => DocumentStatus::Pending,
                'uploaded_at' => now(),
            ]);

            activity('applications')
                ->causedBy($application->citizen)
                ->performedOn($submittedDoc)
                ->event('document.uploaded')
                ->withProperties([
                    'application_public_id' => $application->public_id,
                    'requirement_public_id' => $requirement->public_id,
                    'file_hash' => $fileHash,
                ])
                ->log('Citizen uploaded a document for an application requirement.');

            return $submittedDoc;
        });
    }
}
