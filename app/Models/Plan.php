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

    /** Sin decimales cuando el precio es redondo: "55 €" en vez de "55,00 €". */
    public function getPrecioCortoAttribute(): string
    {
        $precio = (float) $this->precio;
        $decimales = fmod($precio, 1) === 0.0 ? 0 : 2;

        return number_format($precio, $decimales, ',', '.') . ' €';
    }

    /** Duracion de la sesion que corresponde a la modalidad del plan. */
    public function getDuracionAttribute(): ?int
    {
        $configuracion = ConfiguracionDisponibilidad::actual();

        $minutos = $this->modalidad === 'online'
            ? $configuracion->duracion_online
            : $configuracion->duracion_presencial;

        return $minutos > 0 ? (int) $minutos : null;
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
