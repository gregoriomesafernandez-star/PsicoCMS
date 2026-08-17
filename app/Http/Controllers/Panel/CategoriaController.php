<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\CategoriaRequest;
use App\Models\Categoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoriaController extends Controller
{
    public function index(): View
    {
        return view('panel.categorias.index', [
            'categorias' => Categoria::withCount('articulos')
                ->orderBy('orden')
                ->orderBy('nombre')
                ->get(),
        ]);
    }

    public function store(CategoriaRequest $request): RedirectResponse
    {
        $datos = $request->validated();

        Categoria::create([
            'nombre' => $datos['nombre'],
            'slug' => Categoria::generarSlug($datos['nombre']),
            'descripcion' => $datos['descripcion'] ?? null,
            'orden' => $datos['orden'] ?? 0,
        ]);

        return back()->with('exito', 'Categoria creada correctamente.');
    }

    public function update(CategoriaRequest $request, Categoria $categoria): RedirectResponse
    {
        $datos = $request->validated();

        $categoria->update([
            'nombre' => $datos['nombre'],
            'slug' => Categoria::generarSlug($datos['nombre'], $categoria->id),
            'descripcion' => $datos['descripcion'] ?? null,
            'orden' => $datos['orden'] ?? 0,
        ]);

        return back()->with('exito', 'Categoria actualizada correctamente.');
    }

    public function destroy(Categoria $categoria): RedirectResponse
    {
        $articulos = $categoria->articulos()->count();
        $categoria->delete();

        $aviso = $articulos > 0
            ? sprintf(' Sus %d articulo(s) siguen publicados, pero ahora estan sin categoria.', $articulos)
            : '';

        return back()->with('exito', 'Categoria eliminada.' . $aviso);
    }
}
