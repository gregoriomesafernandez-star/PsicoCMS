<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();
            $table->string('telefono')->unique();
            $table->string('nombre');
            $table->string('apellidos')->nullable();
            $table->string('email')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('genero')->nullable();
            $table->string('dni')->nullable();
            $table->string('direccion')->nullable();
            $table->text('motivo_consulta')->nullable();
            $table->text('notas')->nullable();
            $table->enum('estado', ['activo', 'pausado', 'alta'])->default('activo');
            $table->enum('origen', ['web', 'manual'])->default('manual');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->enum('modalidad', ['online', 'presencial']);
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->unsignedInteger('duracion');
            $table->enum('estado', ['pendiente', 'confirmada', 'completada', 'cancelada', 'no_asistio'])->default('pendiente');
            $table->text('motivo')->nullable();
            $table->text('notas')->nullable();
            $table->enum('origen', ['web', 'manual'])->default('manual');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['fecha', 'modalidad']);
        });

        Schema::create('historias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->foreignId('cita_id')->nullable()->constrained('citas')->nullOnDelete();
            $table->date('fecha_sesion');
            $table->string('titulo')->nullable();
            $table->text('contenido')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('historia_adjuntos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('historia_id')->constrained('historias')->cascadeOnDelete();
            $table->string('ruta');
            $table->string('nombre_original');
            $table->string('mime');
            $table->unsignedBigInteger('tamano');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historia_adjuntos');
        Schema::dropIfExists('historias');
        Schema::dropIfExists('citas');
        Schema::dropIfExists('pacientes');
    }
};
