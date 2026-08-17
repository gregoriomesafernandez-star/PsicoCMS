<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\ArticuloRequest;
use App\Models\Articulo;
use App\Models\Categoria;
use App\Services\SanitizadorHtml;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BlogController extends Controller
{
    private const CARPETA_IMAGENES = 'blog';

    public function __construct(private SanitizadorHtml $sanitizador)
    {
    }

    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'categoria' => ['nullable', 'integer', 'exists:categorias,id'],
            'estado' => ['nullable', Rule::in(array_keys(Articulo::ESTADOS))],
        ]);

        $articulos = Articulo::with('categoria')
            ->when($filtros['q'] ?? null, function ($query, $texto) {
                $query->where(function ($sub) use ($texto) {
                    $sub->where('titulo', 'like', "%{$texto}%")
                        ->orWhere('extracto', 'like', "%{$texto}%")
                        ->orWhere('contenido', 'like', "%{$texto}%");
                });
            })
            ->when($filtros['categoria'] ?? null, fn ($q, $categoria) => $q->where('categoria_id', $categoria))
            ->when($filtros['estado'] ?? null, fn ($q, $estado) => $this->filtrarEstado($q, $estado))
            ->orderByDesc('publicado_at')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('panel.blog.index', [
            'articulos' => $articulos,
            'filtros' => $filtros,
            'categorias' => Categoria::paraSelector(),
            'estados' => Articulo::ESTADOS,
            'resumen' => $this->resumen(),
        ]);
    }

    public function create(): View
    {
        return view('panel.blog.formulario', [
            'articulo' => new Articulo(),
            'categorias' => Categoria::paraSelector(),
        ]);
    }

    public function store(ArticuloRequest $request): RedirectResponse
    {
        $articulo = Articulo::create($this->campos($request));

        return $this->volverAlArticulo($request, $articulo, 'Articulo creado correctamente.');
    }

    public function edit(Articulo $articulo): View
    {
        return view('panel.blog.formulario', [
            'articulo' => $articulo,
            'categorias' => Categoria::paraSelector(),
        ]);
    }

    public function previsualizar(Articulo $articulo): View
    {
        return view('panel.blog.previsualizar', [
            'articulo' => $articulo->load('categoria'),
        ]);
    }

    public function update(ArticuloRequest $request, Articulo $articulo): RedirectResponse
    {
        $articulo->update($this->campos($request, $articulo));

        return $this->volverAlArticulo($request, $articulo, 'Articulo actualizado correctamente.');
    }

    private function volverAlArticulo(ArticuloRequest $request, Articulo $articulo, string $mensaje): RedirectResponse
    {
        if ($request->input('accion') === 'previsualizar') {
            return redirect()
                ->route('panel.blog.previsualizar', $articulo)
                ->with('exito', 'Cambios guardados. Asi queda tu articulo.');
        }

        return redirect()
            ->route('panel.blog.edit', $articulo)
            ->with('exito', $mensaje);
    }

    public function destroy(Articulo $articulo): RedirectResponse
    {
        $this->borrarImagen($articulo->imagen);
        $articulo->delete();

        return redirect()
            ->route('panel.blog.index')
            ->with('exito', 'Articulo eliminado definitivamente.');
    }

    private function campos(ArticuloRequest $request, ?Articulo $articulo = null): array
    {
        $datos = $request->validated();
        $publicado = $datos['estado'] === 'publicado';

        return [
            'titulo' => $datos['titulo'],
            'slug' => Articulo::generarSlug($datos['slug'] ?: $datos['titulo'], $articulo?->id),
            'categoria_id' => $datos['categoria_id'] ?? null,
            'extracto' => $datos['extracto'] ?? null,
            'contenido' => $this->sanitizador->limpiar($datos['contenido'] ?? null),
            'imagen' => $this->imagen($request, $articulo),
            'publicado' => $publicado,
            'publicado_at' => $this->fechaPublicacion($datos, $publicado, $articulo),
            'meta_title' => $datos['meta_title'] ?? null,
            'meta_description' => $datos['meta_description'] ?? null,
        ];
    }

    private function fechaPublicacion(array $datos, bool $publicado, ?Articulo $articulo): ?string
    {
        if (! empty($datos['publicado_at'])) {
            return $datos['publicado_at'];
        }

        if (! $publicado) {
            return $articulo?->publicado_at?->toDateTimeString();
        }

        return $articulo?->publicado_at?->toDateTimeString() ?? now()->toDateTimeString();
    }

    private function imagen(ArticuloRequest $request, ?Articulo $articulo): ?string
    {
        $archivo = $request->file('imagen');

        if ($archivo instanceof UploadedFile) {
            $this->borrarImagen($articulo?->imagen);

            return $archivo->store(self::CARPETA_IMAGENES, 'public');
        }

        if ($request->boolean('eliminar_imagen')) {
            $this->borrarImagen($articulo?->imagen);

            return null;
        }

        return $articulo?->imagen;
    }

    private function borrarImagen(?string $ruta): void
    {
        if ($ruta && Storage::disk('public')->exists($ruta)) {
            Storage::disk('public')->delete($ruta);
        }
    }

    private function filtrarEstado($query, string $estado)
    {
        return match ($estado) {
            'borrador' => $query->where('publicado', false),
            'programado' => $query->where('publicado', true)->where('publicado_at', '>', now()),
            default => $query->visibles(),
        };
    }

    private function resumen(): array
    {
        return [
            'total' => Articulo::count(),
            'publicados' => Articulo::visibles()->count(),
            'borradores' => Articulo::where('publicado', false)->count(),
            'programados' => Articulo::where('publicado', true)->where('publicado_at', '>', now())->count(),
        ];
    }
}
