<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
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
    ];
}
