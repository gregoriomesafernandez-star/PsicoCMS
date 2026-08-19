<?php

namespace App\Http\Requests\Panel;

use App\Models\Plan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlanRequest extends FormRequest
{
    /**
     * Si la validacion falla se vuelve a la pestana del formulario, no al principio
     * de la pagina: los errores se ven donde se han producido.
     */
    protected function getRedirectUrl(): string
    {
        return route('panel.contenido') . '#planes';
    }

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $precio = $this->input('precio');

        if (is_string($precio)) {
            $this->merge(['precio' => str_replace(',', '.', trim($precio))]);
        }
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'modalidad' => ['required', Rule::in(array_keys(Plan::MODALIDADES))],
            'precio' => ['required', 'numeric', 'min:0', 'max:99999.99'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'caracteristicas' => ['nullable', 'string', 'max:2000'],
            'destacado' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre' => 'nombre',
            'modalidad' => 'modalidad',
            'precio' => 'precio',
            'descripcion' => 'descripcion',
            'caracteristicas' => 'caracteristicas',
            'destacado' => 'plan destacado',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'Escribe el nombre del plan.',
            'modalidad.in' => 'La modalidad tiene que ser presencial u online.',
            'precio.required' => 'Indica el precio de la sesion.',
            'precio.numeric' => 'El precio tiene que ser un numero, por ejemplo 60 o 60,50.',
        ];
    }
}
