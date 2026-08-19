<?php

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;

class ImagenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'alt' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'imagen' => 'imagen',
            'alt' => 'texto alternativo',
        ];
    }

    public function messages(): array
    {
        return [
            'imagen.image' => 'El archivo tiene que ser una imagen JPG, PNG o WEBP.',
            'imagen.mimes' => 'Solo se admiten imagenes JPG, PNG o WEBP.',
            'imagen.max' => 'La imagen no puede pasar de 4 MB.',
            'alt.max' => 'La descripcion no puede pasar de 255 caracteres.',
        ];
    }
}
