<?php

namespace App\Support;

use RuntimeException;

/**
 * Alguien se ha quedado ese hueco entre que el paciente abrio el formulario y
 * lo envio. No es un error de programa: es el caso normal de dos personas
 * reservando a la vez, y se le cuenta al paciente con sus palabras.
 */
class HuecoOcupado extends RuntimeException
{
    public function __construct(string $mensaje = 'Esa hora acaba de ocuparse. Elige otra, por favor.')
    {
        parent::__construct($mensaje);
    }
}
