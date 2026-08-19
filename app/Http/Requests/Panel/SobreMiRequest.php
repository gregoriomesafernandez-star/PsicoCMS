<?php

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;

class SobreMiRequest extends FormRequest
{
    /**
     * Si la validacion falla se vuelve a la pestana del formulario, no al principio
     * de la pagina: los errores se ven donde se han producido.
     */
    protected function getRedirectUrl(): string
    {
        return route('panel.contenido') . '#sobre-mi';
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sobre_mi' => ['required', 'string', 'max:30000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'sobre_mi' => 'sobre mi',
        ];
    }

    public function messages(): array
    {
        return [
            'sobre_mi.required' => 'Cuenta quien eres: es la seccion que mas se lee de una web de psicologia.',
        ];
    }
}
