<?php

namespace App\Http\Middleware;

use App\Services\InstaladorService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarInstalacion
{
    public function __construct(private InstaladorService $instalador)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $enInstalador = $request->is('instalacion', 'instalacion/*');

        if (! $this->instalador->estaInstalado() && ! $enInstalador) {
            return redirect()->route('instalacion.inicio');
        }

        if ($this->instalador->estaInstalado() && $enInstalador) {
            return redirect('/panel-psicologa');
        }

        return $next($request);
    }
}
