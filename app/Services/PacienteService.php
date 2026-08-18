<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\Paciente;
use Illuminate\Support\Collection;

class PacienteService
{
    public function buscarOCrear(string $telefono, array $datos = []): Paciente
    {
        $normalizado = Paciente::normalizarTelefono($telefono);

        $paciente = Paciente::withTrashed()->where('telefono', $normalizado)->first();

        if (! $paciente) {
            return Paciente::create(array_filter([
                'telefono' => $normalizado,
                'nombre' => $datos['nombre'] ?? 'Paciente',
                'apellidos' => $datos['apellidos'] ?? null,
                'email' => $datos['email'] ?? null,
                'motivo_consulta' => $datos['motivo_consulta'] ?? null,
                'origen' => $datos['origen'] ?? 'manual',
            ], fn ($valor) => $valor !== null));
        }

        if ($paciente->trashed()) {
            $paciente->restore();
        }

        $paciente->fill(array_filter([
            'nombre' => $datos['nombre'] ?? null,
            'apellidos' => $datos['apellidos'] ?? null,
            'email' => $datos['email'] ?? null,
        ], fn ($valor) => filled($valor)))->save();

        return $paciente;
    }

    public function sugerencias(?string $texto, int $limite = 8): Collection
    {
        if (blank($texto) || mb_strlen(trim($texto)) < 2) {
            return collect();
        }

        return Paciente::buscarNombre(trim($texto))
            ->withCount('citas')
            ->orderBy('nombre')
            ->limit($limite)
            ->get()
            ->map(fn (Paciente $paciente) => [
                'id' => $paciente->id,
                'nombre' => $paciente->nombre_completo,
                'nombre_pila' => $paciente->nombre,
                'apellidos' => $paciente->apellidos,
                'telefono' => $paciente->telefono,
                'email' => $paciente->email,
                'iniciales' => $paciente->iniciales,
                'citas' => $paciente->citas_count,
                'estado' => $paciente->estado_legible,
            ]);
    }

    public function resumenDeCitas(Collection $pacientes): array
    {
        $ids = $pacientes->pluck('id')->all();

        if (empty($ids)) {
            return [];
        }

        $hoy = now()->toDateString();

        $citas = Cita::whereIn('paciente_id', $ids)
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->get()
            ->groupBy('paciente_id');

        $resumen = [];

        foreach ($ids as $id) {
            $suyas = $citas->get($id, collect());

            $resumen[$id] = [
                'total' => $suyas->count(),
                'ultima' => $suyas->filter(fn (Cita $cita) => $cita->fecha->toDateString() < $hoy)->last(),
                'proxima' => $suyas
                    ->filter(fn (Cita $cita) => $cita->fecha->toDateString() >= $hoy && $cita->estado !== 'cancelada')
                    ->first(),
                'modalidad' => $this->modalidadHabitual($suyas),
            ];
        }

        return $resumen;
    }

    private function modalidadHabitual(Collection $citas): ?string
    {
        if ($citas->isEmpty()) {
            return null;
        }

        return $citas->countBy('modalidad')->sortDesc()->keys()->first();
    }
}
