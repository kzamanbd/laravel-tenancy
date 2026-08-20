<?php

namespace App\Providers;

use App\Jobs\DeliverStatusNotification;
use App\Services\Dns\DnsResolver;
use App\Services\Dns\SystemDnsResolver;
use App\Tenancy\TenantContext;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // One instance per request, because the Row-Level Security session
        // variables it manages live on the database connection.
        $this->app->singleton(TenantContext::class);

        // Swapped for a fake in tests so ownership rules can be exercised
        // without a network round trip.
        $this->app->bind(DnsResolver::class, SystemDnsResolver::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureNotificationThrottle();
    }

    /**
     * Per-tenant ceiling on notification delivery.
     *
     * Keyed by tenant, not globally: an incident at a page with 40,000
     * subscribers must not occupy every worker and delay delivery for every
     * other tenant -- who are, by definition, also mid-incident. Over the
     * limit, jobs are released back to the queue rather than dropped, so a
     * large fan-out drains steadily instead of starving its neighbours.
     */
    protected function configureNotificationThrottle(): void
    {
        RateLimiter::for('status-notifications', fn (DeliverStatusNotification $job): Limit => Limit::perMinute(
            (int) config('services.notifications.per_tenant_per_minute', 300),
        )->by((string) $job->tenantId));
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
