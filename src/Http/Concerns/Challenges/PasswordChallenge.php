<?php

namespace ClaudioDekker\LaravelAuth\Http\Concerns\Challenges;

use ClaudioDekker\LaravelAuth\Http\Concerns\EmitsAuthenticationEvents;
use ClaudioDekker\LaravelAuth\Http\Concerns\InteractsWithRateLimiting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait PasswordChallenge
{
    use EmitsAuthenticationEvents;
    use InteractsWithRateLimiting;

    /**
     * Sends a response indicating that the password challenge did not succeed.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    abstract protected function sendPasswordChallengeFailedResponse(Request $request);

    /**
     * Sends a response indicating that the password challenge has succeeded.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    abstract protected function sendPasswordChallengeSuccessfulResponse(Request $request);

    /**
     * Handle a password challenge confirmation request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    protected function handlePasswordChallengeRequest(Request $request)
    {
        $this->validatePasswordChallengeRequest($request);

        if ($this->isCurrentlyRateLimited($request)) {
            $this->emitLockoutEvent($request);

            return $this->sendRateLimitedResponse($request, $this->rateLimitingExpiresInSeconds($request));
        }

        if (! $this->hasValidPassword($request)) {
            $this->incrementRateLimitingCounter($request);

            return $this->sendPasswordChallengeFailedResponse($request);
        }

        $this->resetRateLimitingCounter($request);

        return $this->sendPasswordChallengeSuccessfulResponse($request);
    }

    /**
     * Validate the password challenge confirmation request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validatePasswordChallengeRequest(Request $request): void
    {
        $request->validate([
            'password' => 'required|string',
        ]);
    }

    /**
     * Determine whether the provided password matches the current user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function hasValidPassword(Request $request): bool
    {
        return Auth::validate([
            'id' => Auth::id(),
            'password' => $request->input('password'),
        ]);
    }
}
