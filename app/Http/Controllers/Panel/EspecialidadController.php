<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\EspecialidadRequest;
use App\Models\Especialidad;
use App\Services\ContenidoWebService;
use App\Services\SanitizadorHtml;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EspecialidadController extends Controller
{
    private const SUGERENCIAS = [
        ['Ansiedad y estres', 'fa-cloud-sun'],
        ['Depresion y estado de animo', 'fa-face-smile'],
        ['Autoestima y desarrollo personal', 'fa-seedling'],
        ['Terapia de pareja', 'fa-user-group'],
        ['Duelo y perdidas', 'fa-shield-heart'],
        ['Problemas de sueno', 'fa-moon'],
    ];

    public function __construct(private SanitizadorHtml $sanitizador)
    {
    }

    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        return view('panel.especialidades.index', [
            'especialidades' => Especialidad::buscar($filtros['q'] ?? null)->ordenados()->get(),
            'filtros' => $filtros,
            'iconos' => ContenidoWebService::ICONOS,
            'resumen' => [
                'total' => Especialidad::count(),
                'activas' => Especialidad::activas()->count(),
                'ocultas' => Especialidad::where('activo', false)->count(),
            ],
        ]);
    }

    public function store(EspecialidadRequest $request): RedirectResponse
    {
        Especialidad::create($this->campos($request) + ['orden' => Especialidad::siguienteOrden()]);

        return back()->with('exito', 'Especialidad creada correctamente.');
    }

    public function update(EspecialidadRequest $request, Especialidad $especialidad): RedirectResponse
    {
        $especialidad->update($this->campos($request));

        return back()->with('exito', 'Especialidad actualizada correctamente.');
    }

    public function activo(Especialidad $especialidad): RedirectResponse
    {
        $especialidad->update(['activo' => ! $especialidad->activo]);

        return back()->with('exito', $especialidad->activo
            ? 'La especialidad ya se ve en tu web.'
            : 'La especialidad se ha ocultado de tu web.');
    }

    public function mover(Especialidad $especialidad, string $direccion): RedirectResponse
    {
        if (! Especialidad::intercambiar($especialidad, $direccion)) {
            return back();
        }

        return back()->with('exito', 'Orden actualizado.');
    }

    public function destroy(Especialidad $especialidad): RedirectResponse
    {
        $especialidad->delete();
        Especialidad::renumerar();

        return back()->with('exito', 'Especialidad eliminada definitivamente.');
    }

    public function ejemplos(): RedirectResponse
    {
        if (Especialidad::exists()) {
            return back()->with('error', 'Solo se pueden cargar las sugerencias cuando no tienes ninguna especialidad.');
        }

        foreach (self::SUGERENCIAS as $indice => [$nombre, $icono]) {
            Especialidad::create([
                'nombre' => $nombre,
                'icono' => $icono,
                'orden' => $indice + 1,
                'activo' => true,
            ]);
        }

        return back()->with('exito', count(self::SUGERENCIAS) . ' especialidades anadidas. Editalas a tu gusto.');
    }

    private function campos(EspecialidadRequest $request): array
    {
        $datos = $request->validated();

        return [
            'nombre' => $datos['nombre'],
            'descripcion' => $this->sanitizador->limpiar($datos['descripcion'] ?? null) ?: null,
            'icono' => $datos['icono'] ?? null,
            'activo' => $request->boolean('activo'),
        ];
    }
}
