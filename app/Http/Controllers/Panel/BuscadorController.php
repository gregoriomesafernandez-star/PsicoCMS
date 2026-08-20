<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\BuscadorService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BuscadorController extends Controller
{
    public function __construct(private BuscadorService $buscador)
    {
    }

    public function __invoke(Request $request): View
    {
        $filtros = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'tipo' => ['nullable', 'string', 'max:30'],
        ]);

        $texto = trim((string) ($filtros['q'] ?? ''));
        $tipo = $filtros['tipo'] ?? null;

        $resultados = $this->buscador->buscar($texto, $tipo);

        return view('panel.buscador.index', [
            'texto' => $texto,
            'tipo' => $tipo,
            'resultados' => $resultados,
            'contadores' => $this->buscador->contadores($this->buscador->buscar($texto)),
            'total' => $this->buscador->total($resultados),
            'valida' => $this->buscador->valida($texto),
            'tipos' => BuscadorService::TIPOS,
            'minimo' => BuscadorService::MINIMO,
        ]);
    }
}
