<?php

namespace App\Services;

use App\Models\PerfilPublico;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ContenidoWebService
{
    public const DISCO = 'public';

    public const CARPETA_PERFIL = 'perfil';

    public const CARPETA_SERVICIOS = 'servicios';

    public const ICONOS = [
        'Terapia y acompanamiento' => [
            'fa-hand-holding-heart' => 'Acompanamiento',
            'fa-heart-pulse' => 'Bienestar',
            'fa-hands-holding-child' => 'Infancia',
            'fa-people-group' => 'Grupos',
            'fa-user-group' => 'Pareja',
            'fa-comments' => 'Conversacion',
        ],
        'Especialidades' => [
            'fa-brain' => 'Mente',
            'fa-face-smile' => 'Autoestima',
            'fa-cloud-sun' => 'Ansiedad',
            'fa-moon' => 'Sueno',
            'fa-spa' => 'Mindfulness',
            'fa-shield-heart' => 'Duelo y trauma',
        ],
        'Modalidad y contacto' => [
            'fa-video' => 'Online',
            'fa-location-dot' => 'Presencial',
            'fa-phone' => 'Telefono',
            'fa-calendar-check' => 'Citas',
            'fa-book-open' => 'Formacion',
            'fa-seedling' => 'Crecimiento',
        ],
    ];

    public const PAGINAS_SEO = [
        'sobre-mi' => 'Sobre mi',
        'servicios' => 'Servicios',
        'blog' => 'Blog',
        'preguntas-frecuentes' => 'Preguntas frecuentes',
        'pide-cita' => 'Pide cita',
    ];

    public const CLAVE_SEO = 'seo_paginas';

    public function __construct(private AjustesService $ajustes)
    {
    }

    public function iconosPermitidos(): array
    {
        return array_keys(array_merge(...array_values(self::ICONOS)));
    }

    /**
     * Google entrega el mapa como un <iframe>, pero un iframe no puede guardarse
     * como HTML libre. Se queda solo la URL de insercion y la vista publica
     * construye el marco.
     */
    public function urlDelMapa(?string $entrada): ?string
    {
        $entrada = trim((string) $entrada);

        if ($entrada === '') {
            return null;
        }

        if (preg_match('/src\s*=\s*["\']([^"\']+)["\']/i', $entrada, $coincidencias)) {
            $entrada = $coincidencias[1];
        }

        return html_entity_decode($entrada, ENT_QUOTES, 'UTF-8');
    }

    public function esUrlDeMapa(?string $url): bool
    {
        if (blank($url)) {
            return true;
        }

        $partes = parse_url($url);

        if (($partes['scheme'] ?? '') !== 'https' || empty($partes['host'])) {
            return false;
        }

        $host = strtolower($partes['host']);

        return $host === 'maps.google.com'
            || preg_match('/^(www\.)?google\.[a-z.]{2,6}$/', $host) === 1;
    }

    public function guardarFoto(PerfilPublico $perfil, UploadedFile $archivo): void
    {
        $anterior = $perfil->foto;

        $perfil->foto = $archivo->store(self::CARPETA_PERFIL, self::DISCO);
        $perfil->save();

        $this->borrarArchivo($anterior);
    }

    public function borrarFoto(PerfilPublico $perfil): void
    {
        $anterior = $perfil->foto;

        $perfil->foto = null;
        $perfil->save();

        $this->borrarArchivo($anterior);
    }

    public function borrarArchivo(?string $ruta): void
    {
        if ($ruta && Storage::disk(self::DISCO)->exists($ruta)) {
            Storage::disk(self::DISCO)->delete($ruta);
        }
    }

    public function seo(): array
    {
        $guardado = (array) $this->ajustes->obtener(self::CLAVE_SEO, []);
        $paginas = [];

        foreach (array_keys(self::PAGINAS_SEO) as $pagina) {
            $paginas[$pagina] = [
                'title' => $guardado[$pagina]['title'] ?? null,
                'description' => $guardado[$pagina]['description'] ?? null,
            ];
        }

        return $paginas;
    }

    public function guardarSeo(array $paginas): void
    {
        $limpio = [];

        foreach (array_keys(self::PAGINAS_SEO) as $pagina) {
            $title = trim((string) ($paginas[$pagina]['title'] ?? ''));
            $description = trim((string) ($paginas[$pagina]['description'] ?? ''));

            if ($title === '' && $description === '') {
                continue;
            }

            $limpio[$pagina] = [
                'title' => $title ?: null,
                'description' => $description ?: null,
            ];
        }

        $this->ajustes->guardar(self::CLAVE_SEO, $limpio, 'seo');
    }
}
