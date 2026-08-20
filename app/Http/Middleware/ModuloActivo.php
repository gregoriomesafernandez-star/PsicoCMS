<?php

namespace App\Http\Middleware;

use App\Services\ModuloService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cierra una ruta publica cuando su modulo esta desactivado. Se aplica como
 * 'modulo:blog', 'modulo:reservas'... en las rutas de la parte publica.
 */
class ModuloActivo
{
    public function __construct(private ModuloService $modulos)
    {
    }

    public function handle(Request $request, Closure $siguiente, string $clave): Response
    {
        abort_unless($this->modulos->activo($clave), 404);

        return $siguiente($request);
    }
}
