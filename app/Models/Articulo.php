<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Articulo extends Model
{
    public const ESTADOS = [
        'borrador' => 'Borrador',
        'publicado' => 'Publicado',
        'programado' => 'Programado',
    ];

    protected $table = 'articulos';

    protected $fillable = [
        'categoria_id',
        'titulo',
        'slug',
        'extracto',
        'contenido',
        'imagen',
        'publicado',
        'publicado_at',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'publicado' => 'boolean',
        'publicado_at' => 'datetime',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function scopeVisibles(Builder $query): Builder
    {
        return $query->where('publicado', true)
            ->where(function (Builder $sub) {
                $sub->whereNull('publicado_at')->orWhere('publicado_at', '<=', now());
            });
    }

    public function getEstadoAttribute(): string
    {
        if (! $this->publicado) {
            return 'borrador';
        }

        return $this->publicado_at && $this->publicado_at->isFuture() ? 'programado' : 'publicado';
    }

    public function getEstadoLegibleAttribute(): string
    {
        return self::ESTADOS[$this->estado];
    }

    public function getTonoEstadoAttribute(): string
    {
        return match ($this->estado) {
            'publicado' => 'exito',
            'programado' => 'info',
            default => 'neutro',
        };
    }

    public function getUrlImagenAttribute(): ?string
    {
        return $this->imagen ? Storage::disk('public')->url($this->imagen) : null;
    }

    public function getResumenAttribute(): string
    {
        $texto = $this->extracto ?: strip_tags((string) $this->contenido);

        return Str::limit(trim(preg_replace('/\s+/', ' ', $texto)), 90);
    }

    public static function generarSlug(string $titulo, ?int $ignorar = null): string
    {
        $base = Str::slug($titulo) ?: 'articulo';
        $slug = $base;
        $sufijo = 2;

        while (static::where('slug', $slug)->when($ignorar, fn ($q) => $q->where('id', '!=', $ignorar))->exists()) {
            $slug = $base . '-' . $sufijo++;
        }

        return $slug;
    }
}
