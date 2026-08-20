<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\RedSocialRequest;
use App\Services\RedSocialService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RedSocialController extends Controller
{
    public function __construct(private RedSocialService $redes)
    {
    }

    public function index(): View
    {
        $this->redes->sincronizar();

        return view('panel.redes.index', [
            'redes' => $this->redes->ordenadas(),
            'catalogo' => RedSocialService::REDES,
            'visibles' => $this->redes->visibles(),
            'resumen' => $this->redes->resumen(),
        ]);
    }

    public function update(RedSocialRequest $request): RedirectResponse
    {
        $this->redes->guardar(
            $request->validated('urls') ?? [],
            $request->input('activas', [])
        );

        return back()->with('exito', 'Redes sociales guardadas.');
    }
}
