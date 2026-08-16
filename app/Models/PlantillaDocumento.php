<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlantillaDocumento extends Model
{
    protected $table = 'plantillas_documento';

    protected $fillable = [
        'nombre',
        'contenido',
    ];
}
