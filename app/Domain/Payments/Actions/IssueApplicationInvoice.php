<?php

namespace App\Domain\Payments\Actions;

use App\Domain\Applications\Models\Application;
use App\Domain\Payments\Enums\InvoiceStatus;
use App\Domain\Payments\Models\Invoice;
use App\Domain\Payments\Models\InvoiceLine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IssueApplicationInvoice
{
    public function execute(Application $application): Invoice
    {
        $application->loadMissing('procedureVersion.feeSchedules');
        $fees = $application->procedureVersion->feeSchedules;

        $existing = Invoice::query()->where('application_id', $application->id)->first();
        if ($existing) {
            return $existing;
        }

        $totalMinor = $fees->sum('amount_minor');
        $currency = $fees->first()?->currency ?? 'EUR';

        return DB::transaction(function () use ($application, $fees, $totalMinor, $currency) {
            $invoiceNumber = 'INV-'.date('Y').'-'.strtoupper(Str::random(8));

            $invoice = Invoice::create([
                'application_id' => $application->id,
                'invoice_number' => $invoiceNumber,
                'status' => $totalMinor === 0 ? InvoiceStatus::Paid : InvoiceStatus::Unpaid,
                'total_amount_minor' => $totalMinor,
                'currency' => $currency,
                'due_at' => now()->addDays(30),
                'paid_at' => $totalMinor === 0 ? now() : null,
            ]);

            foreach ($fees as $fee) {
                InvoiceLine::create([
                    'invoice_id' => $invoice->id,
                    'fee_schedule_id' => $fee->id,
                    'label_fr' => $fee->label_fr,
                    'label_en' => $fee->label_en,
                    'amount_minor' => $fee->amount_minor,
                    'currency' => $fee->currency,
                    'quantity' => 1,
                ]);
            }

            activity('payments')
                ->causedBy($application->citizen)
                ->performedOn($invoice)
                ->event('invoice.issued')
                ->withProperties([
                    'invoice_number' => $invoiceNumber,
                    'total_amount_minor' => $totalMinor,
                    'currency' => $currency,
                ])
                ->log('Application invoice issued.');

            return $invoice->load('lines');
        });
    }
}
