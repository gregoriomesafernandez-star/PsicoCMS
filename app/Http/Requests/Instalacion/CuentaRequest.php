<?php

namespace App\Http\Requests\Instalacion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class CuentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'apellidos' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'telefono' => ['required', 'string', 'max:20', 'regex:/^[0-9\s+()-]+$/'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre' => 'nombre',
            'apellidos' => 'apellidos',
            'email' => 'email',
            'telefono' => 'telefono',
            'password' => 'contrasena',
        ];
    }

    public function messages(): array
    {
        return [
            'telefono.regex' => 'El telefono solo puede contener numeros.',
            'password.confirmed' => 'Las dos contrasenas no coinciden.',
        ];
    }
}
