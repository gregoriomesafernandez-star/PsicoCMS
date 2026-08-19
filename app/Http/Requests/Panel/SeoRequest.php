<?php

namespace App\Http\Requests\Panel;

use App\Services\ContenidoWebService;
use Illuminate\Foundation\Http\FormRequest;

class SeoRequest extends FormRequest
{
    /**
     * Si la validacion falla se vuelve a la pestana del formulario, no al principio
     * de la pagina: los errores se ven donde se han producido.
     */
    protected function getRedirectUrl(): string
    {
        return route('panel.contenido') . '#seo';
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'meta_title' => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:170'],
            'paginas' => ['nullable', 'array'],
            'paginas.*' => ['array'],
            'paginas.*.title' => ['nullable', 'string', 'max:70'],
            'paginas.*.description' => ['nullable', 'string', 'max:170'],
        ];
    }

    public function paginas(): array
    {
        $enviadas = (array) $this->input('paginas', []);

        return array_intersect_key($enviadas, ContenidoWebService::PAGINAS_SEO);
    }

    public function attributes(): array
    {
        return [
            'meta_title' => 'titulo SEO',
            'meta_description' => 'descripcion SEO',
        ];
    }

    public function messages(): array
    {
        return [
            'meta_title.max' => 'El titulo SEO no puede pasar de 70 caracteres.',
            'meta_description.max' => 'La descripcion SEO no puede pasar de 170 caracteres.',
            'paginas.*.title.max' => 'Cada titulo SEO puede tener como mucho 70 caracteres.',
            'paginas.*.description.max' => 'Cada descripcion SEO puede tener como mucho 170 caracteres.',
        ];
    }
}
