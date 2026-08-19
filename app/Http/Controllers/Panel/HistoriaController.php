<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\HistoriaRequest;
use App\Models\Cita;
use App\Models\Historia;
use App\Models\Paciente;
use App\Services\HistoriaService;
use App\Services\SanitizadorHtml;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HistoriaController extends Controller
{
    public function __construct(
        private HistoriaService $historias,
        private SanitizadorHtml $sanitizador
    ) {
    }

    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'paciente' => ['nullable', 'integer', 'exists:pacientes,id'],
            'q' => ['nullable', 'string', 'max:100'],
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date'],
        ]);

        if (! empty($filtros['paciente'])) {
            return $this->historiaDe(Paciente::findOrFail($filtros['paciente']), $filtros);
        }

        $pacientes = Paciente::whereHas('historias')
            ->buscar($filtros['q'] ?? null)
            ->withCount('historias')
            ->addSelect(['ultima_sesion' => Historia::query()
                ->selectRaw('max(fecha_sesion)')
                ->whereColumn('paciente_id', 'pacientes.id')])
            ->orderByDesc('ultima_sesion')
            ->paginate(15)
            ->withQueryString();

        return view('panel.historias.index', [
            'pacientes' => $pacientes,
            'filtros' => $filtros,
            'resumen' => $this->resumen(null),
            'hayEntradas' => Historia::exists(),
        ]);
    }

    private function historiaDe(Paciente $paciente, array $filtros): View
    {
        $entradas = Historia::with('cita')
            ->withCount('adjuntos')
            ->where('paciente_id', $paciente->id)
            ->buscar($filtros['q'] ?? null)
            ->when($filtros['desde'] ?? null, fn ($q, $desde) => $q->whereDate('fecha_sesion', '>=', $desde))
            ->when($filtros['hasta'] ?? null, fn ($q, $hasta) => $q->whereDate('fecha_sesion', '<=', $hasta))
            ->ordenadas()
            ->paginate(10)
            ->withQueryString();

        return view('panel.historias.paciente', [
            'paciente' => $paciente,
            'entradas' => $entradas,
            'filtros' => $filtros,
            'resumen' => $this->resumen($paciente->id),
        ]);
    }

    public function create(Request $request): View
    {
        $datos = $request->validate([
            'paciente' => ['nullable', 'integer', 'exists:pacientes,id'],
            'cita' => ['nullable', 'integer', 'exists:citas,id'],
        ]);

        $historia = new Historia([
            'paciente_id' => isset($datos['paciente']) ? (int) $datos['paciente'] : null,
            'fecha_sesion' => now()->toDateString(),
        ]);

        $historia->cita_id = $this->citaDelPaciente($datos['cita'] ?? null, $historia->paciente_id);

        return $this->formulario($historia);
    }

    public function store(HistoriaRequest $request): RedirectResponse
    {
        $historia = DB::transaction(function () use ($request) {
            $historia = Historia::create($this->campos($request));

            $this->historias->guardarAdjuntos($historia, $request->file('adjuntos', []));

            return $historia;
        });

        return redirect()
            ->route('panel.historias.show', $historia)
            ->with('exito', 'Entrada guardada en la historia clinica.');
    }

    public function show(Historia $historia): View
    {
        return view('panel.historias.show', [
            'historia' => $historia->load(['paciente', 'cita', 'adjuntos']),
        ]);
    }

    public function edit(Historia $historia): View
    {
        return $this->formulario($historia->load(['paciente', 'adjuntos']));
    }

    public function update(HistoriaRequest $request, Historia $historia): RedirectResponse
    {
        DB::transaction(function () use ($request, $historia) {
            $historia->update($this->campos($request));

            $this->historias->borrarAdjuntosPorId($historia, $request->input('eliminar_adjuntos', []));
            $this->historias->guardarAdjuntos($historia, $request->file('adjuntos', []));
        });

        return redirect()
            ->route('panel.historias.show', $historia)
            ->with('exito', 'Entrada actualizada.');
    }

    public function destroy(Historia $historia): RedirectResponse
    {
        $paciente = $historia->paciente_id;

        $this->historias->borrarHistoria($historia->load('adjuntos'));

        return redirect()
            ->route('panel.historias.index', ['paciente' => $paciente])
            ->with('exito', 'Entrada eliminada definitivamente.');
    }

    public function citasDelPaciente(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'paciente' => ['required', 'integer', 'exists:pacientes,id'],
        ]);

        return response()->json([
            'citas' => $this->citasDe($datos['paciente'])
                ->map(fn (Cita $cita) => [
                    'id' => $cita->id,
                    'texto' => $this->textoDeCita($cita),
                ])
                ->values(),
        ]);
    }

    private function formulario(Historia $historia): View
    {
        return view('panel.historias.formulario', [
            'historia' => $historia,
            'pacientes' => Paciente::paraSelector(),
            'citas' => $this->opcionesDeCitas($historia->paciente_id),
        ]);
    }

    private function opcionesDeCitas(?int $pacienteId): array
    {
        if (! $pacienteId) {
            return [];
        }

        return $this->citasDe($pacienteId)
            ->mapWithKeys(fn (Cita $cita) => [$cita->id => $this->textoDeCita($cita)])
            ->all();
    }

    private function citasDe(int $pacienteId)
    {
        return Cita::where('paciente_id', $pacienteId)
            ->orderByDesc('fecha')
            ->orderByDesc('hora_inicio')
            ->get();
    }

    private function textoDeCita(Cita $cita): string
    {
        return sprintf(
            '%s a las %s - %s',
            $cita->fecha->format('d/m/Y'),
            $cita->hora_corta,
            $cita->modalidad_legible
        );
    }

    private function citaDelPaciente(?int $citaId, ?int $pacienteId): ?int
    {
        if (! $citaId || ! $pacienteId) {
            return null;
        }

        return Cita::where('id', $citaId)->where('paciente_id', $pacienteId)->exists() ? $citaId : null;
    }

    private function campos(HistoriaRequest $request): array
    {
        $datos = $request->validated();

        return [
            'paciente_id' => $datos['paciente_id'],
            'cita_id' => $datos['cita_id'] ?? null,
            'fecha_sesion' => $datos['fecha_sesion'],
            'titulo' => $datos['titulo'] ?? null,
            'contenido' => $this->sanitizador->limpiar($datos['contenido'] ?? null) ?: null,
        ];
    }

    private function resumen(?int $pacienteId): array
    {
        $entradas = Historia::delPaciente($pacienteId);

        return [
            'total' => (clone $entradas)->count(),
            'mes' => (clone $entradas)->whereDate('fecha_sesion', '>=', now()->startOfMonth())->count(),
            'pacientes' => Historia::distinct()->count('paciente_id'),
            'adjuntos' => DB::table('historia_adjuntos')
                ->join('historias', 'historias.id', '=', 'historia_adjuntos.historia_id')
                ->whereNull('historias.deleted_at')
                ->when($pacienteId, fn ($q) => $q->where('historias.paciente_id', $pacienteId))
                ->count(),
        ];
    }
}
