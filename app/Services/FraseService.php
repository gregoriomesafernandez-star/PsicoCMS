<?php

namespace App\Services;

use App\Models\FrasePublica;
use Illuminate\Support\Facades\Cache;

class FraseService
{
    private const CACHE_KEY = 'psicocms.frases';

    /** Nombre legible de cada seccion, para agrupar la pantalla. */
    public const SECCIONES = [
        'hero' => ['Portada', 'fa-house', 'Los botones y textos de lo primero que se ve.'],
        'sobre_mi' => ['Sobre mi', 'fa-user-pen', 'Titulos de tu presentacion.'],
        'servicios' => ['Servicios', 'fa-hand-holding-heart', 'Encabezados del bloque de servicios.'],
        'especialidades' => ['Especialidades', 'fa-brain', 'Encabezados de tus areas de trabajo.'],
        'planes' => ['Planes y precios', 'fa-tags', 'Encabezados de las tarifas.'],
        'como_empezar' => ['Como empezar', 'fa-shoe-prints', 'Los pasos que ve un paciente nuevo.'],
        'blog' => ['Blog', 'fa-newspaper', 'Encabezados de los articulos.'],
        'faq' => ['Preguntas frecuentes', 'fa-circle-question', 'Encabezados de las dudas.'],
        'reservas' => ['Reserva de citas', 'fa-calendar-check', 'Textos del formulario de reserva.'],
        'contacto' => ['Contacto', 'fa-location-dot', 'Encabezados de donde estas.'],
        'footer' => ['Pie de pagina', 'fa-shoe-prints', 'Lo que cierra tu web.'],
    ];

    public function texto(string $clave): ?string
    {
        return $this->cacheadas()[$clave] ?? null;
    }

    /** Frases agrupadas por seccion, en el orden del catalogo. */
    public function agrupadas(): array
    {
        $frases = FrasePublica::orderBy('clave')->get()->groupBy('seccion');
        $grupos = [];

        foreach (self::SECCIONES as $clave => [$nombre, $icono, $descripcion]) {
            if ($frases->has($clave)) {
                $grupos[$clave] = [
                    'nombre' => $nombre,
                    'icono' => $icono,
                    'descripcion' => $descripcion,
                    'frases' => $frases->get($clave),
                ];
            }
        }

        // Cualquier seccion que llegue de un seeder futuro y no este en el catalogo.
        foreach ($frases as $seccion => $lista) {
            if (! isset($grupos[$seccion])) {
                $grupos[$seccion] = [
                    'nombre' => ucfirst(str_replace('_', ' ', $seccion)),
                    'icono' => 'fa-quote-left',
                    'descripcion' => null,
                    'frases' => $lista,
                ];
            }
        }

        return $grupos;
    }

    public function resumen(): array
    {
        $total = FrasePublica::count();
        $personalizadas = FrasePublica::whereNotNull('texto_personalizado')
            ->where('texto_personalizado', '!=', '')
            ->count();

        return [
            'total' => $total,
            'personalizadas' => $personalizadas,
            'por_defecto' => $total - $personalizadas,
        ];
    }

    /** Guarda solo lo que difiere del texto por defecto. */
    public function guardar(array $textos): int
    {
        $cambiadas = 0;

        foreach (FrasePublica::whereIn('clave', array_keys($textos))->get() as $frase) {
            $nuevo = trim((string) ($textos[$frase->clave] ?? ''));
            $personalizado = ($nuevo === '' || $nuevo === $frase->texto_defecto) ? null : $nuevo;

            if ($frase->texto_personalizado !== $personalizado) {
                $frase->update(['texto_personalizado' => $personalizado]);
                $cambiadas++;
            }
        }

        $this->limpiarCache();

        return $cambiadas;
    }

    public function restaurar(FrasePublica $frase): void
    {
        $frase->update(['texto_personalizado' => null]);

        $this->limpiarCache();
    }

    public function restaurarTodas(): int
    {
        $cuantas = FrasePublica::whereNotNull('texto_personalizado')->count();

        FrasePublica::whereNotNull('texto_personalizado')->update(['texto_personalizado' => null]);

        $this->limpiarCache();

        return $cuantas;
    }

    public function limpiarCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function cacheadas(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return FrasePublica::all()
                ->mapWithKeys(fn (FrasePublica $frase) => [$frase->clave => $frase->texto])
                ->all();
        });
    }
}
