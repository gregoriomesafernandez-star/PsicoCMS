<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cita extends Model
{
    public const ESTADOS = [
        'pendiente' => 'Pendiente',
        'confirmada' => 'Confirmada',
        'completada' => 'Completada',
        'cancelada' => 'Cancelada',
        'no_asistio' => 'No asistio',
    ];

    public const MODALIDADES = [
        'online' => 'Online',
        'presencial' => 'Presencial',
    ];

    protected $table = 'citas';

    protected $fillable = [
        'paciente_id',
        'modalidad',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'duracion',
        'estado',
        'motivo',
        'notas',
        'origen',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function scopeActivas(Builder $query): Builder
    {
        return $query->whereNotIn('estado', ['cancelada']);
    }

    public function scopeDelDia(Builder $query, string $fecha): Builder
    {
        return $query->whereDate('fecha', $fecha);
    }

    public function getHoraCortaAttribute(): string
    {
        return substr($this->hora_inicio, 0, 5);
    }

    public function getHoraFinCortaAttribute(): string
    {
        return substr($this->hora_fin, 0, 5);
    }

    public function getTonoEstadoAttribute(): string
    {
        return match ($this->estado) {
            'confirmada' => 'exito',
            'completada' => 'info',
            'cancelada' => 'error',
            'no_asistio' => 'atencion',
            default => 'neutro',
        };
    }

    public function getEstadoLegibleAttribute(): string
    {
        return self::ESTADOS[$this->estado] ?? $this->estado;
    }

    public function getModalidadLegibleAttribute(): string
    {
        return self::MODALIDADES[$this->modalidad] ?? $this->modalidad;
    }

    public function esPasada(): bool
    {
        return $this->fecha->toDateString() < now()->toDateString();
    }
}
