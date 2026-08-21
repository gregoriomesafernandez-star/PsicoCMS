<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Services\SitioService;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __construct(private SitioService $sitio)
    {
    }

    /** En modo landing solo hay una direccion que indexar. */
    public function sitemap(): Response
    {
        $urls = [['url' => route('home'), 'prioridad' => '1.0']];

        if (! $this->sitio->esLanding()) {
            foreach ($this->sitio->navegacion() as $enlace) {
                if ($enlace['ancla'] !== 'inicio') {
                    $urls[] = ['url' => $enlace['url'], 'prioridad' => '0.8'];
                }
            }
        }

        return response()
            ->view('publico.sitemap', ['urls' => $urls, 'fecha' => now()->toAtomString()])
            ->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $lineas = [
            'User-agent: *',
            // El panel y el asistente no tienen nada que indexar.
            'Disallow: /panel-psicologa',
            'Disallow: /acceso-psicologa',
            'Disallow: /instalacion',
            '',
            'Sitemap: ' . route('publico.sitemap'),
        ];

        return response(implode("\n", $lineas) . "\n")
            ->header('Content-Type', 'text/plain');
    }
}
