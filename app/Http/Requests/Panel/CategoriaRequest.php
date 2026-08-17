<?php

namespace App\Http\Requests\Panel;

use App\Models\Categoria;
use Illuminate\Foundation\Http\FormRequest;

class CategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'orden' => ['nullable', 'integer', 'min:0', 'max:999'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre' => 'nombre',
            'descripcion' => 'descripcion',
            'orden' => 'orden',
        ];
    }

    public function categoriaId(): ?int
    {
        $categoria = $this->route('categoria');

        return $categoria instanceof Categoria ? $categoria->id : null;
    }
}
