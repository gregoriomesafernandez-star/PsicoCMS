<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AccesoRequest extends FormRequest
{
    private const MAX_INTENTOS = 5;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'telefono' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string'],
            'recordarme' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => 'email',
            'telefono' => 'telefono',
            'password' => 'contrasena',
        ];
    }

    public function autenticar(): void
    {
        $this->comprobarIntentos();

        $credenciales = [
            'email' => $this->string('email')->trim()->value(),
            'telefono' => preg_replace('/\D/', '', $this->string('telefono')->value()),
            'password' => $this->string('password')->value(),
        ];

        if (! Auth::attempt($credenciales, $this->boolean('recordarme'))) {
            RateLimiter::hit($this->claveIntentos());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->claveIntentos());
    }

    public function comprobarIntentos(): void
    {
        if (! RateLimiter::tooManyAttempts($this->claveIntentos(), self::MAX_INTENTOS)) {
            return;
        }

        event(new Lockout($this));

        $segundos = RateLimiter::availableIn($this->claveIntentos());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $segundos,
                'minutes' => ceil($segundos / 60),
            ]),
        ]);
    }

    public function claveIntentos(): string
    {
        return Str::transliterate(Str::lower($this->string('email')->value()) . '|' . $this->ip());
    }
}
