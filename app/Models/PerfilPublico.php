<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerfilPublico extends Model
{
    protected $table = 'perfil_publico';

    protected $fillable = [
        'nombre_publico',
        'eslogan',
        'num_colegiado',
        'telefono_citas',
        'email_citas',
        'sobre_mi',
        'direccion',
        'lugar_consulta',
        'mapa_embed',
        'horarios',
        'foto',
        'meta_title',
        'meta_description',
    ];

    public static function actual(): self
    {
        return static::firstOrCreate([]);
    }
}
