<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\MoverCitaRequest;
use App\Models\Cita;
use App\Models\FranjaHoraria;
use App\Services\DisponibilidadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class CalendarioController extends Controller
{
    private const HUECOS_EN_MENSAJE = 6;

    public function __construct(private DisponibilidadService $disponibilidad)
    {
    }

    public function index(): View
    {
        $config = $this->disponibilidad->configuracion();

        return view('panel.calendario.index', [
            'config' => $config,
            'hayFranjas' => FranjaHoraria::exists(),
            'horarioLaboral' => $this->horarioLaboral(),
            'horaMinima' => $this->momentoHora($config->hora_inicio)->format('H:i:s'),
            'horaMaxima' => $this->momentoHora($config->hora_fin)->format('H:i:s'),
        ]);
    }

    public function eventos(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'desde' => ['required', 'date'],
            'hasta' => ['required', 'date'],
        ]);

        $desde = Carbon::parse($datos['desde'])->startOfDay();
        $hasta = Carbon::parse($datos['hasta'])->startOfDay();

        $citas = Cita::with('paciente')
            ->whereDate('fecha', '>=', $desde->toDateString())
            ->whereDate('fecha', '<', $hasta->toDateString())
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->get()
            ->map(fn (Cita $cita) => $this->evento($cita))
            ->all();

        return response()->json(array_merge($citas, $this->fondos($desde, $hasta)));
    }

    public function mover(MoverCitaRequest $request, Cita $cita): JsonResponse
    {
        $datos = $request->validated();

        $fecha = Carbon::parse($datos['fecha']);
        $hora = $datos['hora_inicio'];
        $duracion = (int) $datos['duracion'];
        $config = $this->disponibilidad->configuracion();

        if (! $this->disponibilidad->dentroDelHorario($hora, $duracion)) {
            return $this->rechazar(sprintf(
                'La cita se saldria de tu horario de consulta (%s - %s).',
                substr($config->hora_inicio, 0, 5),
                substr($config->hora_fin, 0, 5)
            ));
        }

        $duracionConfigurada = $this->disponibilidad->duracion($cita->modalidad);

        if ($duracion === $duracionConfigurada) {
            if (! $this->disponibilidad->estaDisponible($fecha, $hora, $cita->modalidad, $cita->id)) {
                return $this->rechazar($this->motivoNoDisponible($fecha, $hora, $cita));
            }
        } elseif (! $this->disponibilidad->rangoLibre($fecha, $hora, $duracion, $cita->id)) {
            return $this->rechazar('Con esa duracion la cita se solaparia con otra.');
        }

        $cita->update([
            'fecha' => $fecha->toDateString(),
            'hora_inicio' => $hora . ':00',
            'hora_fin' => Carbon::createFromFormat('H:i', $hora)->addMinutes($duracion)->format('H:i:s'),
            'duracion' => $duracion,
        ]);

        return response()->json([
            'ok' => true,
            'mensaje' => sprintf(
                'Cita movida al %s a las %s.',
                $fecha->locale('es')->isoFormat('dddd D [de] MMMM'),
                $hora
            ),
            'evento' => $this->evento($cita->fresh('paciente')),
        ]);
    }

    private function rechazar(string $mensaje): JsonResponse
    {
        return response()->json(['ok' => false, 'mensaje' => $mensaje], 422);
    }

    private function motivoNoDisponible(Carbon $fecha, string $hora, Cita $cita): string
    {
        if ($this->disponibilidad->configuracion()->modo_vacaciones) {
            return 'Tienes el modo vacaciones activado: desactivalo para reorganizar tus citas.';
        }

        if ($this->disponibilidad->enVacaciones($fecha)) {
            return 'Ese dia esta dentro de un periodo de vacaciones.';
        }

        $modalidad = mb_strtolower($cita->modalidad_legible);

        if (! $this->disponibilidad->tieneFranjas($fecha, $cita->modalidad)) {
            return sprintf('No atiendes %s los %s.', $modalidad, $fecha->locale('es')->dayName);
        }

        $huecos = $this->disponibilidad->huecosDeDia($fecha, $cita->modalidad, $cita->id);

        if (empty($huecos)) {
            return sprintf('Ese %s ya tienes todas las horas %s ocupadas o pasadas.', $fecha->locale('es')->dayName, $modalidad);
        }

        $listado = implode(', ', array_slice($huecos, 0, self::HUECOS_EN_MENSAJE));

        if (count($huecos) > self::HUECOS_EN_MENSAJE) {
            $listado .= '...';
        }

        return sprintf('Las %s no es un hueco libre. Huecos %s: %s', $hora, $modalidad, $listado);
    }

    private function evento(Cita $cita): array
    {
        $fecha = $cita->fecha->toDateString();

        return [
            'id' => (string) $cita->id,
            'title' => $cita->paciente?->nombre_completo ?: 'Sin paciente',
            'start' => $fecha . 'T' . $cita->hora_inicio,
            'end' => $fecha . 'T' . $cita->hora_fin,
            'classNames' => [
                'cita-evento',
                'cita-evento--' . $cita->modalidad,
                'cita-evento--' . $cita->estado,
            ],
            'extendedProps' => [
                'fecha' => $fecha,
                'horaInicio' => $cita->hora_corta,
                'horaFin' => $cita->hora_fin_corta,
                'duracion' => $cita->duracion,
                'modalidad' => $cita->modalidad,
                'modalidadLegible' => $cita->modalidad_legible,
                'estado' => $cita->estado,
                'estadoLegible' => $cita->estado_legible,
                'tono' => $cita->tono_estado,
                'pacienteId' => $cita->paciente_id,
                'pacienteNombre' => $cita->paciente?->nombre,
                'pacienteApellidos' => $cita->paciente?->apellidos,
                'pacienteCompleto' => $cita->paciente?->nombre_completo,
                'pacienteTelefono' => $cita->paciente?->telefono,
                'pacienteEmail' => $cita->paciente?->email,
                'pacienteIniciales' => $cita->paciente?->iniciales,
                'motivo' => $cita->motivo,
                'notas' => $cita->notas,
            ],
        ];
    }

    private function fondos(Carbon $desde, Carbon $hasta): array
    {
        $config = $this->disponibilidad->configuracion();

        $bloqueados = $config->modo_vacaciones
            ? []
            : $this->disponibilidad->diasBloqueados($desde, $hasta->copy()->subDay());

        $conFranjas = FranjaHoraria::distinct()
            ->pluck('dia_semana')
            ->map(fn ($dia) => (int) $dia)
            ->all();

        $fondos = [];
        $cursor = $desde->copy();

        while ($cursor->lessThan($hasta)) {
            $dia = $cursor->toDateString();

            if (in_array($dia, $bloqueados, true)) {
                $fondos = array_merge($fondos, $this->fondo($dia, 'dia-vacaciones', 'Vacaciones'));
            } elseif (! in_array($cursor->dayOfWeek, $conFranjas, true)) {
                $fondos = array_merge($fondos, $this->fondo($dia, 'dia-sin-horario', 'Sin consulta'));
            }

            $cursor->addDay();
        }

        return $fondos;
    }

    private function fondo(string $dia, string $clase, string $titulo): array
    {
        $base = [
            'display' => 'background',
            'classNames' => [$clase],
            'title' => $titulo,
        ];

        // La vista de mes solo pinta fondos de dia completo; las de semana y dia, solo los que llevan hora.
        return [
            $base + ['start' => $dia, 'allDay' => true],
            $base + ['start' => $dia . 'T00:00:00', 'end' => $dia . 'T23:59:59', 'allDay' => false],
        ];
    }

    private function horarioLaboral(): array
    {
        $config = $this->disponibilidad->configuracion();
        $cierre = $this->momentoHora($config->hora_fin);
        $duracion = max($config->duracion_online, $config->duracion_presencial);

        return FranjaHoraria::all()
            ->groupBy('dia_semana')
            ->map(function ($franjas, $dia) use ($cierre, $duracion) {
                $horas = $franjas->map(fn (FranjaHoraria $franja) => substr($franja->hora_inicio, 0, 5))->sort()->values();
                $fin = Carbon::createFromFormat('H:i', $horas->last())->addMinutes($duracion);

                return [
                    'daysOfWeek' => [(int) $dia],
                    'startTime' => $horas->first(),
                    'endTime' => $fin->greaterThan($cierre) ? $cierre->format('H:i') : $fin->format('H:i'),
                ];
            })
            ->values()
            ->all();
    }

    private function momentoHora(string $valor): Carbon
    {
        return Carbon::createFromFormat('H:i:s', substr($valor, 0, 5) . ':00');
    }

}
