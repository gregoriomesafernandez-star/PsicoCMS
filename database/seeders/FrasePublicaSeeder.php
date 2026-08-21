<?php

namespace Database\Seeders;

use App\Models\FrasePublica;
use Illuminate\Database\Seeder;

class FrasePublicaSeeder extends Seeder
{
    public function run(): void
    {
        $frases = [
            'hero' => [
                'hero_boton_cita' => 'Pedir cita',
                'hero_boton_contacto' => 'Hablamos',
                'hero_boton_conoceme' => 'Conoceme',
                'hero_punto_1' => 'Sesiones presenciales y online',
                'hero_punto_2' => 'Confidencialidad total',
                'hero_punto_3' => 'Enfoque basado en evidencia',
            ],
            'sobre_mi' => [
                'sobre_mi_titulo' => 'Sobre mi',
                'sobre_mi_subtitulo' => 'Un acompanamiento cercano y profesional',
            ],
            'servicios' => [
                'servicios_titulo' => 'Como puedo ayudarte',
                'servicios_subtitulo' => 'Terapia adaptada a lo que necesitas en cada momento',
            ],
            'especialidades' => [
                'especialidades_titulo' => 'Especialidades',
                'especialidades_subtitulo' => 'Areas en las que trabajo habitualmente',
            ],
            'planes' => [
                'planes_titulo' => 'Planes y precios',
                'planes_subtitulo' => 'Sesiones presenciales y online',
            ],
            'como_empezar' => [
                'como_empezar_titulo' => 'Como empezamos',
                'como_empezar_paso_1' => 'Pide tu cita',
                'como_empezar_paso_2' => 'Primera sesion de valoracion',
                'como_empezar_paso_3' => 'Empezamos a trabajar juntas',
            ],
            'blog' => [
                'blog_titulo' => 'Ultimos articulos',
                'blog_subtitulo' => 'Recursos y reflexiones sobre salud mental',
            ],
            'faq' => [
                'faq_titulo' => 'Preguntas frecuentes',
                'faq_subtitulo' => 'Resolvemos las dudas mas habituales',
            ],
            'reservas' => [
                'reservas_titulo' => 'Pide tu cita',
                'reservas_subtitulo' => 'Elige el dia y la hora que mejor te venga',
                'reservas_confirmacion' => 'Tu cita ha quedado reservada correctamente',
            ],
            'contacto' => [
                'contacto_titulo' => 'Donde estamos',
                'contacto_subtitulo' => 'Te espero en consulta o nos vemos online',
            ],
            'footer' => [
                'footer_derechos' => 'Todos los derechos reservados',
            ],
        ];

        foreach ($frases as $seccion => $claves) {
            foreach ($claves as $clave => $texto) {
                FrasePublica::firstOrCreate(
                    ['clave' => $clave],
                    ['seccion' => $seccion, 'texto_defecto' => $texto]
                );
            }
        }
    }
}
