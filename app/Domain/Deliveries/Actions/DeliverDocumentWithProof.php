<?php

namespace App\Domain\Deliveries\Actions;

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Applications\Services\TransitionApplicationWorkflow;
use App\Domain\Deliveries\Enums\DeliveryStatus;
use App\Domain\Deliveries\Models\Delivery;
use App\Domain\Deliveries\Models\DeliveryProof;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeliverDocumentWithProof
{
    public function __construct(
        private readonly TransitionApplicationWorkflow $workflow
    ) {}

    public function execute(
        Delivery $delivery,
        User $agent,
        string $recipientName,
        string $identityDocumentNumber,
        ?string $notes = null
    ): DeliveryProof {
        return DB::transaction(function () use ($delivery, $agent, $recipientName, $identityDocumentNumber, $notes) {
            $delivery->update([
                'status' => DeliveryStatus::Delivered,
                'delivered_at' => now(),
            ]);

            $proof = DeliveryProof::create([
                'delivery_id' => $delivery->id,
                'recipient_name' => $recipientName,
                'identity_document_number' => $identityDocumentNumber,
                'agent_user_id' => $agent->id,
                'proof_type' => 'identity_verified',
                'notes' => $notes,
                'delivered_at' => now(),
            ]);

            $application = $delivery->application;

            // Transition to delivered
            $this->workflow->execute(
                $application,
                ApplicationStatus::Delivered,
                $agent,
                "Titre officiel remis en main propre à {$recipientName}.",
                "Official document delivered in person to {$recipientName}."
            );

            // Transition to closed
            $this->workflow->execute(
                $application,
                ApplicationStatus::Closed,
                $agent,
                'Dossier clos après remise effective du titre.',
                'Application closed following effective delivery.'
            );

            activity('deliveries')
                ->causedBy($agent)
                ->performedOn($proof)
                ->event('delivery.proof_recorded')
                ->withProperties([
                    'recipient_name' => $recipientName,
                    'identity_document_number' => $identityDocumentNumber,
                    'application_public_id' => $application->public_id,
                ])
                ->log("Document delivered to {$recipientName} and application closed.");

            return $proof;
        });
    }
}
