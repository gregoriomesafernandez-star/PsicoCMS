<?php

namespace App\Services;

class ModuloService
{
    /**
     * Partes de la web publica que se pueden desactivar. Cada una declara su
     * nombre, icono, explicacion y los elementos del menu lateral que dejan de
     * tener sentido cuando se apaga.
     */
    public const MODULOS = [
        'blog' => [
            'nombre' => 'Blog',
            'icono' => 'fa-newspaper',
            'descripcion' => 'La seccion de articulos de tu web.',
            'aviso' => 'Si lo desactivas, tus articulos dejan de verse en la web. No se borra ninguno.',
            'menu' => ['panel.blog.index', 'panel.categorias.index'],
        ],
        'reservas' => [
            'nombre' => 'Reserva de citas',
            'icono' => 'fa-calendar-check',
            'descripcion' => 'El formulario con el que tus pacientes piden cita solos.',
            'aviso' => 'Si lo desactivas, solo podras crear citas tu desde el panel.',
            'menu' => [],
        ],
        'faq' => [
            'nombre' => 'Preguntas frecuentes',
            'icono' => 'fa-circle-question',
            'descripcion' => 'El bloque de dudas habituales.',
            'aviso' => 'Si lo desactivas, tus preguntas dejan de verse en la web. No se borra ninguna.',
            'menu' => ['panel.faqs.index'],
        ],
        'especialidades' => [
            'nombre' => 'Especialidades',
            'icono' => 'fa-brain',
            'descripcion' => 'Los temas que trabajas en consulta.',
            'aviso' => 'Si lo desactivas, no apareceran en tu web.',
            'menu' => ['panel.especialidades.index'],
        ],
        'planes' => [
            'nombre' => 'Planes y precios',
            'icono' => 'fa-tags',
            'descripcion' => 'Tus tarifas publicas.',
            'aviso' => 'Util si prefieres no mostrar precios y hablarlos en la primera llamada.',
            'menu' => [],
        ],
        'mapa' => [
            'nombre' => 'Mapa de la consulta',
            'icono' => 'fa-location-dot',
            'descripcion' => 'El mapa de Google con tu direccion.',
            'aviso' => 'Util si atiendes solo online o prefieres no publicar la direccion.',
            'menu' => [],
        ],
        'redes' => [
            'nombre' => 'Redes sociales',
            'icono' => 'fa-share-nodes',
            'descripcion' => 'Los iconos de tus redes en el pie de la web.',
            'aviso' => 'Si lo desactivas, no se vera ningun icono de redes.',
            'menu' => ['panel.redes'],
        ],
    ];

    public function __construct(private AjustesService $ajustes)
    {
    }

    public function claves(): array
    {
        return array_keys(self::MODULOS);
    }

    public function existe(string $clave): bool
    {
        return array_key_exists($clave, self::MODULOS);
    }

    public function activo(string $clave): bool
    {
        if (! $this->existe($clave)) {
            return false;
        }

        return (bool) $this->ajustes->obtener('modulo_' . $clave, true);
    }

    /** Estado de todos los modulos, para pintar la pantalla. */
    public function estado(): array
    {
        $estado = [];

        foreach (self::MODULOS as $clave => $modulo) {
            $estado[$clave] = $modulo + ['activo' => $this->activo($clave)];
        }

        return $estado;
    }

    /** Guarda solo las claves del catalogo: lo que no venga marcado se apaga. */
    public function guardar(array $activos): void
    {
        $valores = [];

        foreach ($this->claves() as $clave) {
            $valores['modulo_' . $clave] = in_array($clave, $activos, true);
        }

        $this->ajustes->guardarVarios($valores, 'modulos');
    }

    /** Rutas del menu lateral que se ocultan por tener su modulo apagado. */
    public function rutasOcultas(): array
    {
        $rutas = [];

        foreach (self::MODULOS as $clave => $modulo) {
            if (! $this->activo($clave)) {
                $rutas = array_merge($rutas, $modulo['menu']);
            }
        }

        return $rutas;
    }

    public function resumen(): array
    {
        $activos = count(array_filter($this->claves(), fn (string $clave) => $this->activo($clave)));

        return [
            'total' => count(self::MODULOS),
            'activos' => $activos,
            'apagados' => count(self::MODULOS) - $activos,
        ];
    }
}
