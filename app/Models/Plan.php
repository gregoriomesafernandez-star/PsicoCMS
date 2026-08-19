<?php

namespace App\Models;

use App\Models\Concerns\Ordenable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use Ordenable;

    public const MODALIDADES = [
        'presencial' => 'Presencial',
        'online' => 'Online',
    ];

    protected $table = 'planes';

    protected $fillable = [
        'nombre',
        'modalidad',
        'precio',
        'descripcion',
        'caracteristicas',
        'destacado',
        'orden',
    ];

    protected $casts = [
        'caracteristicas' => 'array',
        'destacado' => 'boolean',
        'precio' => 'decimal:2',
        'orden' => 'integer',
    ];

    public function scopeDeModalidad(Builder $query, ?string $modalidad): Builder
    {
        return blank($modalidad) ? $query : $query->where('modalidad', $modalidad);
    }

    public function getModalidadVisibleAttribute(): string
    {
        return self::MODALIDADES[$this->modalidad] ?? $this->modalidad;
    }

    public function getPrecioVisibleAttribute(): string
    {
        return number_format((float) $this->precio, 2, ',', '.') . ' €';
    }

    public function getCaracteristicasTextoAttribute(): string
    {
        return implode("\n", $this->caracteristicas ?? []);
    }

    public static function caracteristicasDesdeTexto(?string $texto): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $texto))
            ->map(fn (string $linea) => trim($linea))
            ->filter()
            ->take(12)
            ->values()
            ->all();
    }
}
