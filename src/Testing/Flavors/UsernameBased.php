<?php

namespace ClaudioDekker\LaravelAuth\Testing\Flavors;

use ClaudioDekker\LaravelAuth\Testing\Helpers;
use Illuminate\Testing\TestResponse;
use Illuminate\Validation\ValidationException;

trait UsernameBased
{
    use Helpers;

    protected function usernameField(): string
    {
        return 'username';
    }

    protected function defaultUsername(): string
    {
        return 'claudiodekker';
    }

    protected function anotherUsername(): string
    {
        return 'ubient';
    }

    protected function invalidUsername(): string
    {
        return 'foo@bar.com';
    }

    protected function nonExistentUsername(): string
    {
        return 'zonda';
    }

    protected function tooLongUsername(): string
    {
        return str_repeat('a', 256);
    }

    protected function assertUsernameRequiredValidationError(TestResponse $response): void
    {
        $this->assertInstanceOf(ValidationException::class, $response->exception);
        $this->assertSame([$this->usernameField() => ['The username field is required.']], $response->exception->errors());
    }

    protected function assertUsernameMustBeValidValidationError(TestResponse $response): void
    {
        $this->assertInstanceOf(ValidationException::class, $response->exception);
        $this->assertSame([$this->usernameField() => [__('laravel-auth::auth.failed')]], $response->exception->errors());
    }

    protected function assertUsernameTooLongValidationError(TestResponse $response): void
    {
        $this->assertInstanceOf(ValidationException::class, $response->exception);
        $this->assertSame([$this->usernameField() => ['The username must not be greater than 255 characters.']], $response->exception->errors());
    }

    protected function assertUsernameAlreadyExistsValidationError(TestResponse $response): void
    {
        $this->assertInstanceOf(ValidationException::class, $response->exception);
        $this->assertSame([$this->usernameField() => ['The username has already been taken.']], $response->exception->errors());
    }
}
