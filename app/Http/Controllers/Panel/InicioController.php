<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\PerfilPublico;
use App\Services\TemaService;
use Illuminate\View\View;

class InicioController extends Controller
{
    public function __construct(private TemaService $temas)
    {
    }

    public function __invoke(): View
    {
        return view('panel.inicio', [
            'psicologa' => auth()->user(),
            'perfil' => PerfilPublico::first(),
            'tema' => $this->temas->activo(),
            'modoWeb' => ajuste('modo_web', 'landing'),
        ]);
    }
}
