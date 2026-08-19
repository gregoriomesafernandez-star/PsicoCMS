<?php

namespace App\Http\Requests\Panel;

use App\Services\ContenidoWebService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServicioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:20000'],
            'icono' => ['nullable', 'string', Rule::in(app(ContenidoWebService::class)->iconosPermitidos())],
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'eliminar_imagen' => ['nullable', 'boolean'],
            'activo' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'titulo' => 'titulo',
            'descripcion' => 'descripcion',
            'icono' => 'icono',
            'imagen' => 'imagen',
            'activo' => 'visibilidad',
        ];
    }

    public function messages(): array
    {
        return [
            'titulo.required' => 'Escribe el nombre del servicio.',
            'icono.in' => 'Elige uno de los iconos disponibles.',
            'imagen.max' => 'La imagen no puede pasar de 2 MB.',
        ];
    }
}
