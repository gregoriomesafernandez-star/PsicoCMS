<?php

namespace App\Services;

use App\Models\Imagen;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ImagenService
{
    public const DISCO = 'public';

    public const CARPETA = 'imagenes-web';

    private const CACHE_KEY = 'psicocms.imagenes';

    /**
     * Huecos de imagen de la parte publica. Cada uno declara donde aparece y
     * que imagen de stock del tema se usa mientras no se suba otra.
     */
    public const CATALOGO = [
        'Portada' => [
            'hero_principal' => ['Foto principal', 'psicologa.png', 'La foto grande de la portada. Se ve mejor con el fondo recortado (PNG).'],
            'hero_forma_1' => ['Forma decorativa 1', 'shape1.png', 'Adorno que acompana a la foto de portada.'],
            'hero_forma_2' => ['Forma decorativa 2', 'shape2.png', 'Segundo adorno de la portada.'],
        ],
        'Sobre mi' => [
            'sobre_mi' => ['Foto de la seccion Sobre mi', 'exp1.jpg', 'Acompana a tu presentacion.'],
            'sobre_mi_secundaria' => ['Foto secundaria', 'why.jpg', 'Imagen de apoyo en la seccion Sobre mi.'],
        ],
        'Servicios' => [
            'servicio_1' => ['Imagen del servicio 1', 'servicio1.jpg', 'Se usa si el servicio no tiene imagen propia.'],
            'servicio_2' => ['Imagen del servicio 2', 'servicio2.jpg', 'Se usa si el servicio no tiene imagen propia.'],
            'servicio_3' => ['Imagen del servicio 3', 'servicio3.jpg', 'Se usa si el servicio no tiene imagen propia.'],
            'servicio_4' => ['Imagen del servicio 4', 'servicio4.jpg', 'Se usa si el servicio no tiene imagen propia.'],
            'fondo_servicios' => ['Fondo de servicios', 'bg-services.png', 'Fondo de la seccion de servicios.'],
        ],
        'Terapias' => [
            'terapia_1' => ['Terapia 1', 'terapia1.jpg', 'Imagen del bloque de terapias.'],
            'terapia_2' => ['Terapia 2', 'terapia2.jpg', 'Imagen del bloque de terapias.'],
            'terapia_3' => ['Terapia 3', 'terapia3.jpg', 'Imagen del bloque de terapias.'],
        ],
        'Blog y preguntas' => [
            'blog_defecto' => ['Imagen por defecto del blog', 'blog1.jpg', 'Se usa en los articulos que no tienen imagen destacada.'],
            'preguntas' => ['Imagen de preguntas frecuentes', 'question1.jpg', 'Acompana a la seccion de preguntas.'],
        ],
        'Contacto y marca' => [
            'fondo_contacto' => ['Fondo de contacto', 'bg-contactnow.png', 'Fondo de la seccion de pedir cita.'],
            'fondo_estadisticas' => ['Fondo de cifras', 'bg-stats.png', 'Fondo del bloque de cifras.'],
            'logo' => ['Logotipo', 'logo.png', 'Se ve en la cabecera de tu web.'],
        ],
    ];

    /** Alt por defecto de cada hueco, para que ninguna imagen se quede sin describir. */
    private const ALT_POR_DEFECTO = [
        'hero_principal' => 'Psicologa en su consulta',
        'sobre_mi' => 'Retrato de la psicologa',
        'sobre_mi_secundaria' => 'Espacio de terapia',
        'blog_defecto' => 'Imagen del articulo',
        'logo' => 'Logotipo de la consulta',
    ];

    public function __construct(private TemaService $temas)
    {
    }

    /** Todos los huecos en una sola lista, sin agrupar. */
    public function slots(): array
    {
        return array_merge(...array_values(self::CATALOGO));
    }

    public function claves(): array
    {
        return array_keys($this->slots());
    }

    public function existe(string $clave): bool
    {
        return array_key_exists($clave, $this->slots());
    }

    /** Crea en base de datos los huecos del catalogo que todavia no existan. */
    public function sincronizar(): void
    {
        foreach ($this->slots() as $clave => [$nombre, $stock, $descripcion]) {
            Imagen::firstOrCreate(
                ['clave_slot' => $clave],
                ['alt' => self::ALT_POR_DEFECTO[$clave] ?? $nombre]
            );
        }

        $this->limpiarCache();
    }

    /** Imagenes agrupadas por seccion, con su estado, para pintar la pantalla. */
    public function agrupadas(): array
    {
        $guardadas = Imagen::whereIn('clave_slot', $this->claves())->get()->keyBy('clave_slot');
        $grupos = [];

        foreach (self::CATALOGO as $seccion => $slots) {
            foreach ($slots as $clave => [$nombre, $stock, $descripcion]) {
                $imagen = $guardadas->get($clave);

                if (! $imagen) {
                    continue;
                }

                $url = $this->url($clave);

                $grupos[$seccion][] = [
                    'modelo' => $imagen,
                    'clave' => $clave,
                    'nombre' => $nombre,
                    'descripcion' => $descripcion,
                    'url' => $url,
                    'urlStock' => $this->urlDeStock($stock),
                    'personalizada' => filled($imagen->ruta),
                    'transparente' => $this->admiteTransparencia($url),
                ];
            }
        }

        return $grupos;
    }

    public function resumen(): array
    {
        $total = count($this->claves());
        $personalizadas = Imagen::whereIn('clave_slot', $this->claves())->whereNotNull('ruta')->count();

        return [
            'total' => $total,
            'personalizadas' => $personalizadas,
            'de_stock' => $total - $personalizadas,
        ];
    }

    /** URL que debe pintarse: la subida si existe, si no la de stock del tema activo. */
    public function url(string $clave): ?string
    {
        $subida = $this->rutasSubidas()[$clave] ?? null;

        if ($subida && Storage::disk(self::DISCO)->exists($subida)) {
            return Storage::disk(self::DISCO)->url($subida);
        }

        $slot = $this->slots()[$clave] ?? null;

        return $slot ? $this->urlDeStock($slot[1]) : null;
    }

    /** Si la psicologa ha subido su propia imagen para ese hueco. */
    public function personalizada(string $clave): bool
    {
        $subida = $this->rutasSubidas()[$clave] ?? null;

        return (bool) $subida && Storage::disk(self::DISCO)->exists($subida);
    }

    public function alt(string $clave): string
    {
        return (string) ($this->alts()[$clave] ?? self::ALT_POR_DEFECTO[$clave] ?? '');
    }

    public function guardar(Imagen $imagen, ?UploadedFile $archivo, ?string $alt): void
    {
        if ($archivo) {
            $anterior = $imagen->ruta;
            $imagen->ruta = $archivo->store(self::CARPETA, self::DISCO);
            $this->borrarArchivo($anterior);
        }

        $imagen->alt = $alt;
        $imagen->save();

        $this->limpiarCache();
    }

    /** Vuelve a la imagen de stock: borra la subida del disco y deja la ruta vacia. */
    public function restaurar(Imagen $imagen): void
    {
        $anterior = $imagen->ruta;

        $imagen->ruta = null;
        $imagen->save();

        $this->borrarArchivo($anterior);
        $this->limpiarCache();
    }

    public function limpiarCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Un PNG o un WEBP puede tener el fondo recortado, asi que se muestra
     * centrado sobre el fondo de cuadros. Las fotos llenan su recuadro.
     */
    private function admiteTransparencia(?string $url): bool
    {
        $extension = strtolower(pathinfo(parse_url((string) $url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));

        return in_array($extension, ['png', 'webp', 'svg'], true);
    }

    private function urlDeStock(string $fichero): string
    {
        return $this->temas->carpetaImagenes($this->temas->slugActivo()) . '/' . $fichero;
    }

    private function rutasSubidas(): array
    {
        return $this->cacheadas()['rutas'];
    }

    private function alts(): array
    {
        return $this->cacheadas()['alts'];
    }

    private function cacheadas(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            $imagenes = Imagen::all();

            return [
                'rutas' => $imagenes->pluck('ruta', 'clave_slot')->filter()->all(),
                'alts' => $imagenes->pluck('alt', 'clave_slot')->all(),
            ];
        });
    }

    private function borrarArchivo(?string $ruta): void
    {
        if ($ruta && Storage::disk(self::DISCO)->exists($ruta)) {
            Storage::disk(self::DISCO)->delete($ruta);
        }
    }
}
