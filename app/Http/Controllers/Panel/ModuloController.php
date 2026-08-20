<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\ModuloService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModuloController extends Controller
{
    public function __construct(private ModuloService $modulos)
    {
    }

    public function index(): View
    {
        return view('panel.modulos.index', [
            'modulos' => $this->modulos->estado(),
            'resumen' => $this->modulos->resumen(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'modulos' => ['nullable', 'array'],
            'modulos.*' => ['string'],
        ]);

        $this->modulos->guardar($datos['modulos'] ?? []);

        return back()->with('exito', 'Guardado. Tu web ya refleja los cambios.');
    }
}
