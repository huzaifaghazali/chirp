<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Symfony\Component\HttpFoundation\Response;

class RateLimitWithFeedback
{
    public function __construct(protected RateLimiter $limiter) {}

    /**
     * Handle an incoming request.
     *
     * @param  string  $rateLimiterName  The name of the rate limiter to use
     * @param  string|null  $messageKey  Custom message key for translations
     */
    public function handle(Request $request, Closure $next, string $rateLimiterName, ?string $messageKey = null): Response
    {
        $key = $this->resolveRequestSignature($request, $rateLimiterName);

        $maxAttempts = config("ratelimit.{$rateLimiterName}.max_attempts", 60);
        $decayMinutes = config("ratelimit.{$rateLimiterName}.decay_minutes", 1);

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            $seconds = $this->limiter->availableIn($key);

            return $this->buildResponse($request, $rateLimiterName, $seconds, $messageKey);
        }

        $this->limiter->hit($key, $decayMinutes * 60);
        $response = $next($request);

        // Add rate limit headers to successful responses
        return $this->addRateLimitHeaders(
            $response,
            $maxAttempts,
            $maxAttempts - $this->limiter->attempts($key),
            $this->limiter->availableIn($key)
        );
    }

    /**
     * Resolve request signature for rate limiting.
     */
    protected function resolveRequestSignature(Request $request, string $rateLimiterName): string
    {
        $userId = auth()->id() ?? 'guest';
        $ip = $request->ip();

        return "ratelimit:{$rateLimiterName}:{$userId}:{$ip}";
    }

    /**
     * Build rate limit exceeded response.
     */
    protected function buildResponse(Request $request, string $rateLimiterName, int $seconds, ?string $messageKey): Response
    {
        $message = $this->getRateLimitMessage($rateLimiterName, $seconds, $messageKey);

        // JSON response for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'retry_after' => $seconds,
                'retry_after_human' => $this->secondsToHuman($seconds),
            ], 429)->withHeaders([
                'Retry-After' => $seconds,
                'X-RateLimit-Reset' => now()->addSeconds($seconds)->getTimestamp(),
            ]);
        }

        // Redirect back with error for regular requests
        return back()
            ->with('error', $message)
            ->with('rate_limit_hit', true)
            ->with('retry_after', $seconds);
    }

    /**
     * Get localized rate limit message.
     */
    protected function getRateLimitMessage(string $rateLimiterName, int $seconds, ?string $messageKey): string
    {
        $key = $messageKey ?? "ratelimit.{$rateLimiterName}";
        $fallback = 'Too many attempts. Please try again in :time.';

        $time = $this->secondsToHuman($seconds);

        if (Lang::has($key)) {
            return __($key, ['time' => $time, 'seconds' => $seconds]);
        }

        return str_replace(':time', $time, $fallback);
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

    /**
     * Add rate limit headers to response.
     */
    protected function addRateLimitHeaders(Response $response, int $limit, int $remaining, int $retryAfter): Response
    {
        return $response->withHeaders([
            'X-RateLimit-Limit' => $limit,
            'X-RateLimit-Remaining' => max(0, $remaining),
            'X-RateLimit-Reset' => now()->addSeconds($retryAfter)->getTimestamp(),
        ]);
    }
}
