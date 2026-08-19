<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('historias', function (Blueprint $table) {
            $table->longText('contenido')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('historias', function (Blueprint $table) {
            $table->text('contenido')->nullable()->change();
        });
    }
};
