<?php

namespace App\Http\Requests\Panel;

use App\Models\Cita;
use App\Services\DisponibilidadService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CitaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'paciente_nombre' => ['required', 'string', 'max:255'],
            'paciente_telefono' => ['required', 'string', 'max:20', 'regex:/^[0-9\s+()-]+$/'],
            'paciente_email' => ['nullable', 'email', 'max:255'],
            'modalidad' => ['required', Rule::in(array_keys(Cita::MODALIDADES))],
            'fecha' => ['required', 'date'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'estado' => ['required', Rule::in(array_keys(Cita::ESTADOS))],
            'motivo' => ['nullable', 'string', 'max:5000'],
            'notas' => ['nullable', 'string', 'max:10000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'paciente_nombre' => 'nombre del paciente',
            'paciente_telefono' => 'telefono del paciente',
            'paciente_email' => 'email del paciente',
            'modalidad' => 'modalidad',
            'fecha' => 'fecha',
            'hora_inicio' => 'hora',
            'estado' => 'estado',
        ];
    }

    public function messages(): array
    {
        return [
            'paciente_telefono.regex' => 'El telefono del paciente solo puede contener numeros.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $disponibilidad = app(DisponibilidadService::class);
            $fecha = Carbon::parse($this->input('fecha'));

            if ($disponibilidad->haySolapamiento(
                $fecha,
                $this->input('hora_inicio'),
                $this->input('modalidad'),
                $this->citaId()
            )) {
                $validator->errors()->add(
                    'hora_inicio',
                    'Ya tienes otra cita a esa hora. Elige un hueco libre.'
                );
            }
        });
    }

    public function citaId(): ?int
    {
        $cita = $this->route('cita');

        return $cita instanceof Cita ? $cita->id : null;
    }
}
