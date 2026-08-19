<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\ServicioRequest;
use App\Models\Servicio;
use App\Services\ContenidoWebService;
use App\Services\SanitizadorHtml;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServicioController extends Controller
{
    public function __construct(
        private SanitizadorHtml $sanitizador,
        private ContenidoWebService $contenido
    ) {
    }

    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        return view('panel.servicios.index', [
            'servicios' => Servicio::buscar($filtros['q'] ?? null)->ordenados()->get(),
            'filtros' => $filtros,
            'iconos' => ContenidoWebService::ICONOS,
            'resumen' => [
                'total' => Servicio::count(),
                'activos' => Servicio::activos()->count(),
                'ocultos' => Servicio::where('activo', false)->count(),
            ],
        ]);
    }

    public function store(ServicioRequest $request): RedirectResponse
    {
        $servicio = Servicio::create($this->campos($request) + ['orden' => Servicio::siguienteOrden()]);

        $this->guardarImagen($request, $servicio);

        return back()->with('exito', 'Servicio creado correctamente.');
    }

    public function update(ServicioRequest $request, Servicio $servicio): RedirectResponse
    {
        $servicio->update($this->campos($request));

        $this->guardarImagen($request, $servicio);

        return back()->with('exito', 'Servicio actualizado correctamente.');
    }

    public function activo(Servicio $servicio): RedirectResponse
    {
        $servicio->update(['activo' => ! $servicio->activo]);

        return back()->with('exito', $servicio->activo
            ? 'El servicio ya se ve en tu web.'
            : 'El servicio se ha ocultado de tu web.');
    }

    public function mover(Servicio $servicio, string $direccion): RedirectResponse
    {
        if (! Servicio::intercambiar($servicio, $direccion)) {
            return back();
        }

        return back()->with('exito', 'Orden actualizado.');
    }

    public function destroy(Servicio $servicio): RedirectResponse
    {
        $this->contenido->borrarArchivo($servicio->imagen);

        $servicio->delete();
        Servicio::renumerar();

        return back()->with('exito', 'Servicio eliminado definitivamente.');
    }

    private function campos(ServicioRequest $request): array
    {
        $datos = $request->validated();

        return [
            'titulo' => $datos['titulo'],
            'descripcion' => $this->sanitizador->limpiar($datos['descripcion'] ?? null) ?: null,
            'icono' => $datos['icono'] ?? null,
            'activo' => $request->boolean('activo'),
        ];
    }

    private function guardarImagen(ServicioRequest $request, Servicio $servicio): void
    {
        if ($request->boolean('eliminar_imagen') && ! $request->hasFile('imagen')) {
            $this->contenido->borrarArchivo($servicio->imagen);
            $servicio->update(['imagen' => null]);

            return;
        }

        if (! $request->hasFile('imagen')) {
            return;
        }

        $anterior = $servicio->imagen;

        $servicio->update([
            'imagen' => $request->file('imagen')->store(ContenidoWebService::CARPETA_SERVICIOS, ContenidoWebService::DISCO),
        ]);

        $this->contenido->borrarArchivo($anterior);
    }
}
