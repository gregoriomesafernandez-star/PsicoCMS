<?php

namespace App\Http\Requests\Panel;

use App\Models\Paciente;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PacienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'telefono' => Paciente::normalizarTelefono($this->input('telefono')),
        ]);
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'apellidos' => ['nullable', 'string', 'max:150'],
            'telefono' => [
                'required', 'string', 'min:6', 'max:20', 'regex:/^[0-9]+$/',
                Rule::unique('pacientes', 'telefono')->ignore($this->pacienteId())->whereNull('deleted_at'),
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'fecha_nacimiento' => ['nullable', 'date', 'before:today'],
            'genero' => ['nullable', Rule::in(array_keys(Paciente::GENEROS))],
            'dni' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'estado' => ['required', Rule::in(array_keys(Paciente::ESTADOS))],
            'motivo_consulta' => ['nullable', 'string', 'max:5000'],
            'notas' => ['nullable', 'string', 'max:10000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre' => 'nombre',
            'apellidos' => 'apellidos',
            'telefono' => 'telefono',
            'email' => 'email',
            'fecha_nacimiento' => 'fecha de nacimiento',
            'genero' => 'genero',
            'dni' => 'DNI',
            'direccion' => 'direccion',
            'estado' => 'estado',
            'motivo_consulta' => 'motivo de consulta',
            'notas' => 'notas internas',
        ];
    }

    public function messages(): array
    {
        return [
            'telefono.regex' => 'El telefono solo puede contener numeros.',
            'telefono.unique' => 'Ya tienes un paciente con ese telefono. El telefono es su identificador unico.',
            'fecha_nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
        ];
    }

    public function pacienteId(): ?int
    {
        $paciente = $this->route('paciente');

        return $paciente instanceof Paciente ? $paciente->id : null;
    }
}
