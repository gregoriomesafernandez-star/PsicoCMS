<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait Ordenable
{
    public function scopeOrdenados(Builder $query): Builder
    {
        return $query->orderBy('orden')->orderBy('id');
    }

    public static function siguienteOrden(): int
    {
        return (int) static::max('orden') + 1;
    }

    public static function renumerar(): void
    {
        static::ordenados()->get()->each(function (self $registro, int $indice) {
            $registro->updateQuietly(['orden' => $indice + 1]);
        });
    }

    public static function intercambiar(self $registro, string $direccion): bool
    {
        static::renumerar();
        $registro->refresh();

        $vecino = static::query()
            ->where('orden', $direccion === 'arriba' ? '<' : '>', $registro->orden)
            ->when($direccion === 'arriba',
                fn (Builder $query) => $query->orderByDesc('orden'),
                fn (Builder $query) => $query->orderBy('orden'))
            ->first();

        if (! $vecino) {
            return false;
        }

        $posicion = $registro->orden;
        $registro->update(['orden' => $vecino->orden]);
        $vecino->update(['orden' => $posicion]);

        return true;
    }
}
