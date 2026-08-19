<?php

namespace App\Http\Requests\Panel;

use App\Services\ContenidoWebService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class ConsultaRequest extends FormRequest
{
    /**
     * Si la validacion falla se vuelve a la pestana del formulario, no al principio
     * de la pagina: los errores se ven donde se han producido.
     */
    protected function getRedirectUrl(): string
    {
        return route('panel.contenido') . '#consulta';
    }

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'mapa_embed' => app(ContenidoWebService::class)->urlDelMapa($this->input('mapa_embed')),
        ]);
    }

    public function rules(): array
    {
        return [
            'direccion' => ['nullable', 'string', 'max:255'],
            'lugar_consulta' => ['nullable', 'string', 'max:255'],
            'horarios' => ['nullable', 'string', 'max:255'],
            'mapa_embed' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! app(ContenidoWebService::class)->esUrlDeMapa($this->input('mapa_embed'))) {
                $validator->errors()->add(
                    'mapa_embed',
                    'Pega el codigo que te da Google Maps en "Compartir > Insertar un mapa". No reconocemos esa direccion.'
                );
            }
        });
    }

    public function attributes(): array
    {
        return [
            'direccion' => 'direccion',
            'lugar_consulta' => 'lugar de consulta',
            'horarios' => 'horarios',
            'mapa_embed' => 'mapa',
        ];
    }
}
