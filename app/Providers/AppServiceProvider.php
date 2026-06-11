<?php

namespace App\Providers;

use App\Models\PaymentRequest;
use App\Policies\PaymentRequestPolicy;
use App\Services\Contracts\ExchangeRateProviderInterface;
use App\Services\Providers\ExchangeRateApiProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ExchangeRateProviderInterface::class, function (): ExchangeRateApiProvider {
            return new ExchangeRateApiProvider(
                baseUrl: config('services.exchange_rate_api.base_url'),
                sourceName: config('services.exchange_rate_api.source'),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(PaymentRequest::class, PaymentRequestPolicy::class);

        Passport::tokensExpireIn(now()->addDays(15));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));
    }
}
