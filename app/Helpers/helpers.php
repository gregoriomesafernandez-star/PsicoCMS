<?php

use App\Services\AjustesService;
use App\Services\InstaladorService;
use Illuminate\Support\Facades\Route;

if (! function_exists('ajuste')) {
    function ajuste(string $clave, mixed $porDefecto = null): mixed
    {
        return app(AjustesService::class)->obtener($clave, $porDefecto);
    }
}

if (! function_exists('menu_disponible')) {
    function menu_disponible(string $ruta): bool
    {
        return Route::has($ruta);
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
