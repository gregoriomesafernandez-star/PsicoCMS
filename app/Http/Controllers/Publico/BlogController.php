<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Models\Articulo;
use App\Models\Categoria;
use App\Services\BlogService;
use App\Services\SitioService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BlogController extends Controller
{
    public function __construct(
        private BlogService $blog,
        private SitioService $sitio
    ) {
    }

    public function index(): View
    {
        return $this->listado();
    }

    public function categoria(Categoria $categoria): View
    {
        return $this->listado($categoria);
    }

    public function articulo(Request $request, Articulo $articulo): View
    {
        // Un borrador o un articulo programado no existe para quien no ha
        // entrado al panel: 404, no "no autorizado".
        if (! $articulo->publicado || ($articulo->publicado_at && $articulo->publicado_at->isFuture())) {
            throw new NotFoundHttpException();
        }

        $this->blog->registrarVisita($articulo, $request);

        $url = url()->current();

        return view('publico.paginas.articulo', [
            'sitio' => $this->sitio,
            'pagina' => 'blog',
            'activo' => 'blog',
            'articulo' => $articulo->load('categoria'),
            'portada' => $this->blog->portada($articulo),
            // Al compartirlo se ve su foto, no el retrato de la psicologa.
            'imagenSocial' => $this->blog->portada($articulo),
            'relacionados' => $this->blog->relacionados($articulo),
            'compartir' => $this->blog->enlacesCompartir($articulo, $url),
            'estructurados' => $this->blog->datosEstructurados($articulo, $url, $this->sitio->nombre()),
            'tituloSeo' => $articulo->meta_title ?: $articulo->titulo,
            'descripcionSeo' => $articulo->meta_description ?: $articulo->resumen,
            'tipoOg' => 'article',
        ]);
    }

    private function listado(?Categoria $categoria = null): View
    {
        return view('publico.paginas.blog', [
            'sitio' => $this->sitio,
            'pagina' => 'blog',
            'activo' => 'blog',
            'articulos' => $this->blog->listado($categoria),
            'categorias' => $this->blog->categoriasConArticulos(),
            'categoriaActiva' => $categoria,
            'total' => $this->blog->totalPublicados(),
            'tituloSeo' => $categoria ? $categoria->nombre . ' | Blog de ' . $this->sitio->nombre() : null,
            'descripcionSeo' => $categoria?->descripcion,
        ]);
    }
}
