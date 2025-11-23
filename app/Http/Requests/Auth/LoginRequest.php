<?php
namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Allow login with either email or userName
            'email'    => ['nullable', 'string', 'email', 'required_without:userName'],
            'userName' => ['nullable', 'string', 'required_without:email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // Try authenticating by email first, then by username
        $credentials = $this->only('password');
        if ($this->filled('email')) {
            $credentials['email'] = $this->string('email');
        }

        $attempted = false;
        if (isset($credentials['email'])) {
            $attempted = Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $this->boolean('remember'));
        }

        if (! $attempted && $this->filled('userName')) {
            $attempted = Auth::attempt(['userName' => $this->string('userName'), 'password' => $credentials['password']], $this->boolean('remember'));
        }

        if (! $attempted) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        // Use either email or username for throttle key
        $identifier = $this->filled('email') ? $this->string('email') : $this->string('userName');

        return Str::transliterate(Str::lower($identifier) . '|' . $this->ip());
    }

    public function messages()
    {
        return [
            'email.required_without'    => 'Email or userName is required',
            'userName.required_without' => 'Email or userName is required',
            'password.required'         => 'Password is required',
        ];
    }
}
