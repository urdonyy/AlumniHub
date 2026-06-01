<?php

namespace App\Providers;

use App\Models\CommunityCreationRequest;
use App\Models\CommunityCreationRequestModerator;
use App\Policies\CommunityCreationRequestPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        Password::defaults(fn () => Password::min(8)
            ->mixedCase()
            ->numbers()
            ->symbols());

        Gate::policy(CommunityCreationRequest::class, CommunityCreationRequestPolicy::class);

        Gate::define('respondAsCoMod', function ($user, CommunityCreationRequestModerator $invite) {
            return app(CommunityCreationRequestPolicy::class)->respondAsCoMod($user, $invite);
        });
    }
}
