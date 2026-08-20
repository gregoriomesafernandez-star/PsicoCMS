<?php

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;

class NotificacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:16', 'max:64'],
            'nombre' => ['nullable', 'string', 'max:100'],
            'avisar_citas' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => 'correo de Gmail',
            'password' => 'contrasena de aplicacion',
            'nombre' => 'nombre del remitente',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Escribe el correo de Gmail al que quieres recibir los avisos.',
            'email.email' => 'Ese correo no parece valido.',
            'password.min' => 'La contrasena de aplicacion de Google tiene 16 letras. Copiala entera.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Google la muestra en cuatro bloques de cuatro letras.
        if (is_string($this->input('password'))) {
            $this->merge(['password' => preg_replace('/\s+/', '', $this->input('password'))]);
        }
    }
}
