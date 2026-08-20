<?php

namespace App\Http\Requests\Panel;

use App\Services\AparienciaService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AparienciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tema' => ['required', Rule::in(array_keys(AparienciaService::TEMAS))],
            'color' => ['required', Rule::in(array_keys(AparienciaService::COLORES))],
        ];
    }

    public function attributes(): array
    {
        return [
            'tema' => 'tema',
            'color' => 'color principal',
        ];
    }

    public function messages(): array
    {
        return [
            'tema.in' => 'Elige entre el tema claro y el oscuro.',
            'color.in' => 'Elige uno de los colores disponibles.',
        ];
    }
}
