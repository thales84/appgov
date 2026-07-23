<?php

namespace App\Domain\Applications\Actions;

use App\Domain\Applications\Models\Application;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class GenerateSubmissionReceiptPdf
{
    public function execute(Application $application): string
    {
        $application->loadMissing([
            'procedureVersion.service.organization',
            'citizen',
            'submittedDocuments.requirement',
        ]);

        $pdf = Pdf::loadView('pdf.receipt', [
            'application' => $application,
        ]);

        $storagePath = "documents/{$application->public_id}/receipt_{$application->reference}.pdf";

        Storage::disk('private')->put(
            $storagePath,
            $pdf->output()
        );

        return $storagePath;
    }
}
