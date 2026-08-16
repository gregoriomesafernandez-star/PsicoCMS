<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuracion_disponibilidad', function (Blueprint $table) {
            $table->boolean('descanso_online_activo')->default(false)->after('descanso_presencial');
            $table->boolean('descanso_presencial_activo')->default(false)->after('descanso_online_activo');
        });

        DB::table('configuracion_disponibilidad')->update([
            'descanso_online_activo' => DB::raw('descanso_activo'),
            'descanso_presencial_activo' => DB::raw('descanso_activo'),
        ]);

        Schema::table('configuracion_disponibilidad', function (Blueprint $table) {
            $table->dropColumn('descanso_activo');
        });
    }

    public function down(): void
    {
        Schema::table('configuracion_disponibilidad', function (Blueprint $table) {
            $table->boolean('descanso_activo')->default(false)->after('descanso_presencial');
        });

        DB::table('configuracion_disponibilidad')->update([
            'descanso_activo' => DB::raw('descanso_online_activo'),
        ]);

        Schema::table('configuracion_disponibilidad', function (Blueprint $table) {
            $table->dropColumn(['descanso_online_activo', 'descanso_presencial_activo']);
        });
    }
};
