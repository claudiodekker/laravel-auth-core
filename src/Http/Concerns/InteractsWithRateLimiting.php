<?php

namespace ClaudioDekker\LaravelAuth\Http\Concerns;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\RateLimiter;

trait InteractsWithRateLimiting
{
    /**
     * Prepare the identifier used to track the rate limiting state.
     */
    protected function throttleKey(string $key): string
    {
        return "auth::$key";
    }

    /**
     * Sends a response indicating that the requests have been rate limited.
     *
     * @return mixed
     */
    abstract protected function sendRateLimitedResponse(Request $request, int $availableInSeconds);

    /**
     * Determine the rate limits that apply to the request.
     */
    protected function rateLimits(Request $request): array
    {
        return [
            Limit::perMinute(250),
            Limit::perMinute(5)->by('ip::'.$request->ip()),
        ];
    }

    /**
     * Determines whether the request is currently rate limited.
     */
    protected function isCurrentlyRateLimited(Request $request): bool
    {
        return Collection::make($this->rateLimits($request))->contains(function (Limit $limit) {
            return RateLimiter::tooManyAttempts($this->throttleKey($limit->key), $limit->maxAttempts);
        });
    }

    /**
     * Determines the seconds remaining until rate limiting is lifted.
     */
    protected function rateLimitExpiresInSeconds(Request $request): int
    {
        return Collection::make($this->rateLimits($request))
            ->max(fn (Limit $limit) => RateLimiter::availableIn($this->throttleKey($limit->key)));
    }

    /**
     * Increments the rate limiting counter.
     */
    protected function incrementRateLimitingCounter(Request $request): void
    {
        Collection::make($this->rateLimits($request))->each(function (Limit $limit) {
            RateLimiter::hit($this->throttleKey($limit->key), $limit->decayMinutes * 60);
        });
    }

    /**
     * Clears the rate limiting counter (if any).
     */
    protected function resetRateLimitingCounter(Request $request): void
    {
        Collection::make($this->rateLimits($request))
            ->filter(fn (Limit $limit) => $limit->key)
            ->each(fn (Limit $limit) => RateLimiter::clear($this->throttleKey($limit->key)));
    }
}
