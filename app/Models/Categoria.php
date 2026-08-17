<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Categoria extends Model
{
    protected $table = 'categorias';

    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'orden',
    ];

    public function articulos(): HasMany
    {
        return $this->hasMany(Articulo::class);
    }

    public static function generarSlug(string $nombre, ?int $ignorar = null): string
    {
        $base = Str::slug($nombre) ?: 'categoria';
        $slug = $base;
        $sufijo = 2;

        while (static::where('slug', $slug)->when($ignorar, fn ($q) => $q->where('id', '!=', $ignorar))->exists()) {
            $slug = $base . '-' . $sufijo++;
        }

        return $slug;
    }

    public static function paraSelector(): array
    {
        return static::orderBy('orden')->orderBy('nombre')->pluck('nombre', 'id')->all();
    }
}
