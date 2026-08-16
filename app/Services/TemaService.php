<?php

namespace App\Services;

class TemaService
{
    public const TEMA_POR_DEFECTO = 'base';

    public const MODOS = ['landing', 'multipagina'];

    public function disponibles(): array
    {
        $temas = [];

        foreach (glob(public_path('themes/*/theme.json')) as $ruta) {
            $datos = json_decode(file_get_contents($ruta), true);

            if (is_array($datos) && isset($datos['slug'])) {
                $temas[$datos['slug']] = $datos;
            }
        }

        ksort($temas);

        return $temas;
    }

    public function existe(string $slug): bool
    {
        return array_key_exists($slug, $this->disponibles());
    }

    public function activo(): array
    {
        $temas = $this->disponibles();
        $slug = ajuste('tema_activo', self::TEMA_POR_DEFECTO);

        return $temas[$slug] ?? $temas[self::TEMA_POR_DEFECTO] ?? reset($temas);
    }

    public function slugsDisponibles(): array
    {
        return array_keys($this->disponibles());
    }
}
