<?php

namespace App\Models;

use App\Services\SanitizadorHtml;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $table = 'faqs';

    protected $fillable = [
        'pregunta',
        'respuesta',
        'orden',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
        'orden' => 'integer',
    ];

    public function scopeOrdenadas(Builder $query): Builder
    {
        return $query->orderBy('orden')->orderBy('id');
    }

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('activa', true);
    }

    public function scopeBuscar(Builder $query, ?string $texto): Builder
    {
        if (blank($texto)) {
            return $query;
        }

        return $query->where(function (Builder $sub) use ($texto) {
            $sub->where('pregunta', 'like', "%{$texto}%")
                ->orWhere('respuesta', 'like', "%{$texto}%");
        });
    }

    public static function siguienteOrden(): int
    {
        return (int) static::max('orden') + 1;
    }

    /**
     * Deja los ordenes consecutivos (1, 2, 3...) respetando el orden actual.
     * Sin esto, dos preguntas con el mismo numero harian imposible intercambiarlas.
     */
    public static function renumerar(): void
    {
        static::ordenadas()->get()->each(function (self $faq, int $indice) {
            $faq->updateQuietly(['orden' => $indice + 1]);
        });
    }

    public function getRespuestaEnTextoAttribute(): string
    {
        return app(SanitizadorHtml::class)->aTexto($this->respuesta);
    }
}
