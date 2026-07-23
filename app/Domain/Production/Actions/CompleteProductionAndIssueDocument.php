<?php

namespace App\Domain\Production\Actions;

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Applications\Models\Application;
use App\Domain\Applications\Services\TransitionApplicationWorkflow;
use App\Domain\Deliveries\Enums\DeliveryStatus;
use App\Domain\Deliveries\Models\Delivery;
use App\Domain\Production\Enums\ProductionOrderStatus;
use App\Domain\Production\Models\IssuedDocument;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CompleteProductionAndIssueDocument
{
    public function __construct(
        private readonly TransitionApplicationWorkflow $workflow
    ) {}

    public function execute(Application $application, User $agent, ?string $qualityNotes = null): IssuedDocument
    {
        return DB::transaction(function () use ($application, $agent, $qualityNotes) {
            $order = $application->productionOrder ?? abort(400, 'No production order found for application.');

            $order->update([
                'status' => ProductionOrderStatus::QualityChecked,
                'quality_notes' => $qualityNotes,
            ]);

            $documentNumber = 'CM-DOC-'.date('Y').'-'.strtoupper(Str::random(8));

            $issuedDoc = IssuedDocument::create([
                'application_id' => $application->id,
                'document_number' => $documentNumber,
                'document_type' => $application->procedureVersion->title_fr,
                'issued_at' => now(),
                'expires_at' => now()->addYears(5),
            ]);

            Delivery::create([
                'application_id' => $application->id,
                'issued_document_id' => $issuedDoc->id,
                'status' => DeliveryStatus::Available,
                'dispatched_at' => now(),
            ]);

            $this->workflow->execute(
                $application,
                ApplicationStatus::Available,
                $agent,
                "Titre officiel n° {$documentNumber} confectionné et disponible au guichet.",
                "Official document #{$documentNumber} produced and available at counter."
            );

            activity('production')
                ->causedBy($agent)
                ->performedOn($issuedDoc)
                ->event('document.issued')
                ->withProperties([
                    'document_number' => $documentNumber,
                    'application_public_id' => $application->public_id,
                ])
                ->log("Official document #{$documentNumber} issued and available.");

            return $issuedDoc;
        });
    }
}
