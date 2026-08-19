<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\PacienteRequest;
use App\Models\Cita;
use App\Models\Paciente;
use App\Services\HistoriaService;
use App\Services\PacienteService;
use App\Services\SanitizadorHtml;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PacienteController extends Controller
{
    public function __construct(
        private PacienteService $pacientes,
        private HistoriaService $historias,
        private SanitizadorHtml $sanitizador
    ) {
    }

    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', Rule::in(array_keys(Paciente::ESTADOS))],
            'modalidad' => ['nullable', Rule::in(array_keys(Cita::MODALIDADES))],
            'origen' => ['nullable', Rule::in(array_keys(Paciente::ORIGENES))],
        ]);

        $pacientes = Paciente::buscar($filtros['q'] ?? null)
            ->when($filtros['estado'] ?? null, fn ($q, $estado) => $q->where('estado', $estado))
            ->when($filtros['origen'] ?? null, fn ($q, $origen) => $q->where('origen', $origen))
            ->when($filtros['modalidad'] ?? null, function ($q, $modalidad) {
                $q->whereHas('citas', fn ($cita) => $cita->where('modalidad', $modalidad));
            })
            ->withCount('citas')
            ->addSelect(['historias_count' => DB::table('historias')
                ->selectRaw('count(*)')
                ->whereColumn('paciente_id', 'pacientes.id')
                ->whereNull('deleted_at')])
            ->orderBy('nombre')
            ->orderBy('apellidos')
            ->paginate(15)
            ->withQueryString();

        return view('panel.pacientes.index', [
            'pacientes' => $pacientes,
            'agenda' => $this->pacientes->resumenDeCitas($pacientes->getCollection()),
            'filtros' => $filtros,
            'estados' => Paciente::ESTADOS,
            'modalidades' => Cita::MODALIDADES,
            'origenes' => Paciente::ORIGENES,
            'resumen' => $this->resumen(),
        ]);
    }

    public function create(): View
    {
        return view('panel.pacientes.formulario', ['paciente' => new Paciente()]);
    }

    public function store(PacienteRequest $request): RedirectResponse
    {
        $paciente = Paciente::create($this->campos($request) + ['origen' => 'manual']);

        return redirect()
            ->route('panel.pacientes.show', $paciente)
            ->with('exito', 'Paciente creado correctamente.');
    }

    public function show(Paciente $paciente): View
    {
        $citas = $paciente->citas()
            ->orderByDesc('fecha')
            ->orderByDesc('hora_inicio')
            ->get();

        $hoy = now()->toDateString();

        return view('panel.pacientes.show', [
            'paciente' => $paciente,
            'citas' => $citas,
            'proxima' => $citas->filter(fn (Cita $cita) => $cita->fecha->toDateString() >= $hoy && $cita->estado !== 'cancelada')->last(),
            'historias' => $paciente->historias()->ordenadas()->limit(3)->get(),
            'estadisticas' => [
                'total' => $citas->count(),
                'completadas' => $citas->where('estado', 'completada')->count(),
                'canceladas' => $citas->whereIn('estado', ['cancelada', 'no_asistio'])->count(),
                'historias' => $paciente->historias()->count(),
            ],
        ]);
    }

    public function edit(Paciente $paciente): View
    {
        return view('panel.pacientes.formulario', ['paciente' => $paciente]);
    }

    public function update(PacienteRequest $request, Paciente $paciente): RedirectResponse
    {
        $paciente->update($this->campos($request));

        return redirect()
            ->route('panel.pacientes.show', $paciente)
            ->with('exito', 'Ficha del paciente actualizada.');
    }

    public function destroy(Paciente $paciente): RedirectResponse
    {
        $nombre = $paciente->nombre_completo;
        $citas = $paciente->citas()->count();
        $historias = $paciente->historias()->count();

        // La base de datos arrastra citas, historias y adjuntos en cascada, pero los ficheros no.
        $this->historias->borrarArchivosDelPaciente($paciente->id);

        $paciente->forceDelete();

        return redirect()
            ->route('panel.pacientes.index')
            ->with('exito', $this->mensajeBorrado($nombre, $citas, $historias));
    }

    private function mensajeBorrado(string $nombre, int $citas, int $historias): string
    {
        if ($citas === 0 && $historias === 0) {
            return sprintf('%s se ha eliminado definitivamente.', $nombre);
        }

        $borrado = [];

        if ($citas > 0) {
            $borrado[] = $citas . ' cita(s)';
        }

        if ($historias > 0) {
            $borrado[] = $historias . ' entrada(s) de historia';
        }

        return sprintf('%s se ha eliminado definitivamente junto con %s.', $nombre, implode(' y ', $borrado));
    }

    public function buscar(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        return response()->json([
            'pacientes' => $this->pacientes->sugerencias($datos['q'] ?? null)->all(),
        ]);
    }

    private function campos(PacienteRequest $request): array
    {
        $datos = $request->validated();

        return [
            'nombre' => $datos['nombre'],
            'apellidos' => $datos['apellidos'] ?? null,
            'telefono' => $datos['telefono'],
            'email' => $datos['email'] ?? null,
            'fecha_nacimiento' => $datos['fecha_nacimiento'] ?? null,
            'genero' => $datos['genero'] ?? null,
            'dni' => $datos['dni'] ?? null,
            'direccion' => $datos['direccion'] ?? null,
            'estado' => $datos['estado'],
            'motivo_consulta' => $this->sanitizador->limpiar($datos['motivo_consulta'] ?? null) ?: null,
            'notas' => $this->sanitizador->limpiar($datos['notas'] ?? null) ?: null,
        ];
    }

    private function resumen(): array
    {
        return [
            'total' => Paciente::count(),
            'activos' => Paciente::where('estado', 'activo')->count(),
            'nuevos' => Paciente::where('created_at', '>=', now()->startOfMonth())->count(),
            'citas_hoy' => Cita::activas()->whereDate('fecha', now()->toDateString())->count(),
        ];
    }
}
