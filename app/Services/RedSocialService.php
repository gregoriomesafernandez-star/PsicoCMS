<?php

namespace App\Services;

use App\Models\RedSocial;

class RedSocialService
{
    /** red => [nombre, icono de Font Awesome brands, ejemplo de URL] */
    public const REDES = [
        'instagram' => ['Instagram', 'fa-instagram', 'https://instagram.com/tu_usuario'],
        'facebook' => ['Facebook', 'fa-facebook', 'https://facebook.com/tu_pagina'],
        'linkedin' => ['LinkedIn', 'fa-linkedin', 'https://linkedin.com/in/tu_perfil'],
        'x' => ['X (Twitter)', 'fa-x-twitter', 'https://x.com/tu_usuario'],
        'youtube' => ['YouTube', 'fa-youtube', 'https://youtube.com/@tu_canal'],
        'tiktok' => ['TikTok', 'fa-tiktok', 'https://tiktok.com/@tu_usuario'],
        'whatsapp' => ['WhatsApp', 'fa-whatsapp', 'https://wa.me/34600112233'],
    ];

    public function claves(): array
    {
        return array_keys(self::REDES);
    }

    /** Crea las redes del catalogo que falten, sin duplicar las existentes. */
    public function sincronizar(): void
    {
        foreach ($this->claves() as $orden => $red) {
            RedSocial::firstOrCreate(['red' => $red], ['orden' => $orden + 1, 'activa' => false]);
        }
    }

    public function ordenadas(): \Illuminate\Support\Collection
    {
        return RedSocial::orderBy('orden')->orderBy('id')->get()
            ->filter(fn (RedSocial $red) => isset(self::REDES[$red->red]))
            ->values();
    }

    /** Las que se pintan en la web: activas y con una URL puesta. */
    public function visibles(): \Illuminate\Support\Collection
    {
        return $this->ordenadas()->filter(
            fn (RedSocial $red) => $red->activa && filled($red->url)
        )->values();
    }

    public function datos(string $red): array
    {
        return self::REDES[$red] ?? [$red, 'fa-link', ''];
    }

    /**
     * Guarda las URLs y la visibilidad. Una red sin URL no puede quedar activa:
     * seria un icono que no lleva a ninguna parte.
     */
    public function guardar(array $urls, array $activas): void
    {
        foreach ($this->ordenadas() as $red) {
            $url = trim((string) ($urls[$red->red] ?? ''));

            $red->update([
                'url' => $url ?: null,
                'activa' => $url !== '' && in_array($red->red, $activas, true),
            ]);
        }
    }

    public function resumen(): array
    {
        $total = count(self::REDES);
        $visibles = $this->visibles()->count();

        return [
            'total' => $total,
            'visibles' => $visibles,
            'sin_configurar' => $total - $visibles,
        ];
    }
}
