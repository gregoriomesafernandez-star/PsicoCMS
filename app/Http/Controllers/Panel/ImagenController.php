<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\ImagenRequest;
use App\Models\Imagen;
use App\Services\ImagenService;
use App\Services\TemaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ImagenController extends Controller
{
    public function __construct(
        private ImagenService $imagenes,
        private TemaService $temas
    ) {
    }

    public function index(): View
    {
        $this->imagenes->sincronizar();

        return view('panel.imagenes.index', [
            'grupos' => $this->imagenes->agrupadas(),
            'resumen' => $this->imagenes->resumen(),
            'tema' => $this->temas->activo(),
        ]);
    }

    public function update(ImagenRequest $request, Imagen $imagen): RedirectResponse
    {
        abort_unless($this->imagenes->existe($imagen->clave_slot), 404);

        $this->imagenes->guardar($imagen, $request->file('imagen'), $request->validated('alt'));

        return back()->with('exito', $request->hasFile('imagen')
            ? 'Imagen actualizada. Ya se ve en tu web.'
            : 'Descripcion guardada.');
    }

    public function restaurar(Imagen $imagen): RedirectResponse
    {
        abort_unless($this->imagenes->existe($imagen->clave_slot), 404);

        $this->imagenes->restaurar($imagen);

        return back()->with('exito', 'Se ha vuelto a la imagen original del tema.');
    }
}
