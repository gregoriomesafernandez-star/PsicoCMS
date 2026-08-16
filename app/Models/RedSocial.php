<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RedSocial extends Model
{
    protected $table = 'redes_sociales';

    protected $fillable = [
        'red',
        'url',
        'activa',
        'orden',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];
}
