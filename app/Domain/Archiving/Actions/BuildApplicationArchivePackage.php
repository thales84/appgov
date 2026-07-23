<?php

namespace App\Domain\Archiving\Actions;

use App\Domain\Applications\Models\Application;
use App\Domain\Archiving\Models\ArchivePackage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BuildApplicationArchivePackage
{
    public function execute(Application $application, ?User $archivedBy = null): ArchivePackage
    {
        $application->loadMissing([
            'citizen',
            'submittedDocuments',
            'events',
            'decisions',
            'invoice.lines',
            'invoice.payment',
            'issuedDocument',
            'delivery.proof',
            'examAttempts',
        ]);

        return DB::transaction(function () use ($application, $archivedBy) {
            $manifest = [
                'application' => [
                    'public_id' => $application->public_id,
                    'reference' => $application->reference,
                    'status' => $application->status->value,
                    'citizen_name' => $application->citizen->name,
                    'citizen_email' => $application->citizen->email,
                    'started_at' => $application->started_at?->toIso8601String(),
                    'submitted_at' => $application->submitted_at?->toIso8601String(),
                    'form_responses' => $application->form_responses,
                ],
                'documents' => $application->submittedDocuments->map(fn ($doc) => [
                    'public_id' => $doc->public_id,
                    'original_filename' => $doc->original_filename,
                    'file_hash_sha256' => $doc->file_hash_sha256,
                    'mime_type' => $doc->mime_type,
                    'file_size_bytes' => $doc->file_size_bytes,
                    'status' => $doc->status->value,
                ])->all(),
                'decisions' => $application->decisions->map(fn ($dec) => [
                    'public_id' => $dec->public_id,
                    'type' => $dec->decision_type->value,
                    'reason_fr' => $dec->reason_fr,
                    'reason_en' => $dec->reason_en,
                    'decided_at' => $dec->decided_at->toIso8601String(),
                ])->all(),
                'invoice' => $application->invoice ? [
                    'invoice_number' => $application->invoice->invoice_number,
                    'status' => $application->invoice->status->value,
                    'total_amount_minor' => $application->invoice->total_amount_minor,
                    'currency' => $application->invoice->currency,
                    'payment' => $application->invoice->payment ? [
                        'payment_reference' => $application->invoice->payment->payment_reference,
                        'reconciled_at' => $application->invoice->payment->reconciled_at->toIso8601String(),
                    ] : null,
                ] : null,
                'issued_document' => $application->issuedDocument ? [
                    'document_number' => $application->issuedDocument->document_number,
                    'document_type' => $application->issuedDocument->document_type,
                    'issued_at' => $application->issuedDocument->issued_at->toIso8601String(),
                ] : null,
                'delivery_proof' => $application->delivery?->proof ? [
                    'recipient_name' => $application->delivery->proof->recipient_name,
                    'identity_document_number' => $application->delivery->proof->identity_document_number,
                    'delivered_at' => $application->delivery->proof->delivered_at->toIso8601String(),
                ] : null,
                'events' => $application->events->map(fn ($evt) => [
                    'event_type' => $evt->event_type,
                    'label_fr' => $evt->label_fr,
                    'created_at' => $evt->created_at->toIso8601String(),
                ])->all(),
            ];

            $jsonContent = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $packageHash = hash('sha256', $jsonContent);

            $storagePath = "archives/{$application->public_id}.json";
            Storage::disk('private')->put($storagePath, $jsonContent);

            $package = ArchivePackage::updateOrCreate(
                ['application_id' => $application->id],
                [
                    'package_hash' => $packageHash,
                    'storage_path' => $storagePath,
                    'manifest' => $manifest,
                    'sealed_at' => now(),
                    'archived_by_user_id' => $archivedBy?->id,
                ]
            );

            activity('archiving')
                ->causedBy($archivedBy)
                ->performedOn($package)
                ->event('archive.package_sealed')
                ->withProperties([
                    'package_hash' => $packageHash,
                    'application_public_id' => $application->public_id,
                ])
                ->log("Archive package sealed with SHA-256 checksum {$packageHash}.");

            return $package;
        });
    }
}
