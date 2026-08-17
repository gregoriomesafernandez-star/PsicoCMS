<?php

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;

class MoverCitaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha' => ['required', 'date'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'duracion' => ['required', 'integer', 'min:15', 'max:480'],
        ];
    }

    public function attributes(): array
    {
        return [
            'fecha' => 'fecha',
            'hora_inicio' => 'hora',
            'duracion' => 'duracion',
        ];
    }
}
