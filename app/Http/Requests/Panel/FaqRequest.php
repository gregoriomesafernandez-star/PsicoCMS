<?php

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;

class FaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pregunta' => ['required', 'string', 'max:255'],
            'respuesta' => ['nullable', 'string', 'max:20000'],
            'orden' => ['nullable', 'integer', 'min:0', 'max:999'],
            'activa' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'pregunta' => 'pregunta',
            'respuesta' => 'respuesta',
            'orden' => 'orden',
            'activa' => 'visibilidad',
        ];
    }

    public function messages(): array
    {
        return [
            'pregunta.required' => 'Escribe la pregunta que quieres responder.',
            'pregunta.max' => 'La pregunta no puede pasar de 255 caracteres.',
        ];
    }
}
