<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\ConsultaRequest;
use App\Http\Requests\Panel\PerfilPublicoRequest;
use App\Http\Requests\Panel\SeoRequest;
use App\Http\Requests\Panel\SobreMiRequest;
use App\Models\Especialidad;
use App\Models\PerfilPublico;
use App\Models\Plan;
use App\Models\Servicio;
use App\Services\ContenidoWebService;
use App\Services\SanitizadorHtml;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContenidoWebController extends Controller
{
    public function __construct(
        private ContenidoWebService $contenido,
        private SanitizadorHtml $sanitizador
    ) {
    }

    public function index(): View
    {
        $perfil = PerfilPublico::actual();

        return view('panel.contenido.index', [
            'perfil' => $perfil,
            'planes' => Plan::ordenados()->get(),
            'seo' => $this->contenido->seo(),
            'paginasSeo' => ContenidoWebService::PAGINAS_SEO,
            'pestana' => session('pestana', request('pestana', 'perfil')),
            'completitud' => $this->completitud($perfil),
        ]);
    }

    public function guardarPerfil(PerfilPublicoRequest $request): RedirectResponse
    {
        $perfil = PerfilPublico::actual();
        $perfil->fill($request->safe()->except('foto'));
        $perfil->save();

        if ($request->hasFile('foto')) {
            $this->contenido->guardarFoto($perfil, $request->file('foto'));
        }

        return $this->vuelta('perfil', 'Tus datos publicos se han guardado.');
    }

    public function borrarFoto(): RedirectResponse
    {
        $this->contenido->borrarFoto(PerfilPublico::actual());

        return $this->vuelta('perfil', 'Foto eliminada. Sube otra cuando quieras.');
    }

    public function guardarSobreMi(SobreMiRequest $request): RedirectResponse
    {
        $perfil = PerfilPublico::actual();
        $perfil->sobre_mi = $this->sanitizador->limpiar($request->validated('sobre_mi'));
        $perfil->save();

        return $this->vuelta('sobre-mi', 'Tu presentacion se ha guardado.');
    }

    public function guardarConsulta(ConsultaRequest $request): RedirectResponse
    {
        $perfil = PerfilPublico::actual();
        $perfil->fill($request->validated());
        $perfil->save();

        return $this->vuelta('consulta', 'Los datos de tu consulta se han guardado.');
    }

    public function guardarSeo(SeoRequest $request): RedirectResponse
    {
        $perfil = PerfilPublico::actual();
        $perfil->meta_title = $request->validated('meta_title');
        $perfil->meta_description = $request->validated('meta_description');
        $perfil->save();

        $this->contenido->guardarSeo($request->paginas());

        return $this->vuelta('seo', 'Los textos para buscadores se han guardado.');
    }

    private function vuelta(string $pestana, string $mensaje): RedirectResponse
    {
        return back()->with('exito', $mensaje)->with('pestana', $pestana);
    }

    private function completitud(PerfilPublico $perfil): array
    {
        $puntos = [
            'Datos publicos' => filled($perfil->eslogan) && filled($perfil->telefono_citas) && filled($perfil->email_citas),
            'Foto de perfil' => filled($perfil->foto),
            'Sobre mi' => filled($perfil->sobre_mi),
            'Servicios' => Servicio::activos()->exists(),
            'Especialidades' => Especialidad::activas()->exists(),
            'Planes y precios' => Plan::exists(),
            'Lugar de consulta' => filled($perfil->direccion) || filled($perfil->lugar_consulta),
            'Textos para Google' => filled($perfil->meta_title) && filled($perfil->meta_description),
        ];

        $hechos = count(array_filter($puntos));

        return [
            'puntos' => $puntos,
            'hechos' => $hechos,
            'total' => count($puntos),
            'porcentaje' => (int) round($hechos / count($puntos) * 100),
        ];
    }
}
