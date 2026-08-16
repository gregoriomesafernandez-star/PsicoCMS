<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategoriaSeeder::class,
            FrasePublicaSeeder::class,
            ImagenSeeder::class,
            RedSocialSeeder::class,
            PlantillaDocumentoSeeder::class,
        ]);
    }
}
