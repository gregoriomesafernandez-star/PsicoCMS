<?php

namespace App\Http\Requests\Instalacion;

use App\Services\TemaService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TemaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'foto' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            'tema' => ['required', Rule::in(app(TemaService::class)->slugsDisponibles())],
            'modo' => ['required', Rule::in(TemaService::MODOS)],
        ];
    }

    public function attributes(): array
    {
        return [
            'foto' => 'foto',
            'tema' => 'tema visual',
            'modo' => 'formato de la web',
        ];
    }

    public function messages(): array
    {
        return [
            'foto.max' => 'La foto no puede pesar mas de 4 MB.',
            'foto.image' => 'El archivo debe ser una imagen (PNG, JPG o WEBP).',
            'tema.required' => 'Elige uno de los temas visuales.',
        ];
    }
}
