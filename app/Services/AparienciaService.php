<?php

namespace App\Services;

class AparienciaService
{
    public const TEMA_POR_DEFECTO = 'claro';

    public const COLOR_POR_DEFECTO = 'azul';

    public const TEMAS = [
        'claro' => ['Tema claro', 'fa-sun', 'Fondo blanco. Comodo con luz de dia.'],
        'oscuro' => ['Tema oscuro', 'fa-moon', 'Fondo oscuro. Descansa la vista de noche.'],
    ];

    /** Ocho colores sobrios, del tipo que se usa en consultas de psicologia. */
    public const COLORES = [
        'azul' => ['Azul sereno', '#3d5a80'],
        'salvia' => ['Verde salvia', '#5f8d6e'],
        'lavanda' => ['Lavanda', '#7b6a9e'],
        'terracota' => ['Terracota', '#b06a4f'],
        'oceano' => ['Oceano', '#2f7d8c'],
        'ciruela' => ['Ciruela', '#8a5068'],
        'ambar' => ['Ambar', '#a1793a'],
        'grafito' => ['Grafito', '#4a5568'],
    ];

    private const CLAVE_TEMA = 'panel_tema';

    private const CLAVE_COLOR = 'panel_color';

    public function __construct(private AjustesService $ajustes)
    {
    }

    public function tema(): string
    {
        $tema = (string) $this->ajustes->obtener(self::CLAVE_TEMA, self::TEMA_POR_DEFECTO);

        return array_key_exists($tema, self::TEMAS) ? $tema : self::TEMA_POR_DEFECTO;
    }

    public function color(): string
    {
        $color = (string) $this->ajustes->obtener(self::CLAVE_COLOR, self::COLOR_POR_DEFECTO);

        return array_key_exists($color, self::COLORES) ? $color : self::COLOR_POR_DEFECTO;
    }

    public function guardar(string $tema, string $color): void
    {
        $this->ajustes->guardarVarios([
            self::CLAVE_TEMA => $tema,
            self::CLAVE_COLOR => $color,
        ], 'apariencia');
    }

    public function actual(): array
    {
        return ['tema' => $this->tema(), 'color' => $this->color()];
    }

    public function hexDelColor(string $color): string
    {
        return self::COLORES[$color][1] ?? self::COLORES[self::COLOR_POR_DEFECTO][1];
    }
}
