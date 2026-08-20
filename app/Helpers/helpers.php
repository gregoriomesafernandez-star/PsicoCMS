<?php

use App\Services\AjustesService;
use App\Services\FraseService;
use App\Services\ImagenService;
use App\Services\InstaladorService;
use App\Services\ModuloService;
use Illuminate\Support\Facades\Route;

if (! function_exists('ajuste')) {
    function ajuste(string $clave, mixed $porDefecto = null): mixed
    {
        return app(AjustesService::class)->obtener($clave, $porDefecto);
    }
}

if (! function_exists('recurso')) {
    /**
     * URL de un fichero de public/ con la fecha de modificacion como version.
     * Sin ella, el navegador sigue sirviendo el CSS o el JS antiguo tras un cambio.
     */
    function recurso(string $ruta): string
    {
        $completa = public_path($ruta);

        return asset($ruta) . (is_file($completa) ? '?v=' . filemtime($completa) : '');
    }
}

if (! function_exists('imagen')) {
    function imagen(string $clave): ?string
    {
        return app(ImagenService::class)->url($clave);
    }
}

if (! function_exists('frase')) {
    function frase(string $clave, ?string $porDefecto = null): ?string
    {
        return app(FraseService::class)->texto($clave) ?? $porDefecto;
    }
}

if (! function_exists('imagen_alt')) {
    function imagen_alt(string $clave): string
    {
        return app(ImagenService::class)->alt($clave);
    }
}

if (! function_exists('modulo_activo')) {
    function modulo_activo(string $clave): bool
    {
        return app(ModuloService::class)->activo($clave);
    }
}

if (! function_exists('menu_disponible')) {
    function menu_disponible(string $ruta): bool
    {
        return Route::has($ruta);
    }
}

if (! function_exists('menu_oculto')) {
    /**
     * Distinto de menu_disponible(): la ruta existe, pero su modulo esta
     * apagado, asi que el elemento no se pinta en vez de salir con candado.
     */
    function menu_oculto(string $ruta): bool
    {
        return in_array($ruta, app(ModuloService::class)->rutasOcultas(), true);
    }
}

if (! function_exists('menu_url')) {
    function menu_url(string $ruta): string
    {
        return Route::has($ruta) ? route($ruta) : '#';
    }
}

if (! function_exists('menu_activo')) {
    function menu_activo(string $ruta): bool
    {
        $base = str_replace('.index', '', $ruta);

        return request()->routeIs($ruta) || request()->routeIs($base . '.*');
    }
}

if (! function_exists('menu_grupo_activo')) {
    function menu_grupo_activo(array $elementos): bool
    {
        foreach ($elementos as $elemento) {
            if (menu_activo($elemento['ruta'])) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('esta_instalado')) {
    function esta_instalado(): bool
    {
        return app(InstaladorService::class)->estaInstalado();
    }
}
