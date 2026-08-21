<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracionDisponibilidad;
use App\Services\SitioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaginaController extends Controller
{
    public function __construct(private SitioService $sitio)
    {
    }

    public function home(): View
    {
        return $this->pagina('home', 'inicio');
    }

    public function sobreMi(): View|RedirectResponse
    {
        return $this->seccion('sobre-mi');
    }

    public function servicios(): View|RedirectResponse
    {
        return $this->seccion('servicios');
    }

    public function faq(): View|RedirectResponse
    {
        return $this->seccion('faq', 'preguntas-frecuentes');
    }

    public function blog(): View|RedirectResponse
    {
        return $this->seccion('blog');
    }

    public function pideCita(): View|RedirectResponse
    {
        if ($this->sitio->esLanding()) {
            return redirect()->to(route('home') . '#pide-cita');
        }

        $configuracion = ConfiguracionDisponibilidad::actual();

        return view('publico.paginas.pide-cita', [
            'sitio' => $this->sitio,
            'pagina' => 'pide-cita',
            'activo' => 'pide-cita',
            'modoVacaciones' => (bool) $configuracion->modo_vacaciones,
            'mensajeVacaciones' => $configuracion->mensaje_vacaciones
                ?: 'Estare de vuelta pronto. Puedes escribirme y te aviso en cuanto abra la agenda.',
        ]);
    }

    /**
     * En modo landing todo vive en la portada, asi que la direccion de una
     * seccion lleva a su ancla en vez de a una pagina propia.
     */
    private function seccion(string $vista, ?string $ancla = null): View|RedirectResponse
    {
        $ancla = $ancla ?? $vista;

        if ($this->sitio->esLanding()) {
            return redirect()->to(route('home') . '#' . $ancla);
        }

        return $this->pagina($vista, $ancla);
    }

    /**
     * En landing no se marca nada desde el servidor: al ser una sola pagina,
     * quien manda sobre el enlace resaltado es el menu, segun lo que el
     * visitante pulse o tenga a la vista.
     */
    private function pagina(string $vista, string $ancla): View
    {
        return view('publico.paginas.' . $vista, [
            'sitio' => $this->sitio,
            'pagina' => $ancla,
            'activo' => $this->sitio->esLanding() ? null : $ancla,
        ]);
    }
}
