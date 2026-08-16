<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuracion_disponibilidad', function (Blueprint $table) {
            $table->text('mensaje_vacaciones')->nullable()->after('modo_vacaciones');
            $table->date('vuelvo_el')->nullable()->after('mensaje_vacaciones');
        });
    }

    public function down(): void
    {
        Schema::table('configuracion_disponibilidad', function (Blueprint $table) {
            $table->dropColumn(['mensaje_vacaciones', 'vuelvo_el']);
        });
    }
};
