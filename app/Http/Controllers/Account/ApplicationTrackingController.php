<?php

namespace App\Http\Controllers\Account;

use App\Domain\Applications\Models\Application;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ApplicationTrackingController extends Controller
{
    public function show(Application $application): Response
    {
        Gate::authorize('view', $application);

        $application->load(['events.user', 'issuedDocument', 'delivery.proof']);

        return Inertia::render('Account/Applications/TrackingShow', [
            'application' => [
                'publicId' => $application->public_id,
                'reference' => $application->reference,
                'status' => $application->status->value,
                'statusLabel' => $application->status->label(app()->getLocale()),
                'procedureTitle' => ['fr' => $application->procedureVersion->title_fr, 'en' => $application->procedureVersion->title_en],
                'submittedAt' => $application->submitted_at?->toIso8601String(),
                'issuedDocument' => $application->issuedDocument ? [
                    'documentNumber' => $application->issuedDocument->document_number,
                    'documentType' => $application->issuedDocument->document_type,
                    'issuedAt' => $application->issuedDocument->issued_at->toIso8601String(),
                ] : null,
                'delivery' => $application->delivery ? [
                    'status' => $application->delivery->status->value,
                    'statusLabel' => $application->delivery->status->label(app()->getLocale()),
                    'dispatchedAt' => $application->delivery->dispatched_at?->toIso8601String(),
                    'deliveredAt' => $application->delivery->delivered_at?->toIso8601String(),
                ] : null,
                'events' => $application->events->map(fn ($evt) => [
                    'publicId' => $evt->public_id,
                    'eventType' => $evt->event_type,
                    'label' => ['fr' => $evt->label_fr, 'en' => $evt->label_en],
                    'createdAt' => $evt->created_at->toIso8601String(),
                ]),
            ],
        ]);
    }
}
