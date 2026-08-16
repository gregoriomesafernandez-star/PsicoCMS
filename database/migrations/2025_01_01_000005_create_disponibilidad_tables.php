<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_disponibilidad', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('duracion_online')->default(50);
            $table->unsignedInteger('duracion_presencial')->default(50);
            $table->unsignedInteger('descanso_online')->default(10);
            $table->unsignedInteger('descanso_presencial')->default(10);
            $table->boolean('descanso_activo')->default(false);
            $table->time('hora_inicio')->default('09:00');
            $table->time('hora_fin')->default('20:00');
            $table->boolean('modo_vacaciones')->default(false);
            $table->boolean('requiere_reconfig_online')->default(false);
            $table->boolean('requiere_reconfig_presencial')->default(false);
            $table->timestamps();
        });

        Schema::create('franjas_horarias', function (Blueprint $table) {
            $table->id();
            $table->enum('modalidad', ['online', 'presencial']);
            $table->unsignedTinyInteger('dia_semana');
            $table->time('hora_inicio');
            $table->timestamps();

            $table->unique(['modalidad', 'dia_semana', 'hora_inicio'], 'franja_unica');
        });

        Schema::create('periodos_vacaciones', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('motivo')->nullable();
            $table->timestamps();

            $table->index(['fecha_inicio', 'fecha_fin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periodos_vacaciones');
        Schema::dropIfExists('franjas_horarias');
        Schema::dropIfExists('configuracion_disponibilidad');
    }
};
