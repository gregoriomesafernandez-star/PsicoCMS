<?php

namespace Database\Seeders;

use App\Services\ImagenService;
use Illuminate\Database\Seeder;

class ImagenSeeder extends Seeder
{
    public function run(): void
    {
        app(ImagenService::class)->sincronizar();
    }
}
