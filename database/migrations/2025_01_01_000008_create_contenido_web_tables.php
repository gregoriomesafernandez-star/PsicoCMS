<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imagenes', function (Blueprint $table) {
            $table->id();
            $table->string('clave_slot')->unique();
            $table->string('ruta')->nullable();
            $table->string('alt')->nullable();
            $table->timestamps();
        });

        Schema::create('frases_publicas', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique();
            $table->string('seccion')->index();
            $table->text('texto_defecto')->nullable();
            $table->text('texto_personalizado')->nullable();
            $table->timestamps();
        });

        Schema::create('redes_sociales', function (Blueprint $table) {
            $table->id();
            $table->string('red');
            $table->string('url')->nullable();
            $table->boolean('activa')->default(false);
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();
        });

        Schema::create('plantillas_documento', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->longText('contenido')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plantillas_documento');
        Schema::dropIfExists('redes_sociales');
        Schema::dropIfExists('frases_publicas');
        Schema::dropIfExists('imagenes');
    }
};
