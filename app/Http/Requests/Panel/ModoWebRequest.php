<?php

namespace App\Http\Requests\Panel;

use App\Services\TemaService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ModoWebRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'modo' => ['required', Rule::in(array_keys(TemaService::MODOS))],
        ];
    }

    public function attributes(): array
    {
        return [
            'modo' => 'formato de la web',
        ];
    }

    public function messages(): array
    {
        return [
            'modo.required' => 'Elige si tu web sera una sola pagina o varias.',
            'modo.in' => 'Ese formato de web no existe.',
        ];
    }
}
