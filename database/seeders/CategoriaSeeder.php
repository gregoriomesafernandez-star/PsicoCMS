<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            ['nombre' => 'Ansiedad', 'descripcion' => 'Recursos y consejos para entender y manejar la ansiedad.'],
            ['nombre' => 'Depresion', 'descripcion' => 'Informacion sobre el estado de animo y la depresion.'],
            ['nombre' => 'Terapia de pareja', 'descripcion' => 'Comunicacion, vinculo y convivencia en pareja.'],
            ['nombre' => 'Autoestima', 'descripcion' => 'Trabajar la confianza y la valoracion personal.'],
            ['nombre' => 'Mindfulness', 'descripcion' => 'Atencion plena, respiracion y relajacion.'],
            ['nombre' => 'Consejos y bienestar', 'descripcion' => 'Habitos saludables para el dia a dia.'],
        ];

        foreach ($categorias as $orden => $categoria) {
            Categoria::firstOrCreate(
                ['slug' => Str::slug($categoria['nombre'])],
                [
                    'nombre' => $categoria['nombre'],
                    'descripcion' => $categoria['descripcion'],
                    'orden' => $orden,
                ]
            );
        }
    }
}
