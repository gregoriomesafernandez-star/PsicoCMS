<?php

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;

class PlantillaDocumentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contenido' => ['required', 'string', 'max:200000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'contenido' => 'contenido de la plantilla',
        ];
    }

    public function messages(): array
    {
        return [
            'contenido.required' => 'La plantilla no puede quedarse vacia.',
        ];
    }
}
