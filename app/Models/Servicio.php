<?php

namespace App\Models;

use App\Models\Concerns\Ordenable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Servicio extends Model
{
    use Ordenable;

    public const ICONO_POR_DEFECTO = 'fa-hand-holding-heart';

    protected $table = 'servicios';

    protected $fillable = [
        'titulo',
        'descripcion',
        'icono',
        'imagen',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function scopeBuscar(Builder $query, ?string $texto): Builder
    {
        if (blank($texto)) {
            return $query;
        }

        return $query->where(function (Builder $sub) use ($texto) {
            $sub->where('titulo', 'like', "%{$texto}%")
                ->orWhere('descripcion', 'like', "%{$texto}%");
        });
    }

    public function getIconoVisibleAttribute(): string
    {
        return $this->icono ?: self::ICONO_POR_DEFECTO;
    }

    public function getUrlImagenAttribute(): ?string
    {
        return $this->imagen ? Storage::disk('public')->url($this->imagen) : null;
    }
}
