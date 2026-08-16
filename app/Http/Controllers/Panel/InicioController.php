<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\FranjaHoraria;
use App\Models\Paciente;
use App\Models\PerfilPublico;
use App\Models\Servicio;
use App\Services\DisponibilidadService;
use App\Services\TemaService;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InicioController extends Controller
{
    public function __construct(
        private TemaService $temas,
        private DisponibilidadService $disponibilidad
    ) {
    }

    public function __invoke(): View
    {
        $hoy = now()->toDateString();
        $perfil = PerfilPublico::first();

        return view('panel.inicio', [
            'psicologa' => auth()->user(),
            'perfil' => $perfil,
            'pasos' => $this->pasosSugeridos($perfil),
            'tema' => $this->temas->activo(),
            'modoWeb' => ajuste('modo_web', 'landing'),
            'config' => $this->disponibilidad->configuracion(),
            'metricas' => [
                'citas_hoy' => Cita::activas()->whereDate('fecha', $hoy)->count(),
                'pacientes' => Paciente::where('estado', 'activo')->count(),
                'articulos' => DB::table('articulos')->count(),
                'proximas' => Cita::activas()->whereDate('fecha', '>', $hoy)->count(),
            ],
            'citasHoy' => Cita::with('paciente')
                ->activas()
                ->whereDate('fecha', $hoy)
                ->orderBy('hora_inicio')
                ->get(),
            'proximaCita' => Cita::with('paciente')
                ->activas()
                ->whereDate('fecha', '>', $hoy)
                ->orderBy('fecha')
                ->orderBy('hora_inicio')
                ->first(),
            'resumenOnline' => $this->disponibilidad->resumenSemanal('online'),
            'resumenPresencial' => $this->disponibilidad->resumenSemanal('presencial'),
        ]);
    }

    private function pasosSugeridos(?PerfilPublico $perfil): array
    {
        $pasos = [
            [
                'titulo' => 'Instalacion inicial completada',
                'hecho' => esta_instalado(),
                'ruta' => null,
            ],
            [
                'titulo' => 'Configurar tu disponibilidad semanal',
                'hecho' => FranjaHoraria::exists(),
                'ruta' => 'panel.disponibilidad',
            ],
            [
                'titulo' => 'Completar la informacion publica (eslogan, telefono y email)',
                'hecho' => filled($perfil?->eslogan) && filled($perfil?->telefono_citas) && filled($perfil?->email_citas),
                'ruta' => 'panel.contenido',
            ],
            [
                'titulo' => 'Subir tu foto de perfil publica',
                'hecho' => filled($perfil?->foto),
                'ruta' => 'panel.contenido',
            ],
            [
                'titulo' => 'Anadir tus servicios',
                'hecho' => Servicio::exists(),
                'ruta' => 'panel.servicios.index',
            ],
            [
                'titulo' => 'Crear preguntas frecuentes',
                'hecho' => DB::table('faqs')->exists(),
                'ruta' => 'panel.faqs.index',
            ],
            [
                'titulo' => 'Configurar las notificaciones por email (SMTP)',
                'hecho' => filled(ajuste('smtp_email')),
                'ruta' => 'panel.notificaciones',
            ],
            [
                'titulo' => 'Publicar tu primer articulo del blog',
                'hecho' => DB::table('articulos')->where('publicado', true)->exists(),
                'ruta' => 'panel.blog.index',
            ],
        ];

        $completados = count(array_filter($pasos, fn (array $paso) => $paso['hecho']));

        return [
            'lista' => $pasos,
            'completados' => $completados,
            'total' => count($pasos),
            'porcentaje' => (int) round($completados / count($pasos) * 100),
        ];
    }
}
