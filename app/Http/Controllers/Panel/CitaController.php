<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\CitaRequest;
use App\Models\Cita;
use App\Models\Paciente;
use App\Services\DisponibilidadService;
use App\Services\PacienteService;
use App\Services\SanitizadorHtml;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CitaController extends Controller
{
    public function __construct(
        private DisponibilidadService $disponibilidad,
        private SanitizadorHtml $sanitizador,
        private PacienteService $pacientes
    ) {
    }

    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date'],
            'modalidad' => ['nullable', Rule::in(array_keys(Cita::MODALIDADES))],
            'estado' => ['nullable', Rule::in(array_keys(Cita::ESTADOS))],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $citas = Cita::with('paciente')
            ->when($filtros['desde'] ?? null, fn ($q, $desde) => $q->whereDate('fecha', '>=', $desde))
            ->when($filtros['hasta'] ?? null, fn ($q, $hasta) => $q->whereDate('fecha', '<=', $hasta))
            ->when($filtros['modalidad'] ?? null, fn ($q, $modalidad) => $q->where('modalidad', $modalidad))
            ->when($filtros['estado'] ?? null, fn ($q, $estado) => $q->where('estado', $estado))
            ->when($filtros['q'] ?? null, function ($q, $texto) {
                $q->where(function ($sub) use ($texto) {
                    $sub->where('motivo', 'like', "%{$texto}%")
                        ->orWhereHas('paciente', function ($paciente) use ($texto) {
                            $paciente->where('nombre', 'like', "%{$texto}%")
                                ->orWhere('apellidos', 'like', "%{$texto}%")
                                ->orWhere('telefono', 'like', "%{$texto}%");
                        });
                });
            })
            ->orderByDesc('fecha')
            ->orderByDesc('hora_inicio')
            ->paginate(15)
            ->withQueryString();

        return view('panel.citas.index', [
            'citas' => $citas,
            'filtros' => $filtros,
            'estados' => Cita::ESTADOS,
            'modalidades' => Cita::MODALIDADES,
            'config' => $this->disponibilidad->configuracion(),
            'resumen' => $this->resumen(),
        ]);
    }

    public function huecos(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'fecha' => ['required', 'date'],
            'modalidad' => ['required', Rule::in(array_keys(Cita::MODALIDADES))],
            'cita' => ['nullable', 'integer'],
        ]);

        $fecha = Carbon::parse($datos['fecha']);
        $modalidad = $datos['modalidad'];
        $huecos = $this->disponibilidad->huecosDeDia($fecha, $modalidad, $datos['cita'] ?? null);

        return response()->json([
            'huecos' => $huecos,
            'bloqueado' => $this->disponibilidad->enVacaciones($fecha),
            'modo_vacaciones' => (bool) $this->disponibilidad->configuracion()->modo_vacaciones,
            'duracion' => $this->disponibilidad->duracion($modalidad),
            'sin_franjas' => ! $this->disponibilidad->tieneFranjas($fecha, $modalidad),
            'dia_semana' => $fecha->locale('es')->dayName,
            'es_pasado' => $fecha->isPast() && ! $fecha->isToday(),
            'modalidad' => Cita::MODALIDADES[$modalidad],
            'dias_disponibles' => $this->disponibilidad->diasConDisponibilidad($modalidad),
            'url_disponibilidad' => route('panel.disponibilidad'),
        ]);
    }

    public function store(CitaRequest $request): RedirectResponse
    {
        $datos = $request->validated();

        $cita = DB::transaction(function () use ($datos) {
            $paciente = $this->buscarOCrearPaciente($datos);

            return Cita::create($this->camposCita($datos, $paciente->id) + ['origen' => 'manual']);
        });

        return $this->volver($request, $cita, 'Cita creada correctamente.');
    }

    public function update(CitaRequest $request, Cita $cita): RedirectResponse
    {
        $datos = $request->validated();

        DB::transaction(function () use ($datos, $cita) {
            $paciente = $this->buscarOCrearPaciente($datos);

            $cita->update($this->camposCita($datos, $paciente->id));
        });

        return $this->volver($request, $cita->fresh(), 'Cita actualizada correctamente.');
    }

    private function volver(CitaRequest $request, Cita $cita, string $mensaje): RedirectResponse
    {
        $destino = match ($request->input('volver')) {
            'calendario' => redirect()->route('panel.calendario'),
            'paciente' => redirect()->route('panel.pacientes.show', $cita->paciente_id),
            // Se vuelve a la misma busqueda, no a una URL enviada por el cliente.
            'buscador' => redirect()->route('panel.buscador', [
                'q' => substr((string) $request->input('busqueda'), 0, 100),
            ]),
            default => redirect()->route('panel.citas.index'),
        };

        return $destino->with('exito', $mensaje);
    }

    public function cambiarEstado(Request $request, Cita $cita): RedirectResponse
    {
        $datos = $request->validate([
            'estado' => ['required', Rule::in(array_keys(Cita::ESTADOS))],
        ]);

        $cita->update($datos);

        return back()->with('exito', 'Estado actualizado a "' . Cita::ESTADOS[$datos['estado']] . '".');
    }

    public function destroy(Cita $cita): RedirectResponse
    {
        $cita->delete();

        return back()->with('exito', 'Cita eliminada definitivamente.');
    }

    private function buscarOCrearPaciente(array $datos): Paciente
    {
        return $this->pacientes->buscarOCrear($datos['paciente_telefono'], [
            'nombre' => $datos['paciente_nombre'],
            'apellidos' => $datos['paciente_apellidos'] ?? null,
            'email' => $datos['paciente_email'] ?? null,
            'origen' => 'manual',
        ]);
    }

    private function camposCita(array $datos, int $pacienteId): array
    {
        $duracion = $this->disponibilidad->duracion($datos['modalidad']);

        return [
            'paciente_id' => $pacienteId,
            'modalidad' => $datos['modalidad'],
            'fecha' => $datos['fecha'],
            'hora_inicio' => $datos['hora_inicio'] . ':00',
            'hora_fin' => $this->disponibilidad->horaFin($datos['hora_inicio'], $datos['modalidad']),
            'duracion' => $duracion,
            'estado' => $datos['estado'],
            'motivo' => $this->sanitizador->limpiar($datos['motivo'] ?? null) ?: null,
            'notas' => $this->sanitizador->limpiar($datos['notas'] ?? null) ?: null,
        ];
    }

    private function resumen(): array
    {
        $hoy = now()->toDateString();

        return [
            'hoy' => Cita::activas()->whereDate('fecha', $hoy)->count(),
            'semana' => Cita::activas()
                ->whereBetween('fecha', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()])
                ->count(),
            'pendientes' => Cita::where('estado', 'pendiente')->whereDate('fecha', '>=', $hoy)->count(),
            'total' => Cita::count(),
        ];
    }
}
