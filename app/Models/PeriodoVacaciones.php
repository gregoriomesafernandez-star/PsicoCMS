<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class PeriodoVacaciones extends Model
{
    protected $table = 'periodos_vacaciones';

    protected $fillable = [
        'fecha_inicio',
        'fecha_fin',
        'motivo',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function contiene(Carbon $fecha): bool
    {
        return $fecha->betweenIncluded($this->fecha_inicio, $this->fecha_fin);
    }

    public function getDiasAttribute(): int
    {
        return $this->fecha_inicio->diffInDays($this->fecha_fin) + 1;
    }

    public function scopeVigentes($query)
    {
        return $query->whereDate('fecha_fin', '>=', now()->toDateString());
    }
}
