<?php

namespace ClaudioDekker\LaravelAuth\Http\Traits;

use Illuminate\Contracts\Auth\Authenticatable;

trait WithoutVerificationEmail
{
    /**
     * Send the email verification notification.
     */
    protected function sendEmailVerificationNotification(Authenticatable $user): void
    {
        //
    }
}
