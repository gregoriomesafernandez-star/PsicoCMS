<?php

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;

class ModoVacacionesRequest extends FormRequest
{
    public const MENSAJE_POR_DEFECTO = 'Ahora mismo estoy fuera de consulta y no puedo atender nuevas reservas. Dejame tu mensaje o vuelve a intentarlo mas adelante.';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'modo_vacaciones' => ['nullable', 'boolean'],
            'mensaje_vacaciones' => ['nullable', 'string', 'max:500'],
            'vuelvo_el' => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }

    public function attributes(): array
    {
        return [
            'mensaje_vacaciones' => 'mensaje para tus pacientes',
            'vuelvo_el' => 'fecha de vuelta',
        ];
    }

    public function messages(): array
    {
        return [
            'vuelvo_el.after_or_equal' => 'La fecha de vuelta no puede ser anterior a hoy.',
            'mensaje_vacaciones.max' => 'El mensaje no puede pasar de 500 caracteres.',
        ];
    }
}
