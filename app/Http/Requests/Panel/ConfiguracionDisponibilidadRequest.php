<?php

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;

class ConfiguracionDisponibilidadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'duracion_online' => ['required', 'integer', 'min:5', 'max:480'],
            'duracion_presencial' => ['required', 'integer', 'min:5', 'max:480'],
            'descanso_online' => ['required', 'integer', 'min:0', 'max:120'],
            'descanso_presencial' => ['required', 'integer', 'min:0', 'max:120'],
            'descanso_online_activo' => ['nullable', 'boolean'],
            'descanso_presencial_activo' => ['nullable', 'boolean'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
        ];
    }

    public function attributes(): array
    {
        return [
            'duracion_online' => 'duracion de las sesiones online',
            'duracion_presencial' => 'duracion de las sesiones presenciales',
            'descanso_online' => 'descanso entre sesiones online',
            'descanso_presencial' => 'descanso entre sesiones presenciales',
            'hora_inicio' => 'hora de entrada',
            'hora_fin' => 'hora de salida',
        ];
    }

    public function messages(): array
    {
        return [
            'hora_fin.after' => 'La hora de salida debe ser posterior a la hora de entrada.',
        ];
    }
}
