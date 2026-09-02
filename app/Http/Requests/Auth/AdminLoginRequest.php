<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials, scoped to admin
     * accounts only.
     *
     * The `role` constraint is passed straight into Auth::attempt() so a
     * correct password on a company account fails exactly like a wrong
     * password would — a Company must never be able to reach the admin
     * area (PRD #4.2), and the failure message must not reveal *why*.
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt([
            'email' => $this->string('email'),
            'password' => $this->string('password'),
            'role' => User::ROLE_ADMIN,
        ])) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', ['seconds' => $seconds]),
        ]);
    }

    /**
     * Keyed by email + IP, scoped to the admin login form specifically, so
     * exhausting attempts here can't lock the same person out of the
     * company login form (and vice versa).
     */
    public function throttleKey(): string
    {
        return 'admin-login:'.Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
