<?php

namespace Database\Seeders;

use App\Models\Imagen;
use Illuminate\Database\Seeder;

class ImagenSeeder extends Seeder
{
    public function run(): void
    {
        $slots = [
            'hero_principal' => 'Psicologa recibiendo a una paciente en consulta',
            'sobre_mi' => 'Retrato de la psicologa',
            'servicio_1' => 'Sesion de terapia individual',
            'servicio_2' => 'Sesion de terapia de pareja',
            'servicio_3' => 'Sesion de terapia online',
            'servicio_4' => 'Sesion de terapia familiar',
            'terapia_1' => 'Espacio de terapia acogedor',
            'terapia_2' => 'Consulta luminosa',
            'terapia_3' => 'Sala de espera',
            'blog_defecto' => 'Imagen por defecto de los articulos del blog',
            'fondo_contacto' => 'Fondo de la seccion de contacto',
            'fondo_servicios' => 'Fondo de la seccion de servicios',
        ];

        foreach ($slots as $clave => $alt) {
            Imagen::firstOrCreate(['clave_slot' => $clave], ['alt' => $alt]);
        }
    }
}
