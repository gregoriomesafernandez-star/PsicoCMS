<?php

namespace App\Services;

class TemaService
{
    public const TEMA_POR_DEFECTO = 'base';

    public const MODO_POR_DEFECTO = 'landing';

    public const MODOS = [
        'landing' => 'Una sola pagina',
        'multipagina' => 'Varias paginas',
    ];

    /** Hojas de maquetacion que aporta un tema base. El orden importa. */
    private const ESTRUCTURA = ['css/fonts.css', 'css/reset.css', 'css/styles.css', 'css/responsive.css'];

    public function __construct(private AjustesService $ajustes)
    {
    }

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
        $slug = $this->slugActivo();

        return $temas[$slug] ?? $temas[self::TEMA_POR_DEFECTO] ?? reset($temas);
    }

    public function slugActivo(): string
    {
        $slug = (string) $this->ajustes->obtener('tema_activo', self::TEMA_POR_DEFECTO);

        return $this->existe($slug) ? $slug : self::TEMA_POR_DEFECTO;
    }

    public function modoActivo(): string
    {
        $modo = (string) $this->ajustes->obtener('modo_web', self::MODO_POR_DEFECTO);

        return array_key_exists($modo, self::MODOS) ? $modo : self::MODO_POR_DEFECTO;
    }

    public function slugsDisponibles(): array
    {
        return array_keys($this->disponibles());
    }

    public function activar(string $slug): void
    {
        $this->ajustes->guardar('tema_activo', $slug, 'apariencia');
    }

    public function guardarModo(string $modo): void
    {
        $this->ajustes->guardar('modo_web', $modo, 'apariencia');
    }

    public function tema(string $slug): array
    {
        return $this->disponibles()[$slug] ?? $this->activo();
    }

    /**
     * URLs de las hojas de estilo de un tema, en orden de carga: primero la
     * maquetacion del tema del que hereda y despues su propia paleta.
     */
    public function hojas(string $slug): array
    {
        $tema = $this->tema($slug);
        $estructura = $tema['hereda'] ?? $slug;
        $hojas = [];

        foreach (self::ESTRUCTURA as $hoja) {
            $hojas[] = $this->url($estructura, $hoja);
        }

        if (! empty($tema['hoja'])) {
            $hojas[] = $this->url($slug, $tema['hoja']);
        }

        return array_values(array_filter($hojas));
    }

    /** Carpeta de imagenes de la que tira un tema (las hereda si no tiene propias). */
    public function carpetaImagenes(string $slug): string
    {
        $tema = $this->tema($slug);
        $duenio = is_dir(public_path("themes/{$slug}/img")) ? $slug : ($tema['hereda'] ?? $slug);

        return asset("themes/{$duenio}/img");
    }

    private function url(string $slug, string $relativa): ?string
    {
        $ruta = public_path("themes/{$slug}/{$relativa}");

        if (! is_file($ruta)) {
            return null;
        }

        return asset("themes/{$slug}/{$relativa}") . '?v=' . filemtime($ruta);
    }
}
