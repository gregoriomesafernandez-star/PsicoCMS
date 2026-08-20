<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Paciente extends Model
{
    use SoftDeletes;

    public const ESTADOS = [
        'activo' => 'Activo',
        'pausado' => 'Pausado',
        'alta' => 'Alta',
    ];

    public const GENEROS = [
        'mujer' => 'Mujer',
        'hombre' => 'Hombre',
        'otro' => 'Otro',
        'sin_indicar' => 'Prefiero no indicarlo',
    ];

    public const ORIGENES = [
        'web' => 'Reserva web',
        'manual' => 'Alta manual',
    ];

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

    public function historias(): HasMany
    {
        return $this->hasMany(Historia::class);
    }

    public static function paraSelector(): array
    {
        return static::orderBy('nombre')
            ->orderBy('apellidos')
            ->get()
            ->mapWithKeys(fn (self $paciente) => [
                $paciente->id => $paciente->nombre_completo . ' - ' . $paciente->telefono,
            ])
            ->all();
    }

    public function scopeBuscar(Builder $query, ?string $texto): Builder
    {
        if (blank($texto)) {
            return $query;
        }

        $digitos = static::pareceTelefono($texto) ? static::normalizarTelefono($texto) : '';

        return $query->where(function (Builder $sub) use ($texto, $digitos) {
            $sub->where('nombre', 'like', "%{$texto}%")
                ->orWhere('apellidos', 'like', "%{$texto}%")
                ->orWhere('email', 'like', "%{$texto}%")
                ->orWhere('dni', 'like', "%{$texto}%");

            if ($digitos !== '') {
                $sub->orWhere('telefono', 'like', "%{$digitos}%");
            }
        });
    }

    /**
     * Solo se busca por telefono cuando lo escrito **es** un telefono: digitos
     * con la puntuacion habitual y al menos tres cifras.
     *
     * Sin esta comprobacion, buscar "primer articulo2" dejaba los digitos en
     * "2" y sacaba a todos los pacientes con un 2 en su numero.
     */
    public static function pareceTelefono(string $texto): bool
    {
        $texto = trim($texto);

        return $texto !== ''
            && preg_match('/^[0-9\s+()\-\.]+$/', $texto) === 1
            && strlen(static::normalizarTelefono($texto)) >= 3;
    }

    public function scopeBuscarNombre(Builder $query, ?string $texto): Builder
    {
        if (blank($texto)) {
            return $query;
        }

        foreach (preg_split('/\s+/', trim($texto)) as $palabra) {
            $query->where(function (Builder $sub) use ($palabra) {
                $sub->where('nombre', 'like', "%{$palabra}%")
                    ->orWhere('apellidos', 'like', "%{$palabra}%");
            });
        }

        return $query;
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

    public function getEstadoLegibleAttribute(): string
    {
        return self::ESTADOS[$this->estado] ?? $this->estado;
    }

    public function getTonoEstadoAttribute(): string
    {
        return match ($this->estado) {
            'activo' => 'exito',
            'pausado' => 'atencion',
            'alta' => 'info',
            default => 'neutro',
        };
    }

    public function getOrigenLegibleAttribute(): string
    {
        return self::ORIGENES[$this->origen] ?? $this->origen;
    }

    public function getGeneroLegibleAttribute(): ?string
    {
        return $this->genero ? (self::GENEROS[$this->genero] ?? $this->genero) : null;
    }

    public function getEdadAttribute(): ?int
    {
        return $this->fecha_nacimiento?->age;
    }

    public function getTelefonoLegibleAttribute(): string
    {
        return trim(chunk_split($this->telefono, 3, ' '));
    }

    public function getTelefonoWhatsappAttribute(): string
    {
        return strlen($this->telefono) === 9 ? '34' . $this->telefono : $this->telefono;
    }
}
