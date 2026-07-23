<?php

namespace App\Providers;

use App\Domain\Applications\Models\Application;
use App\Domain\Applications\Policies\ApplicationPolicy;
use App\Domain\Catalog\Models\ProcedureVersion;
use App\Domain\Catalog\Models\Service;
use App\Domain\Catalog\Models\ServiceCategory;
use App\Domain\Catalog\Policies\ProcedureVersionPolicy;
use App\Domain\Catalog\Policies\ServiceCategoryPolicy;
use App\Domain\Catalog\Policies\ServicePolicy;
use App\Domain\Identity\Listeners\RecordTwoFactorSecurityEvent;
use App\Domain\Identity\Listeners\UpdateIdentityLevelAfterEmailVerification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Events\RecoveryCodesGenerated;
use Laravel\Fortify\Events\TwoFactorAuthenticationConfirmed;
use Laravel\Fortify\Events\TwoFactorAuthenticationDisabled;
use Laravel\Fortify\Events\TwoFactorAuthenticationEnabled;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Service::class, ServicePolicy::class);
        Gate::policy(ServiceCategory::class, ServiceCategoryPolicy::class);
        Gate::policy(ProcedureVersion::class, ProcedureVersionPolicy::class);
        Gate::policy(Application::class, ApplicationPolicy::class);

        Event::listen(Verified::class, UpdateIdentityLevelAfterEmailVerification::class);
        Event::listen([
            TwoFactorAuthenticationEnabled::class,
            TwoFactorAuthenticationConfirmed::class,
            TwoFactorAuthenticationDisabled::class,
            RecoveryCodesGenerated::class,
        ], RecordTwoFactorSecurityEvent::class);
    }
}
