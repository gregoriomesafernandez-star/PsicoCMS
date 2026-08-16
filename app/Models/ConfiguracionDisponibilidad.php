<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionDisponibilidad extends Model
{
    protected $table = 'configuracion_disponibilidad';

    protected $fillable = [
        'duracion_online',
        'duracion_presencial',
        'descanso_online',
        'descanso_presencial',
        'descanso_online_activo',
        'descanso_presencial_activo',
        'hora_inicio',
        'hora_fin',
        'modo_vacaciones',
        'mensaje_vacaciones',
        'vuelvo_el',
        'requiere_reconfig_online',
        'requiere_reconfig_presencial',
    ];

    protected $casts = [
        'descanso_online_activo' => 'boolean',
        'descanso_presencial_activo' => 'boolean',
        'modo_vacaciones' => 'boolean',
        'vuelvo_el' => 'date',
        'requiere_reconfig_online' => 'boolean',
        'requiere_reconfig_presencial' => 'boolean',
    ];

    public static function actual(): self
    {
        return static::firstOrCreate([]);
    }
}
