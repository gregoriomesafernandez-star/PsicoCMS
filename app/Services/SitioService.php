<?php

namespace App\Services;

use App\Models\Articulo;
use App\Models\Especialidad;
use App\Models\Faq;
use App\Models\PerfilPublico;
use App\Models\Plan;
use App\Models\Servicio;
use Illuminate\Support\Collection;

/**
 * Todo lo que necesita la web publica, en un solo sitio: datos de la
 * psicologa, contenidos activos, navegacion y SEO.
 */
class SitioService
{
    /** ancla => [texto, ruta, modulo del que depende] */
    private const SECCIONES = [
        'inicio' => ['Inicio', 'home', null],
        'sobre-mi' => ['Sobre mi', 'publico.sobre-mi', null],
        'servicios' => ['Servicios', 'publico.servicios', null],
        'blog' => ['Blog', 'publico.blog', 'blog'],
        'preguntas-frecuentes' => ['Preguntas', 'publico.faq', 'faq'],
        'pide-cita' => ['Pide cita', 'publico.pide-cita', 'reservas'],
    ];

    private ?string $temaForzado = null;

    private ?string $modoForzado = null;

    public function __construct(
        private TemaService $temas,
        private ModuloService $modulos,
        private RedSocialService $redes,
        private ImagenService $imagenes
    ) {
    }

    /**
     * Pinta la web con un tema y un modo concretos sin tocar los ajustes.
     * Lo usa la previsualizacion del panel.
     */
    public function previsualizar(string $tema, string $modo): self
    {
        $this->temaForzado = $tema;
        $this->modoForzado = $modo;

        return $this;
    }

    public function perfil(): PerfilPublico
    {
        return PerfilPublico::actual();
    }

    public function esLanding(): bool
    {
        return ($this->modoForzado ?? $this->temas->modoActivo()) === 'landing';
    }

    public function nombre(): string
    {
        return $this->perfil()->nombre_publico ?: config('app.name');
    }

    /* ---------- Fotos de la psicologa ---------- */

    /** Foto de la portada: su retrato si lo ha subido, si no la del hueco. */
    public function fotoPortada(): array
    {
        return $this->foto('hero_principal');
    }

    /**
     * Foto de "Sobre mi". Cuando no hay nada subido -ni el retrato ni una
     * imagen propia para ese hueco- se usa la misma de la portada: una foto de
     * stock distinta ensenaria a otra persona en la misma web.
     */
    public function fotoSobreMi(): array
    {
        return $this->imagenes->personalizada('sobre_mi')
            ? $this->foto('sobre_mi')
            : $this->foto('hero_principal');
    }

    /** @return array{url: ?string, alt: string} */
    private function foto(string $hueco): array
    {
        if ($this->perfil()->url_foto) {
            return ['url' => $this->perfil()->url_foto, 'alt' => 'Foto de ' . $this->nombre()];
        }

        return ['url' => $this->imagenes->url($hueco), 'alt' => $this->imagenes->alt($hueco)];
    }

    /* ---------- Contacto ---------- */

    public function telefono(): ?string
    {
        return $this->perfil()->telefono_citas;
    }

    /** Solo digitos, con prefijo espanol si viene sin el. */
    public function telefonoEnlace(): ?string
    {
        $digitos = preg_replace('/\D/', '', (string) $this->telefono());

        if ($digitos === '') {
            return null;
        }

        return strlen($digitos) === 9 ? '34' . $digitos : $digitos;
    }

    public function whatsapp(): ?string
    {
        $telefono = $this->telefonoEnlace();

        return $telefono ? 'https://wa.me/' . $telefono : null;
    }

    public function email(): ?string
    {
        return $this->perfil()->email_citas;
    }

    /* ---------- Navegacion ---------- */

    /**
     * En modo landing los enlaces son anclas de la portada; en multipagina,
     * direcciones propias. Las secciones de un modulo apagado no aparecen.
     */
    public function navegacion(): array
    {
        $enlaces = [];
        $landing = $this->esLanding();

        foreach (self::SECCIONES as $ancla => [$texto, $ruta, $modulo]) {
            if ($modulo && ! $this->modulos->activo($modulo)) {
                continue;
            }

            if ($ancla !== 'inicio' && ! $this->tieneContenido($ancla)) {
                continue;
            }

            $enlaces[] = [
                'ancla' => $ancla,
                'texto' => $texto,
                // En landing todos llevan ancla, incluido Inicio: es lo que
                // permite al menu saber cual esta a la vista y desmarcar el
                // resto. Sin ancla, Inicio se quedaba marcado para siempre.
                'url' => $landing ? route('home') . '#' . $ancla : route($ruta),
            ];
        }

        return $enlaces;
    }

    /** Una seccion vacia no se enlaza: seria llevar a una pagina en blanco. */
    public function tieneContenido(string $ancla): bool
    {
        return match ($ancla) {
            'sobre-mi' => filled($this->perfil()->sobre_mi),
            'servicios' => $this->servicios()->isNotEmpty(),
            'blog' => $this->articulos()->isNotEmpty(),
            'preguntas-frecuentes' => $this->faqs()->isNotEmpty(),
            default => true,
        };
    }

    /* ---------- Contenidos ---------- */

    public function servicios(): Collection
    {
        return Servicio::activos()->ordenados()->get();
    }

    public function especialidades(): Collection
    {
        return $this->modulos->activo('especialidades')
            ? Especialidad::activas()->ordenados()->get()
            : collect();
    }

    public function planes(): Collection
    {
        return $this->modulos->activo('planes')
            ? Plan::ordenados()->get()
            : collect();
    }

    public function faqs(): Collection
    {
        return $this->modulos->activo('faq')
            ? Faq::activas()->ordenadas()->get()
            : collect();
    }

    public function articulos(int $cuantos = 3): Collection
    {
        return $this->modulos->activo('blog')
            ? Articulo::visibles()->with('categoria')->orderByDesc('publicado_at')->limit($cuantos)->get()
            : collect();
    }

    public function redesVisibles(): Collection
    {
        return $this->modulos->activo('redes') ? $this->redes->visibles() : collect();
    }

    public function mostrarMapa(): bool
    {
        return $this->modulos->activo('mapa') && filled($this->perfil()->mapa_embed);
    }

    /* ---------- SEO ---------- */

    /** Titulo y descripcion de una pagina, con la portada como respaldo. */
    public function seo(string $pagina, ?string $tituloPropio = null, ?string $descripcionPropia = null): array
    {
        $perfil = $this->perfil();
        $guardado = (array) ajuste(ContenidoWebService::CLAVE_SEO, []);
        $suyo = $guardado[$pagina] ?? [];

        $titulo = $tituloPropio
            ?: ($suyo['title'] ?? null)
            ?: ($pagina === 'inicio' ? $perfil->meta_title : null)
            ?: trim($this->nombre() . ($pagina === 'inicio' ? '' : ' | ' . $this->tituloDe($pagina)));

        $descripcion = $descripcionPropia
            ?: ($suyo['description'] ?? null)
            ?: $perfil->meta_description
            ?: $perfil->eslogan;

        return [
            'titulo' => $titulo,
            'descripcion' => $descripcion ? mb_substr(strip_tags($descripcion), 0, 170) : null,
        ];
    }

    private function tituloDe(string $pagina): string
    {
        return self::SECCIONES[$pagina][0] ?? ucfirst(str_replace('-', ' ', $pagina));
    }

    /** Datos estructurados para que Google entienda que es una consulta. */
    public function datosEstructurados(): array
    {
        $perfil = $this->perfil();

        $datos = [
            '@context' => 'https://schema.org',
            '@type' => 'Psychologist',
            'name' => $this->nombre(),
            'url' => route('home'),
        ];

        if ($perfil->eslogan) {
            $datos['description'] = strip_tags($perfil->eslogan);
        }

        if ($perfil->url_foto) {
            $datos['image'] = $perfil->url_foto;
        }

        if ($this->telefono()) {
            $datos['telephone'] = $this->telefono();
        }

        if ($this->email()) {
            $datos['email'] = $this->email();
        }

        if ($perfil->direccion) {
            $datos['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => $perfil->direccion,
            ];
        }

        $redes = $this->redesVisibles()->pluck('url')->all();

        if ($redes) {
            $datos['sameAs'] = $redes;
        }

        return $datos;
    }

    /** Hojas de estilo del tema activo, o del que se este previsualizando. */
    public function hojas(): array
    {
        return $this->temas->hojas($this->temaForzado ?? $this->temas->slugActivo());
    }
}
