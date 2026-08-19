<?php

namespace App\Models;

use App\Services\SanitizadorHtml;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Historia extends Model
{
    use SoftDeletes;

    public const LIMITE_RESUMEN = 260;

    protected $table = 'historias';

    protected $fillable = [
        'paciente_id',
        'cita_id',
        'fecha_sesion',
        'titulo',
        'contenido',
    ];

    protected $casts = [
        'fecha_sesion' => 'date',
        'contenido' => 'encrypted',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class);
    }

    public function adjuntos(): HasMany
    {
        return $this->hasMany(HistoriaAdjunto::class);
    }

    public function scopeDelPaciente(Builder $query, ?int $pacienteId): Builder
    {
        return $pacienteId ? $query->where('paciente_id', $pacienteId) : $query;
    }

    public function scopeBuscar(Builder $query, ?string $texto): Builder
    {
        if (blank($texto)) {
            return $query;
        }

        return $query->where('titulo', 'like', "%{$texto}%");
    }

    public function scopeOrdenadas(Builder $query): Builder
    {
        return $query->orderByDesc('fecha_sesion')->orderByDesc('id');
    }

    public function getTituloLegibleAttribute(): string
    {
        return $this->titulo ?: 'Sesion del ' . $this->fecha_sesion->format('d/m/Y');
    }

    public function getResumenAttribute(): string
    {
        $texto = app(SanitizadorHtml::class)->aTexto($this->contenido);

        return mb_strlen($texto) > self::LIMITE_RESUMEN
            ? mb_substr($texto, 0, self::LIMITE_RESUMEN) . '...'
            : $texto;
    }

    public function getEstaResumidaAttribute(): bool
    {
        return mb_strlen(app(SanitizadorHtml::class)->aTexto($this->contenido)) > self::LIMITE_RESUMEN;
    }
}
