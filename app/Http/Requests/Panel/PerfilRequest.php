<?php

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PerfilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('telefono'))) {
            $this->merge(['telefono' => preg_replace('/\D/', '', $this->input('telefono'))]);
        }
    }

    public function rules(): array
    {
        $psicologa = $this->user();

        return [
            'nombre' => ['required', 'string', 'max:100'],
            'apellidos' => ['nullable', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', Rule::unique('psicologas', 'email')->ignore($psicologa->id)],
            'telefono' => ['required', 'string', 'min:9', 'max:15', Rule::unique('psicologas', 'telefono')->ignore($psicologa->id)],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre' => 'nombre',
            'apellidos' => 'apellidos',
            'email' => 'email de acceso',
            'telefono' => 'telefono de acceso',
            'avatar' => 'foto',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Ese email ya esta en uso.',
            'telefono.unique' => 'Ese telefono ya esta en uso.',
            'telefono.min' => 'El telefono tiene que tener al menos 9 digitos.',
        ];
    }
}
