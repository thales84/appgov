<?php

use App\Domain\Applications\Models\Application;
use App\Domain\Catalog\Models\Service;
use App\Domain\Payments\Actions\IssueApplicationInvoice;
use App\Domain\Payments\Actions\ProcessPaymentCallback;
use App\Domain\Payments\Enums\InvoiceStatus;
use App\Domain\Payments\Enums\PaymentProvider;
use App\Domain\Payments\Models\Payment;
use App\Models\User;
use Database\Seeders\CatalogDemoSeeder;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(CatalogDemoSeeder::class);
    Storage::fake('private');
});

it('issues an invoice for an application with snapshot fee lines in minor currency', function () {
    $citizen = User::factory()->create();
    $service = Service::where('code', 'DEMO-DRIVING-LICENCE')->firstOrFail();

    $this->actingAs($citizen)->post(route('account.services.applications.store', $service));
    $application = Application::firstOrFail();

    $this->actingAs($citizen)
        ->get(route('account.applications.invoice.show', $application))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Account/Applications/InvoiceShow')
            ->has('invoice.lines')
            ->where('invoice.currency', 'XAF')
        );

    $this->assertDatabaseHas('invoices', [
        'application_id' => $application->id,
        'currency' => 'XAF',
    ]);
});

it('initiates a payment transaction and redirects to provider checkout', function () {
    $citizen = User::factory()->create();
    $service = Service::where('code', 'DEMO-DRIVING-LICENCE')->firstOrFail();

    $this->actingAs($citizen)->post(route('account.services.applications.store', $service));
    $application = Application::firstOrFail();

    // Issue invoice
    $action = app(IssueApplicationInvoice::class);
    $invoice = $action->execute($application);

    $this->actingAs($citizen)
        ->post(route('account.applications.payments.initiate', $application), [
            'provider' => PaymentProvider::LocalMock->value,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('payment_transactions', [
        'invoice_id' => $invoice->id,
        'provider_name' => PaymentProvider::LocalMock->value,
    ]);
});

it('processes payment callback idempotently without double payment on repeated callbacks', function () {
    $citizen = User::factory()->create();
    $service = Service::where('code', 'DEMO-DRIVING-LICENCE')->firstOrFail();

    $this->actingAs($citizen)->post(route('account.services.applications.store', $service));
    $application = Application::firstOrFail();

    $invoiceAction = app(IssueApplicationInvoice::class);
    $invoice = $invoiceAction->execute($application);

    $callbackAction = app(ProcessPaymentCallback::class);
    $idempotencyKey = 'TEST-IDEMPOTENCY-KEY-123456';

    // Create initiated transaction
    $tx = $invoice->transactions()->create([
        'provider_name' => PaymentProvider::LocalMock,
        'provider_transaction_id' => 'MOCK-TX-999',
        'idempotency_key' => $idempotencyKey,
        'status' => 'initiated',
        'amount_minor' => $invoice->total_amount_minor,
        'currency' => 'XAF',
        'initiated_at' => now(),
    ]);

    // 1st Callback Execution
    $firstPayment = $callbackAction->execute($idempotencyKey, 'successful', ['tx' => 'MOCK-TX-999']);

    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Paid)
        ->and(Payment::count())->toBe(1)
        ->and($firstPayment->payment_reference)->toStartWith('PAY-');

    // PDF receipt generated
    Storage::disk('private')->assertExists("documents/{$application->public_id}/payment_receipt_{$firstPayment->payment_reference}.pdf");

    // 2nd Callback Execution with SAME idempotency key (Repeated network callback)
    $secondPayment = $callbackAction->execute($idempotencyKey, 'successful', ['tx' => 'MOCK-TX-999']);

    // Assert NO DUPLICATE payment record created
    expect(Payment::count())->toBe(1)
        ->and($secondPayment->id)->toBe($firstPayment->id);
});

it('allows authorized citizen to download PDF payment receipt and forbids unauthorized users', function () {
    $owner = User::factory()->create();
    $attacker = User::factory()->create();
    $service = Service::where('code', 'DEMO-DRIVING-LICENCE')->firstOrFail();

    $this->actingAs($owner)->post(route('account.services.applications.store', $service));
    $application = Application::firstOrFail();

    $invoice = app(IssueApplicationInvoice::class)->execute($application);

    $idempotencyKey = 'TEST-IDEMPOTENCY-DOWNLOAD-99';
    $invoice->transactions()->create([
        'provider_name' => PaymentProvider::LocalMock,
        'provider_transaction_id' => 'MOCK-TX-888',
        'idempotency_key' => $idempotencyKey,
        'status' => 'initiated',
        'amount_minor' => $invoice->total_amount_minor,
        'currency' => 'XAF',
        'initiated_at' => now(),
    ]);

    $payment = app(ProcessPaymentCallback::class)->execute($idempotencyKey, 'successful');

    // Attacker forbidden
    $this->actingAs($attacker)
        ->get(route('account.applications.payments.receipt.download', [$application, $payment]))
        ->assertForbidden();

    // Owner allowed
    $this->actingAs($owner)
        ->get(route('account.applications.payments.receipt.download', [$application, $payment]))
        ->assertOk();
});
