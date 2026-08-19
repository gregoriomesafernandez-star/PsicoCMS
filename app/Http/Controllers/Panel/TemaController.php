<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\ModoWebRequest;
use App\Models\Especialidad;
use App\Models\Faq;
use App\Models\PerfilPublico;
use App\Models\Plan;
use App\Models\Servicio;
use App\Services\TemaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TemaController extends Controller
{
    public const URL_DISENO_PERSONALIZADO = 'https://victorroblesweb.es/contacto';

    public function __construct(private TemaService $temas)
    {
    }

    public function index(): View
    {
        return view('panel.temas.index', [
            'temas' => $this->temas->disponibles(),
            'activo' => $this->temas->slugActivo(),
            'modo' => $this->temas->modoActivo(),
            'modos' => TemaService::MODOS,
            'urlDisenoPersonalizado' => self::URL_DISENO_PERSONALIZADO,
        ]);
    }

    public function activar(string $slug): RedirectResponse
    {
        abort_unless($this->temas->existe($slug), 404);

        $this->temas->activar($slug);

        return back()->with('exito', 'Tema "' . $this->temas->tema($slug)['nombre'] . '" activado en tu web.');
    }

    public function modo(ModoWebRequest $request): RedirectResponse
    {
        $modo = $request->validated('modo');

        $this->temas->guardarModo($modo);

        return back()->with('exito', $modo === 'landing'
            ? 'Tu web sera una sola pagina con scroll.'
            : 'Tu web tendra una pagina por seccion.');
    }

    /**
     * Previsualizacion en pestana nueva. No cambia el tema activo: solo pinta
     * los datos reales con las hojas del tema que se pide.
     */
    public function previsualizar(string $slug, ?string $modo = null): View
    {
        abort_unless($this->temas->existe($slug), 404);

        $modo = array_key_exists((string) $modo, TemaService::MODOS)
            ? $modo
            : $this->temas->modoActivo();

        return view('panel.temas.previsualizar', [
            'tema' => $this->temas->tema($slug),
            'hojas' => $this->temas->hojas($slug),
            'modo' => $modo,
            'esActivo' => $slug === $this->temas->slugActivo(),
            'perfil' => PerfilPublico::actual(),
            'servicios' => Servicio::activos()->ordenados()->get(),
            'especialidades' => Especialidad::activas()->ordenados()->get(),
            'planes' => Plan::ordenados()->get(),
            'faqs' => Faq::activas()->ordenadas()->get(),
        ]);
    }
}
