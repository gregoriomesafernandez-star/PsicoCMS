<?php

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;

class PerfilPublicoRequest extends FormRequest
{
    /**
     * Si la validacion falla se vuelve a la pestana del formulario, no al principio
     * de la pagina: los errores se ven donde se han producido.
     */
    protected function getRedirectUrl(): string
    {
        return route('panel.contenido') . '#perfil';
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_publico' => ['required', 'string', 'max:255'],
            'eslogan' => ['required', 'string', 'max:255'],
            'num_colegiado' => ['nullable', 'string', 'max:50'],
            'telefono_citas' => ['required', 'string', 'max:20', 'regex:/^[0-9\s+()-]+$/'],
            'email_citas' => ['required', 'email', 'max:255'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre_publico' => 'nombre publico',
            'eslogan' => 'frase gancho',
            'num_colegiado' => 'numero de colegiada',
            'telefono_citas' => 'telefono para citas',
            'email_citas' => 'email para citas',
            'foto' => 'foto',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_publico.required' => 'Escribe el nombre con el que quieres aparecer en tu web.',
            'eslogan.required' => 'La frase gancho es lo primero que lee un paciente. No la dejes vacia.',
            'telefono_citas.regex' => 'El telefono solo puede contener numeros.',
            'foto.image' => 'La foto tiene que ser una imagen JPG, PNG o WEBP.',
            'foto.max' => 'La foto no puede pasar de 4 MB.',
        ];
    }
}
