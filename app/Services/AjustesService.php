<?php

namespace App\Services;

use App\Models\Ajuste;
use Illuminate\Support\Facades\Cache;

class AjustesService
{
    private const CACHE_KEY = 'psicocms.ajustes';

    public function todos(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return Ajuste::query()->pluck('valor', 'clave')->all();
        });
    }

    public function obtener(string $clave, mixed $porDefecto = null): mixed
    {
        return $this->todos()[$clave] ?? $porDefecto;
    }

    public function guardar(string $clave, mixed $valor, string $grupo = 'general'): void
    {
        Ajuste::updateOrCreate(
            ['clave' => $clave],
            ['valor' => $valor, 'grupo' => $grupo]
        );

        $this->limpiarCache();
    }

    public function guardarVarios(array $valores, string $grupo = 'general'): void
    {
        foreach ($valores as $clave => $valor) {
            Ajuste::updateOrCreate(
                ['clave' => $clave],
                ['valor' => $valor, 'grupo' => $grupo]
            );
        }

        $this->limpiarCache();
    }

    public function limpiarCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
