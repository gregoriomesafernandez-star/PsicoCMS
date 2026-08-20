<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\FrasePublica;
use App\Services\FraseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FraseController extends Controller
{
    public function __construct(private FraseService $frases)
    {
    }

    public function index(): View
    {
        return view('panel.frases.index', [
            'grupos' => $this->frases->agrupadas(),
            'resumen' => $this->frases->resumen(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'frases' => ['nullable', 'array'],
            'frases.*' => ['nullable', 'string', 'max:500'],
        ], [], ['frases.*' => 'frase']);

        $cambiadas = $this->frases->guardar($datos['frases'] ?? []);

        return back()->with('exito', $cambiadas === 0
            ? 'No habia nada que cambiar.'
            : ($cambiadas === 1 ? 'Se ha guardado 1 frase.' : "Se han guardado {$cambiadas} frases."));
    }

    public function restaurar(FrasePublica $frase): RedirectResponse
    {
        $this->frases->restaurar($frase);

        return back()->with('exito', 'Frase restaurada a su texto original.');
    }

    public function restaurarTodas(): RedirectResponse
    {
        $cuantas = $this->frases->restaurarTodas();

        return back()->with('exito', $cuantas === 0
            ? 'Ya estaban todas con su texto original.'
            : "Se han restaurado {$cuantas} frases.");
    }
}
