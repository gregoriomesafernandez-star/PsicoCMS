<?php

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FranjasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'modalidad' => ['required', Rule::in(['online', 'presencial'])],
            'franjas' => ['nullable', 'array'],
            'franjas.*' => ['string', 'regex:/^[0-6]\|([01][0-9]|2[0-3]):[0-5][0-9]$/'],
        ];
    }

    public function franjasNormalizadas(): array
    {
        $franjas = [];

        foreach ($this->input('franjas', []) as $valor) {
            [$dia, $hora] = explode('|', $valor);

            $franjas[] = [
                'modalidad' => $this->input('modalidad'),
                'dia_semana' => (int) $dia,
                'hora_inicio' => $hora . ':00',
            ];
        }

        return $franjas;
    }
}
