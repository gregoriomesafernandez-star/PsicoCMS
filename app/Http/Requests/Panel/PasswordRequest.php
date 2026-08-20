<?php

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Validator;

class PasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password_actual' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:password_actual'],
        ];
    }

    /** La contrasena actual se comprueba aqui para que el error salga en su campo. */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! Hash::check((string) $this->input('password_actual'), $this->user()->password)) {
                $validator->errors()->add('password_actual', 'La contrasena actual no es correcta.');
            }
        });
    }

    public function attributes(): array
    {
        return [
            'password_actual' => 'contrasena actual',
            'password' => 'contrasena nueva',
        ];
    }

    public function messages(): array
    {
        return [
            'password.min' => 'La contrasena nueva tiene que tener al menos 8 caracteres.',
            'password.confirmed' => 'Las dos contrasenas nuevas no coinciden.',
            'password.different' => 'La contrasena nueva tiene que ser distinta de la actual.',
        ];
    }
}
