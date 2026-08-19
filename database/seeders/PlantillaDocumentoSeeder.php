<?php

namespace Database\Seeders;

use App\Models\PlantillaDocumento;
use Illuminate\Database\Seeder;

class PlantillaDocumentoSeeder extends Seeder
{
    public function run(): void
    {
        PlantillaDocumento::proteccionDatos();
    }
}
