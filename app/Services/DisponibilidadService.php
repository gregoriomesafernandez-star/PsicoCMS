<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\ConfiguracionDisponibilidad;
use App\Models\FranjaHoraria;
use App\Models\PeriodoVacaciones;
use Illuminate\Support\Carbon;

class DisponibilidadService
{
    public function configuracion(): ConfiguracionDisponibilidad
    {
        return ConfiguracionDisponibilidad::actual();
    }

    public function duracion(string $modalidad): int
    {
        $config = $this->configuracion();

        return $modalidad === 'online' ? $config->duracion_online : $config->duracion_presencial;
    }

    public function descanso(string $modalidad): int
    {
        $config = $this->configuracion();

        if (! $config->{'descanso_' . $modalidad . '_activo'}) {
            return 0;
        }

        return $modalidad === 'online' ? $config->descanso_online : $config->descanso_presencial;
    }

    public function paso(string $modalidad): int
    {
        return $this->duracion($modalidad) + $this->descanso($modalidad);
    }

    public function huecosPosibles(string $modalidad): array
    {
        $config = $this->configuracion();
        $paso = $this->paso($modalidad);
        $duracion = $this->duracion($modalidad);

        if ($paso <= 0) {
            return [];
        }

        $inicio = $this->hora($config->hora_inicio);
        $limite = $this->hora($config->hora_fin);
        $huecos = [];

        while ($inicio->copy()->addMinutes($duracion)->lessThanOrEqualTo($limite)) {
            $huecos[] = $inicio->format('H:i');
            $inicio->addMinutes($paso);
        }

        $ultimo = $this->huecoFinalAprovechado($huecos, $duracion, $limite);

        if ($ultimo) {
            $huecos[] = $ultimo;
        }

        return $huecos;
    }

    private function huecoFinalAprovechado(array $huecos, int $duracion, Carbon $limite): ?string
    {
        if (empty($huecos)) {
            return null;
        }

        $candidato = Carbon::createFromFormat('H:i', end($huecos))->addMinutes($duracion);

        if ($candidato->copy()->addMinutes($duracion)->greaterThan($limite)) {
            return null;
        }

        return $candidato->format('H:i');
    }

    public function huecosDeDia(Carbon $fecha, string $modalidad, ?int $excluirCitaId = null): array
    {
        if ($this->configuracion()->modo_vacaciones) {
            return [];
        }

        if ($this->enVacaciones($fecha)) {
            return [];
        }

        $franjas = FranjaHoraria::modalidad($modalidad)
            ->where('dia_semana', $fecha->dayOfWeek)
            ->pluck('hora_inicio')
            ->map(fn (string $hora) => substr($hora, 0, 5))
            ->all();

        if (empty($franjas)) {
            return [];
        }

        $posibles = $this->huecosPosibles($modalidad);
        $duracion = $this->duracion($modalidad);
        $ocupadas = $this->citasDelDia($fecha, $excluirCitaId);
        $ahora = now();
        $huecos = [];

        foreach ($franjas as $hora) {
            if (! in_array($hora, $posibles, true)) {
                continue;
            }

            $inicio = $this->momento($fecha, $hora);
            $fin = $inicio->copy()->addMinutes($duracion);

            if ($inicio->lessThan($ahora)) {
                continue;
            }

            if ($this->solapaConAlguna($inicio, $fin, $ocupadas)) {
                continue;
            }

            $huecos[] = $hora;
        }

        sort($huecos);

        return $huecos;
    }

    public function estaDisponible(Carbon $fecha, string $hora, string $modalidad, ?int $excluirCitaId = null): bool
    {
        return in_array(
            substr($hora, 0, 5),
            $this->huecosDeDia($fecha, $modalidad, $excluirCitaId),
            true
        );
    }

    public function haySolapamiento(Carbon $fecha, string $hora, string $modalidad, ?int $excluirCitaId = null): bool
    {
        $inicio = $this->momento($fecha, $hora);
        $fin = $inicio->copy()->addMinutes($this->duracion($modalidad));

        return $this->solapaConAlguna($inicio, $fin, $this->citasDelDia($fecha, $excluirCitaId));
    }

    public function rangoLibre(Carbon $fecha, string $hora, int $duracion, ?int $excluirCitaId = null): bool
    {
        $inicio = $this->momento($fecha, $hora);
        $fin = $inicio->copy()->addMinutes($duracion);

        return ! $this->solapaConAlguna($inicio, $fin, $this->citasDelDia($fecha, $excluirCitaId));
    }

    public function dentroDelHorario(string $hora, int $duracion): bool
    {
        $config = $this->configuracion();
        $inicio = $this->hora($hora);
        $fin = $inicio->copy()->addMinutes($duracion);

        return $inicio->greaterThanOrEqualTo($this->hora($config->hora_inicio))
            && $fin->lessThanOrEqualTo($this->hora($config->hora_fin));
    }

    public function enVacaciones(Carbon $fecha): bool
    {
        return PeriodoVacaciones::whereDate('fecha_inicio', '<=', $fecha->toDateString())
            ->whereDate('fecha_fin', '>=', $fecha->toDateString())
            ->exists();
    }

    public function diasBloqueados(Carbon $desde, Carbon $hasta): array
    {
        $bloqueados = [];
        $cursor = $desde->copy()->startOfDay();

        if ($this->configuracion()->modo_vacaciones) {
            while ($cursor->lessThanOrEqualTo($hasta)) {
                $bloqueados[] = $cursor->toDateString();
                $cursor->addDay();
            }

            return $bloqueados;
        }

        $periodos = PeriodoVacaciones::whereDate('fecha_fin', '>=', $desde->toDateString())
            ->whereDate('fecha_inicio', '<=', $hasta->toDateString())
            ->get();

        while ($cursor->lessThanOrEqualTo($hasta)) {
            foreach ($periodos as $periodo) {
                if ($periodo->contiene($cursor)) {
                    $bloqueados[] = $cursor->toDateString();
                    break;
                }
            }

            $cursor->addDay();
        }

        return $bloqueados;
    }

    public function horaFin(string $hora, string $modalidad): string
    {
        return Carbon::createFromFormat('H:i', substr($hora, 0, 5))
            ->addMinutes($this->duracion($modalidad))
            ->format('H:i:s');
    }

    public function franjasPorDia(string $modalidad): array
    {
        return FranjaHoraria::modalidad($modalidad)
            ->get()
            ->groupBy('dia_semana')
            ->map(fn ($franjas) => $franjas->map(fn ($franja) => substr($franja->hora_inicio, 0, 5))->all())
            ->all();
    }

    public function tieneFranjas(Carbon $fecha, string $modalidad): bool
    {
        return FranjaHoraria::modalidad($modalidad)
            ->where('dia_semana', $fecha->dayOfWeek)
            ->exists();
    }

    public function diasConDisponibilidad(string $modalidad): array
    {
        $dias = FranjaHoraria::modalidad($modalidad)
            ->distinct()
            ->pluck('dia_semana')
            ->all();

        $nombres = [];

        foreach (self::DIAS as $indice => $nombre) {
            if (in_array($indice, $dias)) {
                $nombres[] = $nombre;
            }
        }

        return $nombres;
    }

    public function resumenSemanal(string $modalidad): array
    {
        $franjas = $this->franjasPorDia($modalidad);
        $resumen = [];

        foreach (self::DIAS as $indice => $nombre) {
            $horas = $franjas[$indice] ?? [];
            sort($horas);

            $resumen[$indice] = [
                'nombre' => $nombre,
                'horas' => $horas,
                'desde' => $horas[0] ?? null,
                'hasta' => $horas ? end($horas) : null,
            ];
        }

        return $resumen;
    }

    public const DIAS = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miercoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sabado',
        0 => 'Domingo',
    ];

    private function citasDelDia(Carbon $fecha, ?int $excluirCitaId): array
    {
        return Cita::activas()
            ->whereDate('fecha', $fecha->toDateString())
            ->when($excluirCitaId, fn ($query) => $query->where('id', '!=', $excluirCitaId))
            ->get(['fecha', 'hora_inicio', 'hora_fin'])
            ->map(fn (Cita $cita) => [
                'inicio' => $this->momento($fecha, $cita->hora_inicio),
                'fin' => $this->momento($fecha, $cita->hora_fin),
            ])
            ->all();
    }

    private function solapaConAlguna(Carbon $inicio, Carbon $fin, array $ocupadas): bool
    {
        foreach ($ocupadas as $ocupada) {
            if ($inicio->lessThan($ocupada['fin']) && $fin->greaterThan($ocupada['inicio'])) {
                return true;
            }
        }

        return false;
    }

    private function hora(string $valor): Carbon
    {
        return Carbon::createFromFormat('H:i:s', substr($valor, 0, 5) . ':00');
    }

    private function momento(Carbon $fecha, string $hora): Carbon
    {
        return Carbon::createFromFormat(
            'Y-m-d H:i',
            $fecha->toDateString() . ' ' . substr($hora, 0, 5)
        );
    }
}
