<?php

use App\Http\Controllers\Account\ApplicationAppointmentController;
use App\Http\Controllers\Account\ApplicationController;
use App\Http\Controllers\Account\ApplicationDocumentController;
use App\Http\Controllers\Account\ApplicationDraftController;
use App\Http\Controllers\Account\ApplicationInvoiceController;
use App\Http\Controllers\Account\ApplicationPaymentController;
use App\Http\Controllers\Account\ApplicationSubmissionController;
use App\Http\Controllers\Account\ApplicationTrackingController;
use App\Http\Controllers\Account\CitizenDashboardController;
use App\Http\Controllers\Account\CitizenProfileController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\CatalogController;
use App\Http\Controllers\Admin\ProcedureVersionController;
use App\Http\Controllers\Admin\ProcedureVersionLifecycleController;
use App\Http\Controllers\Admin\ServiceProcedureVersionController;
use App\Http\Controllers\Agent\AgentApplicationController;
use App\Http\Controllers\Agent\AgentCorrectionController;
use App\Http\Controllers\Agent\AgentDashboardController;
use App\Http\Controllers\Agent\AgentDecisionController;
use App\Http\Controllers\Agent\AgentDeliveryController;
use App\Http\Controllers\Agent\AgentDocumentReviewController;
use App\Http\Controllers\Agent\AgentExaminationController;
use App\Http\Controllers\Agent\AgentMessageController;
use App\Http\Controllers\Agent\AgentProductionController;
use App\Http\Controllers\Agent\AgentSecurityController;
use App\Http\Controllers\PublicCatalogController;
use App\Http\Controllers\PublicPaymentCallbackController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

Route::get('/', function () {
    return Inertia::render('Public/Home');
})->name('home');

Route::get('/services', [PublicCatalogController::class, 'index'])->name('services.index');
Route::get('/services/{service}', [PublicCatalogController::class, 'show'])->name('services.show');

// Public Payment Callback Routes
Route::match(['get', 'post'], '/account/payments/callback/{provider}', [PublicPaymentCallbackController::class, 'handle'])
    ->name('account.payments.callback.handle');

Route::middleware('guest')->group(function (): void {
    Route::get('/agent/login', fn () => Inertia::render('Auth/Login', [
        'portal' => 'agent',
    ]))->name('agent.login');

    Route::post('/agent/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:login')
        ->name('agent.login.store');
});

Route::prefix('account')
    ->name('account.')
    ->middleware(['auth', 'account.type:citizen'])
    ->group(function (): void {
        Route::get('/profile', [CitizenProfileController::class, 'show'])->name('profile.show');
        Route::put('/profile', [CitizenProfileController::class, 'update'])->name('profile.update');

        Route::middleware('verified')->group(function (): void {
            Route::get('/', CitizenDashboardController::class)->name('dashboard');
            Route::get('/services/{service}/start', [ApplicationController::class, 'start'])->name('services.start');
            Route::post('/services/{service}/applications', [ApplicationController::class, 'store'])->name('services.applications.store');
            Route::get('/applications/{application}', [ApplicationController::class, 'show'])->name('applications.show');
            Route::put('/applications/{application}', [ApplicationDraftController::class, 'update'])->name('applications.update');
            Route::post('/applications/{application}/submit', [ApplicationSubmissionController::class, 'store'])->name('applications.submit');
            Route::post('/applications/{application}/requirements/{requirement}/documents', [ApplicationDocumentController::class, 'store'])->name('applications.documents.store');
            Route::get('/applications/{application}/documents/{document}/download', [ApplicationDocumentController::class, 'download'])->name('applications.documents.download');

            // Phase 5 Payment Routes
            Route::get('/applications/{application}/invoice', [ApplicationInvoiceController::class, 'show'])->name('applications.invoice.show');
            Route::post('/applications/{application}/payments/initiate', [ApplicationPaymentController::class, 'initiate'])->name('applications.payments.initiate');
            Route::get('/applications/{application}/payments/{payment}/receipt', [ApplicationPaymentController::class, 'downloadReceipt'])->name('applications.payments.receipt.download');

            // Phase 6 Appointment Routes
            Route::get('/applications/{application}/appointment', [ApplicationAppointmentController::class, 'show'])->name('applications.appointment.show');
            Route::post('/applications/{application}/appointment', [ApplicationAppointmentController::class, 'store'])->name('applications.appointment.store');

            // Phase 7 Tracking Routes
            Route::get('/applications/{application}/tracking', [ApplicationTrackingController::class, 'show'])->name('applications.tracking.show');
        });
    });

Route::prefix('agent')
    ->name('agent.')
    ->middleware([
        'auth',
        'verified',
        'account.type:agent',
        'permission:platform.access.agent',
        'agent.assignment',
    ])
    ->group(function (): void {
        Route::get('/security', AgentSecurityController::class)->name('security');

        Route::middleware('agent.2fa')->group(function (): void {
            Route::get('/', AgentDashboardController::class)->name('dashboard');

            Route::get('/applications', [AgentApplicationController::class, 'index'])->name('applications.index');
            Route::get('/applications/{application}', [AgentApplicationController::class, 'show'])->name('applications.show');
            Route::post('/applications/{application}/start-review', [AgentApplicationController::class, 'startReview'])->name('applications.start-review');
            Route::post('/applications/{application}/documents/{document}/review', [AgentDocumentReviewController::class, 'store'])->name('applications.documents.review');
            Route::post('/applications/{application}/correction', [AgentCorrectionController::class, 'store'])->name('applications.correction.store');
            Route::post('/applications/{application}/decisions', [AgentDecisionController::class, 'store'])->name('applications.decisions.store');
            Route::post('/applications/{application}/messages', [AgentMessageController::class, 'store'])->name('applications.messages.store');

            // Phase 6 Examination Routes
            Route::get('/examinations', [AgentExaminationController::class, 'index'])->name('examinations.index');
            Route::post('/applications/{application}/sessions/{session}/attempts', [AgentExaminationController::class, 'recordResult'])->name('examinations.attempts.store');
            Route::post('/examinations/attempts/{attempt}/correct', [AgentExaminationController::class, 'correctResult'])->name('examinations.attempts.correct');

            // Phase 7 Production & Delivery Routes
            Route::get('/production', [AgentProductionController::class, 'index'])->name('production.index');
            Route::post('/applications/{application}/production/start', [AgentProductionController::class, 'startProduction'])->name('applications.production.start');
            Route::post('/applications/{application}/production/complete', [AgentProductionController::class, 'completeProduction'])->name('applications.production.complete');

            Route::get('/deliveries', [AgentDeliveryController::class, 'index'])->name('deliveries.index');
            Route::post('/deliveries/{delivery}/deliver', [AgentDeliveryController::class, 'deliver'])->name('deliveries.deliver');
        });
    });

Route::prefix('admin')
    ->name('admin.')
    ->middleware([
        'auth',
        'verified',
        'account.type:agent',
        'permission:platform.manage',
        'agent.assignment',
        'agent.2fa',
    ])
    ->group(function (): void {
        Route::get('/', AdminDashboardController::class)->name('dashboard');
    });

Route::prefix('admin/catalog')
    ->name('admin.catalog.')
    ->middleware([
        'auth',
        'verified',
        'account.type:agent',
        'permission:catalog.view',
        'agent.assignment',
        'agent.2fa',
    ])
    ->group(function (): void {
        Route::get('/', [CatalogController::class, 'index'])->name('index');
        Route::post('/categories', [CatalogController::class, 'storeCategory'])->name('categories.store');
        Route::post('/services', [CatalogController::class, 'store'])->name('services.store');
        Route::post('/services/{service}/versions', [ServiceProcedureVersionController::class, 'store'])->name('services.versions.store');
        Route::get('/versions/{version}', [ProcedureVersionController::class, 'show'])->name('versions.show');
        Route::put('/versions/{version}', [ProcedureVersionController::class, 'update'])->name('versions.update');
        Route::post('/versions/{version}/submit-review', [ProcedureVersionLifecycleController::class, 'submit'])->name('versions.submit-review');
        Route::post('/versions/{version}/return-draft', [ProcedureVersionLifecycleController::class, 'returnToDraft'])->name('versions.return-draft');
        Route::post('/versions/{version}/publish', [ProcedureVersionLifecycleController::class, 'publish'])->name('versions.publish');
        Route::post('/versions/{version}/retire', [ProcedureVersionLifecycleController::class, 'retire'])->name('versions.retire');
    });
