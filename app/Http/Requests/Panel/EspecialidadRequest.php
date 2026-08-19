<?php

namespace App\Http\Requests\Panel;

use App\Services\ContenidoWebService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EspecialidadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:20000'],
            'icono' => ['nullable', 'string', Rule::in(app(ContenidoWebService::class)->iconosPermitidos())],
            'activo' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre' => 'nombre',
            'descripcion' => 'descripcion',
            'icono' => 'icono',
            'activo' => 'visibilidad',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'Escribe el nombre de la especialidad.',
            'icono.in' => 'Elige uno de los iconos disponibles.',
        ];
    }
}
