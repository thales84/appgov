<?php

namespace App\Http\Controllers\Agent;

use App\Domain\Deliveries\Actions\DeliverDocumentWithProof;
use App\Domain\Deliveries\Models\Delivery;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AgentDeliveryController extends Controller
{
    public function index(Request $request): Response
    {
        $agent = $request->user();
        $assignment = $agent->activeAssignment();

        abort_unless($assignment, 403, 'No active organizational assignment.');

        $deliveries = Delivery::query()
            ->with(['application.citizen', 'issuedDocument', 'proof'])
            ->whereHas('application.procedureVersion.service', function ($q) use ($assignment) {
                $q->where('organization_id', $assignment->organization_id);
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(15);

        return Inertia::render('Agent/Deliveries/Index', [
            'deliveries' => $deliveries->through(fn ($del) => [
                'publicId' => $del->public_id,
                'applicationReference' => $del->application->reference,
                'citizenName' => $del->application->citizen->name,
                'documentNumber' => $del->issuedDocument->document_number,
                'documentType' => $del->issuedDocument->document_type,
                'status' => $del->status->value,
                'statusLabel' => $del->status->label(app()->getLocale()),
                'proof' => $del->proof ? [
                    'recipientName' => $del->proof->recipient_name,
                    'identityDocumentNumber' => $del->proof->identity_document_number,
                    'deliveredAt' => $del->proof->delivered_at->toIso8601String(),
                ] : null,
            ]),
        ]);
    }

    public function deliver(
        Request $request,
        Delivery $delivery,
        DeliverDocumentWithProof $action,
    ): RedirectResponse {
        $agent = $request->user();
        $assignment = $agent->activeAssignment();

        abort_unless(
            $assignment && $delivery->application->procedureVersion->service->organization_id === $assignment->organization_id,
            403
        );

        $request->validate([
            'recipient_name' => ['required', 'string', 'max:255'],
            'identity_document_number' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $action->execute(
            $delivery,
            $agent,
            $request->input('recipient_name'),
            $request->input('identity_document_number'),
            $request->input('notes')
        );

        return back()->with('status', 'delivery.completed');
    }
}
