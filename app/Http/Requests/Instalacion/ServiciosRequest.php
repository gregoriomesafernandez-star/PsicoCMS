<?php

namespace App\Http\Requests\Instalacion;

use Illuminate\Foundation\Http\FormRequest;

class ServiciosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'servicios' => ['required', 'array', 'min:1'],
            'servicios.*.titulo' => ['required', 'string', 'max:255'],
            'servicios.*.descripcion' => ['nullable', 'string', 'max:1000'],

            'especialidades' => ['required', 'array', 'min:1'],
            'especialidades.*.nombre' => ['required', 'string', 'max:255'],

            'planes' => ['required', 'array', 'min:1'],
            'planes.*.nombre' => ['required', 'string', 'max:255'],
            'planes.*.modalidad' => ['required', 'in:online,presencial'],
            'planes.*.precio' => ['required', 'numeric', 'min:0'],

            'horarios' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'servicios.required' => 'Anade al menos un servicio.',
            'servicios.*.titulo.required' => 'El titulo del servicio es obligatorio.',
            'especialidades.required' => 'Anade al menos una especialidad.',
            'especialidades.*.nombre.required' => 'El nombre de la especialidad es obligatorio.',
            'planes.required' => 'Anade al menos un plan.',
            'planes.*.nombre.required' => 'El nombre del plan es obligatorio.',
            'planes.*.precio.required' => 'El precio del plan es obligatorio.',
        ];
    }
}
