<?php

namespace App\Http\Requests\Instalacion;

use Illuminate\Foundation\Http\FormRequest;

class PerfilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'eslogan' => ['required', 'string', 'max:255'],
            'num_colegiado' => ['nullable', 'string', 'max:50'],
            'telefono_citas' => ['required', 'string', 'max:20', 'regex:/^[0-9\s+()-]+$/'],
            'email_citas' => ['required', 'email', 'max:255'],
            'sobre_mi' => ['required', 'string'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'lugar_consulta' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'eslogan' => 'frase gancho',
            'num_colegiado' => 'numero de colegiado',
            'telefono_citas' => 'telefono para citas',
            'email_citas' => 'email para citas',
            'sobre_mi' => 'sobre mi',
            'direccion' => 'direccion',
            'lugar_consulta' => 'lugar de consulta',
        ];
    }

    public function messages(): array
    {
        return [
            'telefono_citas.regex' => 'El telefono solo puede contener numeros.',
        ];
    }
}
