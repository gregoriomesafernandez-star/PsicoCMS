<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\ConfiguracionDisponibilidad;
use App\Support\HuecoOcupado;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Reserva publica de citas. No calcula disponibilidad por su cuenta: se apoya
 * en DisponibilidadService, que es el unico que sabe de huecos, vacaciones y
 * solapamientos.
 */
class ReservaService
{
    /**
     * Hasta cuantos meses vista se puede reservar, contando el actual. Hay un
     * limite porque cada mes que se pinta consulta la agenda dia a dia, y
     * porque ofrecer un año entero invita a citas que nadie recuerda.
     */
    public const MESES_VISIBLES = 6;

    public function __construct(
        private DisponibilidadService $disponibilidad,
        private PacienteService $pacientes,
        private NotificacionService $notificaciones
    ) {
    }

    public function configuracion(): ConfiguracionDisponibilidad
    {
        return $this->disponibilidad->configuracion();
    }

    public function enModoVacaciones(): bool
    {
        return (bool) $this->configuracion()->modo_vacaciones;
    }

    public function mensajeVacaciones(): string
    {
        $config = $this->configuracion();
        $mensaje = $config->mensaje_vacaciones
            ?: 'Estare de vuelta pronto. Puedes escribirme y te aviso en cuanto abra la agenda.';

        if ($config->vuelvo_el) {
            $mensaje .= ' Vuelvo el ' . Carbon::parse($config->vuelvo_el)->translatedFormat('j \d\e F') . '.';
        }

        return $mensaje;
    }

    /**
     * Orden en que se ofrecen al paciente. Se declara aqui y no se toca
     * Cita::MODALIDADES, que ordena tambien los filtros del panel.
     */
    public const ORDEN = ['presencial', 'online'];

    /** Solo las modalidades con horario marcado: ofrecer una vacia seria enganar. */
    public function modalidades(): array
    {
        $disponibles = [];

        foreach (self::ORDEN as $clave) {
            $nombre = Cita::MODALIDADES[$clave];

            if ($this->disponibilidad->franjasPorDia($clave) !== []) {
                $disponibles[$clave] = [
                    'nombre' => $nombre,
                    'duracion' => $this->disponibilidad->duracion($clave),
                    'dias' => $this->disponibilidad->diasConDisponibilidad($clave),
                ];
            }
        }

        return $disponibles;
    }

    public function hayDisponibilidad(): bool
    {
        return ! $this->enModoVacaciones() && $this->modalidades() !== [];
    }

    /** Primer y ultimo mes que el calendario puede mostrar. */
    public function mesLimite(): string
    {
        return now()->startOfMonth()->addMonths(self::MESES_VISIBLES - 1)->format('Y-m');
    }

    /**
     * Dias del mes con al menos un hueco libre. Se devuelve el mes entero para
     * que el calendario pueda pintar tambien los que estan cerrados.
     *
     * @return array<string, bool>
     */
    public function diasDelMes(string $modalidad, Carbon $mes): array
    {
        $cursor = $mes->copy()->startOfMonth();
        $fin = $mes->copy()->endOfMonth();
        $dias = [];

        while ($cursor->lessThanOrEqualTo($fin)) {
            $dias[$cursor->toDateString()] = $this->disponibilidad->huecosDeDia($cursor, $modalidad) !== [];
            $cursor->addDay();
        }

        return $dias;
    }

    /** @return array<int, string> */
    public function huecos(string $modalidad, Carbon $fecha): array
    {
        return $this->disponibilidad->huecosDeDia($fecha, $modalidad);
    }

    /**
     * Crea la cita y, si hace falta, la ficha del paciente.
     *
     * La disponibilidad se vuelve a comprobar **dentro** de la transaccion y
     * con las citas del dia bloqueadas: entre pintar el formulario y enviarlo
     * puede haber pasado un rato, y dos personas pueden estar a la vez en el
     * mismo hueco.
     *
     * @throws HuecoOcupado
     */
    public function reservar(array $datos): Cita
    {
        $fecha = Carbon::parse($datos['fecha'])->startOfDay();
        $modalidad = $datos['modalidad'];
        $hora = $datos['hora'];

        return DB::transaction(function () use ($datos, $fecha, $modalidad, $hora) {
            Cita::where('fecha', $fecha->toDateString())->lockForUpdate()->get();

            if (! $this->disponibilidad->estaDisponible($fecha, $hora, $modalidad)) {
                throw new HuecoOcupado();
            }

            $paciente = $this->pacientes->buscarOCrear($datos['telefono'], [
                'nombre' => $datos['nombre'],
                'motivo_consulta' => $datos['motivo'] ?? null,
                'origen' => 'web',
            ]);

            return Cita::create([
                'paciente_id' => $paciente->id,
                'modalidad' => $modalidad,
                'fecha' => $fecha->toDateString(),
                'hora_inicio' => $hora . ':00',
                'hora_fin' => $this->disponibilidad->horaFin($hora, $modalidad),
                'duracion' => $this->disponibilidad->duracion($modalidad),
                'estado' => 'pendiente',
                'motivo' => $datos['motivo'] ?? null,
                'origen' => 'web',
            ]);
        });
    }

    /** Aviso a la psicologa. Nunca lanza: una reserva no puede caerse por el correo. */
    public function avisar(Cita $cita): void
    {
        if (! $this->notificaciones->configuracion()['avisar_citas']) {
            return;
        }

        $this->notificaciones->avisar(
            'Nueva cita: ' . $cita->paciente->nombre_completo,
            'publico.correo.nueva-cita',
            ['cita' => $cita->load('paciente')]
        );
    }

    /** Enlace de "anadir al calendario" de Google, sin API ni permisos. */
    public function enlaceGoogleCalendar(Cita $cita, string $nombrePsicologa, ?string $lugar): string
    {
        $inicio = Carbon::parse($cita->fecha->toDateString() . ' ' . $cita->hora_inicio);
        $fin = Carbon::parse($cita->fecha->toDateString() . ' ' . $cita->hora_fin);
        $formato = 'Ymd\THis';

        return 'https://calendar.google.com/calendar/render?' . http_build_query([
            'action' => 'TEMPLATE',
            'text' => 'Cita con ' . $nombrePsicologa,
            'dates' => $inicio->format($formato) . '/' . $fin->format($formato),
            'details' => 'Sesion ' . strtolower($cita->modalidad_legible) . ' de '
                . $cita->duracion . ' minutos.',
            'location' => $cita->modalidad === 'presencial' ? (string) $lugar : 'Online',
            'ctz' => config('app.timezone'),
        ]);
    }
}
