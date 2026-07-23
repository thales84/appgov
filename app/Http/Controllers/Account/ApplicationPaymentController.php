<?php

namespace App\Http\Controllers\Account;

use App\Domain\Applications\Models\Application;
use App\Domain\Payments\Actions\InitiatePaymentTransaction;
use App\Domain\Payments\Enums\PaymentProvider;
use App\Domain\Payments\Models\Payment;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicationPaymentController extends Controller
{
    public function initiate(
        Request $request,
        Application $application,
        InitiatePaymentTransaction $action,
    ): RedirectResponse {
        Gate::authorize('view', $application);

        $invoice = $application->invoice ?? abort(404, 'Invoice not found.');

        $request->validate([
            'provider' => ['required', 'string'],
        ]);

        $provider = PaymentProvider::from($request->input('provider'));

        $result = $action->execute($invoice, $provider);

        return redirect()->away($result['checkout_url']);
    }

    public function downloadReceipt(
        Application $application,
        Payment $payment,
    ): StreamedResponse {
        Gate::authorize('view', $application);

        abort_unless($payment->invoice_id === $application->invoice?->id, 404);

        $path = "documents/{$application->public_id}/payment_receipt_{$payment->payment_reference}.pdf";
        abort_unless(Storage::disk('private')->exists($path), 404);

        return Storage::disk('private')->download(
            $path,
            "Quittance_{$payment->payment_reference}.pdf",
            ['Content-Type' => 'application/pdf']
        );
    }
}
