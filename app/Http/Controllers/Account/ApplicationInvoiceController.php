<?php

namespace App\Http\Controllers\Account;

use App\Domain\Applications\Models\Application;
use App\Domain\Payments\Actions\IssueApplicationInvoice;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ApplicationInvoiceController extends Controller
{
    public function show(Application $application, IssueApplicationInvoice $issueInvoice): Response
    {
        Gate::authorize('view', $application);

        $invoice = $issueInvoice->execute($application);
        $invoice->load(['lines', 'payment', 'transactions']);

        return Inertia::render('Account/Applications/InvoiceShow', [
            'application' => [
                'publicId' => $application->public_id,
                'reference' => $application->reference,
                'status' => $application->status->value,
                'procedureTitle' => ['fr' => $application->procedureVersion->title_fr, 'en' => $application->procedureVersion->title_en],
            ],
            'invoice' => [
                'publicId' => $invoice->public_id,
                'invoiceNumber' => $invoice->invoice_number,
                'status' => $invoice->status->value,
                'statusLabel' => $invoice->status->label(app()->getLocale()),
                'totalAmountMinor' => $invoice->total_amount_minor,
                'currency' => $invoice->currency,
                'dueAt' => $invoice->due_at?->toIso8601String(),
                'paidAt' => $invoice->paid_at?->toIso8601String(),
                'lines' => $invoice->lines->map(fn ($line) => [
                    'publicId' => $line->public_id,
                    'label' => ['fr' => $line->label_fr, 'en' => $line->label_en],
                    'amountMinor' => $line->amount_minor,
                    'currency' => $line->currency,
                    'quantity' => $line->quantity,
                ]),
                'payment' => $invoice->payment ? [
                    'publicId' => $invoice->payment->public_id,
                    'paymentReference' => $invoice->payment->payment_reference,
                    'amountMinor' => $invoice->payment->amount_minor,
                    'currency' => $invoice->payment->currency,
                    'reconciledAt' => $invoice->payment->reconciled_at->toIso8601String(),
                ] : null,
            ],
        ]);
    }
}
