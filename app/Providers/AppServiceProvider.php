<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

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
        // Register the policy
        \Illuminate\Support\Facades\Gate::policy(User::class, UserPolicy::class);

        // Use custom pagination view
        Paginator::defaultView('vendor.pagination.tailwind');
    }
}
