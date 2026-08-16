<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Paciente extends Model
{
    use SoftDeletes;

    protected $table = 'pacientes';

    protected $fillable = [
        'telefono',
        'nombre',
        'apellidos',
        'email',
        'fecha_nacimiento',
        'genero',
        'dni',
        'direccion',
        'motivo_consulta',
        'notas',
        'estado',
        'origen',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    protected function telefono(): Attribute
    {
        return Attribute::set(fn (?string $valor) => static::normalizarTelefono($valor));
    }

    public static function normalizarTelefono(?string $valor): string
    {
        return preg_replace('/\D/', '', (string) $valor);
    }

    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class);
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim($this->nombre . ' ' . $this->apellidos);
    }

    public function getInicialesAttribute(): string
    {
        $inicial = mb_substr($this->nombre, 0, 1) . mb_substr($this->apellidos ?? '', 0, 1);

        return mb_strtoupper($inicial ?: 'P');
    }
}
