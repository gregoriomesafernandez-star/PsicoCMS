<?php

namespace Database\Seeders;

use App\Models\RedSocial;
use Illuminate\Database\Seeder;

class RedSocialSeeder extends Seeder
{
    public function run(): void
    {
        $redes = ['instagram', 'facebook', 'linkedin', 'x', 'youtube', 'tiktok', 'whatsapp'];

        foreach ($redes as $orden => $red) {
            RedSocial::firstOrCreate(['red' => $red], ['orden' => $orden, 'activa' => false]);
        }
    }
}
