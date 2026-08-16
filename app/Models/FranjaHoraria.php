<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FranjaHoraria extends Model
{
    protected $table = 'franjas_horarias';

    protected $fillable = [
        'modalidad',
        'dia_semana',
        'hora_inicio',
    ];

    public function scopeModalidad($query, string $modalidad)
    {
        return $query->where('modalidad', $modalidad);
    }

    public function getHoraCortaAttribute(): string
    {
        return substr($this->hora_inicio, 0, 5);
    }
}
