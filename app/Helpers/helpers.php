<?php

use App\Services\AjustesService;
use App\Services\InstaladorService;

if (! function_exists('ajuste')) {
    function ajuste(string $clave, mixed $porDefecto = null): mixed
    {
        return app(AjustesService::class)->obtener($clave, $porDefecto);
    }
}

if (! function_exists('esta_instalado')) {
    function esta_instalado(): bool
    {
        return app(InstaladorService::class)->estaInstalado();
    }
}
