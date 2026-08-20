<?php

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;

class RedSocialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'urls' => ['nullable', 'array'],
            'urls.*' => ['nullable', 'url', 'max:255', 'starts_with:https://,http://'],
            'activas' => ['nullable', 'array'],
            'activas.*' => ['string'],
        ];
    }

    public function messages(): array
    {
        return [
            'urls.*.url' => 'Esa direccion no es valida. Copiala del navegador tal cual.',
            'urls.*.starts_with' => 'La direccion tiene que empezar por https://',
            'urls.*.max' => 'La direccion es demasiado larga.',
        ];
    }
}
