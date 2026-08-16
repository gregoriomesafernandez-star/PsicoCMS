<?php

namespace App\Http\Requests\Instalacion;

use Illuminate\Foundation\Http\FormRequest;

class BaseDatosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'db_host' => ['required', 'string', 'max:255'],
            'db_port' => ['required', 'numeric'],
            'db_database' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9_]+$/'],
            'db_username' => ['required', 'string', 'max:255'],
            'db_password' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'db_host' => 'servidor',
            'db_port' => 'puerto',
            'db_database' => 'nombre de la base de datos',
            'db_username' => 'usuario',
            'db_password' => 'contrasena',
        ];
    }

    public function messages(): array
    {
        return [
            'db_database.regex' => 'El nombre de la base de datos solo puede tener letras, numeros y guiones bajos.',
        ];
    }
}
