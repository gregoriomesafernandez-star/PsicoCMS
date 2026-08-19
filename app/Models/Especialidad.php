<?php

namespace App\Models;

use App\Models\Concerns\Ordenable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Especialidad extends Model
{
    use Ordenable;

    public const ICONO_POR_DEFECTO = 'fa-brain';

    protected $table = 'especialidades';

    protected $fillable = [
        'nombre',
        'descripcion',
        'icono',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function scopeBuscar(Builder $query, ?string $texto): Builder
    {
        if (blank($texto)) {
            return $query;
        }

        return $query->where(function (Builder $sub) use ($texto) {
            $sub->where('nombre', 'like', "%{$texto}%")
                ->orWhere('descripcion', 'like', "%{$texto}%");
        });
    }

    public function getIconoVisibleAttribute(): string
    {
        return $this->icono ?: self::ICONO_POR_DEFECTO;
    }
}
