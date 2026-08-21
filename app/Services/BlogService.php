<?php

namespace App\Services;

use App\Models\Articulo;
use App\Models\Categoria;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Blog publico. Todo lo que sale de aqui pasa por Articulo::visibles(), que ya
 * descarta borradores y articulos programados para mas adelante.
 */
class BlogService
{
    public const POR_PAGINA = 6;

    public const RELACIONADOS = 3;

    public function listado(?Categoria $categoria = null): LengthAwarePaginator
    {
        return Articulo::visibles()
            ->with('categoria')
            ->when($categoria, fn ($query) => $query->where('categoria_id', $categoria->id))
            ->orderByDesc('publicado_at')
            ->orderByDesc('id')
            ->paginate(self::POR_PAGINA)
            ->withQueryString();
    }

    /** Solo las categorias que tienen algo publicado: filtrar por una vacia frustra. */
    public function categoriasConArticulos(): Collection
    {
        return Categoria::whereHas('articulos', fn ($query) => $query->visibles())
            ->withCount(['articulos' => fn ($query) => $query->visibles()])
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();
    }

    public function totalPublicados(): int
    {
        return Articulo::visibles()->count();
    }

    /** De la misma categoria; si no llegan, se completa con los mas recientes. */
    public function relacionados(Articulo $articulo): Collection
    {
        $mismos = $articulo->categoria_id
            ? Articulo::visibles()
                ->with('categoria')
                ->where('categoria_id', $articulo->categoria_id)
                ->where('id', '!=', $articulo->id)
                ->orderByDesc('publicado_at')
                ->limit(self::RELACIONADOS)
                ->get()
            : collect();

        if ($mismos->count() >= self::RELACIONADOS) {
            return $mismos;
        }

        $completar = Articulo::visibles()
            ->with('categoria')
            ->whereNotIn('id', $mismos->pluck('id')->push($articulo->id))
            ->orderByDesc('publicado_at')
            ->limit(self::RELACIONADOS - $mismos->count())
            ->get();

        return $mismos->concat($completar);
    }

    /**
     * Una visita por sesion y articulo. Sin esto, recargar la pagina inflaria
     * el contador y el dato dejaria de servir para nada.
     */
    public function registrarVisita(Articulo $articulo, Request $request): void
    {
        $vistos = (array) $request->session()->get('articulos_vistos', []);

        if (in_array($articulo->id, $vistos, true)) {
            return;
        }

        $articulo->increment('vistas');

        $request->session()->put('articulos_vistos', array_merge($vistos, [$articulo->id]));
    }

    /**
     * Enlaces de compartir. Son URLs normales de cada red: sin SDKs, sin
     * scripts de terceros y sin cookies ajenas.
     *
     * @return array<int, array{nombre: string, icono: string, url: string}>
     */
    public function enlacesCompartir(Articulo $articulo, string $url): array
    {
        $titulo = $articulo->titulo;

        return [
            [
                'nombre' => 'WhatsApp',
                'icono' => 'fa-brands fa-whatsapp',
                'url' => 'https://wa.me/?text=' . rawurlencode($titulo . ' ' . $url),
            ],
            [
                'nombre' => 'Facebook',
                'icono' => 'fa-brands fa-facebook',
                'url' => 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($url),
            ],
            [
                'nombre' => 'X',
                'icono' => 'fa-brands fa-x-twitter',
                'url' => 'https://twitter.com/intent/tweet?' . http_build_query(['text' => $titulo, 'url' => $url]),
            ],
            [
                'nombre' => 'LinkedIn',
                'icono' => 'fa-brands fa-linkedin',
                'url' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode($url),
            ],
            [
                'nombre' => 'Email',
                'icono' => 'fa-regular fa-envelope',
                'url' => 'mailto:?' . http_build_query(['subject' => $titulo, 'body' => $url]),
            ],
        ];
    }

    /** Su foto, o la del hueco "blog" si no tiene: la misma que su tarjeta. */
    public function portada(Articulo $articulo): ?string
    {
        return $articulo->url_imagen ?: imagen('blog_defecto');
    }

    /** JSON-LD del articulo, para que los buscadores lo entiendan como tal. */
    public function datosEstructurados(Articulo $articulo, string $url, string $autor): array
    {
        $datos = [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $articulo->titulo,
            'mainEntityOfPage' => $url,
            'author' => ['@type' => 'Person', 'name' => $autor],
            'publisher' => ['@type' => 'Organization', 'name' => $autor],
        ];

        if ($articulo->publicado_at) {
            $datos['datePublished'] = $articulo->publicado_at->toIso8601String();
            $datos['dateModified'] = $articulo->updated_at?->toIso8601String();
        }

        if ($imagen = $this->portada($articulo)) {
            $datos['image'] = $imagen;
        }

        if ($articulo->extracto) {
            $datos['description'] = $articulo->extracto;
        }

        return $datos;
    }
}
