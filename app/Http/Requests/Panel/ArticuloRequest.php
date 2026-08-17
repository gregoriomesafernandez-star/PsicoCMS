<?php

namespace App\Http\Requests\Panel;

use App\Models\Articulo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class ArticuloRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug((string) $this->input('slug')) ?: null,
        ]);
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable', 'string', 'max:255',
                Rule::unique('articulos', 'slug')->ignore($this->articuloId()),
            ],
            'categoria_id' => ['nullable', 'integer', 'exists:categorias,id'],
            'extracto' => ['nullable', 'string', 'max:300'],
            'contenido' => ['nullable', 'string', 'max:200000'],
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'eliminar_imagen' => ['nullable', 'boolean'],
            'estado' => ['required', Rule::in(['borrador', 'publicado'])],
            'publicado_at' => ['nullable', 'date'],
            'meta_title' => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:170'],
        ];
    }

    public function attributes(): array
    {
        return [
            'titulo' => 'titulo',
            'slug' => 'enlace permanente',
            'categoria_id' => 'categoria',
            'extracto' => 'extracto',
            'contenido' => 'contenido',
            'imagen' => 'imagen destacada',
            'estado' => 'estado',
            'publicado_at' => 'fecha de publicacion',
            'meta_title' => 'titulo SEO',
            'meta_description' => 'descripcion SEO',
        ];
    }

    public function messages(): array
    {
        return [
            'imagen.max' => 'La imagen destacada no puede pesar mas de 2 MB.',
            'imagen.mimes' => 'La imagen destacada debe ser JPG, PNG o WEBP.',
            'slug.unique' => 'Ya tienes otro articulo con ese enlace permanente.',
        ];
    }

    public function articuloId(): ?int
    {
        $articulo = $this->route('articulo');

        return $articulo instanceof Articulo ? $articulo->id : null;
    }
}
