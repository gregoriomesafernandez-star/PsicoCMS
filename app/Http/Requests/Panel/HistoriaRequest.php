<?php

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HistoriaRequest extends FormRequest
{
    public const MAXIMO_ADJUNTOS = 10;
    public const MAXIMO_KB = 8192;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'paciente_id' => ['required', 'integer', 'exists:pacientes,id'],
            'cita_id' => [
                'nullable', 'integer',
                Rule::exists('citas', 'id')->where('paciente_id', $this->input('paciente_id')),
            ],
            'fecha_sesion' => ['required', 'date', 'before_or_equal:today'],
            'titulo' => ['nullable', 'string', 'max:150'],
            'contenido' => ['nullable', 'string', 'max:200000'],
            'adjuntos' => ['nullable', 'array', 'max:' . self::MAXIMO_ADJUNTOS],
            'adjuntos.*' => ['file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:' . self::MAXIMO_KB],
            'eliminar_adjuntos' => ['nullable', 'array'],
            'eliminar_adjuntos.*' => ['integer'],
        ];
    }

    public function attributes(): array
    {
        return [
            'paciente_id' => 'paciente',
            'cita_id' => 'cita vinculada',
            'fecha_sesion' => 'fecha de la sesion',
            'titulo' => 'titulo',
            'contenido' => 'contenido',
            'adjuntos' => 'archivos adjuntos',
        ];
    }

    public function messages(): array
    {
        return [
            'paciente_id.required' => 'Elige de que paciente es esta entrada.',
            'cita_id.exists' => 'Esa cita no pertenece al paciente elegido.',
            'fecha_sesion.before_or_equal' => 'La fecha de la sesion no puede ser posterior a hoy.',
            'adjuntos.max' => 'Puedes subir como maximo ' . self::MAXIMO_ADJUNTOS . ' archivos a la vez.',
            'adjuntos.*.mimes' => 'Solo se admiten imagenes (JPG, PNG o WEBP) y documentos PDF.',
            'adjuntos.*.max' => 'Cada archivo puede pesar como maximo 8 MB.',
        ];
    }
}
