<?php

namespace App\Support;

class Resaltado
{
    /**
     * Envuelve en <mark> las coincidencias del termino buscado.
     *
     * El texto se escapa **antes** de insertar el marcado, asi que el nombre de
     * un paciente con < o > no puede inyectar HTML en la pagina de resultados.
     */
    public static function marcar(?string $texto, ?string $termino): string
    {
        $texto = e((string) $texto);
        $termino = trim((string) $termino);

        if ($termino === '') {
            return $texto;
        }

        return preg_replace(
            '/(' . preg_quote(e($termino), '/') . ')/iu',
            '<mark class="resaltado">$1</mark>',
            $texto
        ) ?? $texto;
    }
}
