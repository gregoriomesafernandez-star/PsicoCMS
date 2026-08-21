<?php

namespace App\Http\Requests\Publico;

use App\Models\Cita;
use App\Models\Paciente;
use App\Services\ReservaService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ReservaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** El telefono se limpia antes de validar: es el identificador del paciente. */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'telefono' => Paciente::normalizarTelefono((string) $this->input('telefono')),
        ]);
    }

    public function rules(): array
    {
        return [
            'modalidad' => ['required', Rule::in(array_keys(Cita::MODALIDADES))],
            'fecha' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'hora' => ['required', 'date_format:H:i'],
            'nombre' => ['required', 'string', 'max:100'],
            'telefono' => ['required', 'digits_between:6,15'],
            'motivo' => ['nullable', 'string', 'max:500'],
            'consentimiento' => ['accepted'],
        ];
    }

    public function attributes(): array
    {
        return [
            'modalidad' => 'tipo de sesion',
            'fecha' => 'dia',
            'hora' => 'hora',
            'nombre' => 'nombre',
            'telefono' => 'telefono',
            'motivo' => 'motivo de la consulta',
        ];
    }

    public function messages(): array
    {
        return [
            'modalidad.required' => 'Elige si prefieres sesion online o presencial.',
            'fecha.required' => 'Elige un dia en el calendario.',
            'fecha.after_or_equal' => 'Ese dia ya ha pasado. Elige otro.',
            'hora.required' => 'Elige una hora.',
            'telefono.digits_between' => 'Escribe tu telefono solo con numeros.',
            'consentimiento.accepted' => 'Necesito tu permiso para guardar tus datos y darte la cita.',
        ];
    }

    /**
     * La hora tiene que seguir libre. Se comprueba tambien al guardar, dentro
     * de la transaccion; esto es para que el paciente vea el motivo en el
     * formulario en vez de un error seco.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $reservas = app(ReservaService::class);

            if ($reservas->enModoVacaciones()) {
                $validator->errors()->add('fecha', 'Ahora mismo la agenda esta cerrada.');

                return;
            }

            $libres = $reservas->huecos(
                $this->input('modalidad'),
                Carbon::parse($this->input('fecha'))
            );

            if (! in_array($this->input('hora'), $libres, true)) {
                $validator->errors()->add('hora', 'Esa hora ya no esta libre. Elige otra, por favor.');
            }
        });
    }
}
