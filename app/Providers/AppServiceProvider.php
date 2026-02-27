<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
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

        // Configure rate limiters
        $this->configureRateLimiting();
    }

    /**
     * Configure rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // Likes rate limiter: 30 per minute
        RateLimiter::for('likes', function (Request $request) {
            return Limit::perMinute(30)
                ->by($this->resolveRateLimitKey($request, 'likes'))
                ->response(function (Request $request, array $headers) {
                    return $this->buildRateLimitResponse(
                        $request,
                        'likes',
                        $headers['Retry-After'] ?? 60
                    );
                });
        });

        // Reports rate limiter: 5 per hour (strict to prevent abuse)
        RateLimiter::for('reports', function (Request $request) {
            return Limit::perHour(5)
                ->by($this->resolveRateLimitKey($request, 'reports'))
                ->response(function (Request $request, array $headers) {
                    return $this->buildRateLimitResponse(
                        $request,
                        'reports',
                        $headers['Retry-After'] ?? 3600
                    );

                });
        });

        // Searches rate limiter: 60 per minute
        RateLimiter::for('searches', function (Request $request) {
            return Limit::perMinute(60)
                ->by($this->resolveRateLimitKey($request, 'searches'))
                ->response(function (Request $request, array $headers) {
                    return $this->buildRateLimitResponse(
                        $request,
                        'searches',
                        $headers['Retry-After'] ?? 60
                    );
                });
        });

        // Chirps rate limiter: 10 per minute
        RateLimiter::for('chirps', function (Request $request) {
            return Limit::perMinute(10)
                ->by($this->resolveRateLimitKey($request, 'chirps'))
                ->response(function (Request $request, array $headers) {
                    return $this->buildRateLimitResponse(
                        $request,
                        'chirps',
                        $headers['Retry-After'] ?? 60
                    );
                });
        });

        // Login rate limiter: 5 per minute (security)
        RateLimiter::for('login', function (Request $request) {
            // Use email or IP as key to prevent brute force per account
            $key = strtolower($request->input('email', '')).'|'.$request->ip();

            return Limit::perMinute(5)
                ->by($key)
                ->response(function (Request $request, array $headers) {
                    return back()
                        ->withErrors(['email' => 'Too many login attempts. Please try again later.'])
                        ->withInput($request->only('email'));
                });
        });
    }

    /**
     * Resolve rate limit key for request.
     */
    protected function resolveRateLimitKey(Request $request, string $type): string
    {
        $userId = auth()->id() ?? 'guest';
        $ip = $request->ip();

        return "{$type}:{$userId}:{$ip}";
    }

    /**
     * Build rate limit exceeded response.
     */
    protected function buildRateLimitResponse(Request $request, string $type, int $retryAfter): mixed
    {
        $message = __("ratelimit.{$type}", [
            'time' => $this->secondsToHuman($retryAfter),
            'seconds' => $retryAfter,
        ]);

        // JSON response for AJAX/fetch requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'retry_after' => $retryAfter,
                'retry_after_human' => $this->secondsToHuman($retryAfter),
            ], 429, [
                'Retry-After' => $retryAfter,
                'X-RateLimit-Reset' => now()->addSeconds($retryAfter)->getTimestamp(),
            ]);
        }

        // Standard redirect with flash message
        return back()->with('error', $message);
    }

    /**
     * Convert seconds to human-readable format.
     */
    protected function secondsToHuman(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds.' second'.($seconds !== 1 ? 's' : '');
        }

        $minutes = ceil($seconds / 60);

        if ($minutes < 60) {
            return $minutes.' minute'.($minutes !== 1 ? 's' : '');
        }

        $hours = ceil($minutes / 60);

        return $hours.' hour'.($hours !== 1 ? 's' : '');
    }
}
